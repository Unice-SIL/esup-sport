#!/bin/sh
composer update
yarn install

echo unice2019acatus
echo DROP DATABASE uca;
echo CREATE DATABASE uca;
echo exit
mysql -u root -p

php bin/console doctrine:schema:update --force
php bin/console uca:sql:load InitData.sql

cp src/UcaBundle/Datatables/Response/DatatableQueryBuilder.Fixed.php vendor/sg/datatablesbundle/Response/DatatableQueryBuilder.php
php bin/console uca:table:annotation:load

php bin/console cache:clear --env=dev
php bin/console cache:clear --env=prod

php bin/console assets:install --symlink assets
php bin/console bazinga:js-translation:dump assets/bundles/bazingajstranslation --merge-domains --format=js
php bin/console fos:js-routing:dump --format=json --target=assets/bundles/fosjsrouting/fos_routes.json
yarn encore dev