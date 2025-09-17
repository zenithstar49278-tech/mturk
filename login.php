<!-- login.php - Login Page -->
<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'db.php';
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['type'] = $user['type'];
            echo "<script>redirectTo('" . ($user['type'] == 'worker' ? 'dashboard.php' : 'marketplace.php') . "');</script>";
        } else {
            echo "<script>alert('Invalid password.');</script>";
        }
    } else {
        echo "<script>alert('User not found.');</script>";
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: url('https://source.unsplash.com/random/1920x1080/?technology') no-repeat center center fixed; background-size: cover; color: #333; }
        .container { max-width: 600px; margin: 100px auto; padding: 20px; background: rgba(255, 255, 255, 0.9); border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        form { display: flex; flex-direction: column; }
        input { margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #007bff; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
        @media (max-width: 768px) { .container { margin: 50px auto; padding: 15px; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <button onclick="redirectTo('index.php')">Sign Up</button></p>
    </div>
    <script>
        function redirectTo(url) {
            location.href = url;
        }
    </script>
</body>
</html>
