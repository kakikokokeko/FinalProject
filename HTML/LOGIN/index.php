<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login to DaMeatUp POS</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initialscale=1">
    <link rel="stylesheet" href="../../CSS/LOGIN/stylelogin.css">
    <link rel="icon" href="../../pics/logo.png" sizes="any">
</head>

<body>
    <?php
	include("../LOGIN/database_config.php");
	?>
    <div class="login">
        <img class="logo" src="../../pics/logo.png" alt="Logo">
        
        <div class="loginAs">
            <h1 id="label">Welcome back!</h1>
            <p id="welcome">Please choose where to login</p>

            <div class="buttons">
                <a href="loginCashier.php"><button class="bttn">CASHIER</button></a>
                <a href="loginAdmin.php"><button class="bttn">ADMIN</button></a>
                
            </div>
        </div>
        </div>

    
</body>
</html>