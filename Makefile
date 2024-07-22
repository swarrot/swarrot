.PHONY: ${TARGETS}
.DEFAULT_GOAL := help

DIR := ${CURDIR}
QA_IMAGE := jakzal/phpqa:php7.3-alpine

help:
	@echo "\033[1;36mAVAILABLE COMMANDS :\033[0m"
	@awk 'BEGIN {FS = ":.*##"} /^[a-zA-Z_0-9-]+:.*?##/ { printf "  \033[32m%-20s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[33m%s\033[0m\n", substr($$0, 5) } ' Makefile

##@ Quality commands

cs-lint: ## Lint all files
	@composer install --working-dir=tools/php-cs-fixer
	@tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix: ## Fix CS using PHP-CS
	@composer install --working-dir=tools/php-cs-fixer
	@tools/php-cs-fixer/vendor/bin/php-cs-fixer fix

phpstan: ## Run PHPStan
	@composer install --working-dir=tools/phpstan
	@tools/phpstan/vendor/bin/phpstan analyse --memory-limit=512M
