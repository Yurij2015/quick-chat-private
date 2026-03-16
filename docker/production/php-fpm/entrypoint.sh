#!/bin/sh
set -e

# Copy the initialization storage structure back to the mounted volume if it's empty
if [ -d /var/www/html/storage-init ] && [ -z "$(ls -A /var/www/html/storage 2>/dev/null)" ]; then
    echo "Initializing storage directory..."
    cp -rp /var/www/html/storage-init/. /var/www/html/storage/
fi

# Ensure correct permissions on the storage directory
# Since we are running as www-data, we can't chown, but we assume the volume is writable
# or the base image setup handled it.

exec "$@"
