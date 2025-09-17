<!-- index.php - Homepage -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MicroTask Platform - Home</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: url('https://source.unsplash.com/random/1920x1080/?nature') no-repeat center center fixed; background-size: cover; color: #333; }
        header { background: rgba(0, 123, 255, 0.8); color: white; padding: 20px; text-align: center; }
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; background: rgba(255, 255, 255, 0.9); border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .intro { text-align: center; margin-bottom: 40px; }
        .signup-options { display: flex; justify-content: space-around; margin-bottom: 40px; }
        .option { background: #f8f9fa; padding: 20px; border-radius: 8px; width: 40%; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .option img { width: 100px; height: 100px; border-radius: 50%; margin-bottom: 10px; }
        .featured-tasks { margin-top: 40px; }
        .task { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
        @media (max-width: 768px) { .signup-options { flex-direction: column; } .option { width: 100%; margin-bottom: 20px; } }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to MicroTask Platform</h1>
        <p>Earn money by completing small tasks or post tasks for others to complete!</p>
    </header>
    <div class="container">
        <div class="intro">
            <h2>How It Works</h2>
            <p>Sign up as a Worker to complete tasks and earn money, or as a Requester to post tasks and get them done quickly.</p>
        </div>
        <div class="signup-options">
            <div class="option">
                <img src="https://source.unsplash.com/random/100x100/?worker" alt="Worker">
                <h3>Sign Up as Worker</h3>
                <button onclick="redirectTo('signup.php?type=worker')">Sign Up</button>
            </div>
            <div class="option">
                <img src="https://source.unsplash.com/random/100x100/?requester" alt="Requester">
                <h3>Sign Up as Requester</h3>
                <button onclick="redirectTo('signup.php?type=requester')">Sign Up</button>
            </div>
        </div>
        <div class="featured-tasks">
            <h2>Featured Tasks</h2>
            <?php
            include 'db.php';
            $sql = "SELECT * FROM tasks WHERE status = 'open' LIMIT 5";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<div class='task'><h3>" . $row['title'] . "</h3><p>" . $row['description'] . "</p><p>Payment: $" . $row['payment'] . "</p><p>Deadline: " . $row['deadline'] . "</p></div>";
                }
            } else {
                echo "<p>No featured tasks available.</p>";
            }
            $conn->close();
            ?>
        </div>
        <p>Already have an account? <button onclick="redirectTo('login.php')">Login</button></p>
    </div>
    <script>
        function redirectTo(url) {
            location.href = url;
        }
    </script>
</body>
</html>
