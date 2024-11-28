#!/bin/bash
# Service configuration script: v.1.0.0 (2024-10-22)
# Tested to be compliant with PHP 8.3.12
# This script creates a systemd service and timer to run practicle PHP script each 10 seconds.
# This service is necessary for general job executions in practicle.
# Practicle can run without this service, but automated features like checking incoming mails, syncronization tasks etc. will not work properly.

# Function to create the systemd service
create_service() {
    WEB_PROJECT_PATH=$1
    SERVICE_NAME="practicle-service"

    # Define the full path to the jobengine.php file
    PHP_FILE="$WEB_PROJECT_PATH/watchers/jobengine.php"

    # Check if the PHP file exists
    if [ ! -f "$PHP_FILE" ]; then
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
}

# Prompt for the web project path
read -p "Enter the full path to your web project (e.g., /var/www/html/webproject): " WEB_PROJECT_PATH

# Call the function to create the service
create_service "$WEB_PROJECT_PATH"
