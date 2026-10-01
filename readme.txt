=== Really Simple Featured Image: Automatic Featured Images ===
Contributors: jetixwp, lushkant
Requires at least: 6.3
Requires PHP: 8.0
Tested up to: 7.1
Stable tag: 1.1.0
Tags: featured image, auto featured image, featured image from video, video thumbnail, post thumbnails
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically set missing featured images from the first image or a YouTube, Vimeo or Dailymotion video in your content.

== Description ==

Really Simple Featured Image keeps your posts and pages visually consistent by filling in missing featured images automatically. When you publish or update a post, page or custom post type, the plugin reads the content and sets an image or video thumbnail from it as the featured image. No extra clicks.

= Key Features =
* Sets the featured image when a post does not have one.
* Finds images in blocks, classic editor markup, lazy-loaded images, srcset and inline background images.
* Uses the thumbnail of a YouTube (including Shorts and live), Vimeo or Dailymotion video in your content.
* Choose which image or video to use: first, second, second last or last.
* Turn it on for the post types you want, including custom post types and WooCommerce products.
* Reuses images already in your Media Library and never downloads the same image twice.
* Never replaces a featured image you set, and if you remove one it stays removed.
* Works for posts saved in the editor, through the REST API or by importers.

= How It Works =
1. Go to JetixWP -> Featured Image and choose the source: images in content or video thumbnails.
2. Select which post types should get automatic featured images.
3. Publish or update a post. If it has no featured image, the plugin sets the first match it finds.

= Requirements =
* WordPress 6.3 or newer.
* PHP 8.0 or newer.

== Installation ==

1. Upload the plugin files to the /wp-content/plugins/really-simple-featured-image directory or install it via Plugins -> Add New.
2. Activate Really Simple Featured Image through the Plugins screen.
3. Go to JetixWP -> Featured Image to pick your source and post types.
4. Save a post without a featured image to see it work.

== Frequently Asked Questions ==

= Where can I get help? =
You can get help by sending us an email at support@jetixwp.com.

= Which post types are supported? =
Any post type that supports featured images. Turn individual post types on or off on the settings screen. Posts and pages are on by default.

= Will the plugin overwrite an existing featured image? =
No. Really Simple Featured Image only sets a featured image when the post does not already have one.

= I removed a featured image. Will it come back? =
No. When you remove a featured image from a post, the plugin remembers it and does not set one automatically for that post again.

= Can I switch between images and video thumbnails? =
Yes. Set the source to either "Image in Post Content" or "Video in Post Content" in the settings. Video thumbnails support YouTube, Vimeo and Dailymotion.

= What happens with remote images? =
If the image is not already in your Media Library, the plugin downloads it and adds it to the library before setting it as the featured image. Only real image files up to 15 MB and 50 megapixels are accepted, addresses on your local network are refused, and images are only downloaded when the person saving the post can upload files.

= Does it work with page builders? =
It reads the post content. Builders that save their markup in the post content work; builders that keep content elsewhere (for example Elementor) are not read yet.

== Screenshots ==

1. Featured Image from Video of Youtube, Vimeo and Dailymotion inside content.
2. Featured Image from Post content images.
3. Settings page view.

== External Services ==

This plugin connects to third-party video platform APIs to get video thumbnails when you choose "Video in Post Content" as your source. These connections only happen when a post without a featured image is saved and contains a video from one of the supported platforms. Results are cached for up to 12 hours.

= YouTube =
When a YouTube video is found in your post content, the plugin requests its thumbnail from YouTube's image server and its title from YouTube's oEmbed API.

* Data sent: YouTube video ID.
* When: On post save/update if the post has no featured image and contains a YouTube video.
* Service provider: Google LLC.
* [Terms of Service](https://www.youtube.com/t/terms) & [Privacy Policy](https://policies.google.com/privacy)

= Vimeo =
When a Vimeo video is found in your post content, the plugin sends the video URL to Vimeo's oEmbed API to get the video title and thumbnail URL.

* Data sent: Vimeo video URL (contains the video ID).
* When: On post save/update if the post has no featured image and contains a Vimeo video.
* Service provider: Vimeo, Inc.
* [Terms of Service](https://vimeo.com/terms) & [Privacy Policy](https://vimeo.com/privacy)

= Dailymotion =
When a Dailymotion video is found in your post content, the plugin sends the video ID to Dailymotion's API to get the video title and thumbnail URL.

* Data sent: Dailymotion video ID.
* When: On post save/update if the post has no featured image and contains a Dailymotion video.
* Service provider: Dailymotion SA.
* [Terms of Service](https://www.dailymotion.com/legal) & [Privacy Policy](https://www.dailymotion.com/legal/privacy)

== Upgrade Notice ==

= 1.1.0 =
The Post types setting is now respected: only checked post types get automatic featured images (posts and pages by default). Check the setting if you relied on other post types. Remote images are only downloaded for people who can upload files. Deleting the plugin now removes its settings.

== Changelog ==

= 1.1.0 =
* Fix: The Post types setting is now respected. Before, every post type was scanned, including attachments, menus and templates.
* Fix: Removing a featured image no longer brings it back right away, and it stays removed on later saves.
* Fix: Dailymotion thumbnails work again.
* Fix: Vimeo thumbnails now use Vimeo's oEmbed API.
* Fix: Second, second last and last positions no longer download duplicate copies of an image.
* Fix: Scan Content Length settings now work without PRO, and an empty value no longer causes an error.
* Fix: Default Source showed a value that was not one of the choices.
* Improvement: YouTube live, Shorts and youtube-nocookie links are detected.
* Improvement: Works for posts created through the REST API and importers, and sees the featured image picked in the block editor before looking for one.
* Security: Remote images are downloaded safely: real images only, up to 15 MB and 50 megapixels, no local network addresses, and only absolute image URLs. The same remote image is never downloaded twice.
* Security: Remote images are only downloaded when the person saving the post (or its author, for imports) can upload files. Images already in the Media Library are still used for everyone.
* Security: At most 3 found images or videos are tried per save, so a post full of broken links cannot slow the site down.
* Security: Settings are checked on the server (allowed post types only, scan length 100 to 100000), and video titles are stored as plain text.
* Improvement: Images downloaded from a video use the video title as title and alt text.
* Improvement: Video lookups are cached.
* Improvement: Settings no longer add extra options to the database on every admin page load.
* Improvement: Plugin data is removed when the plugin is deleted, on every site of a network (featured images are kept).
* Improvement: License is now GPLv2 or later.
* Requires WordPress 6.3 or newer. Tested up to 7.1.

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