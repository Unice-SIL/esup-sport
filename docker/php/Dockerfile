FROM php:7.4.33-cli

RUN apt-get update \
    &&  apt-get install -y --no-install-recommends \
        locales apt-utils git libicu-dev g++ libpng-dev libxml2-dev libzip-dev libonig-dev libxslt-dev unzip libpq-dev nodejs wget \
        apt-transport-https lsb-release ca-certificates libldap2-dev

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen  \
    &&  echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen \
    &&  locale-gen

RUN curl -sL https://deb.nodesource.com/setup_14.x | bash - \
    && apt install -y nodejs \
    && mkdir ~/.npm-global \
    && npm config set prefix '~/.npm-global' \
    && export PATH=~/.npm-global/bin:$PATH \
    && curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /usr/share/keyrings/yarnkey.gpg >/dev/null \
    && echo "deb [signed-by=/usr/share/keyrings/yarnkey.gpg] https://dl.yarnpkg.com/debian stable main" | tee /etc/apt/sources.list.d/yarn.list \
    && apt update \
    && apt install -y yarn

RUN curl -sS https://getcomposer.org/installer | php -- \
    &&  mv composer.phar /usr/local/bin/composer

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    &&  mv /root/.symfony5/bin/symfony /usr/local/bin

RUN docker-php-ext-configure \
            intl

RUN docker-php-ext-install \
            pdo pdo_mysql pdo_pgsql opcache intl zip calendar dom mbstring gd xsl ldap xml

RUN pecl install apcu && docker-php-ext-enable apcu

RUN git config --global user.email "you@example.com" \
    &&  git config --global user.name "Your Name"

RUN echo memory_limit = -1 >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

CMD tail -f /dev/null

WORKDIR /web/app

#RUN cd /web/app \
    #&& composer install \
    #&& yarn install \
    #&& php bin/console doctrine:database:create --if-not-exists --no-interaction \
    #&& php bin/console doctrine:migration:migrate --configuration=config/packages/migrations/app.yaml --no-interaction \
    #&& php bin/console doctrine:database:create --connection=statistique --if-not-exists --no-interaction \
    #&& php bin/console doctrine:migration:migrate --configuration=config/packages/migrations/stat.yaml --no-interaction \
    #&& php bin/console ckeditor:install \
    #&& php bin/console assets:install --symlink assets \
    #&& php bin/console bazinga:js-translation:dump assets/bundles/bazingajstranslation --merge-domains --format=js \
    #&& php bin/console fos:js-routing:dump --format=json --target=assets/bundles/fosjsrouting/fos_routes.json \
    #&& yarn encore prod