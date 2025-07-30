#!/bin/bash

# Kinde PHP SDK Test Runner
# This script runs different test suites and generates reports

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    print_error "Please run this script from the kinde-php-sdk directory"
    exit 1
fi

# Create necessary directories
mkdir -p coverage
mkdir -p logs

# Function to run tests
run_tests() {
    local suite=$1
    local output_file="logs/${suite// /_}_test_results.txt"
    
    print_status "Running $suite tests..."
    
    if php vendor/bin/phpunit --testsuite="$suite" --coverage-html="coverage/${suite// /_}" --coverage-clover="coverage/${suite// /_}_clover.xml" > "$output_file" 2>&1; then
        print_success "$suite tests passed"
        return 0
    else
        print_error "$suite tests failed. Check $output_file for details"
        return 1
    fi
}

# Function to run all tests
run_all_tests() {
    print_status "Running all tests..."
    
    if php vendor/bin/phpunit --coverage-html="coverage/all" --coverage-clover="coverage/all_clover.xml" > "logs/all_test_results.txt" 2>&1; then
        print_success "All tests passed"
        return 0
    else
        print_error "Some tests failed. Check logs/all_test_results.txt for details"
        return 1
    fi
}

# Function to show test coverage
show_coverage() {
    if [ -f "coverage/all/index.html" ]; then
        print_status "Test coverage report generated at: coverage/all/index.html"
        print_status "You can open this file in your browser to view detailed coverage"
    fi
}

# Function to clean up
cleanup() {
    print_status "Cleaning up..."
    rm -rf coverage
    rm -rf logs
    print_success "Cleanup complete"
}

# Function to show help
show_help() {
    echo "Kinde PHP SDK Test Runner"
    echo ""
    echo "Usage: $0 [OPTION]"
    echo ""
    echo "Options:"
    echo "  core              Run core SDK tests only (default)"
    echo "  laravel           Run Laravel framework tests only (requires Laravel environment)"
    echo "  symfony           Run Symfony framework tests only (requires Symfony environment)"
    echo "  codeigniter       Run CodeIgniter framework tests only (requires CodeIgniter environment)"
    echo "  integration       Run integration tests only"
    echo "  framework         Run all framework tests (requires respective framework environments)"
    echo "  all               Run all tests (framework tests may fail without proper environment)"
    echo "  coverage          Show coverage information"
    echo "  clean             Clean up test artifacts"
    echo "  help              Show this help message"
    echo ""
    echo "Note: Framework tests require their respective framework environments to be properly"
    echo "      set up. Core SDK tests can run independently."
    echo ""
    echo "Examples:"
    echo "  $0 core           # Run only core SDK tests"
    echo "  $0 laravel        # Run only Laravel tests (requires Laravel app)"
    echo "  $0 all            # Run all tests"
    echo "  $0 coverage       # Show coverage information"
}

# Main script logic
case "${1:-core}" in
    "core")
        run_tests "Core SDK"
        ;;
    "laravel")
        print_warning "Laravel framework tests require a Laravel application environment"
        run_tests "Laravel Framework"
        ;;
    "symfony")
        print_warning "Symfony framework tests require a Symfony application environment"
        run_tests "Symfony Framework"
        ;;
    "codeigniter")
        print_warning "CodeIgniter framework tests require a CodeIgniter application environment"
        run_tests "CodeIgniter Framework"
        ;;
    "integration")
        run_tests "Integration"
        ;;
    "framework")
        print_warning "Framework tests may fail without proper framework environments"
        run_tests "Laravel Framework"
        run_tests "Symfony Framework"
        run_tests "CodeIgniter Framework"
        ;;
    "all")
        print_warning "Framework tests may fail without proper framework environments"
        run_all_tests
        ;;
    "coverage")
        show_coverage
        ;;
    "clean")
        cleanup
        ;;
    "help"|"-h"|"--help")
        show_help
        ;;
    *)
        print_error "Unknown option: $1"
        show_help
        exit 1
        ;;
esac

# Show coverage if tests were run
if [ "$1" != "coverage" ] && [ "$1" != "clean" ] && [ "$1" != "help" ]; then
    show_coverage
fi

print_success "Test runner completed!" 