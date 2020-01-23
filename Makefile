.PHONY: static

DIR := ${CURDIR}
QA_IMAGE := jakzal/phpqa:php7.3-alpine

cs-lint:
	@docker run --rm -v $(DIR):/project -w /project $(QA_IMAGE) php-cs-fixer fix --diff-format udiff --dry-run -vvv

cs-fix:
	@docker run --rm -v $(DIR):/project -w /project $(QA_IMAGE) php-cs-fixer fix --diff-format udiff -vvv

phpstan:
	@docker run --rm -v $(DIR):/project -w /project $(QA_IMAGE) phpstan analyze
