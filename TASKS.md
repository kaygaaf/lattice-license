# lattice-license — TASKS

## NEW OPPORTUNITIES

### P2-1: Automatic license key delivery via WooCommerce (NEW)
**Labels:** Growth, Automation
**Impact:** High
**Problem:** Lattice License Manager creates and manages license keys but has no automatic delivery mechanism. After a customer purchases, an admin must manually generate and send the key. For digital product stores, this is a critical gap.
**Solution:** Add a WooCommerce integration: when an order with a "License Key" product is completed, automatically generate a license key, assign it to the customer, and either: (1) email it via Lattice Mail, (2) add it to the order confirmation page, or (3) create a "Downloads > License Keys" page in My Account. Configurable per product.
**Files:** `includes/class-woocommerce-integration.php`, WooCommerce order hooks
**Why it matters:** Without auto-delivery, the plugin requires manual work for every sale. Auto-delivery turns it into a true passive income tool — great for selling WordPress plugins, SaaS seats, or digital goods.

### P2-2: Usage-based license metering (API calls / seat counting) (NEW)
**Labels:** Growth, SaaS
**Impact:** High
**Problem:** Many license key sales are for SaaS products that track usage (API calls, seat logins, data processed). Current license management only validates "valid/invalid" — no usage tracking.
**Solution:** Add a license key validation endpoint (`/wp-json/lattice-license/v1/validate`) that accepts: license key, site URL, and optional usage metrics. Return: valid/invalid, expiry date, plan tier. Store usage per license in a new `lattice_license_usage` table. Admin dashboard: usage chart per key, over-limit alerts, top users.
**Files:** `includes/class-api.php`, `includes/class-usage-tracker.php`, new database table
**Why it matters:** Opens Lattice License Manager to SaaS businesses, not just one-time digital product sales. Enables usage-based pricing models. High revenue potential.

### P2-3: Gumroad + ThriveCart webhook integration (NEW)
**Labels:** Growth, External Integrations
**Impact:** Medium
**Problem:** License key sales often happen outside WooCommerce — via Gumroad, ThriveCart, or LemonSqueezy for one-click checkout. These platforms need webhook integration to trigger license creation.
**Solution:** Add a Webhooks section in Lattice License settings. Support incoming webhooks from: Gumroad, ThriveCart, LemonSqueezy. Configure a webhook secret for validation. On webhook: create license key, send email to buyer, log sale. Add a "Test Webhook" button to verify the integration.
**Files:** `includes/class-webhooks.php`, admin settings tab
**Why it matters:** Expands the plugin's addressable market beyond WooCommerce stores. Gumroad/ThriveCart are popular for digital products and courses. Removes a major integration barrier.
