<?php
/**
 * Plugin Name:     Local Development Login
 * Plugin URI:      https://adeptdigital.com.au/wordpress/plugins/local-dev-login/
 * Description:     Allow login with a default username and password when developing locally.
 * Version:         1.1.0
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
 * Allow an empty password when `PASSWORD` is empty.
 *
 * Required for WordPress 6.3+.
 *
 * @return void
 */
function allow_empty_password(): void
{
    if (PASSWORD !== '') {
        return;
    }

    echo <<<'HTML'
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const password_input = document.querySelector('#user_pass');
            if (!password_input) {
                return;
            }
            password_input.required = false;
        });
        </script>
        HTML;
}

/**
 * Allow selecting from a list of users.
 *
 * @return void
 */
function add_user_list(): void
{
    /** @var array<\WP_User> $users */
    $users = get_users([
        'orderby' => 'ID',
        'order' => 'ASC',
        'count_total' => false,
    ]);

    if (!$users) {
        return;
    }

    \usort($users, function ($user1, $user2) {
        $id1 = $user1->ID ?? 0;
        $id2 = $user2->ID ?? 0;
        $role1 = $user1->roles[0] ?? '';
        $role2 = $user2->roles[0] ?? '';

        if ($role1 === $role2) {
            return $id1 <=> $id2;
        }
        return $role1 <=> $role2;
    });

    $default_user = $users[0]->user_login ?? '';
    ?>
    <datalist id="ad-dev-login-user-list">
        <?php foreach ($users as $user): ?>
            <option value="<?= esc_attr($user->user_login) ?>">
                <?php $roles = \implode(', ', $user->roles) ?>
                <?= esc_html("#{$user->ID} {$user->user_login}: {$roles}") ?>
            </option>
        <?php endforeach; ?>
    </datalist>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const default_user = <?= \json_encode($default_user) ?>;
            const username_input = document.querySelector('#user_login');
            const username_list = document.querySelector('#ad-dev-login-user-list');
            if (!username_input || !username_list) {
                return;
            }

            username_input.value = default_user;
            username_input.setAttribute('list', username_list.getAttribute('id'));
        });
    </script>
    <?php
}

/**
 * Check environment and initialize the plugin.
 */
if (\wp_get_environment_type() !== 'local') {
    return;
}

\add_filter('authenticate', __NAMESPACE__ . '\authenticate', 10, 3);
\add_action('login_form', __NAMESPACE__ . '\allow_empty_password');
\add_action('login_form', __NAMESPACE__ . '\add_user_list');