<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <link rel="stylesheet" href="../../CSS/ADMIN/styleAdminInventoryLandingpage.css" />
    <link rel="stylesheet" href="../../CSS/ADMIN/styleAdminDashboard.css" />
    <script src="../../JavaScript/ADMIN/admin.js"></script>
    <title>Admin Inventory Landing Page</title>
    <link rel="icon" href="../../pics/logo.png" sizes="any">
</head>
<body>
<div class="main-container">
    <div class="header">
        <img class="logo" src="../../pics/logo.png">

        <div class="dashboard">
            <img class="dashLogo" src="../../pics/admin_icons/inventory-fill.png">
            <p id="Dashboard">Inventory</p>
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

            <div class="sidebar-item" onclick="account()">
                <img class="sidebarLogo" src="../../pics/admin_icons/account.png" alt="Accounts Icon">
                <button class="bttn">Accounts</button>
            </div>

            <div class="sidebar-itemActive" >
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
                <div class="AccTable">
                    <h1>All Products</h1>
                    <table>
                        <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                        </tr>
                        </thead>
                    </table>
                </div>

            </div>

        </div>

        <div class="sidebar-containerRight">
            <div class="search-container">
                <div class="filter-dropdown">
                    <select class="sales-filter">
                        <option>Select Category</option>
                        <option value="daily">Chicken</option>
                        <option value="weekly">Beef</option>
                        <option value="monthly">Pork</option>
                        <option value="monthly">Processed</option>
                        <option value="monthly">Sari-sari</option>
                    </select>
                </div>

                <div class="accounts_button">
                    <div class="acc-button" onclick="addProd()">
                        <a>
                            <button class="Accountbttn">Add Product</button>
                        </a>
                    </div>

                    <div class="acc-button" onclick="editProd()">
                        <a>
                            <button class="Accountbttn">Edit Product</button>
                        </a>
                    </div>

                    <div class="acc-button" onclick="deleteProd()">
                        <a>
                            <button class="Accountbttn">Delete Product</button>
                        </a>
                    </div>
                </div>


            </div>
        </div>

    </div>


</div>
</body>
</html>