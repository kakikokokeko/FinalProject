<?php
session_start();

// Handle AJAX request for account search
if (isset($_POST['action']) && $_POST['action'] === 'search') {
    header('Content-Type: application/json');
    
    if (!isset($_POST['acc_code'])) {
        echo json_encode(['success' => false, 'message' => 'Account code is required']);
        exit;
    }

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=DaMeatUp', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM Account WHERE acc_code = ?");
        $stmt->execute([$_POST['acc_code']]);
        
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($account) {
            echo json_encode([
                'success' => true,
                'data' => $account
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Account not found'
            ]);
        }
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Handle AJAX request for account deletion
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    header('Content-Type: application/json');
    
    if (!isset($_POST['acc_code'])) {
        echo json_encode(['success' => false, 'message' => 'Account code is required']);
        exit;
    }

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=DaMeatUp', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if account exists
        $checkStmt = $pdo->prepare("SELECT acc_code FROM Account WHERE acc_code = ?");
        $checkStmt->execute([$_POST['acc_code']]);
        
        if (!$checkStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Account not found']);
            exit;
        }

        // Delete the account
        $stmt = $pdo->prepare("DELETE FROM Account WHERE acc_code = ?");
        $success = $stmt->execute([$_POST['acc_code']]);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Account deleted successfully' : 'Failed to delete account'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=DaMeatUp', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Handle account update
        if (isset($_POST['action']) && $_POST['action'] === 'update') {
            // Start building the SQL query and parameters
            $sql = "UPDATE Account SET 
                    first_name = ?,
                    last_name = ?,
                    acc_position = ?,
                    acc_address = ?,
                    gender = ?,
                    acc_contact = ?,
                    username = ?";
            
            $params = [
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['acc_position'],
                $_POST['acc_address'],
                $_POST['gender'],
                $_POST['acc_contact'],
                $_POST['username']
            ];

            // If password is provided, update it
            if (!empty($_POST['password'])) {
                $sql .= ", password = ?";
                $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            $sql .= " WHERE acc_code = ?";
            $params[] = $_POST['acc_code'];

            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute($params);

            $_SESSION['form_success'] = $success;
            $_SESSION['form_message'] = $success ? 'Account updated successfully!' : 'Failed to update account';
            
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        }
        // Handle new account creation
        else {
        if (!is_numeric($_POST['acc_code'])) {
            throw new Exception("Account Code must be a number");
        }

        $checkStmt = $pdo->prepare("SELECT acc_code FROM Account WHERE acc_code = ?");
        $checkStmt->execute([$_POST['acc_code']]);
        
        if ($checkStmt->fetch()) {
            throw new Exception("Account Code already exists");
        }

        // Optional: check if username is unique
        $usernameCheck = $pdo->prepare("SELECT username FROM Account WHERE username = ?");
        $usernameCheck->execute([$_POST['username']]);
        if ($usernameCheck->fetch()) {
            throw new Exception("Username already exists");
        }

        // Hash password
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO Account 
            (acc_code, first_name, last_name, acc_position, acc_address, gender, acc_contact, username, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $success = $stmt->execute([
            $_POST['acc_code'],
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['acc_position'],
            $_POST['acc_address'],
            $_POST['gender'],
            $_POST['acc_contact'],
            $_POST['username'],
            $hashedPassword
        ]);

        $response['success'] = $success;
        $response['message'] = $success ? 'Account added successfully!' : 'Failed to add account';
        }
        
    } catch(PDOException $e) {
        $response['message'] = 'Database Error: ' . $e->getMessage();
    } catch(Exception $e) {
        $response['message'] = $e->getMessage();
    }

    $_SESSION['form_message'] = $response['message'];
    $_SESSION['form_success'] = $response['success'];
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}


//-----------------------------------------------------------------------DISPLAY TABLE---------------------------------------------------------------------
$accounts = [];
try {
    $pdo = new PDO('mysql:host=localhost;dbname=DaMeatUp', 'root', '');
    
    // Get sort parameters
    $sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'acc_code';
    $sort_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
    
    // Validate sort column to prevent SQL injection
    $allowed_columns = ['acc_code', 'full_name', 'username', 'acc_position'];
    if (!in_array($sort_column, $allowed_columns)) {
        $sort_column = 'acc_code';
    }
    
    // Build the ORDER BY clause
    $order_clause = $sort_column;
    if ($sort_column === 'full_name') {
        $order_clause = "first_name $sort_order, last_name";
    }
    
    $stmt = $pdo->query("SELECT acc_code, CONCAT(first_name, ' ', last_name) AS full_name, username, acc_position 
                         FROM Account 
                         ORDER BY $order_clause $sort_order");
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['form_message'] = 'Error loading accounts: ' . $e->getMessage();
}

// Function to generate sort URL
function getSortUrl($column, $currentSort, $currentOrder) {
    $newOrder = ($column === $currentSort && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
    return "?sort=$column&order=$newOrder";
}

// Get current sort parameters
$current_sort = isset($_GET['sort']) ? $_GET['sort'] : 'acc_code';
$current_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Store message in a data attribute instead of inline script
$has_message = isset($_SESSION['form_message']);
$message = $has_message ? $_SESSION['form_message'] : '';
$success = isset($_SESSION['form_success']) ? $_SESSION['form_success'] : false;

if ($has_message) {
    unset($_SESSION['form_message']);
    unset($_SESSION['form_success']);
}
?>




<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <link rel="stylesheet" href="../../CSS/ADMIN/styleAdminAccounts.css" />
    <link rel="stylesheet" href="../../CSS/ADMIN/logoutModal.css" />
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../../JavaScript/ADMIN/admin.js" defer></script>
    <script src="../../JavaScript/ADMIN/adminAccounts.js" defer></script>
    <title>Admin Accounts</title>
    <link rel="icon" href="../../pics/logo.png" sizes="any">
</head>
<body <?php if ($has_message): ?>data-message="<?php echo htmlspecialchars($message); ?>" data-success="<?php echo $success ? 'true' : 'false'; ?>"<?php endif; ?>>
    <div class="main-container">
        <!-- HEADER -->
        <div class="header">
            <img class="logo" src="../../pics/logo.png">

            <div class="dashboard">
                <img class="dashLogo" src="../../pics/admin_icons/account_white.png">
                <p id="Dashboard">Accounts</p>
            </div>

            <div class="profile">
                <img class="ProfLogo" src="../../pics/admin_icons/accountAdmin.png">
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
            
            <div class="sidebar-container">
                <!-- Close button for mobile -->
                <button id="close-sidebar" class="close-sidebar">
                    <i class="fas fa-times"></i>
                </button>

                <div class="sidebar-item" onclick="dashboard()">
                    <img class="sidebarLogo" src="../../pics/admin_icons/dashboard.png" alt="Dashboard Icon">
                    <button class="bttn">Dashboard</button>
                </div>

                <div class="sidebar-itemActive">
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
                <div class="tables">
                    <!-- --------------------------------------------ADD NA POPUP------------------------------------------------------------------------ -->
                    <div id="addAccountPopup" class="popup-form" style="display: none;">
                        <div class="form-container">
                            <form id="accountForm" method="post">
                                <h2>Add Account</h2>
                                
                                <div class="form-section">
                                    <h3>Personal Information</h3>
                                    <div class="form-group">
                                        <label>Code</label>
                                        <input type="text" name="acc_code" required pattern="\d+" title="Account code must be numbers only" placeholder="ex. 0012">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" placeholder="Type here" name="first_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" placeholder="Type here" name="acc_address" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" placeholder="Type here" name="last_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Contact Number</label>
                                        <input type="tel" placeholder="Type here" name="acc_contact" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Username</label>
                                        <input type="text" placeholder="Type here" name="username" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="tel" placeholder="Type here" name="password" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Gender</label>
                                        <select name="gender" required>
                                            <option value="">Select</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Position</label>
                                        <select name="acc_position" required>
                                            <option value="">Select</option>
                                            <option value="Cashier">Cashier</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn-primary">Add Account</button>
                                    <button type="button" class="btn-secondary" onclick="closeAddAccountForm()">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>


                     <!-- --------------------------------------------EDIT NA POPUP------------------------------------------------------------------------ -->
                     <div id="editAccountPopup" class="popup-form" onclick="closePopupOnOutsideClick(event, 'editAccountPopup')">
                        <div class="form-container">
                            <div class="popup-header">
                                <h2>Edit Account</h2>
                                <button type="button" class="close-btn" onclick="closeEditAccountForm()">&times;</button>
                            </div>
                            <form id="editAccountForm" method="post" action="Accounts.php">
                                <input type="hidden" name="action" value="update">
                                
                                <div class="form-section">
                                    <h3>Personal Information</h3>
                                    <div class="form-group">
                                        <label>Code</label>
                                        <div class="search-code-container">
                                            <input type="text" name="acc_code" id="edit_acc_code" required pattern="\d+" title="Account code must be numbers only" placeholder="Enter account code">
                                            <button type="button" onclick="searchAccount()" class="btn-search">Search</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="edit-form-fields" style="display: none;">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>First Name</label>
                                            <input type="text" placeholder="Type here" name="first_name" id="edit_first_name" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Address</label>
                                            <input type="text" placeholder="Type here" name="acc_address" id="edit_acc_address" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Last Name</label>
                                            <input type="text" placeholder="Type here" name="last_name" id="edit_last_name" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Contact Number</label>
                                            <input type="tel" placeholder="Type here" name="acc_contact" id="edit_acc_contact" required>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Username</label>
                                            <input type="text" placeholder="Type here" name="username" id="edit_username" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="password" placeholder="Leave blank to keep current password" name="password" id="edit_password">
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Gender</label>
                                            <select name="gender" id="edit_gender" required>
                                                <option value="">Select</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Position</label>
                                            <select name="acc_position" id="edit_acc_position" required>
                                                <option value="">Select</option>
                                                <option value="Cashier">Cashier</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn-primary">Save Changes</button>
                                        <button type="button" class="btn-secondary" onclick="closeEditAccountForm()">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                     </div>
                    

                    <!-- --------------------------------------------DELETE NA POPUP------------------------------------------------------------------------ -->
                    <div id="deleteAccountPopup" class="popup-form" onclick="closePopupOnOutsideClick(event, 'deleteAccountPopup')">
                        <div class="form-container delete-form">
                            <div class="popup-header">
                                <h2>Delete Account</h2>
                                <button type="button" class="close-btn" onclick="closeDeleteAccountForm()">&times;</button>
                            </div>
                            <div class="form-section" style="margin-bottom: 0;">
                                <div class="form-group">
                                    <label>Enter Account Code to Delete:</label>
                                    <div class="search-code-container">
                                        <input type="text" id="delete_acc_code" required pattern="\d+" title="Account code must be numbers only" placeholder="Enter account code">
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-primary" onclick="confirmDelete()">Delete Account</button>
                                <button type="button" class="btn-secondary" onclick="closeDeleteAccountForm()">Cancel</button>
                            </div>
                        </div>
                     </div>

                    <!-- --------------------------------------------DETAILS NA POPUP------------------------------------------------------------------------ -->
                    <div id="accountDetailsPopup" class="popup-form" onclick="closePopupOnOutsideClick(event, 'accountDetailsPopup')">
                        <div class="form-container" style="height: auto;">
                            <div class="popup-header">
                                <h2>Account Details</h2>
                                <button type="button" class="close-btn" onclick="closeDetailsPopup()">&times;</button>
                            </div>
                            <div class="details-container">
                                <div class="details-row">
                                    <div class="details-label">Account Code:</div>
                                    <div id="details_acc_code" class="details-value"></div>
                                </div>
                                <div class="details-row">
                                    <div class="details-label">Full Name:</div>
                                    <div id="details_full_name" class="details-value"></div>
                                </div>
                                <div class="details-row">
                                    <div class="details-label">Position:</div>
                                    <div id="details_position" class="details-value"></div>
                                </div>
                                <div class="details-row">
                                    <div class="details-label">Address:</div>
                                    <div id="details_address" class="details-value"></div>
                                </div>
                                <div class="details-row">
                                    <div class="details-label">Gender:</div>
                                    <div id="details_gender" class="details-value"></div>
                                </div>
                                <div class="details-row">
                                    <div class="details-label">Contact:</div>
                                    <div id="details_contact" class="details-value"></div>
                                </div>
                                <div class="details-row">
                                    <div class="details-label">Username:</div>
                                    <div id="details_username" class="details-value"></div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-primary" onclick="closeDetailsPopup(); setTimeout(() => { openEditAccountForm(); document.getElementById('edit_acc_code').value = document.getElementById('details_acc_code').textContent; searchAccount(); }, 100);">Reset Password</button>
                                <button type="button" class="btn-secondary" onclick="closeDetailsPopup()">Close</button>
                            </div>
                        </div>
                    </div>

                    <div class="tables">
                        <div class="AccTable">
                            <h1>All Accounts</h1>
                            <div class="table-controls">
                                <div class="table-search">
                                    <input type="text" id="tableSearch" placeholder="Search in table..." onkeyup="filterTable()">
                                </div>
                            </div>
                            <div class="scrollable-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="sortable" onclick="window.location='<?php echo getSortUrl('acc_code', $current_sort, $current_order); ?>'">
                                                Code
                                                <?php if($current_sort === 'acc_code'): ?>
                                                <span class="sort-icon"><?php echo $current_order === 'ASC' ? '↑' : '↓'; ?></span>
                                                <?php endif; ?>
                                            </th>
                                            <th class="sortable" onclick="window.location='<?php echo getSortUrl('full_name', $current_sort, $current_order); ?>'">
                                                Name
                                                <?php if($current_sort === 'full_name'): ?>
                                                <span class="sort-icon"><?php echo $current_order === 'ASC' ? '↑' : '↓'; ?></span>
                                                <?php endif; ?>
                                            </th>
                                            <th class="sortable" onclick="window.location='<?php echo getSortUrl('username', $current_sort, $current_order); ?>'">
                                                Username
                                                <?php if($current_sort === 'username'): ?>
                                                <span class="sort-icon"><?php echo $current_order === 'ASC' ? '↑' : '↓'; ?></span>
                                                <?php endif; ?>
                                            </th>
                                            <th class="sortable" onclick="window.location='<?php echo getSortUrl('acc_position', $current_sort, $current_order); ?>'">
                                                Position
                                                <?php if($current_sort === 'acc_position'): ?>
                                                <span class="sort-icon"><?php echo $current_order === 'ASC' ? '↑' : '↓'; ?></span>
                                                <?php endif; ?>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="accountTableBody">
                                        <?php foreach ($accounts as $account): ?>
                                        <tr class="clickable-row" onclick="showAccountDetails('<?= htmlspecialchars($account['acc_code']) ?>')">
                                            <td><?= htmlspecialchars($account['acc_code']) ?></td>
                                            <td><?= htmlspecialchars($account['full_name']) ?></td>
                                            <td><?= htmlspecialchars($account['username']) ?></td>
                                            <td><?= htmlspecialchars($account['acc_position']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($accounts)): ?>
                                        <tr>
                                            <td colspan="4">No accounts found</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sidebar-containerRight">
                <div class="search-container">
                    

                    <div class="accounts_button">
                        <div class="acc-button" onclick="openAddAccountForm()">
                            <a>
                                <button class="Accountbttn">
                                    Add Account
                                </button>
                            </a>
                        </div>

                        <div class="acc-button" onclick="openEditAccountForm()">
                            <a>
                                <button class="Accountbttn">Edit Account</button>
                            </a>
                        </div>

                        <div class="acc-button" onclick="openDeleteAccountForm()">
                            <a>
                                <button class="Accountbttn">Delete Account</button>
                            </a>
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