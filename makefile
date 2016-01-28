.PHONY: clean lint phpunit test

test: clean lint phpunit

clean:
	@tput setaf 2; echo cleaning up...; tput sgr0
	@rm -rf build
	@rm -rf coverage

lint:
	@tput setaf 2; echo running lint...; tput sgr0
	@vendor/bin/php-cs-fixer fix --dry-run --diff Schnittstabil
	@vendor/bin/php-cs-fixer fix --dry-run --diff tests

phpunit:
	@tput setaf 2; echo running phpunit...; tput sgr0
	@vendor/bin/phpunit
