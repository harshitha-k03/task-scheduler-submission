#!/bin/bash

# Absolute path to the cron.php script
CRON_PHP_PATH="$(cd "$(dirname "$0")" && pwd)/cron.php"

# Cron job command to run every hour
CRON_JOB="0 * * * * /usr/bin/php \"$CRON_PHP_PATH\" >/dev/null 2>&1"

# Check if the cron job already exists
(crontab -l 2>/dev/null | grep -F "$CRON_PHP_PATH") && EXISTS=true || EXISTS=false

if [ "$EXISTS" = false ]; then
    # Add the cron job
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    echo "Cron job added to run cron.php every hour."
else
    echo "Cron job for cron.php already exists."
fi
