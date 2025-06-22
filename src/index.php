<?php
require_once 'functions.php';

$message = '';
$tasks = getAllTasks();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task-name'])) {
        $task_name = trim($_POST['task-name']);
        if ($task_name !== '') {
            if (addTask($task_name)) {
                $message = "Task added successfully.";
            } else {
                $message = "Task already exists or could not be added.";
            }
        }
    } elseif (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (subscribeEmail($email)) {
                $message = "Verification email sent. Please check your inbox.";
            } else {
                $message = "Email already subscribed or pending verification.";
            }
        } else {
            $message = "Invalid email address.";
        }
    } elseif (isset($_POST['complete-task-id'])) {
        $task_id = $_POST['complete-task-id'];
        $is_completed = isset($_POST['task-completed']) && $_POST['task-completed'] === '1';
        if (markTaskAsCompleted($task_id, $is_completed)) {
            $message = "Task status updated.";
        } else {
            $message = "Failed to update task status.";
        }
    } elseif (isset($_POST['delete-task-id'])) {
        $task_id = $_POST['delete-task-id'];
        if (deleteTask($task_id)) {
            $message = "Task deleted.";
        } else {
            $message = "Failed to delete task.";
        }
    }
    // Refresh tasks after POST
    $tasks = getAllTasks();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Task Scheduler</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 20px 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"],
        input[type="email"] {
            width: calc(100% - 110px);
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-right: 10px;
        }
        button {
            padding: 10px 18px;
            font-size: 16px;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .tasks-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .task-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }
        .task-item.completed span.task-name {
            text-decoration: line-through;
            color: gray;
        }
        .task-status {
            margin-right: 10px;
            cursor: pointer;
        }
        .delete-task {
            background-color: #dc3545;
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 4px;
        }
        .delete-task:hover {
            background-color: #a71d2a;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Task Scheduler</h1>

        <?php if ($message !== ''): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Add Task Form -->
        <form method="POST" action="">
            <input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
            <button type="submit" id="add-task">Add Task</button>
        </form>

        <!-- Tasks List -->
        <ul id="tasks-list" class="tasks-list">
            <?php foreach ($tasks as $task): ?>
                <li class="task-item <?php echo $task['completed'] ? 'completed' : ''; ?>">
                    <form method="POST" action="" style="display:flex; align-items:center; margin:0;">
                        <input type="hidden" name="complete-task-id" value="<?php echo htmlspecialchars($task['id']); ?>">
                        <input type="checkbox" class="task-status" name="task-completed" value="1" onchange="this.form.submit()" <?php echo $task['completed'] ? 'checked' : ''; ?>>
                        <span class="task-name"><?php echo htmlspecialchars($task['name']); ?></span>
                    </form>
                    <form method="POST" action="" style="margin:0;">
                        <input type="hidden" name="delete-task-id" value="<?php echo htmlspecialchars($task['id']); ?>">
                        <button type="submit" class="delete-task">Delete</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Subscription Form -->
        <form method="POST" action="">
            <input type="email" name="email" id="email" placeholder="Enter your email to subscribe" required>
            <button id="submit-email" type="submit">Subscribe</button>
        </form>
    </div>
</body>

</html>
