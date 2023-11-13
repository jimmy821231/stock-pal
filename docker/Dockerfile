FROM php:8.1-fpm-alpine

RUN docker-php-ext-install exif
RUN apk add --no-cache nginx wget
RUN apk add --no-cache bash
#RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
#        && pecl install redis \
#        && docker-php-ext-enable redis.so

#RUN apk add --no-cache imagemagick imagemagick-dev \
#        && pecl install imagick \
#        && docker-php-ext-enable imagick

RUN docker-php-ext-install mysqli pdo pdo_mysql

#RUN apt add -y install ffmpeg
#RUN apt add install -yqq php7.4-redis

#RUN apt-get update && apt-get install -y \
#    imagemagick libmagickwand-dev --no-install-recommends \
#    && pecl install imagick \
#    && docker-php-ext-enable imagick

#RUN apt-get -yqq install ffmpeg;

WORKDIR /app
COPY .. /app

RUN sh -c "wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer"
RUN cd /app && \
    /usr/local/bin/composer install --no-dev --no-scripts

COPY nginx-prod.conf /etc/nginx/nginx.conf
COPY php8.1.ini /usr/local/etc/php/php.ini
RUN chown -R www-data:www-data /app

CMD sh /app/docker/startup.sh
