# syntax=docker/dockerfile:1

ARG PHP_VERSION=8.4
ARG NODE_VERSION=22

############################################
# Base: serversideup/php already ships every PHP extension BMAC needs.
# If one is ever missing, add it here as root with `install-php-extensions`.
############################################
FROM serversideup/php:${PHP_VERSION}-fpm-nginx AS base

############################################
# Composer dependencies (no dev packages)
############################################
FROM base AS vendor

COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

############################################
# Frontend assets
#
# The Bootstrap colors are compiled into the CSS by Vite (see vite.config.js),
# so they have to be known at build time. Pass them as build args; when
# building through compose.yaml they are read from your .env.
############################################
FROM node:${NODE_VERSION}-alpine AS assets

ARG BOOTSTRAP_COLOR_PRIMARY
ARG BOOTSTRAP_COLOR_SECONDARY
ARG BOOTSTRAP_COLOR_TERTIARY
ARG BOOTSTRAP_COLOR_SUCCESS
ARG BOOTSTRAP_COLOR_DANGER
ARG BOOTSTRAP_COLOR_WARNING

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY vite.config.js ./
COPY resources ./resources
# PurgeCSS scans Laravel's pagination views, so keep those classes in the build
COPY --from=vendor /var/www/html/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views

RUN npm run build

############################################
# Production image
############################################
FROM base AS production

ENV PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true \
    HEALTHCHECK_PATH=/up

COPY --chown=www-data:www-data --from=vendor /var/www/html/vendor ./vendor
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build

RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-interaction
