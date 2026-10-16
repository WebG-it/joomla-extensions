#!/bin/bash
# Makes the public site URL (http://localhost:<DEV_PORT>) reachable from inside the
# container too, for tools that crawl the site through its own URL (4SEO site analysis
# and sitemap, Smart Search crawlers). Apache listens on that port as well.
# Dev-only tool; lost when the container is recreated.
#   docker compose ... exec web bash /dev-scripts/loopback_port.sh
set -e
PORT="${DEV_SITE_URL##*:}"
[ -n "$PORT" ] && [ "$PORT" != "80" ] || exit 0
grep -q "^Listen $PORT$" /etc/apache2/ports.conf || echo "Listen $PORT" >> /etc/apache2/ports.conf
apache2ctl -k graceful
sleep 1
curl -s -o /dev/null -w "loopback $DEV_SITE_URL -> %{http_code}\n" "$DEV_SITE_URL/"
