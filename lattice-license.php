<?php
/**
 * Plugin Name: Lattice License Manager
 * Plugin URI: https://latticeplugins.com/license-manager
 * Description: License validation for Lattice WooCommerce plugins
 * Version: 1.0.0
 * Author: Lattice Plugins
 * Author URI: https://latticeplugins.com
 * Text Domain: lattice-license
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('LATTICE_LICENSE_VERSION', '1.0.0');
define('LATTICE_LICENSE_API_URL', 'https://license.latticeplugins.com/api/v1');
define('LATTICE_LICENSE_PLUGIN_DIR', plugin_dir_path(__FILE__));

class Lattice_License_Manager {

    private $license_key;
    private $site_url;
    private $product;
    private $api_secret;

    public function __construct() {
        $this->license_key = get_option('lattice_license_key', '');
        $this->site_url = get_site_url();
        $this->product = $this->get_product_name();
        $this->api_secret = get_option('lattice_license_api_secret', '');

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_notices', [$this, 'admin_notices']);

        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        // Add AJAX handler for manual validation
        add_action('wp_ajax_lattice_validate_license', [$this, 'ajax_validate_license']);

        // Cron for periodic validation (weekly)
        add_action('lattice_license_check', [$this, 'check_license']);
        if (!wp_next_scheduled('lattice_license_check')) {
            wp_schedule_event(time(), 'weekly', 'lattice_license_check');
        }
    }

    private function get_product_name() {
        // This should be overridden by individual plugins
        return 'lattice-plugin';
    }

    public function activate() {
        if (!empty($this->license_key)) {
            $this->validate_and_activate();
        }
        update_option('lattice_license_activated', '1');
    }

    public function deactivate() {
        // Notify license server of deactivation
        if (!empty($this->license_key) && !empty($this->api_secret)) {
            wp_remote_post(LATTICE_LICENSE_API_URL . '/deactivate', [
                'body' => [
                    'license_key' => $this->license_key,
                    'site_url' => $this->site_url
                ],
                'headers' => [
                    'X-API-Secret' => $this->api_secret
                ],
                'timeout' => 15
            ]);
        }
    }

    public function add_admin_menu() {
        add_options_page(
            __('Lattice License', 'lattice-license'),
            __('Lattice License', 'lattice-license'),
            'manage_options',
            'lattice-license',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('lattice_license', 'lattice_license_key', 'sanitize_text_field');
        register_setting('lattice_license', 'lattice_license_api_secret', 'sanitize_text_field');
    }

    public function render_settings_page() {
        $license_status = $this->get_license_status();
        ?>
        <div class="wrap">
            <h1><?php _e('Lattice License Manager', 'lattice-license'); ?></h1>

            <style>
                .lattice-license-card {
                    background: #fff;
                    border: 1px solid #c3c4c7;
                    border-radius: 8px;
                    padding: 20px;
                    max-width: 600px;
                    margin-top: 20px;
                }
                .lattice-license-card h2 {
                    margin-top: 0;
                    font-size: 1.2em;
                }
                .lattice-license-status {
                    padding: 10px 15px;
                    border-radius: 5px;
                    margin: 15px 0;
                }
                .lattice-license-status.valid {
                    background: #d4edda;
                    border: 1px solid #c3e6cb;
                    color: #155724;
                }
                .lattice-license-status.invalid {
                    background: #f8d7da;
                    border: 1px solid #f5c6cb;
                    color: #721c24;
                }
                .lattice-license-status.warning {
                    background: #fff3cd;
                    border: 1px solid #ffeeba;
                    color: #856404;
                }
                .lattice-license-form {
                    margin-top: 20px;
                }
                .lattice-license-form p {
                    margin-bottom: 15px;
                }
                .lattice-license-form label {
                    display: block;
                    margin-bottom: 5px;
                    font-weight: 600;
                }
                .lattice-license-form input[type="text"] {
                    width: 100%;
                    max-width: 400px;
                    padding: 8px 12px;
                }
                .lattice-license-info {
                    background: #f0f6fc;
                    border: 1px solid #c3c4c7;
                    border-radius: 4px;
                    padding: 10px 15px;
                    margin: 15px 0;
                    font-size: 0.9em;
                }
            </style>

            <div class="lattice-license-card">
                <h2><?php _e('License Status', 'lattice-license'); ?></h2>

                <?php if ($license_status['valid']): ?>
                    <div class="lattice-license-status valid">
                        <strong>✓ License Valid</strong>
                        <p style="margin: 5px 0 0;">
                            <?php echo esc_html($license_status['message']); ?>
                        </p>
                    </div>
                    <div class="lattice-license-info">
                        <strong>Product:</strong> <?php echo esc_html($license_status['product']); ?><br>
                        <strong>Level:</strong> <?php echo esc_html(ucfirst($license_status['level'])); ?><br>
                        <strong>Sites:</strong> <?php echo esc_html($license_status['sites_used']); ?> / <?php echo esc_html($license_status['sites_allowed']); ?> activated
                    </div>
                <?php elseif (empty($this->license_key)): ?>
                    <div class="lattice-license-status warning">
                        <strong>⚠ No License Key</strong>
                        <p style="margin: 5px 0 0;">
                            Enter your license key below to activate this plugin.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="lattice-license-status invalid">
                        <strong>✗ License Invalid</strong>
                        <p style="margin: 5px 0 0;">
                            <?php echo esc_html($license_status['message']); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <div class="lattice-license-form">
                    <form method="post" action="options.php">
                        <?php settings_fields('lattice_license'); ?>

                        <p>
                            <label for="lattice_license_key"><?php _e('License Key', 'lattice-license'); ?></label>
                            <input type="text" 
                                   id="lattice_license_key" 
                                   name="lattice_license_key" 
                                   value="<?php echo esc_attr($this->license_key); ?>" 
                                   class="regular-text"
                                   placeholder="LTXXX-XXXXXXXXXXXXXXXX">
                        </p>

                        <p>
                            <label for="lattice_license_api_secret"><?php _e('API Secret (from purchase email)', 'lattice-license'); ?></label>
                            <input type="text" 
                                   id="lattice_license_api_secret" 
                                   name="lattice_license_api_secret" 
                                   value="<?php echo esc_attr($this->api_secret); ?>" 
                                   class="regular-text"
                                   placeholder="API secret for this license">
                        </p>

                        <?php submit_button(__('Save & Validate', 'lattice-license')); ?>
                    </form>
                </div>

                <div class="lattice-license-info">
                    <strong>Site URL:</strong> <?php echo esc_url($this->site_url); ?><br>
                    <strong>Product:</strong> <?php echo esc_html($this->product); ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function admin_notices() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $screen = get_current_screen();
        if ($screen && $screen->id === 'settings_page_lattice-license') {
            return;
        }

        $status = $this->get_license_status();

        if (!$status['valid'] && !empty($this->license_key)) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>Lattice License:</strong> ' . esc_html($status['message']) . ' ';
            echo '<a href="' . admin_url('options-general.php?page=lattice-license') . '">' . __('Fix now', 'lattice-license') . '</a></p>';
            echo '</div>';
        }
    }

    public function ajax_validate_license() {
        check_ajax_referer('lattice_validate_license');

        $result = $this->validate_and_activate();

        wp_send_json($result);
    }

    public function get_license_status() {
        if (empty($this->license_key)) {
            return [
                'valid' => false,
                'message' => 'No license key entered',
                'message_type' => 'warning'
            ];
        }

        if (empty($this->api_secret)) {
            return [
                'valid' => false,
                'message' => 'API secret not configured',
                'message_type' => 'error'
            ];
        }

        // Check transient cache (12h TTL)
        $cache_key = 'lattice_license_status_' . md5($this->license_key . $this->site_url);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $response = wp_remote_post(LATTICE_LICENSE_API_URL . '/validate', [
            'body' => [
                'license_key' => $this->license_key,
                'site_url' => $this->site_url,
                'product' => $this->product
            ],
            'headers' => [
                'X-API-Secret' => $this->api_secret
            ],
            'timeout' => 20
        ]);

        if (is_wp_error($response)) {
            // If API is unreachable, allow the plugin to work (fail open)
            $result = [
                'valid' => true,
                'message' => 'Unable to reach license server (allowing operation)',
                'message_type' => 'warning'
            ];
            set_transient($cache_key, $result, 1 * HOUR_IN_SECONDS);
            return $result;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!$body || !isset($body['valid'])) {
            $result = [
                'valid' => false,
                'message' => 'Invalid response from license server',
                'message_type' => 'error'
            ];
            set_transient($cache_key, $result, 1 * HOUR_IN_SECONDS);
            return $result;
        }

        if ($body['valid']) {
            $result = [
                'valid' => true,
                'message' => 'Your license is active and valid',
                'message_type' => 'success',
                'level' => $body['license']['level'] ?? '',
                'product' => $body['license']['product'] ?? '',
                'sites_allowed' => $body['license']['sites_allowed'] ?? 0,
                'sites_used' => $body['license']['sites_used'] ?? 0,
                'valid_until' => $body['license']['valid_until'] ?? null
            ];
            set_transient($cache_key, $result, 12 * HOUR_IN_SECONDS);
            return $result;
        } else {
            $result = [
                'valid' => false,
                'message' => $body['error'] ?? 'License validation failed',
                'message_type' => 'error'
            ];
            set_transient($cache_key, $result, 1 * HOUR_IN_SECONDS);
            return $result;
        }
    }

    private function validate_and_activate() {
        if (empty($this->license_key) || empty($this->api_secret)) {
            return ['valid' => false, 'message' => 'License key or API secret missing'];
        }

        // Clear cache so we get a fresh check
        $cache_key = 'lattice_license_status_' . md5($this->license_key . $this->site_url);
        delete_transient($cache_key);

        $response = wp_remote_post(LATTICE_LICENSE_API_URL . '/validate', [
            'body' => [
                'license_key' => $this->license_key,
                'site_url' => $this->site_url,
                'product' => $this->product
            ],
            'headers' => [
                'X-API-Secret' => $this->api_secret
            ],
            'timeout' => 20
        ]);

        if (is_wp_error($response)) {
            return ['valid' => false, 'message' => 'Could not reach license server'];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($body && isset($body['valid']) && $body['valid']) {
            update_option('lattice_license_valid', '1');
            return ['valid' => true, 'message' => 'License activated successfully'];
        }

        update_option('lattice_license_valid', '0');
        return ['valid' => false, 'message' => $body['error'] ?? 'Activation failed'];
    }

    public function check_license() {
        // Clear cache so cron always checks fresh
        $cache_key = 'lattice_license_status_' . md5($this->license_key . $this->site_url);
        delete_transient($cache_key);

        $status = $this->get_license_status();
        update_option('lattice_license_last_check', current_time('mysql'));

        if (!$status['valid']) {
            update_option('lattice_license_valid', '0');
        } else {
            update_option('lattice_license_valid', '1');
        }
    }

    // Static method to protect plugin functionality
    public static function is_license_valid() {
        $instance = new self();
        $status = $instance->get_license_status();
        return $status['valid'];
    }
}

// Initialize
new Lattice_License_Manager();

// Helper function for plugins to check license
function lattice_check_license($product = '') {
    $manager = new Lattice_License_Manager();
    $status = $manager->get_license_status();
    return $status['valid'];
}
