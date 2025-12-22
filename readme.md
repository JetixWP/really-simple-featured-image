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

Automatically assigns missing featured images from content images or video thumbnails for posts, pages and CPTs.

== Description ==

Really Simple Featured Image keeps your posts and pages visually consistent by filling in missing featured images automatically. When you update or publish posts, pages, and CPTs, the plugin inspects the editor content and assigns the first available image or streaming video thumbnail as the featured image - no extra clicks required.

= Key Features =
* Automatically sets the featured image when one is not provided.
* Detects inline images from blocks, classic editor markup, builders, and srcset/background sources.
* Fetches thumbnails from YouTube, Vimeo, and Dailymotion embeds when you prefer video covers.
* Lets you enable or disable automation per post type that supports featured images.
* Respects existing featured images and runs quietly in the background.

= How It Works =
1. Choose the default source (images in content or video thumbnails) from JetixWP -> Auto Featured Image.
2. Select which post types should receive automatic featured images.
3. Save or update a post - if it has no featured image, the plugin will attach the first match it finds.

= Requirements =
* WordPress 6.0 or newer.
* PHP 8.0 or newer.

== Installation ==

1. Upload the plugin files to the /wp-content/plugins/really-simple-featured-image directory or install it via Plugins -> Add New.
2. Activate Really Simple Featured Image through the Plugins screen.
3. Navigate to JetixWP -> Auto Featured Image to pick your default source and supported post types.
4. Save a post without a featured image to see the automation in action.

== Frequently Asked Questions ==

= Where can I get help? =
You can get help by sending us an email at support@jetixwp.com.

= Which post types are supported? =
Any post type that has thumbnail support can use automatic featured images. Enable or disable individual post types on the settings screen.

= Will the plugin overwrite an existing featured image? =
No. Really Simple Featured Image only assigns a featured image when the post does not already have one.

= Can I switch between images and video thumbnails? =
Yes. Set the default source to either "Image in Post Content" or "Video in Post Content" in the settings. Video thumbnails currently support YouTube, Vimeo, and Dailymotion.

= What happens with remote images? =
If the image does not already exist in your media library, the plugin downloads it and creates a local attachment before assigning it as the featured image.


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