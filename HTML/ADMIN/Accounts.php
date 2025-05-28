<?php
session_start();

// PROCESS FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=DaMeatUp', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    $stmt = $pdo->query("SELECT acc_code, CONCAT(first_name, ' ', last_name) AS full_name, username FROM Account ORDER BY acc_code");
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['form_message'] = 'Error loading accounts: ' . $e->getMessage();
}

// Display message from session if exists
if (isset($_SESSION['form_message'])) {
    echo "<script>
        alert('".addslashes($_SESSION['form_message'])."');
        ".(isset($_SESSION['form_success']) && $_SESSION['form_success'] ? 
           "document.getElementById('addAccountPopup').style.display='none';" : "")."
    </script>";
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
    <script src="../../JavaScript/ADMIN/admin.js"></script>
    <title>Admin Accounts</title>
    <link rel="icon" href="../../pics/logo.png" sizes="any">
</head>
<body>
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
        </div>

        <div class="main-content">
            <div class="sidebar-container">
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
                    <a href="../LOGIN/login.html">
                        <button class="logbttn">
                            <img class="logoutlogo" src="../../pics/admin_icons/logout.png" alt="Logout Icon">
                            LOGOUT
                        </button>
                    </a>
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
                     <div id="editAccountPopup" class="popup-form" style="display: none;">
                        <div class="form-container">
                            <form id="accountForm" method="post">
                                
                            </form>
                        </div>
                     </div>
                    

                    <div class="tables">
                        <div class="AccTable">
                            <h1>All Accounts</h1>
                            <div class="scrollable-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Username</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($accounts as $account): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($account['acc_code']) ?></td>
                                            <td><?= htmlspecialchars($account['full_name']) ?></td>
                                            <td><?= htmlspecialchars($account['username']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($accounts)): ?>
                                        <tr>
                                            <td colspan="3">No accounts found</td>
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
                    <div class="search-bar">
                        <img src="../../pics/admin_icons/search.png" alt="" class="search-img">
                        <input type="text" placeholder="Search...">
                    </div>
                    <div class="searchbutton">
                        <a href=" ">
                            <button class="Searchbttn">SEARCH</button>
                        </a>
                    </div>

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

                        <div class="acc-button" onclick="deleteAccount()">
                            <a>
                                <button class="Accountbttn">Delete Account</button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>