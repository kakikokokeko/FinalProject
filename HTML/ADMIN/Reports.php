<?php
session_start();
require_once '../LOGIN/database_config.php';

// Function to calculate percentage change
function calculateChange($current, $previous) {
    if ($previous == 0) return 0;
    return round((($current - $previous) / $previous) * 100, 2);
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        switch ($_GET['action']) {
            case 'get_sales_data':
                // Get sales data
                $query = "SELECT 
                    DATE_FORMAT(s.transaction_date, '%Y-%m-%d') as date,
                    s.sale_id as order_id,
                    p.prod_name as product,
                    sd.quantity,
                    p.stock_unit as unit,
                    sd.unit_price,
                    (sd.quantity * sd.unit_price) as total,
                    'Cash' as payment_method
                FROM Sales s
                JOIN SalesDetails sd ON s.sale_id = sd.sale_id
                JOIN Products p ON sd.prod_code = p.prod_code
                ORDER BY s.transaction_date DESC";

                $stmt = $conn->prepare($query);
                $stmt->execute();
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
                break;

            case 'get_filtered_sales':
                $date_filter = $_GET['date_filter'] ?? 'all';
                $category = $_GET['category'] ?? 'all';
                $payment_method = $_GET['payment_method'] ?? 'all';

                $where_clauses = array();
                $params = array();

                // Date filter
                switch($date_filter) {
                    case 'today':
                        $where_clauses[] = "DATE(s.transaction_date) = CURDATE()";
                        break;
                    case 'yesterday':
                        $where_clauses[] = "DATE(s.transaction_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                        break;
                    case 'last7days':
                        $where_clauses[] = "s.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                        break;
                    case 'last30days':
                        $where_clauses[] = "s.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                        break;
                }

                // Category filter
                if ($category !== 'all') {
                    $where_clauses[] = "p.category_code = :category";
                    $params[':category'] = $category;
                }

                $query = "SELECT 
                    DATE_FORMAT(s.transaction_date, '%Y-%m-%d') as date,
                    s.sale_id as order_id,
                    p.prod_name as product,
                    sd.quantity,
                    sd.unit_price,
                    (sd.quantity * sd.unit_price) as total,
                    'Cash' as payment_method
                FROM Sales s
                JOIN SalesDetails sd ON s.sale_id = sd.sale_id
                JOIN Products p ON sd.prod_code = p.prod_code";

                if (!empty($where_clauses)) {
                    $query .= " WHERE " . implode(" AND ", $where_clauses);
                }

                $query .= " ORDER BY s.transaction_date DESC";

                $stmt = $conn->prepare($query);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                $stmt->execute();
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
                break;

            case 'get_chart_data':
                $period = $_GET['period'] ?? 'daily';
                $query = "";

                switch ($period) {
                    case 'daily':
                        // Last 7 days daily data
                        $query = "SELECT 
                            DATE_FORMAT(dates.date, '%W') as label,
                            dates.date as full_date,
                            COALESCE(SUM(sd.quantity * sd.unit_price), 0) as total
                        FROM (
                            SELECT CURDATE() - INTERVAL (a.a) DAY as date
                            FROM (SELECT 0 as a UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) as a
                        ) dates
                        LEFT JOIN Sales s ON DATE(s.transaction_date) = dates.date
                        LEFT JOIN SalesDetails sd ON s.sale_id = sd.sale_id
                        GROUP BY dates.date
                        ORDER BY dates.date";
                        break;

                    case 'weekly':
                        // Last 12 weeks
                        $query = "SELECT 
                            CONCAT('Week ', WEEK(dates.date)) as label,
                            dates.date as full_date,
                            COALESCE(SUM(sd.quantity * sd.unit_price), 0) as total
                        FROM (
                            SELECT CURDATE() - INTERVAL (a.a * 7) DAY as date
                            FROM (SELECT 0 as a UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
                                  UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 
                                  UNION SELECT 9 UNION SELECT 10 UNION SELECT 11) as a
                        ) dates
                        LEFT JOIN Sales s ON WEEK(s.transaction_date) = WEEK(dates.date)
                        LEFT JOIN SalesDetails sd ON s.sale_id = sd.sale_id
                        GROUP BY WEEK(dates.date)
                        ORDER BY dates.date";
                        break;

                    case 'monthly':
                        // Last 12 months
                        $query = "SELECT 
                            DATE_FORMAT(dates.date, '%M %Y') as label,
                            dates.date as full_date,
                            COALESCE(SUM(sd.quantity * sd.unit_price), 0) as total
                        FROM (
                            SELECT DATE_SUB(CURDATE(), INTERVAL (a.a) MONTH) as date
                            FROM (SELECT 0 as a UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
                                  UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 
                                  UNION SELECT 9 UNION SELECT 10 UNION SELECT 11) as a
                        ) dates
                        LEFT JOIN Sales s ON MONTH(s.transaction_date) = MONTH(dates.date) 
                            AND YEAR(s.transaction_date) = YEAR(dates.date)
                        LEFT JOIN SalesDetails sd ON s.sale_id = sd.sale_id
                        GROUP BY YEAR(dates.date), MONTH(dates.date)
                        ORDER BY dates.date";
                        break;

                    case 'annual':
                        // Last 5 years
                        $query = "SELECT 
                            YEAR(dates.date) as label,
                            dates.date as full_date,
                            COALESCE(SUM(sd.quantity * sd.unit_price), 0) as total
                        FROM (
                            SELECT DATE_SUB(CURDATE(), INTERVAL (a.a) YEAR) as date
                            FROM (SELECT 0 as a UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) as a
                        ) dates
                        LEFT JOIN Sales s ON YEAR(s.transaction_date) = YEAR(dates.date)
                        LEFT JOIN SalesDetails sd ON s.sale_id = sd.sale_id
                        GROUP BY YEAR(dates.date)
                        ORDER BY dates.date";
                        break;
                }

                $stmt = $conn->prepare($query);
                $stmt->execute();
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
                break;

            case 'get_summary_data':
                // Today's sales
                $query = "SELECT 
                    COALESCE(SUM(sd.quantity * sd.unit_price), 0) as today_sales,
                    (SELECT COALESCE(SUM(sd2.quantity * sd2.unit_price), 0)
                     FROM Sales s2
                     JOIN SalesDetails sd2 ON s2.sale_id = sd2.sale_id
                     WHERE DATE(s2.transaction_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                    ) as yesterday_sales
                FROM Sales s
                JOIN SalesDetails sd ON s.sale_id = sd.sale_id
                WHERE DATE(s.transaction_date) = CURDATE()";

                $stmt = $conn->prepare($query);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $today = array(
                    'value' => $row['today_sales'],
                    'change' => calculateChange($row['today_sales'], $row['yesterday_sales'])
                );

                // Weekly sales
                $query = "SELECT 
                    (SELECT COALESCE(SUM(sd1.quantity * sd1.unit_price), 0)
                     FROM Sales s1
                     JOIN SalesDetails sd1 ON s1.sale_id = sd1.sale_id
                     WHERE s1.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    ) as this_week,
                    (SELECT COALESCE(SUM(sd2.quantity * sd2.unit_price), 0)
                     FROM Sales s2
                     JOIN SalesDetails sd2 ON s2.sale_id = sd2.sale_id
                     WHERE s2.transaction_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    ) as last_week";

                $stmt = $conn->prepare($query);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $weekly = array(
                    'value' => $row['this_week'],
                    'change' => calculateChange($row['this_week'], $row['last_week'])
                );

                // Monthly sales
                $query = "SELECT 
                    (SELECT COALESCE(SUM(sd1.quantity * sd1.unit_price), 0)
                     FROM Sales s1
                     JOIN SalesDetails sd1 ON s1.sale_id = sd1.sale_id
                     WHERE s1.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    ) as this_month,
                    (SELECT COALESCE(SUM(sd2.quantity * sd2.unit_price), 0)
                     FROM Sales s2
                     JOIN SalesDetails sd2 ON s2.sale_id = sd2.sale_id
                     WHERE s2.transaction_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    ) as last_month";

                $stmt = $conn->prepare($query);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $monthly = array(
                    'value' => $row['this_month'],
                    'change' => calculateChange($row['this_month'], $row['last_month'])
                );

                // Total orders today
                $query = "SELECT 
                    COUNT(*) as today_orders,
                    (SELECT COUNT(*)
                     FROM Sales
                     WHERE DATE(transaction_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                    ) as yesterday_orders
                FROM Sales
                WHERE DATE(transaction_date) = CURDATE()";

                $stmt = $conn->prepare($query);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $orders = array(
                    'value' => $row['today_orders'],
                    'change' => calculateChange($row['today_orders'], $row['yesterday_orders'])
                );

                echo json_encode(array(
                    'today' => $today,
                    'weekly' => $weekly,
                    'monthly' => $monthly,
                    'orders' => $orders
                ));
                break;

            case 'get_quick_summary':
                // Get filter parameters
                $date_filter = $_GET['date_filter'] ?? 'all';
                $category = $_GET['category'] ?? 'all';

                // Build date condition
                $date_condition = '';
                switch($date_filter) {
                    case 'today':
                        $date_condition = "WHERE DATE(s.transaction_date) = CURDATE()";
                        break;
                    case 'yesterday':
                        $date_condition = "WHERE DATE(s.transaction_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                        break;
                    case 'last7days':
                        $date_condition = "WHERE s.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                        break;
                    case 'last30days':
                        $date_condition = "WHERE s.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                        break;
                    default:
                        $date_condition = "";
                }

                // Add category condition if specified
                $category_condition = "";
                $params = array();
                if ($category !== 'all') {
                    if ($date_condition) {
                        $category_condition = " AND p.category_code = :category";
                    } else {
                        $category_condition = "WHERE p.category_code = :category";
                    }
                    $params[':category'] = $category;
                }

                // Total items sold
                $query = "SELECT 
                    COALESCE(SUM(sd.quantity), 0) as total_items 
                FROM SalesDetails sd
                JOIN Sales s ON sd.sale_id = s.sale_id
                JOIN Products p ON sd.prod_code = p.prod_code
                " . ($date_condition ? $date_condition : "") . "
                " . $category_condition;

                $stmt = $conn->prepare($query);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                $stmt->execute();
                $totalItems = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total_items'];

                echo json_encode(array(
                    'totalItems' => $totalItems
                ));
                break;
        }
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    
    exit;
}

// If not an AJAX request, display the HTML page
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <link rel="stylesheet" href="../../CSS/ADMIN/styleAdminReport.css" />
    <link rel="stylesheet" href="../../CSS/ADMIN/logoutModal.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Admin Reports</title>
    <link rel="icon" href="../../pics/logo.png" sizes="any">
    <script src="../../JavaScript/ADMIN/admin.js" defer></script>
    <script src="../../JavaScript/ADMIN/reports.js" defer></script>
</head>
<body>
<div class="main-container">
    <div class="header">
        <img class="logo" src="../../pics/logo.png">

        <div class="dashboard">
            <img class="dashLogo" src="../../pics/admin_icons/reports-solid.png">
            <p id="Dashboard">Reports</p>
        </div>

        <div class="profile">
            <img class="ProfLogo" src="../../pics/admin_icons/accountAdmin.png">
            <p id="Profile">Admin</p>
        </div>
    </div>

    <div class="main-content">
        <div class="sidebar-container">
            <div class="sidebar-item" onclick="window.location.href='dashboard.php'">
                <img class="sidebarLogo" src="../../pics/admin_icons/dashboard.png" alt="Dashboard Icon">
                <button class="bttn">Dashboard</button>
            </div>

            <div class="sidebar-item" onclick="window.location.href='accounts.php'">
                <img class="sidebarLogo" src="../../pics/admin_icons/account.png" alt="Accounts Icon">
                <button class="bttn">Accounts</button>
            </div>

            <div class="sidebar-item" onclick="window.location.href='inventory.php'">
                <img class="sidebarLogo" src="../../pics/admin_icons/inventory.png" alt="Inventory Icon">
                <button class="bttn">Inventory</button>
            </div>

            <div class="sidebar-itemActive">
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
            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Today's Sales</h3>
                    <p class="number">₱0.00</p>
                    <p class="change positive">+0% from yesterday</p>
                </div>
                <div class="summary-card">
                    <h3>Weekly Sales</h3>
                    <p class="number">₱0.00</p>
                    <p class="change positive">+0% from last week</p>
                </div>
                <div class="summary-card">
                    <h3>Monthly Sales</h3>
                    <p class="number">₱0.00</p>
                    <p class="change positive">+0% from last month</p>
                </div>
                <div class="summary-card">
                    <h3>Total Orders</h3>
                    <p class="number">0</p>
                    <p class="change neutral">Today</p>
                </div>
            </div>

            <!-- Sales Chart -->
            <div class="chart-container">
                <div class="chart-header">
                    <h2>Sales</h2>
                    <div class="chart-toggle">
                        <button class="chart-toggle-btn active" data-period="daily">Daily</button>
                        <button class="chart-toggle-btn" data-period="weekly">Weekly</button>
                        <button class="chart-toggle-btn" data-period="monthly">Monthly</button>
                        <button class="chart-toggle-btn" data-period="annual">Annual</button>
                    </div>
                </div>
                <canvas id="salesChart"></canvas>
            </div>

            <div class="tables">
                <div class="AccTable">
                    <div class="table-container">
                        <div class="table-header">
                            <h2>Sales History</h2>
                        </div>
                        <table id="salesTable">
                            <thead>
                                <tr>
                                    <th style="width: 15%">Date</th>
                                    <th style="width: 10%">Order ID</th>
                                    <th style="width: 35%">Product</th>
                                    <th style="width: 15%">Quantity</th>
                                    <th style="width: 10%">Unit Price</th>
                                    <th style="width: 15%">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated by DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="sidebar-containerRight">
            <div class="rectangle-container">
                <div class="filter-section">
                    <h3>Filters</h3>
                    <div class="filter-group">
                        <label>Date Range:</label>
                        <select class="date-filter">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="last7days">Last 7 Days</option>
                            <option value="last30days">Last 30 Days</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Product Category:</label>
                        <select class="category-filter">
                            <option value="all">All Categories</option>
                            <option value="CAT001">Chicken</option>
                            <option value="CAT002">Beef</option>
                            <option value="CAT003">Pork</option>
                            <option value="CAT004">Processed</option>
                            <option value="CAT005">Sari-sari</option>
                        </select>
                    </div>

                    <button class="apply-filters">Apply Filters</button>
                </div>

                <div class="summary-section">
                    <h3>Quick Summary</h3>
                    <div class="summary-item">
                        <span>Total Items Sold:</span>
                        <strong>Loading...</strong>
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

</body>
</html>