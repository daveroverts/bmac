####################################
# Node stage: build frontend assets
####################################
FROM node:22-alpine AS node

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY resources/ resources/
COPY vite.config.js ./

# Bootstrap color customization — override at build time if needed:
#   docker build --build-arg BOOTSTRAP_COLOR_PRIMARY="#FF0000" ...
ARG BOOTSTRAP_COLOR_PRIMARY="#2C3E50"
ARG BOOTSTRAP_COLOR_SECONDARY="#95a5a6"
ARG BOOTSTRAP_COLOR_TERTIARY="#18BC9C"
ARG BOOTSTRAP_COLOR_SUCCESS="#18BC9C"
ARG BOOTSTRAP_COLOR_DANGER="#E74C3C"
ARG BOOTSTRAP_COLOR_WARNING="#F39C12"

RUN npm run build

#########################################
# Composer stage: install PHP dependencies
#########################################
FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev

#########################################
# Final stage: production PHP image
#########################################
FROM serversideup/php:8.4-fpm-nginx AS production

LABEL maintainer="Dave Roverts"
LABEL description="BMAC - Book Me A Controller"

# Switch to root to install additional PHP extensions
USER root

# Install PHP extensions needed by the application
RUN install-php-extensions \
    pdo_mysql \
    pdo_pgsql \
    redis \
    gd \
    zip \
    bcmath \
    intl \
    opcache

# Copy custom Nginx configuration
COPY docker/nginx.conf /etc/nginx/server-opts.d/bmac.conf

# Copy the entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/bmac-entrypoint.sh
RUN chmod +x /usr/local/bin/bmac-entrypoint.sh

# Switch back to the default non-root user
USER www-data

WORKDIR /var/www/html

# Copy Composer dependencies from the composer stage
COPY --chown=www-data:www-data --from=composer /app/vendor vendor/

# Copy built frontend assets from the node stage
COPY --chown=www-data:www-data --from=node /app/public/build public/build/

# Copy application source code
COPY --chown=www-data:www-data . .

# Remove files not needed in production
RUN rm -rf \
    node_modules \
    tests \
    docker \
    .devcontainer \
    .github \
    .env.example \
    docker-compose.yml \
    docker-compose.prod.yml \
    vite.config.js \
    package.json \
    package-lock.json \
    phpstan.neon \
    phpunit.xml \
    rector.php
