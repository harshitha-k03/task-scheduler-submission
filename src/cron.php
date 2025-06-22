<?php
// src/cron.php

require_once __DIR__ . '/functions.php';

// Log start of CRON job (optional for debugging)
file_put_contents(__DIR__ . '/cron_log.txt', "[" . date('Y-m-d H:i:s') . "] CRON started\n", FILE_APPEND);

// Send task reminders to all verified subscribers
sendTaskReminders();

// Log completion (optional)
file_put_contents(__DIR__ . '/cron_log.txt', "[" . date('Y-m-d H:i:s') . "] Reminders sent\n", FILE_APPEND);
