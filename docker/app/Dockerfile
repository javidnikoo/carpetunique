FROM ghcr.io/sylius/sylius-php:8.3-alpine AS php_base

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM php_base AS vendor

WORKDIR /srv/sylius

COPY composer.json composer.lock symfony.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

FROM node:22-alpine AS assets

WORKDIR /srv/sylius

COPY package.json yarn.lock webpack.config.js .babelrc ./
COPY assets ./assets
COPY public ./public
COPY --from=vendor /srv/sylius/vendor ./vendor

RUN yarn install --frozen-lockfile
RUN yarn build:prod

FROM php_base AS app

WORKDIR /srv/sylius

COPY . .
COPY --from=vendor /srv/sylius/vendor ./vendor
COPY --from=assets /srv/sylius/public/build ./public/build

RUN mkdir -p var/cache var/log public/media \
    && chown -R www-data:www-data var public

EXPOSE 9000

CMD ["php-fpm", "-F"]
