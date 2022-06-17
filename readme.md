# Helper Plugins for WordPress Development

A collection of plugins useful for WordPress development. These plugins are for development purposes only and are not
intended for use on a production site.

Plugins will not function without setting `WP_ENVIRONMENT_TYPE` to `local` in the `wp-config.php` file:

```php
define( 'WP_ENVIRONMENT_TYPE', 'local' );
```

Each plugin is stand alone; intended for use as must-use plugins, uploaded to `wp-content/mu-plugins`.

## Plugins

###  Local Development Login [local-dev-login.php](src/local-dev-login.php)

Allow login with a default username and password when developing locally.

Login with `admin` and `password` to login as the first admin user, or other users can login their username and
`password`.

### Rewrite Media URLs [rewrite-media.php](src/rewrite-media.php)

Rewrite URLs for media to a remote server. This is useful when developing for a website with a large media library which
you would prefer not to store locally.

Requires setting `REWRITE_MEDIA_REMOTE` in the `wp-config.php` file:

```php
define( 'REWRITE_MEDIA_REMOTE', 'https://.../wp-content/uploads' );
```

## Development and Testing

These plugins are developed using [Lando](https://docs.lando.dev/). The included `.lando.yml` will create a development
environment with some data for testing.

Run the following commands to get started:

```shell
lando start
lando setup
```

## License

Copyright Adept Digital, David Gallagher and contributors. Released for use under the terms of the
[GPL v2](https://opensource.org/licenses/GPL-2.0) or later licences.