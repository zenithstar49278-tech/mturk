<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'db.php';
    
    // Sanitize and validate input
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($type)) {
        echo "<script>alert('All fields are required!');</script>";
    } elseif (!$email) {
        echo "<script>alert('Invalid email format!');</script>";
    } elseif ($type !== 'worker' && $type !== 'requester') {
        echo "<script>alert('Invalid user type!');</script>";
    } else {
        // Check for duplicate email
        $sql = "SELECT email FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "<script>alert('Email already exists! Please use a different email.');</script>";
        } else {
            // Hash password
            $password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO users (username, email, password, type) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                echo "<script>alert('Database error: " . addslashes($conn->error) . "');</script>";
            } else {
                $stmt->bind_param("ssss", $username, $email, $password, $type);
                if ($stmt->execute()) {
                    // Auto-login
                    $user_id = $conn->insert_id;
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['type'] = $type;
                    $redirect_url = $type === 'worker' ? 'dashboard.php' : 'marketplace.php';
                    echo "<script>
                            document.getElementById('success-message').style.display = 'block';
                            setTimeout(() => { redirectTo('$redirect_url'); }, 2000);
                          </script>";
                } else {
                    echo "<script>alert('Error: " . addslashes($stmt->error) . "');</script>";
                }
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - MicroTask Platform</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: url('https://source.unsplash.com/random/1920x1080/?abstract') no-repeat center center fixed;
            background-size: cover;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            margin: 20px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .container:hover {
            transform: translateY(-5px);
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 30px;
            font-size: 2em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        input {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        input:focus {
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
        .success-message {
            display: none;
            text-align: center;
            color: #28a745;
            font-size: 1.2em;
            margin-top: 20px;
            padding: 15px;
            background: #e6f4ea;
            border-radius: 8px;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link button {
            background: linear-gradient(90deg, #6c757d, #495057);
        }
        .login-link button:hover {
            background: linear-gradient(90deg, #495057, #343a40);
        }
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 20px;
            }
            h2 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sign Up as <?php echo isset($_GET['type']) ? ucfirst($_GET['type']) : 'User'; ?></h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="hidden" name="type" value="<?php echo isset($_GET['type']) ? $_GET['type'] : ''; ?>">
            <button type="submit">Sign Up</button>
        </form>
        <div id="success-message" class="success-message">
            Sign up successful! Redirecting to your <?php echo isset($_GET['type']) ? $_GET['type'] : 'account'; ?> page...
        </div>
        <div class="login-link">
            <p>Already have an account?</p>
            <button onclick="redirectTo('login.php')">Login</button>
        </div>
    </div>
    <script>
        function redirectTo(url) {
            location.href = url;
        }
    </script>
</body>
</html>
