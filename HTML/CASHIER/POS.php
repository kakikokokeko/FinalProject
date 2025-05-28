<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cashier Point of Sale</title>
    <link rel="stylesheet" href="../../CSS/CASHIER/cashier.css">
    <script src="../../JavaScript/CASHIER/categories.js"></script>
    <script src="../../JavaScript/CASHIER/logout.js"></script>
    <script src="../../JavaScript/CASHIER/sidebar.js"></script>
    <script src="../../JavaScript/CASHIER/footer.js"></script>
    <link rel="icon" href="../../pics/logo.png" sizes="any">
</head>
<body>
<?php
include("../../HTML/LOGIN/database_config.php");
?>

    <div class="container">

      <div class="Header">

        <div class="image_container">
          <img src="../../pics/logo.png" alt="Logo" id="logo">
        </div>

        <div class="text_container">
          <h1>Welcome, <span>Employee</span>!</h1>
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

          <div class="orders-table">
              <p>Item name</p>
              <p>Qty.</p>
              <p>Price</p>

          </div>
          <hr>

          <div class="orders_container">

          </div>

          <div class="Sidebar-bottom">
              <div class="total">
                  <p id="TOTAL">TOTAL:</p>
                  <p>CASH:</p>

                  <hr>
                  <p>CHANGE:</p>
              </div>

              <div class="sidebarbttn-container">
                  <div class="sidebarbttn">
                      <button id="select" onclick="toggleSelection(this)">Select</button>
                  </div>

                  <div class="container_button">
                      <div class="button-wrapper">
                          <button class="buttons" onclick="toggleColor(this)">
                              <img src="../../pics/cashier_icons/Edit.png" alt="Edit icon">
                          </button>
                          <p class="button_name">Edit</p>
                      </div>

                      <div class="button-wrapper">
                          <button class="buttons" onclick="toggleColor(this)">
                              <img src="../../pics/cashier_icons/Delete.png" alt="delete">
                          </button>
                          <p class="button_name">Delete All</p>
                      </div>

                      <div class="button-wrapper">
                          <button class="buttons" onclick="toggleColor(this)">
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
                    <input type="text" id="searchInput" placeholder="Search items">
                    <button id="searchButton">
                        <img src="../../pics/cashier_icons/search-icon.png" alt="Search">
                    </button>
                </div>
            </div>

            <div class="product_container">
                <p id="category_selected">Select a category</p>
                <div class="products" id="product_list">
                    <button class="productType" onclick="selectProduct(this)">
                        <div class="product_img">
                            <img src="../../pics/category_products/chickenwings.jpg" alt="Chicken Wings" class="img_prod" id="pr1">
                        </div>
                        <p class="product_name" id="pn1">Chicken Wings</p>
                    </button>

                    <button class="productType" onclick="selectProduct(this)">
                        <div class="product_img">
                            <img src="../../pics/category_products/chickendrums.webp" alt="Chicken Drumsticks" class="img_prod" id="pr2">
                        </div>
                        <p class="product_name" id="pn2">Chicken Drumsticks</p>
                    </button>

                    <button class="productType" onclick="selectProduct(this)">
                        <div class="product_img">
                            <img src="../../pics/category_products/chickenneck.avif" alt="Chicken Neck" class="img_prod" id="pr3">
                        </div>
                        <p class="product_name" id="pn3">Chicken Neck</p>
                    </button>

                    <button class="productType" onclick="selectProduct(this)">
                        <div class="product_img">
                            <img src="../../pics/category_products/chickenfeet.webp" alt="Chicken Feet" class="img_prod" id="pr4">
                        </div>
                        <p class="product_name" id="pn4">Chicken Feet</p>
                    </button>

                    <button class="productType" onclick="selectProduct(this)">
                        <div class="product_img">
                            <img src="../../pics/category_products/wholechicken.webp" alt="Whole Chicken" class="img_prod" id="pr5">
                        </div>
                        <p class="product_name" id="pn5">Whole Chicken</p>
                    </button>
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
                        <p id="scale_number">0</p>
                    </div>
                    <button class="minus" onclick="decrease()">
                        -
                    </button>
                    <button class="add" onclick="increase()">
                        +
                    </button>
                </div>

                <button class="button_holder" onclick="showOverlay('priceList')">
                    <p>Price List</p>
                    <img id="pricelist" src="../../pics/footer_icons/priceList.png" alt="Price List">
                </button>

                <button class="button_holder" onclick="showOverlay('orderList')">
                    <p>Order List</p>
                    <img src="../../pics/footer_icons/orderList.png" alt="Order List">
                </button>

                <button class="button_holder2" onclick="addToCart(), calculateTotal()">
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
            <button class="close-btn" onclick="hideOverlay()">Close</button>
        </div>
    </div>


</body>
</html>