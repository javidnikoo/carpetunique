#!/bin/sh
set -e

mkdir -p /srv/sylius/var/cache /srv/sylius/var/log /srv/sylius/public/media
chown -R www-data:www-data /srv/sylius/var /srv/sylius/public/media

exec "$@"
