FROM php:8.2-cli

# Use the standard PHP dev config as a base (short_open_tag off, session support built-in)
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Make sure PHP has a writable place to store session files
RUN mkdir -p /var/lib/php/sessions \
    && chmod 1733 /var/lib/php/sessions
ENV PHP_SESSION_SAVE_PATH=/var/lib/php/sessions

WORKDIR /var/www/html

# Copy exercise source files into the image (also bind-mounted in dev, see docker-compose.yml)
COPY src/ /var/www/html/

EXPOSE 8000

# PHP's built-in dev server, good enough for cookies/sessions exercises
CMD ["php", "-d", "session.save_path=/var/lib/php/sessions", "-S", "0.0.0.0:8000", "-t", "/var/www/html"]
