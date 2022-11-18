PHP ?=
TEST ?=

docs:
	docker run --rm -v $$(pwd)/src:/data/src -v $$(pwd)/docs:/data/docs -w /data php:cli bash -c "\
		curl -s -L -O https://phpdoc.org/phpDocumentor.phar;\
		php phpDocumentor.phar --directory=src --target=docs --visibility=public --defaultpackagename='Geo' --title='Geo';"

test:
	VERSION=$$(echo "$(PHP)-cli" | sed "s/^-//");\
	test $$(docker images -q matthiasmullie/geo:$$VERSION) || docker build -t matthiasmullie/geo:$$VERSION . --build-arg VERSION=$$VERSION;\
	docker run -v $$(pwd)/src:/var/www/src -v $$(pwd)/tests:/var/www/tests -v $$(pwd)/build:/var/www/build matthiasmullie/geo:$$VERSION env XDEBUG_MODE=coverage vendor/bin/phpunit $(TEST) --coverage-clover build/coverage-$(PHP)-$(TEST).clover

format:
	test $$(docker images -q matthiasmullie/geo:cli) || docker build -t matthiasmullie/geo:cli .
	docker run -v $$(pwd)/src:/var/www/src -v $$(pwd)/tests:/var/www/tests matthiasmullie/geo:cli sh -c "vendor/bin/php-cs-fixer fix && vendor/bin/phpcbf --standard=ruleset.xml"

.PHONY: docs
