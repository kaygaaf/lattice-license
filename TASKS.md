# Lattice License Manager — TASKS.md

**Product:** WordPress plugin for license key management
**Repo:** kaygaaf/lattice-license
**Test Site:** https://wordpress-test.kayorama.nl

## Priority: P0 (Critical — fix immediately)
*None for now.*

## Priority: P1 (Important — this week)
*None for now.*

## Priority: P2 (Nice to have)
*None for now.*

## ⚠️ Critical Rule: No Local Files
Everything must be committed to git. If you create a new file, immediately `git add` and commit it.

## GitHub
- Issues: https://github.com/kaygaaf/lattice-license/issues

## Development
- WordPress/WooCommerce PHP plugin
- Test on: wordpress-test.kayorama.nl

## PROPOSED FEATURES

### Lattice License: License Analytics Dashboard + MRR Tracking
**Plugin:** Lattice License Manager
**Problem:** Plugin issues and validates license keys but has no analytics showing license usage patterns, revenue from licenses, or customer health. Store owner can't see: which license tiers are selling, geographic distribution of licensees, churn (expired vs active), or MRR from license subscriptions.
**Solution:** Add a "License Analytics" tab to the admin dashboard. Show: (1) Active vs Expired vs Revoked license count with trend indicators, (2) MRR calculation based on license tier prices × active licenses, (3) Licenses by tier (Basic/Pro/Enterprise) as a donut chart, (4) New licenses this month vs last month, (5) Geographic distribution of licensees (from IP or manual country field), (6) License upgrade rate (% of Basic users who upgraded to Pro). Export revenue data to CSV for financial reconciliation.
**Impact:** Revenue visibility for license-based businesses. MRR tracking is essential SaaS/license business metric. Helps identify churn risk (large number of expiring licenses approaching).
**Effort:** Low–Medium

### Lattice License: Usage-Based Licensing & Metering
**Plugin:** Lattice License Manager
**Problem:** SaaS products charging per-usage need meter billing. Current plugin only handles flat license keys, not metered consumption (API calls, users, storage).
**Solution:** Add "metered license" type. Licensee reports usage via API call (POST /licenses/{key}/meter?value=100). Dashboard shows usage over time. Configurable billing cycle (monthly). Usage beyond tier triggers upgrade prompt.
**Impact:** Enables SaaS licensing model — significantly expands addressable market for the plugin. High effort, very high value.
**Effort:** High

### Lattice License: License Upgrade/Downgrade Paths
**Plugin:** Lattice License Manager
**Problem:** Customers on plan A want to upgrade to plan B mid-cycle. No prorated upgrade path.
**Solution:** Add license tiers (Basic/Pro/Enterprise). Each tier has different price. Upgrade triggers prorated charge for remaining period. API: /licenses/{key}/upgrade?to_tier=pro. Downgrade takes effect at next billing cycle.
**Impact:** Revenue uplift from existing customers. Standard SaaS feature expected by buyers.
**Effort:** Medium

### Lattice License: License Key Gifting / Transfer
**Plugin:** Lattice License Manager
**Problem:** Current license system is tied to a single user account. There's no way for a customer to transfer their license to someone else (gift, sell on secondary market) or for a business to reassign a license to a different employee when someone leaves. License transfers are a common support request in any license-based SaaS.
**Solution:** Add "Transfer License" feature: customer initiates transfer from their portal, enters recipient email address, confirms. Transfer deactivates the key for the original user and generates a new activation URL for the recipient. Transfer history is logged (original owner, recipient, transfer date). Admin can enable/disable transfers, set a transfer cooldown period (e.g., once per 6 months), and charge a transfer fee (optional). Email notifications to both parties on transfer completion.
**Impact:** Reduces support burden from license reassignment requests. Unexpected revenue stream — some businesses will pay for license transfers. Differentiator from competitors that lock licenses permanently to accounts.
**Effort:** Low–Medium

### Lattice License: White-Label Licensing (Reseller Mode)
**Plugin:** Lattice License Manager
**Problem:** Developers who license Lattice License Manager for their own SaaS product want to rebrand it entirely (no "Lattice" branding) for their customers. Current plugin shows "Lattice License Manager" throughout — visible to end customers in license confirmation emails, activation pages, and admin UI labels.
**Solution:** Add a "White-Label Mode" in admin settings: replaces all "Lattice" branding with custom product name, logo upload (for license emails and activation pages), custom support email, custom support URL. Removes "Powered by Lattice" footer from license activation pages. Reseller can save their custom branding as a configuration export. The plugin's internal codebase still references "Lattice" but the customer-facing layer is fully rebranded.
**Impact:** Enables resale of the plugin under a different brand name. Developers building SaaS tools can use Lattice License as the engine while presenting it as their own product. Potential revenue from white-label licensing fees (Pro version).
**Effort:** Low–Medium

---

## Done ✅
- **P1-1: Core License Manager** (2026-04-10) — Full WordPress plugin with license key validation, API secret management, admin settings page, admin notices, weekly cron validation, AJAX validation endpoint, activation/deactivation hooks, and license status display card.
