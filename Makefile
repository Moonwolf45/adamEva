# Executables
PHP      = php
COMPOSER = composer
SYMFONY  = $(PHP) bin/console

# Misc
.DEFAULT_GOAL = help
.PHONY        : help sh bash test composer vendor sf cc db-diff db-migrate \
                lint lint-twig lint-yaml lint-container \
                cs-check cs-fix phpstan psalm deptrac analyze analyze-all fix security
## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

sh: ## Connect to the FrankenPHP container
	@$(PHP_CONT) sh

bash: ## Connect to the FrankenPHP container via bash so up and down arrows go to previous commands
	@$(PHP_CONT) bash

test: ## Start tests with phpunit, pass the parameter "c=" to add options to phpunit, example: make test c="--group e2e --stop-on-failure"
	@$(eval c ?=)
	@APP_ENV=test php bin/phpunit $(c)

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

db-diff: ## Generate a migration diff from schema changes
	@$(SYMFONY) doctrine:migrations:diff --conn=default --formatted

db-migrate: ## Run pending migrations
	@$(SYMFONY) doctrine:migrations:migrate --conn=default

## —— Lint 🧹 ——————————————————————————————————————————————————————————————————
lint: lint-twig lint-yaml lint-container ## Run all Symfony linters

lint-twig: ## Lint Twig templates
	@$(SYMFONY) lint:twig templates

lint-yaml: ## Lint YAML configuration files
	@$(SYMFONY) lint:yaml config --parse-tags

lint-container: ## Validate the compiled DI container
	@$(SYMFONY) lint:container

## —— Static analysis 🔍 ———————————————————————————————————————————————————————
cs-check: ## Check code style without changes (php-cs-fixer, dry-run)
	@$(COMPOSER) cs-check

cs-fix: ## Fix code style (php-cs-fixer)
	@$(COMPOSER) cs-fix

phpstan: ## Run PHPStan (level 8, warms up the container cache first)
	@$(COMPOSER) phpstan

psalm: ## Run Psalm static analysis
	@$(COMPOSER) psalm --alter --issues=ClassMustBeFinal

deptrac: ## Check architectural layer dependencies (Deptrac)
	@$(COMPOSER) deptrac

analyze: cs-check phpstan psalm deptrac security ## Run all analyzers

analyze-all: analyze ## Alias for analyze

fix: cs-fix ## Auto-fix everything that can be fixed

## —— Security 🔒 ——————————————————————————————————————————————————————————————
security: ## Check composer.lock for known vulnerabilities (fabpot/local-php-security-checker, runs on host)
	@./local-php-security-checker.exe
