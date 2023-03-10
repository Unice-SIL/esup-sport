FROM php:7.4.33-cli

# LIBRARIES
RUN apt-get update  &&  apt-get install -y --no-install-recommends \
    apt-transport-https \
    apt-utils \
    ca-certificates \
    gcc \
    git libicu-dev \
    g++ \
    libldap2-dev \
    libmagickwand-dev \
    libonig-dev \
    libpng-dev \
    libpq-dev \
    libxml2-dev \
    libxslt-dev \
    libzip-dev \
    locales  \
    lsb-release \
    make \
    nodejs \
    unzip \
    wget

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# PHP
RUN docker-php-ext-install \
    calendar \
    dom \
    exif \
    gd \
    intl \
    ldap \
    mbstring \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    opcache \
    xml \
    xsl \
    zip

RUN docker-php-ext-configure \
    intl

RUN pecl install \
    apcu \
    imagick

RUN docker-php-ext-enable \
    apcu \
    imagick

RUN echo memory_limit = -1 >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

# LOCALES
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && locale-gen

# NODEJS + YARN
RUN curl -sL https://deb.nodesource.com/setup_14.x | bash - \
    && apt install -y nodejs \
    && mkdir ~/.npm-global \
    && npm config set prefix '~/.npm-global' \
    && export PATH=~/.npm-global/bin:$PATH \
    && curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /usr/share/keyrings/yarnkey.gpg >/dev/null \
    && echo "deb [signed-by=/usr/share/keyrings/yarnkey.gpg] https://dl.yarnpkg.com/debian stable main" | tee /etc/apt/sources.list.d/yarn.list \
    && apt update \
    && apt install -y yarn

# COMPOSER
#RUN curl -sS https://getcomposer.org/installer | php -- &&  mv composer.phar /usr/local/bin/composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# SYMFONY
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    &&  mv /root/.symfony5/bin/symfony /usr/local/bin

# GIT
#RUN git config --global user.email "you@example.com" \
#    &&  git config --global user.name "Your Name"

WORKDIR /web/app