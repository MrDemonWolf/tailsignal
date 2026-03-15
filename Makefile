.PHONY: zip test clean css coverage lint lint-fix

COMPOSER := $(shell command -v composer 2>/dev/null || echo php composer.phar)

css:
	npm -w src run build:css

zip:
	@echo "Building TailSignal plugin ZIP..."
	cd src && $(COMPOSER) install --no-dev --optimize-autoloader --quiet
	mkdir -p build
	rm -rf build/tailsignal build/tailsignal.zip
	rsync -a --exclude-from='src/.distignore' src/ build/tailsignal/
	cd build && zip -r tailsignal.zip tailsignal/ -x "*.DS_Store"
	rm -rf build/tailsignal
	cd src && $(COMPOSER) install --quiet 2>/dev/null || true
	@echo "Built: build/tailsignal.zip"

test:
	$(COMPOSER) install --quiet
	./vendor/bin/phpunit

coverage:
	$(COMPOSER) install --quiet
	./vendor/bin/phpunit --coverage-html build/coverage
	@echo "Coverage report: build/coverage/index.html"

lint:
	$(COMPOSER) install --quiet
	./vendor/bin/phpcs

lint-fix:
	$(COMPOSER) install --quiet
	./vendor/bin/phpcbf

clean:
	rm -rf build/
