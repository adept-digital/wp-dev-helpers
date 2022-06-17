<?php
/**
 * Plugin Name:     Rewrite Media URLs
 * Plugin URI:      https://adeptdigital.com.au/wordpress/plugins/rewrite-media/
 * Description:     Rewrite URLs for media to a remote server for local development.
 * Version:         1.0.1
 * Author:          Adept Digital
 * Author URI:      https://adeptdigital.com.au/
 * License:         GPL v2 or later
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * Rewrite URLs for media to a remote server for local development.
 *
 * URLs are only rewritten when ALL the following conditions are met:
 *   - URL points to the uploads path (usually `wp-content/uploads`).
 *   - The file does not exist locally.
 *   - Required constants are set in `wp-config.php`.
 *
 * The following constants are required to be set in `wp-config.php`:
 * ```php
 * define( 'WP_ENVIRONMENT_TYPE', 'local' );
 * define( 'REWRITE_MEDIA_REMOTE', 'https://.../wp-content/uploads' );
 * ```
 */

namespace AdeptDigital\RewriteMedia;

/**
 * Get the remote base URL.
 *
 * @return string
 */
function get_remote_url(): string
{
    if (!\defined('REWRITE_MEDIA_REMOTE')) {
        return '';
    }
    return REWRITE_MEDIA_REMOTE;
}

/**
 * Rewrite a single URL.
 *
 * @param string $url
 * @return string
 */
function rewrite_url(string $url): string
{
    static $baseUrl, $baseDir;
    if (!isset($baseUrl) || !isset($baseDir)) {
        $base = \wp_get_upload_dir();
        $baseUrl = \set_url_scheme($base['baseurl']);
        $baseDir = $base['basedir'];
    }

    if (!$url) {
        return $url;
    }

    $compareUrl = \set_url_scheme($url);
    if (!\str_starts_with($compareUrl, $baseUrl)) {
        return $url;
    }

    $relative = \substr($compareUrl, \strlen($baseUrl));
    if (\file_exists($baseDir . $relative)) {
        return $url;
    }
    return get_remote_url() . $relative;
}

/**
 * Rewrite for `wp_get_attachment_image_src` filter.
 *
 * @param array{string, int, int, bool}|false $image
 * @return array|false
 */
function filter_wp_get_attachment_image_src($image)
{
    if (!isset($image[0])) {
        return $image;
    }

    $image[0] = rewrite_url($image[0]);
    return $image;
}

/**
 * Rewrite for `wp_get_attachment_image_attributes` filter.
 *
 * @param array{src: string} $attributes
 * @return array
 */
function filter_wp_get_attachment_image_attributes(array $attributes): array
{
    if (!isset($attributes['src'])) {
        return $attributes;
    }

    $attributes['src'] = rewrite_url($attributes['src']);
    return $attributes;
}

/**
 * Rewrite for `wp_calculate_image_srcset` filter.
 *
 * @param array<int, array{url: string, descriptor: string, value: int}> $sources
 * @return array
 */
function filter_wp_calculate_image_srcset(array $sources): array
{
    foreach ($sources as &$source) {
        $source['url'] = rewrite_url($source['url']);
    }
    return $sources;
}

function filter_the_content(string $content): string
{
    return \preg_replace_callback('#[\'"](https?://[^\'"]+)[\'"]#', fn($match) => rewrite_url($match[1]), $content);
}

/**
 * Check environment and initialize the plugin.
 */
if (\wp_get_environment_type() !== 'local' || !get_remote_url()) {
    return;
}

\add_filter('wp_get_attachment_url', __NAMESPACE__ . '\rewrite_url', 0, 1);
\add_filter('wp_get_attachment_image_src', __NAMESPACE__ . '\filter_wp_get_attachment_image_src', 0, 1);
\add_filter('wp_get_attachment_image_attributes', __NAMESPACE__ . '\filter_wp_get_attachment_image_attributes', 0, 1);
\add_filter('wp_calculate_image_srcset', __NAMESPACE__ . '\filter_wp_calculate_image_srcset', 0, 1);
\add_filter('the_content', __NAMESPACE__ . '\filter_the_content', 0, 1);