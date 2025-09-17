<?php
session_start();
include 'db.php';

// Handle task posting
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_task']) && $_SESSION['type'] === 'requester') {
    $title = htmlspecialchars(trim($_POST['title']));
    $description = htmlspecialchars(trim($_POST['description']));
    $category = trim($_POST['category']);
    $payment = floatval($_POST['payment']);
    $deadline = $_POST['deadline'];
    $requester_id = $_SESSION['user_id'];

    if (empty($title) || empty($description) || empty($category) || $payment <= 0 || empty($deadline)) {
        echo "<script>alert('All fields are required and payment must be positive!');</script>";
    } else {
        $sql = "INSERT INTO tasks (title, description, category, payment, deadline, requester_id, status) VALUES (?, ?, ?, ?, ?, ?, 'open')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdsis", $title, $description, $category, $payment, $deadline, $requester_id, $status);
        if ($stmt->execute()) {
            echo "<script>alert('Task posted successfully!'); document.getElementById('task-form').reset();</script>";
        } else {
            echo "<script>alert('Error: " . addslashes($stmt->error) . "');</script>";
        }
        $stmt->close();
    }
}

// Handle task application
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_task']) && $_SESSION['type'] === 'worker') {
    $task_id = intval($_POST['task_id']);
    $worker_id = $_SESSION['user_id'];

    $sql = "SELECT status FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    
    if ($task['status'] !== 'open') {
        echo "<script>alert('This task is no longer available!');</script>";
    } else {
        $sql = "INSERT INTO task_applications (task_id, worker_id, status) VALUES (?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $task_id, $worker_id, $status);
        if ($stmt->execute()) {
            echo "<script>alert('Applied to task successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . addslashes($stmt->error) . "');</script>";
        }
    }
    $stmt->close();
}

// Fetch tasks for display
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$sql = "SELECT t.*, u.username FROM tasks t JOIN users u ON t.requester_id = u.id WHERE t.status = 'open'";
if ($category_filter) {
    $sql .= " AND t.category = ?";
}
$stmt = $conn->prepare($sql);
if ($category_filter) {
    $stmt->bind_param("s", $category_filter);
}
$stmt->execute();
$tasks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - MicroTask Platform</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: url('https://source.unsplash.com/random/1920x1080/?marketplace') no-repeat center center fixed;
            background-size: cover;
            color: #333;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        h2 {
            text-align: center;
            color: #007bff;
            font-size: 2em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .task-form, .task-list {
            margin: 20px 0;
        }
        .task-form form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 500px;
            margin: 0 auto;
        }
        input, select, textarea {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
            outline: none;
        }
        button {
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        button:hover {
            background: linear-gradient(90deg, #0056b3, #003d80);
            transform: scale(1.05);
        }
        .task-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .task-card {
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .task-card:hover {
            transform: translateY(-5px);
        }
        .task-card h3 {
            margin: 0 0 10px;
            color: #007bff;
        }
        .task-card p {
            margin: 5px 0;
        }
        .filter {
            margin: 20px 0;
            text-align: center;
        }
        .filter select {
            width: 200px;
        }
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 15px;
            }
            .task-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Task Marketplace</h2>
        <?php if (isset($_SESSION['type']) && $_SESSION['type'] === 'requester'): ?>
            <div class="task-form">
                <h3>Post a New Task</h3>
                <form id="task-form" method="POST">
                    <input type="text" name="title" placeholder="Task Title" required>
                    <textarea name="description" placeholder="Task Description" rows="4" required></textarea>
                    <select name="category" required>
                        <option value="" disabled selected>Select Category</option>
                        <option value="data_entry">Data Entry</option>
                        <option value="surveys">Surveys</option>
                        <option value="transcription">Transcription</option>
                    </select>
                    <input type="number" name="payment" placeholder="Payment Amount ($)" step="0.01" required>
                    <input type="date" name="deadline" required>
                    <input type="hidden" name="post_task" value="1">
                    <button type="submit">Post Task</button>
                </form>
            </div>
        <?php endif; ?>
        <div class="filter">
            <form method="GET">
                <select name="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <option value="data_entry" <?php echo $category_filter === 'data_entry' ? 'selected' : ''; ?>>Data Entry</option>
                    <option value="surveys" <?php echo $category_filter === 'surveys' ? 'selected' : ''; ?>>Surveys</option>
                    <option value="transcription" <?php echo $category_filter === 'transcription' ? 'selected' : ''; ?>>Transcription</option>
                </select>
            </form>
        </div>
        <div class="task-list">
            <?php while ($task = $tasks->fetch_assoc()): ?>
                <div class="task-card">
                    <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($task['description']); ?></p>
                    <p><strong>Category:</strong> <?php echo ucfirst($task['category']); ?></p>
                    <p><strong>Payment:</strong> $<?php echo number_format($task['payment'], 2); ?></p>
                    <p><strong>Deadline:</strong> <?php echo $task['deadline']; ?></p>
                    <p><strong>Posted by:</strong> <?php echo htmlspecialchars($task['username']); ?></p>
                    <?php if (isset($_SESSION['type']) && $_SESSION['type'] === 'worker'): ?>
                        <form method="POST">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <input type="hidden" name="apply_task" value="1">
                            <button type="submit">Apply</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script>
        function redirectTo(url) {
            location.href = url;
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
