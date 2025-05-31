<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login to DaMeatUp POS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="../../CSS/LOGIN/stylelogin.css">
    <link rel="icon" href="../../pics/logo.png" sizes="any">
</head>

<body>
    <?php
	include("../LOGIN/database_config.php");
	
	// Function to check if user is on mobile
	function isMobile() {
		if (empty($_SERVER['HTTP_USER_AGENT'])) {
			return false;
		}
		
		$userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$mobileKeywords = [
			'mobile', 'android', 'iphone', 'ipod', 'phone', 'tablet', 'ipad', 
			'windows phone', 'webos', 'opera mini', 'blackberry', 'samsung', 'nokia'
		];
		
		foreach ($mobileKeywords as $keyword) {
			if (strpos($userAgent, $keyword) !== false) {
				return true;
			}
		}
		
		// Also check screen width using JavaScript
		echo '<script>
			document.addEventListener("DOMContentLoaded", function() {
				if (window.innerWidth <= 768) {
					document.querySelector(".cashier-button").style.display = "none";
					document.querySelector(".mobile-notice").style.display = "block";
				}
			});
			window.addEventListener("resize", function() {
				if (window.innerWidth <= 768) {
					document.querySelector(".cashier-button").style.display = "none";
					document.querySelector(".mobile-notice").style.display = "block";
				} else {
					document.querySelector(".cashier-button").style.display = "block";
					document.querySelector(".mobile-notice").style.display = "none";
				}
			});
		</script>';
		
		return false;
	}
	
	$isMobileDevice = isMobile();
	?>
    <div class="login">
        <img class="logo" src="../../pics/logo.png" alt="Logo">
        
        <div class="loginAs">
            <h1 id="label">Welcome back!</h1>
            <p id="welcome">Please choose where to login</p>

            <div class="buttons">
                <a href="loginCashier.php" class="login-button cashier-button" <?php if ($isMobileDevice) echo 'style="display: none;"'; ?>><button class="bttn">CASHIER</button></a>
                <a href="loginAdmin.php" class="login-button"><button class="bttn">ADMIN</button></a>
            </div>
            
            <p class="mobile-notice" <?php if (!$isMobileDevice) echo 'style="display: none;"'; ?>>Cashier login is only available on desktop devices.</p>
        </div>
    </div>
</body>
</html>