.PHONY: help install serve test clean lint

help:
	@echo "DecentraShare - PHP Version"
	@echo ""
	@echo "Available commands:"
	@echo "  make install           - Install PHP dependencies"
	@echo "  make serve             - Start development server"
	@echo "  make test              - Run tests"
	@echo "  make clean             - Clean up temporary files"
	@echo "  make lint              - Run PHP linter"
	@echo "  make setup             - Full setup (install deps)"
	@echo ""

install:
	@echo "Installing PHP dependencies..."
	composer install
	@echo "Done! Run 'make serve' to start the development server"

serve:
	@echo "Starting PHP development server on http://localhost:8000"
	php -S localhost:8000 -t public

test:
	@echo "Running tests..."
	composer run-script test

lint:
	@echo "Linting PHP code..."
	@find src -name "*.php" -exec php -l {} \;
	@find public -name "*.php" -exec php -l {} \;
	@echo "Lint complete!"

clean:
	@echo "Cleaning up temporary files..."
	@rm -rf vendor/
	@find . -name ".DS_Store" -delete
	@find . -name "*.log" -delete
	@echo "Clean complete!"

setup: install
	@echo "Setup complete!"
	@echo ""
	@echo "Next steps:"
	@echo "1. Copy .env.example to .env"
	@echo "2. Configure IPFS credentials in .env"
	@echo "3. Run 'make serve' to start development server"

.DEFAULT_GOAL := help
