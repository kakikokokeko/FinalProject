<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../../CSS/ADMIN/styleAdminDashboard.css" />
	<script src="../../JavaScript/ADMIN/admin.js" defer></script>
	<script src="../../JavaScript/ADMIN/dashboard.js" defer></script>
	<!-- Add Tailwind CSS from CDN -->
	<script src="https://cdn.tailwindcss.com"></script>
	<!-- Add Flowbite CSS -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet" />
	<!-- Add ApexCharts -->
	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
	<!-- Add Font Awesome for icons -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<title>Admin Dashboard</title>
	<link rel="icon" href="../../pics/logo.png" sizes="any">
	<!-- Configure Tailwind with Flowbite -->
	<script>
		tailwind.config = {
			content: [
				"./node_modules/flowbite/**/*.js"
			],
			theme: {
				extend: {
					colors: {
						primary: '#991b1b',
						secondary: '#ECDCBF',
					},
				},
			},
			plugins: [],
		}
	</script>
</head>
<body>
	<?php
	include("../../HTML/LOGIN/database_config.php");
	
	try {
		// Create database connection
		$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// Debug connection
		echo "<!-- Database connected successfully -->";

		// First, let's check if we have any sales data
		$checkQuery = "SELECT COUNT(*) as count FROM SalesDetails";
		$checkStmt = $conn->query($checkQuery);
		$salesCount = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];
		echo "<!-- Total sales records: " . $salesCount . " -->";

		// Check Products table
		$checkQuery = "SELECT COUNT(*) as count FROM Products";
		$checkStmt = $conn->query($checkQuery);
		$productsCount = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];
		echo "<!-- Total products: " . $productsCount . " -->";

		// Check Categories table
		$checkQuery = "SELECT COUNT(*) as count FROM Category";
		$checkStmt = $conn->query($checkQuery);
		$categoriesCount = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];
		echo "<!-- Total categories: " . $categoriesCount . " -->";

		// Count total accounts
		$checkQuery = "SELECT COUNT(*) as count FROM Account";
		$checkStmt = $conn->query($checkQuery);
		$accountsCount = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

		// Get total sales amount
		$salesAmountQuery = "SELECT SUM(total_amount) as total FROM Sales";
		$salesAmountStmt = $conn->query($salesAmountQuery);
		$totalSales = $salesAmountStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

		// Modified query to match the correct table and column names
		$query = "SELECT 
			COALESCE(c.category_type, 'Uncategorized') as category_name,
			SUM(sd.quantity) as total_quantity
		FROM Products p
		LEFT JOIN Category c ON p.category_code = c.category_code
		LEFT JOIN SalesDetails sd ON p.prod_code = sd.prod_code
		LEFT JOIN Sales s ON sd.sale_id = s.sale_id
		WHERE 1=1";

		// Add date filtering if dates are set
		if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
			$start_date = $_GET['start_date'];
			$query .= " AND DATE(s.transaction_date) >= :start_date";
		}
		if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
			$end_date = $_GET['end_date'];
			$query .= " AND DATE(s.transaction_date) <= :end_date";
		}

		$query .= " GROUP BY c.category_code, c.category_type
				   HAVING total_quantity > 0
				   ORDER BY total_quantity DESC";

		$stmt = $conn->prepare($query);
		
		// Bind date parameters if they exist
		if (isset($start_date)) {
			$stmt->bindParam(':start_date', $start_date);
		}
		if (isset($end_date)) {
			$stmt->bindParam(':end_date', $end_date);
		}
		
		$stmt->execute();
		
		$labels = [];
		$series = [];
		$rowCount = 0;
		
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$rowCount++;
			echo "<!-- Row " . $rowCount . ": Category=" . $row['category_name'] . ", Quantity=" . $row['total_quantity'] . " -->";
			$labels[] = $row['category_name'];
			$series[] = floatval($row['total_quantity']);
		}

		echo "<!-- Total categories with sales: " . $rowCount . " -->";
		
		if (empty($labels)) {
			echo "<!-- Warning: No data returned from query -->";
			$labels = ['No Sales Data'];
			$series = [100];
		}
		
		// Convert PHP arrays to JSON for JavaScript
		$labelsJSON = json_encode($labels);
		$seriesJSON = json_encode($series);

	} catch(PDOException $e) {
		echo "<!-- Database Error: " . $e->getMessage() . " -->";
		$labels = ['Database Error'];
		$series = [100];
		$labelsJSON = json_encode($labels);
		$seriesJSON = json_encode($series);
		$productsCount = 0;
		$accountsCount = 0;
		$totalSales = 0;
	}
	?>
