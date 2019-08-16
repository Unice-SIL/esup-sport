rem copy js to assets/bundles
php bin/console assets:install --symlink assets

rem met à jour les traductions pour le js
php bin/console bazinga:js-translation:dump assets/bundles/bazingajstranslation --merge-domains --format=js

rem met à jour les routes (expose=true) pour le js
php bin/console fos:js-routing:dump --format=json --target=assets/bundles/fosjsrouting/fos_routes.json

rem compile uca.js pour prendre en compte les nouvelles traductions et les nouvelles routes.
yarn encore dev