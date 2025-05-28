<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="initial-scale=1, width=device-width">
	<link rel="stylesheet" href="../../CSS/ADMIN/styleAdminDashboard.css" />
	<script src="../../JavaScript/ADMIN/admin.js"></script>
	<title>Admin Dashboard</title>
	<link rel="icon" href="../../pics/logo.png" sizes="any">
</head>
<body>
	<?php
	include("../../HTML/LOGIN/database_config.php");
	?>
<div class="main-container">
	<div class="header">
		<img class="logo" src="../../pics/logo.png">

		<div class="dashboard">
			<img class="dashLogo" src="../../pics/admin_icons/dashboard_white.png">
			<p id="Dashboard">Dashboard</p>
		</div>

		<div class="profile">
			<img class="ProfLogo" src="../../pics/admin_icons/accountAdmin.png">
			<p id="Profile">Admin</p>
		</div>
	</div>

	<div class="main-content">
		<div class="sidebar-container">
			<div class="sidebar-itemActive">
				<img class="sidebarLogo" src="../../pics/admin_icons/dashboard.png" alt="Dashboard Icon">
				<button class="bttn">Dashboard</button>
			</div>

			<div class="sidebar-item" onclick="account()">
				<img class="sidebarLogo" src="../../pics/admin_icons/account.png" alt="Accounts Icon">
				<button class="bttn">Accounts</button>
			</div>

			<div class="sidebar-item" onclick="inventory()">
				<img class="sidebarLogo" src="../../pics/admin_icons/inventory.png" alt="Inventory Icon">
				<button class="bttn">Inventory</button>
			</div>

			<div class="sidebar-item" onclick="reports()">
				<img class="sidebarLogo" src="../../pics/admin_icons/reports.png" alt="Reports Icon">
				<button class="bttn">Reports</button>
			</div>

			<div class="logoutbutton">
				<a href="../../HTML/LOGIN/login.html">
					<button class="logbttn">
						<img class="logoutlogo" src="../../pics/admin_icons/logout.png" alt="Logout Icon">
						LOGOUT
					</button>
				</a>
			</div>
		</div>

		<div class="content-area">
			<div class="dashContent">
				<div class="rectangle-div">
					<img class="accLogo" src="../../pics/admin_icons/account1.png" alt="Accounts Icon">
					<p class="info">Accounts</p>
				</div>

				<div class="rectangle-div">
					<img class="accLogo" src="../../pics/admin_icons/meat.png" alt="Products Icon">
					<p class="info">Products</p>
				</div>

				<div class="rectangle-div">
					<img class="accLogo" src="../../pics/admin_icons/sales.png" alt="Sales Icon">
					<p class="info">Sales</p>
				</div>
			</div>

			<div class="tables">
				<div class="AccTable">
					<h1>Accounts</h1>
					<table>
						<thead>
						<tr>
							<th>Code</th>
							<th>Name</th>
							<th>Position</th>
						</tr>
						</thead>
					</table>
				</div>

				<div class="AccTable">
					<h1>Products</h1>
					<table>
						<thead>
						<tr>
							<th>Name</th>
							<th>Quantity</th>
							<th>Price</th>
						</tr>
						</thead>
					</table>
				</div>
				
			</div>

		</div>

	</div>
</div>
</body>
</html>