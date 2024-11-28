#!/bin/bash
# Service uninstallation script: v.1.0.0 (2024-10-22)
# This script removes the Practicle systemd service and timer.
# It stops and disables the service and timer, then deletes the service and timer files.

SERVICE_NAME="practicle-service"

# Function to remove the systemd service and timer
remove_service() {
    # Stop and disable the timer
    echo "Stopping and disabling the $SERVICE_NAME.timer..."
    sudo systemctl stop ${SERVICE_NAME}.timer
    sudo systemctl disable ${SERVICE_NAME}.timer

    # Stop and disable the service
    echo "Stopping and disabling the $SERVICE_NAME.service..."
    sudo systemctl stop ${SERVICE_NAME}.service
    sudo systemctl disable ${SERVICE_NAME}.service

    # Remove the systemd service and timer files
    SERVICE_FILE="/etc/systemd/system/${SERVICE_NAME}.service"
    TIMER_FILE="/etc/systemd/system/${SERVICE_NAME}.timer"

    if [ -f "$SERVICE_FILE" ]; then
        echo "Removing $SERVICE_FILE..."
        sudo rm "$SERVICE_FILE"
    else
        echo "Service file $SERVICE_FILE does not exist."
    fi

    if [ -f "$TIMER_FILE" ]; then
        echo "Removing $TIMER_FILE..."
        sudo rm "$TIMER_FILE"
    else
        echo "Timer file $TIMER_FILE does not exist."
    fi

    # Reload the systemd daemon
    echo "Reloading systemd daemon..."
    sudo systemctl daemon-reload

    echo "$SERVICE_NAME service and timer removed successfully!"
}

# Call the function to remove the service
remove_service
