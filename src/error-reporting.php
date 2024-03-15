<?php
/**
 * Plugin Name:     Development Error Reporting
 * Plugin URI:      https://adeptdigital.com.au/wordpress/plugins/local-dev-login/
 * Description:     Allow login with a default username and password when developing locally.
 * Version:         1.0.0
 * Author:          Adept Digital
 * Author URI:      https://adeptdigital.com.au/
 * License:         GPL v2 or later
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * The following constant is required to be set in `wp-config.php`:
 * ```php
 * define( 'WP_ENVIRONMENT_TYPE', 'local' );
 * ```
 *
 * By default, this plugin will disable error reporting for `E_DEPRECATED` and
 * `E_USER_DEPRECATED`.
 *
 * The error reporting value can be customized by adding the following constant
 * to `wp-config.php`:
 * ```php
 * define( 'DEV_ERROR_REPORTING', E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE );
 * ```
 */

namespace AdeptDigital\ErrorReporting;

/**
 * Get the custom error reporting value.
 *
 * @return int
 */
function get_error_reporting(): int
{
    $error_reporting = \E_ALL & ~\E_DEPRECATED & ~\E_USER_DEPRECATED;
    if (\defined('\DEV_ERROR_REPORTING') && \is_int(\DEV_ERROR_REPORTING)) {
        $error_reporting = \DEV_ERROR_REPORTING;
    }
    return $error_reporting;
}

/**
 * Check environment and initialize the plugin.
 */
if (\wp_get_environment_type() !== 'local') {
    return;
}

/**
 * Set the customized error reporting value.
 */
\error_reporting(get_error_reporting());