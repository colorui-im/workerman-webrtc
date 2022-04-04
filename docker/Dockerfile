ARG PHP_VERSION="registry.cn-shanghai.aliyuncs.com/wpjscc/php:7.4.7-fpm-alpine"
FROM ${PHP_VERSION}

COPY  . /var/www/html

WORKDIR /var/www/html

RUN composer install --ignore-platform-reqs --no-dev --no-interaction -o

