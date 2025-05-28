<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../../CSS/LOGIN/styleAdmin.css">
    <script src="../../JavaScript/LOGIN/loginAdmin.js"></script>
    <link rel="icon" href="../../pics/logo.png" sizes="any">
</head>

<body>
  <div class="main-container">
    <div class="left-content">
        <div class="login-form">
            <h1>Welcome back, Admin!</h1>

            <form>
                <div class="form-group">
                    <label for="adminUsername">Username</label>
                    <input type="text" id="adminUsername" placeholder="Enter your username">
                </div>

                <div class="form-group">
                    <label for="adminPassword">Password</label>
                    <input type="password" id="adminPassword" placeholder="Enter your password">
                    <div class="show-password">
                        <input type="checkbox" id="show-password">
                        <label for="show-password">Show password</label>
                    </div>
                </div>

                <button type="submit" class="signin-btn">LOGIN</button>

                
            </form>

        </div>
    </div>
    

    <div class="rightBar">
        <img class="logo" src="../../pics/logo.png" alt="Company Logo">
        <p id="wrongbttn">Pressed the wrong button? <a href="loginCashier.php" id="goTo">Go to cashier</a></p>
    </div>
</div>


<script src="../../JavaScript/LOGIN/loginAdmin.js"></script>
</body>
</html>