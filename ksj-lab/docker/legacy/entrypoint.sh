#!/bin/sh
set -eu
case "${CMS_APP:-}" in
    xe|rb) ;;
    *) echo 'CMS_APP must be xe or rb' >&2; exit 1 ;;
esac
root="/var/www/html/$CMS_APP"
# A named volume preserves the installation, configuration, uploads and caches.
# Never replace files or reset a database on ordinary restarts.
if [ ! -f "$root/.ksj-source-ready" ]; then
    mkdir -p "$root"
    tar -xzf "/opt/ksj-sources/$CMS_APP.tgz" -C "$root" --strip-components=1
    if [ "$CMS_APP" = xe ]; then
        mkdir -p "$root/files/config" "$root/files/cache" "$root/files/attach"
    else
        mkdir -p "$root/_tmp/session" "$root/_tmp/cache" "$root/_tmp/backup" \
            "$root/_tmp/widget" "$root/files" "$root/_var/sitephp" \
            "$root/_var/menu" "$root/_var/peak" "$root/_var/xml" \
            "$root/modules/admin/var/users"
    fi
    touch "$root/.ksj-source-ready"
    chown -R www-data:www-data "$root"
fi
printf 'session.name = KSJ_%s\n' "$CMS_APP" > /usr/local/etc/php/conf.d/zzz-session.ini
exec docker-php-entrypoint "$@"
