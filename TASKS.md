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

---

## Done ✅
- **P1-1: Core License Manager** (2026-04-10) — Full WordPress plugin with license key validation, API secret management, admin settings page, admin notices, weekly cron validation, AJAX validation endpoint, activation/deactivation hooks, and license status display card.
