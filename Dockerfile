FROM php:7.4-cli

RUN \
    apt-get update \
    && apt-get install -y \
        git \
        curl \
        zip \
        #libmcrypt-dev \
        #mysql-client libmagickwand-dev --no-install-recommends \
    # && pecl install imagick \
    # && docker-php-ext-enable imagick \
    #&& docker-php-ext-install mcrypt pdo_mysql
    # Install Composer
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    ;

VOLUME [ "/collections" ]

WORKDIR /collections

CMD /bin/bash
