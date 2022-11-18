FROM composer:2.4.2 as vendor

WORKDIR /app
COPY --chown=101:101 . ./

RUN composer install \
    --prefer-dist \
    --no-dev \
    --no-scripts \
    --no-plugins \
    --no-interaction \
    --ignore-platform-reqs 

