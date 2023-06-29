<?php
/**
 * Plugin Name:     Local Development Login
 * Plugin URI:      https://adeptdigital.com.au/wordpress/plugins/local-dev-login/
 * Description:     Allow login with a default username and password when developing locally.
 * Version:         1.0.1
 * Author:          Adept Digital
 * Author URI:      https://adeptdigital.com.au/
 * License:         GPL v2 or later
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * The following constant is required to be set in `wp-config.php`:
 * ```php
 * define( 'WP_ENVIRONMENT_TYPE', 'local' );
 * ```
 */

namespace AdeptDigital\LocalDevLogin;

/**
 * The default username which will map to the first admin user if not found.
 */
const USERNAME = 'admin';

/**
 * The password which can be used to authenticate any user.
 */
const PASSWORD = '';

/**
 * Authenticate the user with the password specified by `PASSWORD`.
 *
 * @param \WP_User|\WP_Error|null $user
 * @param string $username
 * @param string $password
 * @return \WP_User|null
 */
function authenticate($user, string $username, string $password): ?\WP_User
{
    if ($user !== null || $password !== PASSWORD) {
        return $user;
    }

    $user = \get_user_by('login', $username) ?: null;
    if ($user === null && $username === USERNAME) {
        $users = \get_users([
            'role' => 'administrator',
            'orderby' => 'ID',
            'number' => 1,
        ]);
        $user = $users[0] ?? null;
    }
    return $user;
}

/**
 * Check environment and initialize the plugin.
 */
if (\wp_get_environment_type() !== 'local') {
    return;
}

\add_filter('authenticate', __NAMESPACE__ . '\authenticate', 10, 3);