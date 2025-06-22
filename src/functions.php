<?php

function readJsonFile(string $file, $default = []) {
    if (!file_exists($file)) return $default;
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    return is_array($data) ? $data : $default;
}

function writeJsonFile(string $file, $data): bool {
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT)) !== false;
}

function addTask(string $task_name): bool {
    $file = __DIR__ . '/tasks.txt';
    $tasks = readJsonFile($file, []);

    foreach ($tasks as $task) {
        if (strcasecmp($task['name'], $task_name) === 0) return false; // Duplicate
    }

    $tasks[] = [
        'id' => uniqid('', true),
        'name' => $task_name,
        'completed' => false
    ];

    $result = writeJsonFile($file, $tasks);

    // Debug log for task addition
    $log_message = "[" . date('Y-m-d H:i:s') . "] addTask called for task: $task_name, write result: " . ($result ? 'success' : 'failure') . "\n";
    file_put_contents(__DIR__ . '/task_add.log', $log_message, FILE_APPEND);

    return $result;
}

function getAllTasks(): array {
    $file = __DIR__ . '/tasks.txt';
    return readJsonFile($file, []);
}

function markTaskAsCompleted(string $task_id, bool $is_completed): bool {
    $file = __DIR__ . '/tasks.txt';
    $tasks = readJsonFile($file, []);

    foreach ($tasks as &$task) {
        if ($task['id'] === $task_id) {
            $task['completed'] = $is_completed;
            return writeJsonFile($file, $tasks);
        }
    }
    return false;
}

function deleteTask(string $task_id): bool {
    $file = __DIR__ . '/tasks.txt';
    $tasks = readJsonFile($file, []);
    $filtered = array_filter($tasks, fn($t) => $t['id'] !== $task_id);
    return writeJsonFile($file, array_values($filtered));
}

function generateVerificationCode(): string {
    return str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

define('BASE_URL', getenv('BASE_URL') ?: 'http://127.0.0.1:8000'); // Change this to your accessible domain or IP and port

function subscribeEmail(string $email): bool {
    $file = __DIR__ . '/pending_subscriptions.txt';
    $pending = readJsonFile($file, []);

    $code = generateVerificationCode();
    $pending[$email] = [
        'code' => $code,
        'timestamp' => time()
    ];
    writeJsonFile($file, $pending);

    $link = BASE_URL . '/src/verify.php?email=' . urlencode($email) . '&code=' . $code;
    $subject = 'Verify subscription to Task Planner';
    $body = "<p>Click the link below to verify your subscription to Task Planner:</p><p><a id='verification-link' href='$link'>Verify Subscription</a></p>";

    return mail($email, $subject, $body, "From: no-reply@example.com\r\nContent-Type: text/html; charset=UTF-8");
}

function verifySubscription(string $email, string $code): bool {
    $pending_file = __DIR__ . '/pending_subscriptions.txt';
    $subscribers_file = __DIR__ . '/subscribers.txt';

    $pending = readJsonFile($pending_file, []);
    if (!isset($pending[$email]) || $pending[$email]['code'] !== $code) return false;

    $subscribers = readJsonFile($subscribers_file, []);
    if (!in_array($email, $subscribers)) {
        $subscribers[] = $email;
    }

    unset($pending[$email]);
    return writeJsonFile($pending_file, $pending) && writeJsonFile($subscribers_file, $subscribers);
}

function unsubscribeEmail(string $email): bool {
    $subscribers_file = __DIR__ . '/subscribers.txt';
    $subscribers = readJsonFile($subscribers_file, []);
    $subscribers = array_values(array_filter($subscribers, fn($e) => $e !== $email));
    return writeJsonFile($subscribers_file, $subscribers);
}

function sendTaskReminders(): void {
    $subscribers_file = __DIR__ . '/subscribers.txt';
    $tasks_file = __DIR__ . '/tasks.txt';

    $subscribers = readJsonFile($subscribers_file, []);
    $tasks = readJsonFile($tasks_file, []);
    $pending = array_filter($tasks, fn($task) => !$task['completed']);

    foreach ($subscribers as $email) {
        sendTaskEmail($email, $pending);
    }
}

function sendTaskEmail(string $email, array $pending_tasks): bool {
    $subject = 'Task Planner - Pending Tasks Reminder';

    $list_items = '';
    foreach ($pending_tasks as $task) {
        $list_items .= '<li>' . htmlspecialchars($task['name']) . '</li>';
    }

    $unsubscribe_link = BASE_URL . '/src/unsubscribe.php?email=' . urlencode(base64_encode($email));

    $body = "<h2>Pending Tasks Reminder</h2>
            <p>Here are the current pending tasks:</p>
            <ul>$list_items</ul>
            <p><a id='unsubscribe-link' href='$unsubscribe_link'>Unsubscribe from notifications</a></p>";

    return mail($email, $subject, $body, "From: no-reply@example.com\r\nContent-Type: text/html; charset=UTF-8");
}