<div class="main-container">
	<div class="header">
		<div class="header-left">
			<img class="logo" src="../../pics/logo.png" alt="DaMeatUp Logo">
			<div class="dashboard">
				<img class="dashLogo" src="../../pics/admin_icons/dashboard_white.png" alt="Dashboard Icon">
				<p id="Dashboard">Dashboard</p>
			</div>
		</div>

		<div class="profile">
			<img class="ProfLogo" src="../../pics/admin_icons/accountAdmin.png" alt="Admin Icon">
			<p id="Profile">Admin</p>
		</div>
		
		<!-- Mobile menu button -->
		<button id="mobile-menu-button" class="mobile-menu-button">
			<i class="fas fa-bars"></i>
		</button>
	</div>

	<div class="main-content">
		<!-- Mobile sidebar overlay -->
		<div id="sidebar-overlay" class="sidebar-overlay"></div>
		
		<div class="sidebar-container" id="sidebar">
			<!-- Close button for mobile -->
			<button id="close-sidebar" class="close-sidebar">
				<i class="fas fa-times"></i>
			</button>
			
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
				<button class="logbttn" onclick="showLogoutModal()">
					<img class="logoutlogo" src="../../pics/admin_icons/logout.png" alt="Logout Icon">
					LOGOUT
				</button>
			</div>
		</div>

		<div class="content-area">
			<!-- Dashboard Summary Cards -->
			<div class="dashboard-summary">
				<div class="summary-card">
					<div class="summary-icon">
						<i class="fas fa-users"></i>
					</div>
					<div class="summary-info">
						<h3>Total Accounts</h3>
						<p class="summary-value"><?php echo $accountsCount; ?></p>
					</div>
				</div>
				
				<div class="summary-card">
					<div class="summary-icon">
						<i class="fas fa-drumstick-bite"></i>
					</div>
					<div class="summary-info">
						<h3>Total Products</h3>
						<p class="summary-value"><?php echo $productsCount; ?></p>
					</div>
				</div>
				
				<div class="summary-card">
					<div class="summary-icon">
						<i class="fas fa-money-bill-wave"></i>
					</div>
					<div class="summary-info">
						<h3>Total Sales</h3>
						<p class="summary-value">₱<?php echo number_format($totalSales, 2); ?></p>
					</div>
				</div>
			</div>

			<!-- Chart Container -->
			<div class="chart-container">
				<div class="chart-header">
					<h5 class="chart-title">Sales by Category</h5>
					
					<!-- Filter dropdown -->
					<div class="filter-dropdown">
						<button
							id="dropdownDefaultButton"
							data-dropdown-toggle="lastDaysdropdown"
							data-dropdown-placement="bottom"
							class="filter-button"
							type="button">
						<?php 
						$filter = isset($_GET['filter']) ? $_GET['filter'] : 'last7days';
						$filterText = [
							'today' => 'Today',
							'last7days' => 'Last 7 days',
							'last30days' => 'Last 30 days',
							'last90days' => 'Last 90 days'
						];
						echo $filterText[$filter] ?? 'Last 7 days';
						?>
						<svg class="w-2.5 m-2.5 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
							<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
						</svg>
						</button>
						<div id="lastDaysdropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700">
							<ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownDefaultButton">
							<li>
								<a href="?filter=today" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white <?php echo $filter === 'today' ? 'bg-gray-100 dark:bg-gray-600' : ''; ?>">Today</a>
							</li>
							<li>
								<a href="?filter=last7days" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white <?php echo $filter === 'last7days' ? 'bg-gray-100 dark:bg-gray-600' : ''; ?>">Last 7 days</a>
							</li>
							<li>
								<a href="?filter=last30days" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white <?php echo $filter === 'last30days' ? 'bg-gray-100 dark:bg-gray-600' : ''; ?>">Last 30 days</a>
							</li>
							<li>
								<a href="?filter=last90days" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white <?php echo $filter === 'last90days' ? 'bg-gray-100 dark:bg-gray-600' : ''; ?>">Last 90 days</a>
							</li>
							</ul>
						</div>
					</div>
				</div>

				<!-- Pie Chart -->
				<div class="chart-wrapper">
					<div id="pie-chart"></div>
				</div>
			</div>

			<!-- Quick Access Buttons -->
			<div class="quick-access">
				<h2 class="section-title">Quick Access</h2>
				<div class="quick-access-grid">
					<div class="quick-access-card" onclick="account()">
						<div class="quick-access-icon">
							<img src="../../pics/admin_icons/account1.png" alt="Accounts Icon">
						</div>
						<h3>Manage Accounts</h3>
						<p>Add, edit or remove user accounts</p>
					</div>
					
					<div class="quick-access-card" onclick="inventory()">
						<div class="quick-access-icon">
							<img src="../../pics/admin_icons/meat.png" alt="Products Icon">
						</div>
						<h3>Manage Products</h3>
						<p>Update inventory and product details</p>
					</div>
					
					<div class="quick-access-card" onclick="reports()">
						<div class="quick-access-icon">
							<img src="../../pics/admin_icons/sales.png" alt="Sales Icon">
						</div>
						<h3>View Reports</h3>
						<p>Access detailed sales reports</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Logout Modal -->
<div class="overlay" id="logoutModal">
    <div class="logout-content">
        <p>Are you sure you want to logout?</p>
        <div class="logout-buttons">
            <button id="confirmLogout" onclick="confirmLogout()">Yes</button>
            <button id="cancelLogout" onclick="hideLogoutModal()">No</button>
        </div>
    </div>
</div>

<!-- Add Flowbite JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>

<!-- Initialize the chart -->
<script>
    window.addEventListener("load", function() {
        initializeChart(<?php echo $labelsJSON; ?>, <?php echo $seriesJSON; ?>);
        
        // Mobile menu functionality
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const sidebar = document.getElementById('sidebar');
        const closeSidebar = document.getElementById('close-sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        
        if(mobileMenuButton && sidebar && closeSidebar && sidebarOverlay) {
            mobileMenuButton.addEventListener('click', function() {
                sidebar.classList.add('active');
                sidebarOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
            
            closeSidebar.addEventListener('click', function() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
            
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    });
</script>
</body>
</html>