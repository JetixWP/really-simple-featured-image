=== Really Simple Featured Image: Automatic Featured Image from Content (Video & Image) ===
Contributors: jetixwp, lushkant
Requires at least: 6.0
Requires PHP: 8.0
Tested up to: 6.9
Stable tag: 1.0.4
Tags: featured image, automatic featured image, featured image from video, featured image from video thumbnail
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically generate missing featured images from video or image inside content for Posts, Pages and CPTs.

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
3. Navigate to JetixWP -> Featured Image to pick your default source and supported post types.
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

== Screenshots ==

1. Featured Image from Video of Youtube, Vimeo and Dailymotion inside content.
2. Featured Image from Post content images.
3. Settings page view.

== External Services ==

This plugin connects to third-party video platform APIs to retrieve video thumbnails when you choose "Video in Post Content" as your default source. These connections only occur when a post is saved or updated and contains an embedded video from one of the supported platforms.

= YouTube =
When a YouTube video is detected in your post content, the plugin sends the video ID to YouTube's oEmbed API to retrieve the video title and thumbnail URL.

* Data sent: YouTube video ID.
* When: On post save/update if the post has no featured image and contains a YouTube embed.
* Service provider: Google LLC.
* [Terms of Service](https://www.youtube.com/t/terms) & [Privacy Policy](https://policies.google.com/privacy)

= Vimeo =
When a Vimeo video is detected in your post content, the plugin sends the video ID to Vimeo's API to retrieve the video title and thumbnail URL.

* Data sent: Vimeo video ID.
* When: On post save/update if the post has no featured image and contains a Vimeo embed.
* Service provider: Vimeo, Inc.
* [Terms of Service](https://vimeo.com/terms) & [Privacy Policy](https://vimeo.com/privacy)

= Dailymotion =
When a Dailymotion video is detected in your post content, the plugin sends the video ID to Dailymotion's API to retrieve the video title and thumbnail URL.

* Data sent: Dailymotion video ID.
* When: On post save/update if the post has no featured image and contains a Dailymotion embed.
* Service provider: Dailymotion SA.
* [Terms of Service](https://www.dailymotion.com/legal) & [Privacy Policy](https://www.dailymotion.com/legal/privacy)

== Changelog ==

= 1.0.4 =
* Release for wp.org
* Other minor changes

= 1.0.3 =
* Fix wp.org reported issues

= 1.0.2 =
* Added Image and Video options
* PCP improvements
* Other minor changes

= 1.0.1 =
* Add PROMO settings
* Add base rollback feature
* Add languages .pot file
* Update settings view
* Other minor changes

= 1.0.0 =
* Initial release