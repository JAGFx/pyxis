#!/bin/sh
set -e

# Wait for database to be ready (optional, but recommended)
echo "Waiting for database to be ready..."
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
    echo "Database is not ready yet - sleeping"
    sleep 2
done

echo "Database is ready!"

# Run migrations
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "Application is ready!"

# Execute the main command
exec "$@"
