#!/usr/bin/env bash

cd "${LANDO_WEBROOT}"

SITE_URL="$(wp option get siteurl)"

# for testing media rewrite plugin
wp config set REWRITE_MEDIA_REMOTE 'https://adeptdigital.com.au/wp-content/uploads'
wp post create \
  --post_type=page \
  --post_title='Test Rewrite' \
  --post_name=test-rewrite \
  --post_status=publish \
  --post_content='<img src="'"${SITE_URL}"'/wp-content/uploads/2021/02/cropped-ad-logo-mark-white-padded-1-192x192.png">'