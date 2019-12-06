.PHONY: static

DIR := ${CURDIR}
QA_IMAGE := jakzal/phpqa:php7.3-alpine

static:
	@docker run --rm -v $(DIR):/project -w /project $(QA_IMAGE) php-cs-fixer fix --diff-format udiff
