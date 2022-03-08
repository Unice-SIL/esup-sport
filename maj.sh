#!/bin/bash

# echo composer install
# composer install
echo php bin/console uca:datatables:fixLang
php bin/console uca:datatables:fixLang

# echo yarn upgrade
# yarn upgrade
# echo yarn install
# yarn install
# echo php bin/console doctrine:schema:update --force
# php bin/console doctrine:schema:update --force
# echo php bin/console doctrine:schema:update --em=statistique --force
# php bin/console doctrine:schema:update --em=statistique --force
# echo php bin/console assets:install --symlink assets
# php bin/console assets:install --symlink assets
# echo bazinga assets
# php bin/console bazinga:js-translation:dump assets/bundles/bazingajstranslation --merge-domains --format=js
# echo asset .json
# php bin/console fos:js-routing:dump --format=json --target=assets/bundles/fosjsrouting/fos_routes.json
echo yarn encore
yarn encore prod --ucaEnv=prod
echo cache clear
php bin/console cache:clear --env=prod

#position sur le repertoire parent pour changer les fichier root en apache
cd ..
chown apache:apache Uca/ -R
