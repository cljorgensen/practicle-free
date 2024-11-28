#!/bin/bash
# Practicle Install Script: v.1.0.0 (2024-11-19)

# Exit immediately if a command exits with a non-zero status.
version="3.93.111"

set -e

# Function to install PHP extension
install_php_extension() {
    WEB_PROJECT_PATH=$1
    EXTENSION_PATH="$WEB_PROJECT_PATH/inc/practiclefunctions.so"
    PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
    PHP_EXT_DIR=$(php -i | grep "^extension_dir" | awk '{print $3}')

    # Verify the extension file exists
    if [ ! -f "$EXTENSION_PATH" ]; then
        echo "Error: Extension file $EXTENSION_PATH not found."
        exit 1
    fi

    echo "Installing PHP extension practiclefunctions.so for PHP $PHP_VERSION..."

    # Copy the .so file to the PHP extension directory
    sudo cp "$EXTENSION_PATH" "$PHP_EXT_DIR/"

    # Add configuration files for CLI, Apache2, and FPM
    CONF_PATHS=(
        "/etc/php/$PHP_VERSION/cli/conf.d"
        "/etc/php/$PHP_VERSION/apache2/conf.d"
        "/etc/php/$PHP_VERSION/fpm/conf.d"
    )

    for CONF_PATH in "${CONF_PATHS[@]}"; do
        if [ -d "$CONF_PATH" ]; then
            INI_FILE="$CONF_PATH/20-practiclefunctions.ini"
            echo "Adding configuration to $INI_FILE..."
            echo "extension=practiclefunctions.so" | sudo tee "$INI_FILE" > /dev/null
        fi
    done

    # Verify the extension is loaded
    echo "Verifying the extension is loaded in CLI..."
    php -m | grep practiclefunctions && echo "Extension loaded successfully in CLI." || echo "Error: Extension not loaded in CLI."

    sudo apt install php-imap -y
    sudo apt install php-curl -y
    sudo apt install php-gd -y
    sudo apt install php-mbstring -y
    sudo apt install php-zip -y

    # Reload Apache2 and FPM (if installed) to apply the extension
    echo "Reloading services..."
    sudo systemctl reload apache2
    if command -v php-fpm > /dev/null; then
        sudo systemctl reload php$PHP_VERSION-fpm
    fi
}

# Function to create systemd service
create_service() {
    WEB_PROJECT_PATH=$1
    SERVICE_NAME="practicle-service"

    # Define the full path to the jobengine.php file
    PHP_FILE="$WEB_PROJECT_PATH/watchers/jobengine.php"

    # Check if the PHP file exists
    if [ ! -f "$PHP_FILE" ];then
        echo "Error: $PHP_FILE does not exist."
        exit 1
    fi

    # Create a systemd service file
    SERVICE_FILE="/etc/systemd/system/${SERVICE_NAME}.service"

    echo "Creating systemd service file at $SERVICE_FILE..."

    sudo tee "$SERVICE_FILE" > /dev/null <<EOL
[Unit]
Description=Job Engine PHP Service
After=network.target

[Service]
ExecStart=/usr/bin/php $PHP_FILE
Restart=always
User=www-data
Group=www-data

[Install]
WantedBy=multi-user.target
EOL

    # Create the systemd timer file
    TIMER_FILE="/etc/systemd/system/${SERVICE_NAME}.timer"

    echo "Creating systemd timer file at $TIMER_FILE..."

    sudo tee "$TIMER_FILE" > /dev/null <<EOL
[Unit]
Description=Run Job Engine PHP Service every 10 seconds

[Timer]
OnBootSec=10
OnUnitActiveSec=10
Unit=${SERVICE_NAME}.service

[Install]
WantedBy=timers.target
EOL

    # Reload systemd and enable the timer
    echo "Reloading systemd daemon..."
    sudo systemctl daemon-reload

    echo "Enabling and starting the service and timer..."
    sudo systemctl enable ${SERVICE_NAME}.timer
    sudo systemctl start ${SERVICE_NAME}.timer

    echo "Job engine service and timer created successfully!"
    echo "Service: $SERVICE_NAME"
    echo "Timer: $SERVICE_NAME.timer"
    echo "Job file: $PHP_FILE"
    systemctl enable $SERVICE_NAME.service
}

