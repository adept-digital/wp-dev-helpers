#!/usr/bin/env bash
set -euo pipefail

WP_FALLBACK_THEME='twentytwentytwo'

download_wp() {
  echo 'Downloading WordPress...'
  if [[ -f "${LANDO_WEBROOT}/wp-load.php" ]]; then
    echo 'Skipped. wp-load.php already exists.'
    return
  fi

  wp core download \
    --path="${LANDO_WEBROOT}" \
    --skip-content

  mkdir -p "${LANDO_WEBROOT}/wp-content/uploads"
  mkdir -p "${LANDO_WEBROOT}/wp-content/plugins"
  mkdir -p "${LANDO_WEBROOT}/wp-content/themes"

  echo 'Done.'
}

make_wp_config() {
  echo 'Generating wp-config.php...'
  if [[ -f "${LANDO_WEBROOT}/wp-config.php" ]]; then
    echo 'Skipped. wp-config.php already exists.'
    return
  fi

  wp config create \
    --path="${LANDO_WEBROOT}" \
    --dbname=wordpress \
    --dbuser=wordpress \
    --dbpass=wordpress \
    --dbhost=database

  set_config 'WP_DEBUG'                       'true'
  set_config 'WP_DEBUG_DISPLAY'               'false'
  set_config 'WP_DEBUG_LOG'                   'true'
  set_config 'WP_DISABLE_FATAL_ERROR_HANDLER' 'true'
  set_config 'WP_ENVIRONMENT_TYPE'            "'local'"
  set_config 'DISABLE_WP_CRON'                'true'

  echo 'Done.'
}

set_config() {
  local name="${1}"
  local value="${2}"

  wp config set \
    --path="${LANDO_WEBROOT}" \
    --type='constant' \
    --raw \
    --anchor='/* Add any custom values between this line and the "stop editing" line. */' \
    --placement='before' \
    "${name}" "${value}"
}

install_wp() {
  echo 'Installing WordPress...'
  if wp core is-installed --path="${LANDO_WEBROOT}"; then
    echo 'WordPress already installed'
    return
  fi

  wp core install \
    --path="${LANDO_WEBROOT}" \
    --url="https://${LANDO_APP_NAME}.${LANDO_DOMAIN}" \
    --title="${LANDO_APP_NAME}" \
    --admin_user="lando" \
    --admin_password="lando" \
    --admin_email="lando@${LANDO_APP_NAME}.${LANDO_DOMAIN}" \
    --skip-email
}

install_theme() {
  if wp theme is-installed "${WP_FALLBACK_THEME}" --path="${LANDO_WEBROOT}"; then
    return;
  fi

  echo 'Installing fallback theme...'
  wp theme install "${WP_FALLBACK_THEME}" --path="${LANDO_WEBROOT}"

  local count="$(wp theme list --path="${LANDO_WEBROOT}" --format=count)"
  if [[ "${count}" -gt 1 ]]; then
    return;
  fi
  wp theme activate "${WP_FALLBACK_THEME}" --path="${LANDO_WEBROOT}"
  echo 'done.'
}

print_info() {
  echo "Site URL: $(wp option get siteurl --path="${LANDO_WEBROOT}")/"
  echo "Admin URL: $(wp option get siteurl --path="${LANDO_WEBROOT}")/wp-admin/"
  echo "Admin Username: lando"
  echo "Admin Password: lando"
}

run() {
  download_wp
  make_wp_config
  install_wp
  install_theme
  print_info
}

run