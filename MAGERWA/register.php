<?php
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $names = $_POST['names'];
    $email = $_POST['email'];
    $national_id = $_POST['national_id'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $check_stmt = $conn->prepare("SELECT id FROM admins WHERE email = ? OR national_id = ?");
    $check_stmt->bind_param("ss", $email, $national_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "<script>alert('User already exists!'); window.history.back();</script>";
        $check_stmt->close();
        exit();
    }
    $check_stmt->close();
    $stmt = $conn->prepare("INSERT INTO admins (names, email, national_id, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $names, $email, $national_id, $password);
    
    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MAGERWA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2> MAGERWA_MOVE.</h2>
            <p>Create an account to get started.</p>
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label>Full Names</label>
                    <input type="text" name="names" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>National ID</label>
                    <input type="text" name="national_id" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Sign Up.</button>
            </form>
            <p style="margin-top:20px; text-align:center;"><a href="login.php" style="color:var(--accent);">Back to Login</a></p>
        </div>
    </div>
</body>
</html>