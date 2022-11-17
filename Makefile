r.PHONY: help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

deploy_dev: ## Deploy project for dev environment
	composer install --prefer-dist --no-ansi --no-interaction
	yarn install
	php bin/console doctrine:database:create --if-not-exists --no-interaction
	php bin/console doctrine:database:create --connection=statistique --if-not-exists --no-interaction
	php bin/console doctrine:migration:migrate --configuration=config/packages/migrations/app.yaml --no-interaction
	php bin/console doctrine:migration:migrate --configuration=config/packages/migrations/stat.yaml --no-interaction
	php bin/console uca:datatables:fixLang
	php bin/console uca:table:annotation:load
	php bin/console assets:install --symlink assets
	php bin/console ckeditor:install --clear=drop
	php bin/console bazinga:js-translation:dump assets/bundles/bazingajstranslation --merge-domains --format=js
	php bin/console fos:js-routing:dump --format=json --target=assets/bundles/fosjsrouting/fos_routes.json
	yarn encore dev

redeploy_dev: ## Deploy project for dev environment
	php bin/console doctrine:database:drop --if-exists --force --no-interaction
	php bin/console doctrine:database:drop --connection=statistique --if-exists --force --no-interaction
	make deploy_dev