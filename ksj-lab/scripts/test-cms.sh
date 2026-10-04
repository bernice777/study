#!/usr/bin/env bash
# Non-destructive smoke checks. Does not seed, reset or alter application data.
source "$(dirname "$0")/_common.sh"
load_env
case "${1:-}" in
  ''|--installed) ;;
  *) echo 'Usage: test-cms.sh [--installed]' >&2; exit 2 ;;
esac
for app in xe rb; do
  if [ "$app" = xe ]; then port="${XE_PORT:-8003}"; else port="${RB_PORT:-8004}"; fi
  curl --fail --silent --show-error --max-time 10 --retry 5 --retry-connrefused \
    --retry-delay 1 "http://127.0.0.1:$port/$app/" >/dev/null
  $DC exec -T "$app-db" healthcheck.sh --connect --innodb_initialized
  $DC exec -T "$app" php -r '
    foreach (array("mysql", "mysqli", "mbstring", "gd", "xml", "iconv") as $extension) {
      if (!extension_loaded($extension)) { fwrite(STDERR, "Missing: $extension\n"); exit(1); }
    }
    $body = file_get_contents("http://127.0.0.1/" . getenv("CMS_APP") . "/");
    if ($body === false || strlen($body) < 100 || stripos($body, "Fatal error") !== false) {
      fwrite(STDERR, "CMS page failed\n"); exit(1);
    }
    echo getenv("CMS_APP") . ": HTTP and PHP extensions OK\n";
  '
done
if [ "${1:-}" = --installed ]; then
  $DC exec -T xe-db sh -c 'MYSQL_PWD="$MARIADB_PASSWORD" mariadb -u xe xe_db -N -e "SELECT COUNT(*) FROM xe_member WHERE is_admin = '\''Y'\''"' | awk '$1 > 0 {ok=1} END {exit !ok}'
  $DC exec -T rb-db sh -c 'MYSQL_PWD="$MARIADB_PASSWORD" mariadb -u rb kimsq -N -e "SELECT COUNT(*) FROM rb_s_mbrdata WHERE admin = 1"' | awk '$1 > 0 {ok=1} END {exit !ok}'
  echo 'XE/RB administrator rows OK'
fi
