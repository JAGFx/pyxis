#!/bin/sh
set -e
set -o pipefail

# Wait for database to be ready (optional, but recommended)
echo "Waiting for database to be ready..."
until frankenphp php-cli bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
    echo "Database is not ready yet - sleeping"
    sleep 2
done

# Dump the database into BACKUP_DIR (mounted volume), keeping the BACKUP_RETENTION most recent dumps
backup_database() {
    mkdir -p "${BACKUP_DIR}"
    backup_file="${BACKUP_DIR}/${DB_NAME:-bugr}-$(date +%Y%m%d-%H%M%S).sql.gz"

    echo "Dump de la base avant migration : ${backup_file}"
    MYSQL_PWD="${DB_PASSWORD}" mariadb-dump \
        -h "${DB_HOST:-db}" -P "${DB_PORT:-3306}" -u "${DB_USER:-root}" \
        --skip-ssl-verify-server-cert --single-transaction --no-tablespaces \
        "${DB_NAME:-bugr}" | gzip > "${backup_file}"

    ls -1t "${BACKUP_DIR}"/*.sql.gz | tail -n +$((${BACKUP_RETENTION:-10} + 1)) | xargs -r rm --
}

# Only one container must run the migrations (set RUN_MIGRATIONS=0 on the workers)
if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
    if frankenphp php-cli bin/console doctrine:migrations:up-to-date --no-interaction > /dev/null 2>&1; then
        echo "Aucune migration à exécuter."
    else
        if [ -n "${BACKUP_DIR:-}" ]; then
            backup_database
        else
            echo "BACKUP_DIR non défini : migration sans dump préalable."
        fi

        echo "Exécution des migrations Doctrine…"
        frankenphp php-cli bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
    fi
fi

echo "Application is ready!"

# Execute the main command
exec "$@"
