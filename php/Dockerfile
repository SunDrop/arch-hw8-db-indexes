FROM php:8.1-fpm

RUN apt-get update  \
    && apt-get install -y \
      curl; \
    docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd; \
    docker-php-ext-install pdo pdo_mysql; \
    docker-php-ext-enable pdo pdo_mysql; \
    docker-php-ext-configure shmop --enable-shmop; \
    docker-php-ext-install -j "$(nproc)" \
      shmop;

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

CMD ["php-fpm"]
