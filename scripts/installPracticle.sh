#!/bin/bash

# Practicle Installation Script with Dry-Run and PHP 8.3 FPM Configuration

DRY_RUN=false
PHP_VERSION="8.3"
FPM_POOL_CONF="/etc/php/$PHP_VERSION/fpm/pool.d/www.conf"
WEB_PROJECT_PATH="/var/www/html/practicle"
VERSION="3.93.127" # Replace with the desired Practicle release version

function run_command() {
    local command=$1
    if [ "$DRY_RUN" = true ]; then
        echo "[DRY-RUN] $command"
    else
        eval "$command"
    fi
}

function print_usage() {
    echo "Usage: $0 [--dry-run]"
    echo "  --dry-run    Preview commands without executing them"
}

install_php_extensions() {
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

create_mysql_admin() {
    # Variables
    MYSQL_USER="mysql-pracadm"
    PASSWORD=$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9!@#$%^&*()-_' | head -c 16)
    PASSWORD_FILE="./mysql_pracadm_credentials.txt"

    # Check if MySQL is running
    if ! systemctl is-active --quiet mysql; then
        echo "Error: MySQL service is not running."
        return 1
    fi

    # Run MySQL commands to create user
    echo "Creating MySQL admin user '$MYSQL_USER'..."
    mysql -e "
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

configure_mysql_case_insensitivity() {
    local MYSQL_CONFIG_FILE="/etc/mysql/mysql.conf.d/mysqld.cnf"
    local MYSQL_DATA_DIR="/var/lib/mysql"
    local MYSQL_BACKUP_DIR="/var/lib/mysql_backup"

    # Backup the existing MySQL configuration file
    cp "$MYSQL_CONFIG_FILE" "${MYSQL_CONFIG_FILE}.bak"
    echo "Backup created: ${MYSQL_CONFIG_FILE}.bak"

    # Add or update the lower_case_table_names setting
    if grep -q "^lower_case_table_names" "$MYSQL_CONFIG_FILE"; then
        sed -i 's/^lower_case_table_names.*/lower_case_table_names = 1/' "$MYSQL_CONFIG_FILE"
        echo "Updated 'lower_case_table_names' setting to 1."
    else
        echo -e "\n[mysqld]\nlower_case_table_names = 1" >> "$MYSQL_CONFIG_FILE"
        echo "Added 'lower_case_table_names = 1' to the configuration file."
    fi

    # Stop MySQL to make changes
    systemctl stop mysql || { echo "Error stopping MySQL. Aborting."; return 1; }
    echo "MySQL service stopped successfully."

    # Backup existing data directory
    if [ -d "$MYSQL_DATA_DIR" ]; then
        mv "$MYSQL_DATA_DIR" "$MYSQL_BACKUP_DIR"
        echo "Existing MySQL data directory backed up to $MYSQL_BACKUP_DIR."
    fi

    # Fix ownership and permissions for the MySQL data directory
    mkdir -p "$MYSQL_DATA_DIR"
    chown -R mysql:mysql "$MYSQL_DATA_DIR"
    chmod 750 "$MYSQL_DATA_DIR"
    echo "Permissions and ownership corrected for $MYSQL_DATA_DIR."

    # Reinitialize the MySQL data directory
    if mysqld --initialize-insecure --user=mysql; then
        echo "MySQL data directory reinitialized successfully."
    else
        echo "Error reinitializing MySQL data directory."
        return 1
    fi

    # Restart MySQL service
    if systemctl start mysql; then
        echo "MySQL service restarted successfully."
    else
        echo "Error restarting MySQL. Please check the configuration."
        return 1
    fi

    # Enable passwordless root login using auth_socket
    mysql --skip-password --execute="
    ALTER USER 'root'@'localhost' IDENTIFIED WITH 'mysql_native_password' BY '';
    FLUSH PRIVILEGES;"
    if [ $? -eq 0 ]; then
        echo "MySQL root user configured for passwordless local login."
    else
        echo "Error configuring MySQL root user for passwordless login."
        return 1
    fi

    echo "MySQL is now configured to be case-insensitive and allows root login without a password."
}

configure_locales() {
    sudo locale-gen en_US.UTF-8 en_US da_DK.UTF-8 da_DK es_ES.UTF-8 es_ES de_DE.UTF-8 de_DE fr_FR.UTF-8 fr_FR fi_FI.UTF-8 fi_FI it_IT.UTF-8 it_IT tr_TR.UTF-8 tr_TR zh_CN.UTF-8 zh_CN ru_RU.UTF-8 ru_RU ja_JP.UTF-8 ja_JP pt_PT.UTF-8 pt_PT
    sudo update-locale
}

# Parse arguments
for arg in "$@"; do
    case $arg in
    --dry-run)
        DRY_RUN=true
        shift
        ;;
    *)
        print_usage
        exit 1
        ;;
    esac
done

# Step 1: Update system packages
run_command "apt update && apt upgrade -y"

# Step 2: Install necessary packages
run_command "apt install -y mysql-server apache2 php$PHP_VERSION php$PHP_VERSION-fpm php$PHP_VERSION-mysql php$PHP_VERSION-gettext wget tar php-cli php$PHP_VERSION-common git php$PHP_VERSION-dev re2c gcc make autoconf"

echo "Setting timezone on server to Europe/Copenhagen..."
run_command "timedatectl set-timezone Europe/Copenhagen"

# Step 3: Enable Apache modules and PHP-FPM
run_command "a2enmod proxy_fcgi setenvif"
run_command "a2enconf php$PHP_VERSION-fpm"

# Step 4: Set up PHP-FPM pool configuration
run_command "sed -i 's/^listen = .*/listen = \/run\/php\/php$PHP_VERSION-fpm.sock/' $FPM_POOL_CONF"
run_command "systemctl restart php$PHP_VERSION-fpm"

# Step 5: Create Apache VirtualHost
APACHE_CONF="/etc/apache2/sites-available/practicle.conf"
run_command "cat <<EOL >$APACHE_CONF
<VirtualHost *:80>
    ServerName practicle.local
    DocumentRoot $WEB_PROJECT_PATH

    <Directory $WEB_PROJECT_PATH>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \".+\.php$\">
        SetHandler \"proxy:unix:/run/php/php$PHP_VERSION-fpm.sock|fcgi://localhost/\"
    </FilesMatch>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOL"

run_command "a2ensite practicle.conf"
echo "Starting apache2 service..."
run_command "systemctl restart apache2"
echo "Starting MySQL service..."
run_command "systemctl start mysql"
run_command "systemctl enable mysql"

# Step 6: Download and extract Practicle release
run_command "mkdir -p $WEB_PROJECT_PATH"
run_command "wget -O $WEB_PROJECT_PATH/${VERSION}.tar.gz https://github.com/cljorgensen/practicle-free/archive/refs/tags/${VERSION}.tar.gz"
run_command "tar -xvf $WEB_PROJECT_PATH/${VERSION}.tar.gz -C $WEB_PROJECT_PATH --strip-components=1"
run_command "rm $WEB_PROJECT_PATH/${VERSION}.tar.gz"

install_php_extensions "$WEB_PROJECT_PATH"
configure_locales
set_project_permissions "$WEB_PROJECT_PATH"
configure_mysql_case_insensitivity
create_mysql_admin

run_command "systemctl restart php8.3-fpm.service"

# Final message
if [ "$DRY_RUN" = true ]; then
    echo "Dry-run completed. No changes were made."
else
    echo "Installation completed successfully. Visit http://practicle.local/practicle/install.php in your browser."
fi
