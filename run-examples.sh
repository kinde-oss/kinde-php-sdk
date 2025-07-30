#!/bin/bash

# Kinde PHP SDK Examples Runner
# This script helps you run the various examples

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

# Function to check environment variables
check_env_vars() {
    local missing_vars=()
    
    if [ -z "$KINDE_DOMAIN" ]; then
        missing_vars+=("KINDE_DOMAIN")
    fi
    
    if [ -z "$KINDE_CLIENT_ID" ]; then
        missing_vars+=("KINDE_CLIENT_ID")
    fi
    
    if [ -z "$KINDE_CLIENT_SECRET" ]; then
        missing_vars+=("KINDE_CLIENT_SECRET")
    fi
    
    if [ ${#missing_vars[@]} -ne 0 ]; then
        print_warning "Missing environment variables: ${missing_vars[*]}"
        print_status "Please set them before running examples:"
        echo "export KINDE_DOMAIN=\"https://your-domain.kinde.com\""
        echo "export KINDE_CLIENT_ID=\"your_client_id\""
        echo "export KINDE_CLIENT_SECRET=\"your_client_secret\""
        echo ""
        return 1
    fi
    
    return 0
}

# Function to run management client example
run_management_example() {
    print_status "Running Management Client Example..."
    
    if ! check_env_vars; then
        print_error "Cannot run management example without environment variables"
        return 1
    fi
    
    if php examples/management_client_example.php; then
        print_success "Management client example completed"
    else
        print_error "Management client example failed"
        return 1
    fi
}

# Function to show framework examples info
show_framework_info() {
    print_status "Framework Examples Available:"
    echo "  📁 Laravel: examples/laravel/ExampleController.php"
    echo "  📁 Slim: examples/slim/ExampleApp.php"
    echo "  📁 Symfony: examples/symfony/ExampleController.php"
    echo "  📁 CodeIgniter: examples/codeigniter/ExampleController.php"
    echo ""
    print_status "To use framework examples:"
    echo "  1. Copy the example files to your framework application"
    echo "  2. Install the Kinde SDK: composer require kinde/kinde-php-sdk"
    echo "  3. Set up routes and environment variables"
    echo "  4. Test the authentication endpoints"
}

# Function to show help
show_help() {
    echo "Kinde PHP SDK Examples Runner"
    echo ""
    echo "Usage: $0 [OPTION]"
    echo ""
    echo "Options:"
    echo "  management    Run the management client example (requires env vars)"
    echo "  frameworks    Show information about framework examples"
    echo "  setup         Show setup instructions"
    echo "  help          Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 management    # Run management client example"
    echo "  $0 frameworks   # Show framework examples info"
    echo "  $0 setup        # Show setup instructions"
}

# Function to show setup instructions
show_setup() {
    echo "Kinde PHP SDK Examples Setup"
    echo "============================"
    echo ""
    echo "1. Install Dependencies:"
    echo "   composer install"
    echo ""
    echo "2. Set Environment Variables:"
    echo "   export KINDE_DOMAIN=\"https://your-domain.kinde.com\""
    echo "   export KINDE_CLIENT_ID=\"your_client_id\""
    echo "   export KINDE_CLIENT_SECRET=\"your_client_secret\""
    echo "   export KINDE_REDIRECT_URI=\"http://localhost:8000/auth/callback\""
    echo "   export KINDE_GRANT_TYPE=\"authorization_code\""
    echo "   export KINDE_LOGOUT_REDIRECT_URI=\"http://localhost:8000\""
    echo "   export KINDE_SCOPES=\"openid profile email offline\""
    echo "   export KINDE_PROTOCOL=\"https\""
    echo ""
    echo "3. Run Examples:"
    echo "   $0 management    # Run management client example"
    echo "   $0 frameworks   # View framework examples"
    echo ""
    echo "4. Framework Integration:"
    echo "   - Copy example files to your framework application"
    echo "   - Set up routes and middleware"
    echo "   - Test authentication flows"
    echo ""
    echo "For detailed instructions, see: examples/README.md"
}

# Main script logic
case "${1:-help}" in
    "management")
        run_management_example
        ;;
    "frameworks")
        show_framework_info
        ;;
    "setup")
        show_setup
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

print_success "Examples runner completed!" 