# Function to create Apache configuration
create_apache_config() {
    echo "Enabling rewrite mod..."
    sudo a2enmod rewrite

    SITE_NAME="practicle"
    APACHE_CONFIG_FILE="/etc/apache2/sites-available/${SITE_NAME}.conf"

    echo "Creating Apache configuration file at $APACHE_CONFIG_FILE..."

    sudo tee "$APACHE_CONFIG_FILE" > /dev/null <<EOL
<VirtualHost *:80>
    ServerName ${SITE_NAME}.dk
    DocumentRoot $WEB_PROJECT_PATH/
    Redirect permanent / https://${SITE_NAME}.dk
    RewriteEngine on
    RewriteCond %{SERVER_NAME} =${SITE_NAME}.dk
    RewriteRule ^ https://${SITE_NAME}.dk%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>
EOL

    echo "Enabling site ${SITE_NAME}..."
    sudo a2ensite ${SITE_NAME}

    echo "Reloading Apache..."
    sudo systemctl reload apache2

    echo "Apache configuration for ${SITE_NAME} created and enabled."
}

create_mysql_admin() {
    # Variables
    MYSQL_USER="mysql-pracadm"
    PASSWORD=$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9!@#$%^&*()-_' | head -c 16)
    MYSQL_ROOT_PASSWORD="$PASSWORD" # Replace with your actual MySQL root password
    PASSWORD_FILE="./mysql_pracadm_credentials.txt"

    # Check if MySQL is running
    if ! systemctl is-active --quiet mysql; then
        echo "Error: MySQL service is not running."
        return 1
    fi

    # Run MySQL commands to create user
    echo "Creating MySQL admin user '$MYSQL_USER'..."
    mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "
    CREATE USER '${MYSQL_USER}'@'localhost' IDENTIFIED BY '${PASSWORD}';
    GRANT ALL PRIVILEGES ON *.* TO '${MYSQL_USER}'@'localhost' WITH GRANT OPTION;
    FLUSH PRIVILEGES;
    "
    sudo mysql_tzinfo_to_sql /usr/share/zoneinfo | sudo mysql -u$MYSQL_USER -p$PASSWORD mysql
    sudo systemctl restart mysql.service

    # Store credentials securely
    echo "MySQL Admin User: $MYSQL_USER" > "$PASSWORD_FILE"
    echo "Password: $PASSWORD" >> "$PASSWORD_FILE"
    chmod 600 "$PASSWORD_FILE"

    echo "MySQL admin user created successfully."
    echo "Credentials have been saved to $PASSWORD_FILE."

}

set_project_permissions() {
    WEB_PROJECT_PATH=$1

    WEB_USER="www-data"

    # Define paths relative to the web project root directory
    PROJECT_DIR=$WEB_PROJECT_PATH
    SCRIPTS_DIR="$WEB_PROJECT_PATH/scripts"
    BACKUPS_DIR="$WEB_PROJECT_PATH/backups/dbs"
    UPLOADS_DIR="$WEB_PROJECT_PATH/uploads"
    LOCALES_DIR="$WEB_PROJECT_PATH/locales"

    # Change ownership of all files and directories to $WEB_USER
    echo "Changing ownership of all files and directories in $PROJECT_DIR to $WEB_USER..."
    if [ -d "$PROJECT_DIR" ]; then
        chown -R "$WEB_USER:$WEB_USER" "$PROJECT_DIR"
        echo "Ownership of $PROJECT_DIR set to $WEB_USER."
    else
        echo "Project directory $PROJECT_DIR does not exist."
    fi

    # Set all files and folders to 775
    echo "Setting permissions for $PROJECT_DIR to 775..."
    if [ -d "$PROJECT_DIR" ]; then
        chmod -R 775 "$PROJECT_DIR"
        echo "Permissions for $PROJECT_DIR set to 775."
    else
        echo "Directory $PROJECT_DIR does not exist."
    fi

    # Set all other directories in the project to 775, excluding $SCRIPTS_DIR
    echo "Setting all directories in $PROJECT_DIR to 775, excluding $SCRIPTS_DIR..."
    if [ -d "$PROJECT_DIR" ]; then
        find "$PROJECT_DIR" -type d -name "$(basename "$SCRIPTS_DIR")" -prune -o -type d -exec chmod 775 {} +
        echo "All directories in $PROJECT_DIR set to 775, excluding $SCRIPTS_DIR."
    else
        echo "Project directory $PROJECT_DIR does not exist."
    fi

    # Set all other files in the project to 664, excluding $SCRIPTS_DIR
    echo "Setting all files in $PROJECT_DIR to 664, excluding $SCRIPTS_DIR..."
    if [ -d "$PROJECT_DIR" ]; then
        find "$PROJECT_DIR" -type f -not -path "$SCRIPTS_DIR/*" -print0 | xargs -0 chmod 664
        echo "All files in $PROJECT_DIR set to 664, excluding $SCRIPTS_DIR."
    else
        echo "Project directory $PROJECT_DIR does not exist."
    fi

    # Set /backups/dbs directory to 775
    echo "Setting permissions for $BACKUPS_DIR to 775..."
    if [ -d "$BACKUPS_DIR" ]; then
        chmod 775 "$BACKUPS_DIR"
        echo "Permissions for $BACKUPS_DIR set to 775."
    else
        echo "Directory $BACKUPS_DIR does not exist."
    fi

    # Set /uploads/ and its subdirectories to 775 (to allow uploads)
    echo "Setting permissions for $UPLOADS_DIR and its subdirectories to 775..."
    if [ -d "$UPLOADS_DIR" ]; then
        chmod -R 775 "$UPLOADS_DIR"
        chown -R "$WEB_USER:$WEB_USER" "$UPLOADS_DIR"
        echo "Permissions for $UPLOADS_DIR set to 775, with write access for $WEB_USER."
    else
        echo "Directory $UPLOADS_DIR does not exist."
    fi

    # Set /locales/ and its subdirectories to 775
    echo "Setting permissions for $LOCALES_DIR and its subdirectories to 775..."
    if [ -d "$LOCALES_DIR" ]; then
        chmod -R 775 "$LOCALES_DIR"
        echo "Permissions for $LOCALES_DIR set to 775."
    else
        echo "Directory $LOCALES_DIR does not exist."
    fi

    # Set /scripts directory to 775
    echo "Setting permissions for $SCRIPTS_DIR to 775..."
    if [ -d "$SCRIPTS_DIR" ]; then
        chmod 775 "$SCRIPTS_DIR"
        echo "Permissions for $SCRIPTS_DIR set to 775."
    else
        echo "Directory $SCRIPTS_DIR does not exist."
    fi

    LOG_FILE="${PROJECT_DIR}/installation.log"
    touch "$LOG_FILE"
    chmod 777 "$LOG_FILE"

    echo "Permission setting completed."
}

