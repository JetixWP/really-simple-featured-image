[![License](https://img.shields.io/badge/license-GPL--2.0%2B-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

# Really Simple Featured Image

Automatically sets missing featured images from the first image or a YouTube, Vimeo or Dailymotion video in your content, for posts, pages and custom post types.

- WordPress.org: https://wordpress.org/plugins/really-simple-featured-image/
- Plugin page: https://jetixwp.com/plugins/really-simple-featured-image/
- User facing readme and changelog: [readme.txt](readme.txt)

## Requirements

- WordPress 6.3 or newer
- PHP 8.0 or newer

## Development

```bash
composer install
npm install
npm run translate   # regenerate languages/really-simple-featured-image.pot
npm run package     # build the plugin zip
```

`develop` is the main branch. Pushing to `develop` updates the readme and assets on WordPress.org; pushing a tag deploys that version.

## Hooks

- `rs_featured_image_should_process_post` (filter): decide if a post gets an automatic featured image.
- `rs_featured_image_default_enabled_post_types` (filter): post types enabled before settings are saved.
- `rs_featured_image_read_content_length_limit` (filter): characters of content to scan.
- `rs_featured_image_max_download_size` (filter): largest remote image to download, in bytes.
- `rs_featured_image_{before_,after_,}setting_featured_image_from_content` and `..._from_content_video` (actions).

## License

GPLv2 or later.
