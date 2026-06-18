<?php
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_id'] = $row['id'];
            header("Location: dashboard.php");
            exit();
        }
    }
    $stmt->close();
    
    echo "<script>alert('Invalid credentials! Please try again.'); window.history.back();</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MAGERWA </title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2>Welcome Back</h2>
            <p>Sign in to M_MOVE.</p>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-accent" style="width:100%;">Sign In</button>
            </form>
            <p style="margin-top:20px; text-align:center;"><a href="register.php" style="color:var(--accent);">Create Account</a></p>
        </div>
    </div>
</body>
</html>