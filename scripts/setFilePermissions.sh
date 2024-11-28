#!/bin/bash

cd "$(dirname "$0")"

WEB_USER="www-data"

# Define paths relative to the web project root directory
SCRIPTS_DIR="../scripts"
BACKUPS_DIR="../backups/dbs"
UPLOADS_DIR="../uploads"
LOCALES_DIR="../locales"
PROJECT_DIR="../"

# Change ownership of all files and directories to $WEB_USER
echo "Changing ownership of all files and directories in $PROJECT_DIR to $WEB_USER..."
if [ -d "$PROJECT_DIR" ]; then
    chown -R $WEB_USER:$WEB_USER "$PROJECT_DIR"
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

# Set /backups/dbs directory to 664
echo "Setting permissions for $BACKUPS_DIR to 664..."
if [ -d "$BACKUPS_DIR" ]; then
    chmod 775 "$BACKUPS_DIR"
    echo "Permissions for $BACKUPS_DIR set to 664."
else
    echo "Directory $BACKUPS_DIR does not exist."
fi

# Set /uploads/ and its subdirectories to 775 (to allow uploads)
echo "Setting permissions for $UPLOADS_DIR and its subdirectories to 775..."
if [ -d "$UPLOADS_DIR" ]; then
    chmod -R 775 "$UPLOADS_DIR"
    # Ensure $WEB_USER has write access to uploads
    chown -R $WEB_USER:$WEB_USER "$UPLOADS_DIR"
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

echo "Permission setting completed."
