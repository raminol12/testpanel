#!/bin/bash

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

PROJECT_DIR="/var/www/marzban-panel"
PHP_MIN_VERSION="7.4"

install_system_dependencies() {
    clear
    echo -e "${YELLOW}Installing system dependencies...${NC}"
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS=$ID
    else
        echo -e "${RED}Error: Cannot identify operating system!${NC}"
        exit 1
    fi

    case "$OS" in
        ubuntu|debian)
            sudo apt update
            sudo apt install -y nginx php-fpm php-mysql php-curl php-json php-mbstring php-xml php-zip unzip curl git
            ;;
        centos|rhel)
            sudo yum install -y epel-release
            sudo yum install -y nginx php-fpm php-mysqlnd php-curl php-json php-mbstring php-xml php-zip unzip curl git
            ;;
        fedora)
            sudo dnf install -y nginx php-fpm php-mysqlnd php-curl php-json php-mbstring php-xml php-zip unzip curl git
            ;;
        *)
            echo -e "${RED}Error: Operating system $OS is not supported!${NC}"
            exit 1
            ;;
    esac
}

check_php_version() {
    clear
    echo -e "${YELLOW}Checking PHP version...${NC}"
    PHP_VERSION=$(php -v | head -n1 | cut -d " " -f 2 | cut -d "." -f 1,2)
    if (( $(echo "$PHP_VERSION < $PHP_MIN_VERSION" | bc -l) )); then
        echo -e "${RED}PHP $PHP_MIN_VERSION or higher is required. Found: $PHP_VERSION${NC}"
        exit 1
    fi
}

setup_project_directory() {
    clear
    echo -e "${YELLOW}Setting up project directory...${NC}"
    if [ ! -d "$PROJECT_DIR" ]; then
        sudo mkdir -p "$PROJECT_DIR"
        sudo chown -R www-data:www-data "$PROJECT_DIR"
    fi

    cd "$PROJECT_DIR"
    
    # Download project files
    echo -e "${YELLOW}Downloading project files...${NC}"
    git clone https://github.com/your-repo/marzban-panel.git .
    
    # Set permissions
    sudo chown -R www-data:www-data "$PROJECT_DIR"
    sudo chmod -R 755 "$PROJECT_DIR"
    sudo chmod -R 777 "$PROJECT_DIR/storage"
}

setup_nginx() {
    clear
    echo -e "${YELLOW}Setting up Nginx configuration...${NC}"
    NGINX_CONF="/etc/nginx/sites-available/marzban-panel"
    
    sudo bash -c "cat > $NGINX_CONF" << EOL
server {
    listen 80;
    server_name _;
    root $PROJECT_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOL

    # Enable site
    sudo ln -sf "$NGINX_CONF" /etc/nginx/sites-enabled/
    sudo nginx -t
    sudo systemctl restart nginx
}

setup_database() {
    clear
    echo -e "${YELLOW}Setting up database...${NC}"
    read -p "Enter MySQL root password: " MYSQL_ROOT_PASSWORD
    read -p "Enter database name: " DB_NAME
    read -p "Enter database user: " DB_USER
    read -p "Enter database password: " DB_PASSWORD

    # Create database and user
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" << EOF
CREATE DATABASE $DB_NAME;
CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

    # Update .env file
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" "$PROJECT_DIR/.env"
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" "$PROJECT_DIR/.env"
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" "$PROJECT_DIR/.env"
}

install_composer() {
    clear
    echo -e "${YELLOW}Installing Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
}

install_dependencies() {
    clear
    echo -e "${YELLOW}Installing PHP dependencies...${NC}"
    cd "$PROJECT_DIR"
    composer install --no-interaction --no-dev --optimize-autoloader
}

setup_permissions() {
    clear
    echo -e "${YELLOW}Setting up permissions...${NC}"
    sudo chown -R www-data:www-data "$PROJECT_DIR"
    sudo chmod -R 755 "$PROJECT_DIR"
    sudo chmod -R 777 "$PROJECT_DIR/storage"
    sudo chmod -R 777 "$PROJECT_DIR/bootstrap/cache"
}

generate_app_key() {
    clear
    echo -e "${YELLOW}Generating application key...${NC}"
    cd "$PROJECT_DIR"
    php artisan key:generate
}

run_migrations() {
    clear
    echo -e "${YELLOW}Running database migrations...${NC}"
    cd "$PROJECT_DIR"
    php artisan migrate --force
}

main() {
    echo -e "${BLUE}Starting Marzban Panel installation...${NC}"
    
    install_system_dependencies
    check_php_version
    setup_project_directory
    setup_nginx
    setup_database
    install_composer
    install_dependencies
    setup_permissions
    generate_app_key
    run_migrations
    
    echo -e "${GREEN}Installation completed successfully!${NC}"
    echo -e "${YELLOW}Please configure your web server and database settings in the .env file.${NC}"
}

main 