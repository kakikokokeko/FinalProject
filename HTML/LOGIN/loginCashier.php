<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=DaMeatUp', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM Account WHERE username = ? AND acc_position = 'Cashier'");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['acc_code'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['position'] = $user['acc_position'];

            header('Location: ../CASHIER/POS.php');
            exit;
        } else {
            $_SESSION['login_error'] = 'Invalid username or password.';
            header('Location: loginCashier.php');
            exit;
        }
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Cashier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../../CSS/LOGIN/styleCashier.css">
    <link rel="icon" href="../../pics/logo.png" sizes="any">
    <script src="../../JavaScript/LOGIN/loginCashier.js"></script>
</head>
<body>

  <div class="main-container">
    <div class="rightBar">
        <img class="logo" src="../../pics/logo.png" alt="Company Logo">
        <p id="wrongbttn">Pressed the wrong button? <a href="loginAdmin.php" id="goTo">Go to admin</a></p>
    </div>


    <div class="left-content">
        <div class="login-form">
            <h1>Welcome back, Cashier!</h1>
            
            <form action="loginCashier.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    
                    <div class="show-password" >
                        <input type="checkbox" id="show-password" >
                        <label for="show-password">Show password</label>
                    </div>
                </div>
                
                
                
                <button type="submit" class="signin-btn">
                    LOGIN
                </button>

            
            </form>
        </div>
    </div>
    

    
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
  const passwordInput = document.getElementById('password');
  const showPasswordCheckbox = document.getElementById('show-password');

  showPasswordCheckbox.addEventListener('change', () => {
    if (showPasswordCheckbox.checked) {
      passwordInput.type = 'text';
    } else {
      passwordInput.type = 'password';
    }
  });
});
</script>

</body>
</html>