# Prompt for the web project path with a default value
read -p "Enter the full path to your web project (e.g., /var/www/html/practicle) [default: /var/www/html/practicle]: " WEB_PROJECT_PATH
WEB_PROJECT_PATH=${WEB_PROJECT_PATH:-/var/www/html/practicle}

sudo timedatectl set-timezone Europe/Copenhagen

# Install Apache web server
echo "Installing Apache web server..."
sudo apt update # Added to ensure package lists are up-to-date
sudo apt install -y apache2

# Start Apache and enable it to run on boot
echo "Starting Apache service..."
sudo systemctl start apache2
sudo systemctl enable apache2

sudo apt install -y libapache2-mod-php
sudo systemctl restart apache2

# Install MySQL server
echo "Installing MySQL server..."
sudo apt install -y mysql-server

# Secure MySQL installation (optional)
echo "Securing MySQL installation..."
sudo mysql_secure_installation

# Start MySQL and enable it to run on boot
echo "Starting MySQL service..."
sudo systemctl start mysql
sudo systemctl enable mysql

echo "Installing prerequisites..."
sudo apt install -y php php-mysql php-cli php-common
sudo apt install -y git php php-dev re2c gcc make autoconf
sudo locale-gen da_DK.UTF-8
sudo locale-gen de_DE.UTF-8
sudo locale-gen es_ES.UTF-8
sudo locale-gen fr_FR.UTF-8
sudo locale-gen fi_FI.UTF-8
sudo locale-gen it_IT.UTF-8
sudo locale-gen tr_TR.UTF-8
sudo locale-gen zh_CN.UTF-8
sudo locale-gen zh_TW.UTF-8
sudo locale-gen ru_RU.UTF-8
sudo locale-gen ja_JP.UTF-8
sudo locale-gen pt_PT.UTF-8
sudo update-locale
echo "Installed prerequisites."

# Download latest release of Practicle
echo "Downloading and installing Practicle..."
mkdir -p $WEB_PROJECT_PATH # Added -p to prevent errors if the directory already exists
wget -O $WEB_PROJECT_PATH/${version}.tar.gz https://support.practicle.dk/backups/releases/hekx85klqcs5yhw7vfw5mq9sak0g/practicle_release_${version}.tar.gz
tar -xvf $WEB_PROJECT_PATH/${version}.tar.gz -C $WEB_PROJECT_PATH --strip-components=1 # Added --strip-components to extract directly into the directory
rm $WEB_PROJECT_PATH/${version}.tar.gz
echo "Downloaded Practicle."

# Call the function to install the PHP extension
install_php_extension "$WEB_PROJECT_PATH"

# Call the function to create the service
create_service "$WEB_PROJECT_PATH"

# Call the function to create the Apache configuration
create_apache_config

set_project_permissions "$WEB_PROJECT_PATH"

create_mysql_admin
