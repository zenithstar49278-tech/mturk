<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['type'] !== 'worker') {
    echo "<script>location.href='login.php';</script>";
    exit;
}
include 'db.php';

// Fetch accepted and completed tasks
$sql = "SELECT t.*, ta.status, ta.id AS application_id FROM tasks t JOIN task_applications ta ON t.id = ta.task_id WHERE ta.worker_id = ? AND ta.status IN ('accepted', 'completed')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$tasks = $stmt->get_result();

// Fetch earnings
$sql = "SELECT SUM(t.payment) as total_earnings FROM tasks t JOIN task_applications ta ON t.id = ta.task_id WHERE ta.worker_id = ? AND ta.status = 'completed'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$earnings = $stmt->get_result()->fetch_assoc()['total_earnings'] ?? 0;

// Handle task completion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_task'])) {
    $application_id = intval($_POST['application_id']);
    $sql = "UPDATE task_applications SET status = 'completed' WHERE id = ? AND worker_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $application_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        echo "<script>alert('Task marked as completed!');</script>";
    } else {
        echo "<script>alert('Error: " . addslashes($stmt->error) . "');</script>";
    }
}

// Handle rating and review
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $task_id = intval($_POST['task_id']);
    $rating = intval($_POST['rating']);
    $review = htmlspecialchars(trim($_POST['review']));
    
    if ($rating < 1 || $rating > 5 || empty($review)) {
        echo "<script>alert('Invalid rating or review!');</script>";
    } else {
        $sql = "INSERT INTO task_reviews (task_id, worker_id, rating, review) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiis", $task_id, $_SESSION['user_id'], $rating, $review);
        if ($stmt->execute()) {
            echo "<script>alert('Review submitted successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . addslashes($stmt->error) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard - MicroTask Platform</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: url('https://source.unsplash.com/random/1920x1080/?dashboard') no-repeat center center fixed;
            background-size: cover;
            color: #333;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        h2, h3 {
            color: #007bff;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .earnings {
            text-align: center;
            margin: 20px 0;
            font-size: 1.2em;
        }
        .task-list {
            margin: 20px 0;
        }
        .task-card {
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }
        .task-card:hover {
            transform: translateY(-5px);
        }
        .task-card p {
            margin: 5px 0;
        }
        button {
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 8px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        button:hover {
            background: linear-gradient(90deg, #0056b3, #003d80);
            transform: scale(1.05);
        }
        .review-form {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        select, textarea {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .withdrawal {
            text-align: center;
            margin: 20px 0;
        }
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Worker Dashboard</h2>
        <div class="earnings">
            <p><strong>Total Earnings:</strong> $<?php echo number_format($earnings, 2); ?></p>
            <div class="withdrawal">
                <button onclick="alert('Withdrawal feature coming soon!')">Withdraw Earnings</button>
            </div>
        </div>
        <div class="task-list">
            <h3>Your Tasks</h3>
            <?php while ($task = $tasks->fetch_assoc()): ?>
                <div class="task-card">
                    <p><strong>Title:</strong> <?php echo htmlspecialchars($task['title']); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($task['description']); ?></p>
                    <p><strong>Category:</strong> <?php echo ucfirst($task['category']); ?></p>
                    <p><strong>Payment:</strong> $<?php echo number_format($task['payment'], 2); ?></p>
                    <p><strong>Status:</strong> <?php echo ucfirst($task['status']); ?></p>
                    <?php if ($task['status'] === 'accepted'): ?>
                        <form method="POST">
                            <input type="hidden" name="application_id" value="<?php echo $task['application_id']; ?>">
                            <input type="hidden" name="complete_task" value="1">
                            <button type="submit">Mark as Completed</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($task['status'] === 'completed'): ?>
                        <div class="review-form">
                            <h4>Rate & Review Task</h4>
                            <form method="POST">
                                <select name="rating" required>
                                    <option value="" disabled selected>Select Rating (1-5)</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                                <textarea name="review" placeholder="Write your review" required></textarea>
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <input type="hidden" name="submit_review" value="1">
                                <button type="submit">Submit Review</button>
                            </form>
                        </div>
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
