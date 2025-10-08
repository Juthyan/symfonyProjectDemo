#!/bin/sh
set -e

# This script is executed at container startup.
# It ensures migrations are run before starting the FPM server,
# and includes a retry mechanism for database connectivity issues (exit 255 fix).

# 1. Check and Execute Migrations (Robust)
if [ -n "$DATABASE_URL" ]; then
    echo "Checking for pending database migrations..."
    
    MAX_RETRIES=5
    RETRY_COUNT=0
    
    # Retry loop to wait for the database connection to be established.
    # CRITICAL FIX: We explicitly set --env=prod to avoid loading dev bundles (like DebugBundle) 
    # which are missing in a production Docker build.
    until php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=prod || [ $RETRY_COUNT -ge $MAX_RETRIES ]
    do
        RETRY_COUNT=$((RETRY_COUNT + 1))
        echo "Database connection failed, retrying in 5 seconds... (Attempt $RETRY_COUNT/$MAX_RETRIES)"
        sleep 5
    done
    
    if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
        echo "ERROR: Database connection failed after $MAX_RETRIES attempts. Exiting."
        exit 1
    fi
fi

# 2. Clear and Warm up the Cache
# The cache was already managed in the build stage, but we warm it up for certainty.
echo "Warming up production cache..."
php bin/console cache:warmup --env=prod --no-debug

# 3. Start the main process (CRITICAL)
# 'exec "$@"' replaces the current shell with the command passed to the container (CMD: ["php-fpm"]),
# which allows Docker to track it as the main process, keeping the container alive.
echo "Starting PHP-FPM..."
exec "$@"
