<?php
session_start();
include("../../HTML/LOGIN/database_config.php");

// Check if user is logged in
if (!isset($_SESSION['acc_code']) || $_SESSION['acc_position'] !== 'Cashier') {
    header('Location: ../LOGIN/loginCashier.php');
    exit;
}

// Fetch all products from database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=DaMeatUp', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT p.*, c.category_type 
                         FROM Products p 
                         LEFT JOIN Category c ON p.category_code = c.category_code 
                         ORDER BY p.prod_name");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "<script>alert('Error loading products: " . addslashes($e->getMessage()) . "');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Point of Sale</title>
    <link rel="stylesheet" href="../../CSS/CASHIER/cashier.css">
    <script src="../../JavaScript/CASHIER/categories.js"></script>
    <script src="../../JavaScript/CASHIER/logout.js"></script>
    <script src="../../JavaScript/CASHIER/sidebar.js"></script>
    <script src="../../JavaScript/CASHIER/footer.js"></script>
    <script src="../../JavaScript/CASHIER/orders.js"></script>
    <script src="../../JavaScript/CASHIER/checkout.js"></script>
    <link rel="icon" href="../../pics/logo.png" sizes="any">
</head>
<body>
    <!-- Mobile restriction message -->
    <div class="mobile-restriction">
        <img src="../../pics/logo.png" alt="Logo" class="mobile-logo">
        <h2>Desktop Only</h2>
        <p>This POS system is optimized for desktop use only. Please access it from a computer.</p>
    </div>

    <div class="container">
      <div class="Header">
        <div class="image_container">
          <img src="../../pics/logo.png" alt="Logo" id="logo">
        </div>

        <div class="text_container">
          <h1>Welcome, <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>!</h1>
          <h3>DaMeatUp POS System</h3>
        </div>

        <div class="logout_container">
          <button id="button" >
            <img src="../../pics/cashier_icons/logout.png" alt="Logout Icon" id="logout" onclick="show()">
          </button>
        </div>
      </div>

      <div class="Sidebar">
          <p class="current_orders">Current Orders</p>

          <div class="orders-section">
              <table class="orders-table">
                  <thead>
                      <tr>
                          <th>Item Name</th>
                          <th>Qty.</th>
                          <th>Price</th>
                      </tr>
                  </thead>
              </table>
              
              <div class="orders-container">
                  <table class="orders-table">
                      <tbody id="ordersTableBody">
                          <!-- Orders will be dynamically added here -->
                      </tbody>
                  </table>
              </div>
          </div>

          <div class="Sidebar-bottom">
              <div class="total">
                  <p id="TOTAL">₱0.00</p>
              </div>

              <div class="sidebarbttn-container">
                  <div class="sidebarbttn">
                      <button id="select" onclick="toggleSelection(this)">Select</button>
                  </div>

                  <div class="container_button">
                      <div class="button-wrapper">
                          <button class="buttons" onclick="editSelected()">
                              <img src="../../pics/cashier_icons/Edit.png" alt="Edit icon">
                          </button>
                          <p class="button_name">Edit</p>
                      </div>

                      <div class="button-wrapper">
                          <button class="buttons" onclick="deleteAll()">
                              <img src="../../pics/cashier_icons/Delete.png" alt="delete">
                          </button>
                          <p class="button_name">Delete All</p>
                      </div>

                      <div class="button-wrapper">
                          <button class="buttons" onclick="deleteSelected()">
                              <img src="../../pics/cashier_icons/Delete%20.png" alt="delete">
                          </button>
                          <p class="button_name">Delete</p>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <div class="Content">
          <div class="Category">
              <p>Category</p>
              <div class="items">
                  <button onclick="selectCategory(this, 'all')" class="active">All</button>
                  <button onclick="selectCategory(this, 'chicken')">Chicken</button>
                  <button onclick="selectCategory(this, 'beef')">Beef</button>
                  <button onclick="selectCategory(this, 'pork')">Pork</button>
                  <button onclick="selectCategory(this, 'processed')">Processed</button>
                  <button onclick="selectCategory(this, 'sari-sari')">Sari-sari</button>
              </div>
          </div>

        <div class="Items">
            <div class="top">
                <div class="search_bar">
                    <input type="text" id="searchInput" placeholder="Search items" onkeyup="filterProducts()">
                    <button id="searchButton">
                        <img src="../../pics/cashier_icons/search-icon.png" alt="Search">
                    </button>
                </div>
            </div>

            <div class="product_container">
                <p id="category_selected">All Products</p>
                <div class="products" id="product_list">
                    <?php foreach ($products as $product): ?>
                    <button class="productType" onclick="selectProduct(this)" 
                            data-category="<?= strtolower($product['category_type']) ?>"
                            data-price="<?= $product['prod_price'] ?>"
                            data-unit="<?= $product['stock_unit'] ?>"
                            data-code="<?= $product['prod_code'] ?>">
                        <div class="product_img">
                            <img src="../../<?= $product['image_path'] ?: 'pics/admin_icons/inventory.png' ?>" 
                                 alt="<?= htmlspecialchars($product['prod_name']) ?>" 
                                 class="img_prod">
                        </div>
                        <p class="product_name"><?= htmlspecialchars($product['prod_name']) ?></p>
                        <p class="product_price">₱<?= number_format($product['prod_price'], 2) ?></p>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

          <div class="Footer">
            <div class="footer_content">
                <button class="button_holder" onclick="metric()">
                        <p>Metric</p>
                        <p id="scale">kg</p>
                </button>

                <div class="button_holder1">
                    <div class="number_display">
                        <input type="number" id="scale_number" value="0" min="0" step="1" onchange="validateInput(this)">
                    </div>
                    <button class="minus" onclick="decrease()">
                        -
                    </button>
                    <button class="add" onclick="increase()">
                        +
                    </button>
                </div>

                <button class="button_holder" onclick="showOrderHistory()">
                    <p>Order List</p>
                    <img src="../../pics/footer_icons/orderList.png" alt="Order List">
                </button>

                <button class="button_holder" onclick="showCheckoutModal()">
                    <p>Checkout</p>
                    <span class="checkmark">✓</span>
                </button>


                <button class="button_holder2" onclick="addToCart()">
                    <p>Add Order</p>
                    <img src="../../pics/footer_icons/addOrder.png" alt="Add Order">
                </button>
            </div>
          </div>
      </div>
    </div>

    <div class="overlay" id="lc">
        <div class="logout_content">
            <p>Are you sure you want to logout?</p>
            <button id="confirmLogout" onclick="logout()">Yes</button>
            <button id="cancelLogout" onclick="hide()">No</button>
        </div>
    </div>

    <div id="overlay" class="overlay">
        <div class="modal">
            <h2 id="modalTitle"></h2>
            <div id="modalContent"></div>
        </div>
    </div>

    <div id="checkoutModal" class="overlay">
        <div class="modal checkout-modal">
            <h2>Order Summary</h2>
            <div class="checkout-content">
                <table class="checkout-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody id="checkoutTableBody">
                    </tbody>
                </table>
                <div class="checkout-total">
                    <p>Total Amount: <span id="checkoutTotal">₱0.00</span></p>
                </div>
                <div class="payment-section">
                    <div class="input-group">
                        <label for="cashAmount">Cash Amount:</label>
                        <input type="number" id="cashAmount" step="0.01" min="0">
                    </div>
                    <div class="change-amount">
                        <p>Change: <span id="changeAmount">₱0.00</span></p>
                    </div>
                </div>
                <div class="checkout-buttons">
                    <button id="processPayment" onclick="processPayment()">Process Payment</button>
                    <button id="cancelCheckout" onclick="hideCheckoutModal()">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order History Modal -->
    <div id="orderHistoryModal" class="overlay">
        <div class="modal order-history-modal">
            <h2>Order History</h2>
            <div class="order-history-controls">
                <select id="orderHistoryFilter" onchange="filterOrderHistory()">
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="all">All Orders</option>
                </select>
            </div>
            <div class="order-history-content">
                <table class="order-history-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Order ID</th>
                            <th>Total Amount</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody id="orderHistoryTableBody">
                    </tbody>
                </table>
            </div>
            <div class="modal-buttons">
                <button onclick="hideOrderHistory()">Close</button>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="overlay">
        <div class="modal order-details-modal">
            <h2>Order Details</h2>
            <div class="order-details-content">
                <div class="order-info">
                    <p>Order ID: <span id="detailOrderId"></span></p>
                    <p>Date: <span id="detailDate"></span></p>
                    <p>Cashier: <span id="detailCashier"></span></p>
                </div>
                <table class="order-details-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="orderDetailsTableBody">
                    </tbody>
                </table>
                <div class="order-summary">
                    <p>Total Amount: <span id="detailTotal"></span></p>
                    <p>Cash Amount: <span id="detailCash"></span></p>
                    <p>Change: <span id="detailChange"></span></p>
                </div>
            </div>
            <div class="modal-buttons">
                <button onclick="hideOrderDetails()">Close</button>
            </div>
        </div>
    </div>
</body>
</html>