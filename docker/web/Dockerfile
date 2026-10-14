# nginx in front of PHP-FPM. It holds no application code: every request,
# assets included, goes to public/index.php, which serves them from PHP.
FROM nginx:1.27-alpine

ENV LEGO_APP_UPSTREAM=app:9000
COPY docker/web/default.conf.template /etc/nginx/templates/default.conf.template

HEALTHCHECK --interval=10s --timeout=3s --start-period=10s --retries=5 \
  CMD wget -qO- http://127.0.0.1/health > /dev/null || exit 1
