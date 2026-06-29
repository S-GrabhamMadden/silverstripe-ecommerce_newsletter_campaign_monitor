# Upgrade Guide: Moving to Silverstripe 6

This document outlines the essential changes required to upgrade this module to be compatible with Silverstripe 6.

## 🚨 CRITICAL REVIEW REQUIRED / RISKY

**The `sunnysideup/campaignmonitor` dependency has not been updated and was removed from the main composer requirements. There is currently no compatible stable release for this new version. Your project will not work without a compatible version of this module or a replacement.**

**You must manually find a compatible version or an alternative solution for the Campaign Monitor integration.**

---

## ⚠️ BREAKING CHANGES

### Build and Configuration

*   **Database Administration:** The deprecated `SilverStripe\ORM\DatabaseAdmin` has been replaced with `SilverStripe\Dev\DbBuild` in the YAML configuration. You must update any custom scripts or configurations referencing the old class.

### Dependencies

*   **Silverstripe CMS:** The core framework requirement has been updated to `silverstripe/recipe-cms: ^6.0`.
*   **Ecommerce Module:** The dependency on `sunnysideup/ecommerce` has been upgraded to `^33.0`.
