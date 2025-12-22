[![License](https://img.shields.io/badge/license-GPL--2.0%2B-red.svg)](https://github.com/jetixwp/really-simple-featured-image/blob/master/license.txt)

# Really Simple Featured Image - Setup Documentation

## 📋 Table of Contents

1. [Overview](#overview)
2. [Installation](#installation)
3. [Basic Configuration](#basic-configuration)
4. [Advanced Settings](#advanced-settings)
5. [Shortcodes](#shortcodes)
6. [Troubleshooting](#troubleshooting)
7. [Developer Guide](#developer-guide)

---

## Overview


### Key Features


### System Requirements

- WordPress 6.0 or higher
- PHP 8.0 or higher
- WooCommerce 7.0 or higher
- At least 20 MB free disk space

---

## Installation

### Method 1: Via Our Site

1. Download the plugin zip file from [JetixWP.com >> Really Simple Featured Image](https://jetixwp.com/plugins/really-simple-featured-image/#pricing)
2. Navigate to **Plugins > Add New**
3. Upload the `really-simple-featured-image.zip` downloaded zip via the Upload selector and hit Install.
4. Go to **Plugins** in WordPress admin and find "Really Simple Featured Image".
5. Once installed, click **Activate Plugin**
6. You'll see a new menu item: **JetixWP > Auto Featured Image**

### Method 2: Via WordPress Plugin Directory

1. Log in to your WordPress dashboard
2. Navigate to **Plugins > Add New**
3. Search for "Really Simple Featured Image"
4. Click **Install Now** button
5. Once installed, click **Activate Plugin**
6. You'll see a new menu item: **JetixWP > Auto Featured Image**

### Verification

After activation, verify that:
- A new menu item "JetixWP" appears in the WordPress sidebar
- Under "JetixWP", you see "Featured Image" submenu
- You can click "Featured Image" to access the settings page

---

### Debug Mode

Enable debug mode for development and troubleshooting:

```php
define( 'JETIXWP_DEBUG', true );
```

**What Changes in Debug Mode:**
- Asset versioning uses file modification times (no caching)
- Product list cache is bypassed
- Stricter error reporting
- Additional logging information

**When to Use:**
- During development
- When troubleshooting issues
- When making changes to CSS/JS files

---

## Support

- **Documentation:** See this file and the plugin settings pages
- **Issues:** [GitHub Issues](https://github.com/jetixwp/really-simple-featured-image/issues)
- **Email:** support@jetixwp.com

---