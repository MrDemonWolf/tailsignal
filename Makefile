.PHONY: zip test clean css

COMPOSER := $(shell command -v composer 2>/dev/null || echo php composer.phar)

css:
	npx tailwindcss -i admin/css/tailwind-input.css -o admin/css/tailsignal-tailwind.css --minify

zip:
	@echo "Building TailSignal plugin ZIP..."
	$(COMPOSER) install --no-dev --optimize-autoloader --quiet
	mkdir -p build
	rm -rf build/tailsignal build/tailsignal.zip
	rsync -a --exclude-from='.distignore' . build/tailsignal/
	cd build && zip -r tailsignal.zip tailsignal/ -x "*.DS_Store"
	rm -rf build/tailsignal
	$(COMPOSER) install --quiet
	@echo "Built: build/tailsignal.zip"

test:
	$(COMPOSER) install --quiet
	./vendor/bin/phpunit

clean:
	rm -rf build/
