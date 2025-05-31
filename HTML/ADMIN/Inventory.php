<?php
// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

// Start session
session_start();

// Handle AJAX requests first, before any HTML output
if (isset($_POST['action'])) {
    // Clear any previous output
    ob_clean();
    
    header('Content-Type: application/json');
    
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=DaMeatUp', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        switch ($_POST['action']) {
            case 'search':
                if (!isset($_POST['prod_code'])) {
                    throw new Exception('Product code is required');
                }
                
                $stmt = $pdo->prepare("SELECT p.*, c.category_type 
                                     FROM Products p 
                                     LEFT JOIN Category c ON p.category_code = c.category_code 
                                     WHERE p.prod_code = ?");
                $stmt->execute([$_POST['prod_code']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($product) {
                    echo json_encode([
                        'success' => true,
                        'data' => $product
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Product not found'
                    ]);
                }
                break;
                
            case 'delete':
                if (!isset($_POST['prod_code'])) {
                    throw new Exception('Product code is required');
                }

                // Check if product exists
                $checkStmt = $pdo->prepare("SELECT prod_code FROM Products WHERE prod_code = ?");
                $checkStmt->execute([$_POST['prod_code']]);
                
                if (!$checkStmt->fetch()) {
                    throw new Exception('Product not found');
                }

                // Start transaction
                $pdo->beginTransaction();
                
                try {
                    // First delete related records in salesdetails
                    $deleteDetailsStmt = $pdo->prepare("DELETE FROM SalesDetails WHERE prod_code = ?");
                    $deleteDetailsStmt->execute([$_POST['prod_code']]);
                    
                    // Then delete the product
                    $deleteProductStmt = $pdo->prepare("DELETE FROM Products WHERE prod_code = ?");
                    $deleteProductStmt->execute([$_POST['prod_code']]);
                    
                    // Commit the transaction
                    $pdo->commit();
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Product and related sales records deleted successfully'
                    ]);
                } catch (Exception $e) {
                    // Rollback on error
                    $pdo->rollBack();
                    throw new Exception('Failed to delete product: ' . $e->getMessage());
                }
                break;
                
            case 'update':
                // Handle product update
                if (!isset($_POST['prod_code'])) {
                    throw new Exception('Product code is required for update');
                }

                // Start building the SQL query and parameters
                $sql = "UPDATE Products SET 
                    prod_name = ?,
                    prod_price = ?,
                    stock_atty = ?,
                    stock_unit = ?,
                    category_code = ?";
                
                $params = [
                    $_POST['prod_name'],
                    $_POST['prod_price'],
                    $_POST['stock_atty'],
                    $_POST['stock_unit'],
                    $_POST['category_code']
                ];

                // Handle image upload if provided
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../../uploads/products/';
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
                    $new_filename = 'product_' . $_POST['prod_code'] . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                        $sql .= ", image_path = ?";
                        $params[] = 'uploads/products/' . $new_filename;
                    } else {
                        throw new Exception("Failed to upload image");
                    }
                }

                $sql .= " WHERE prod_code = ?";
                $params[] = $_POST['prod_code'];

                $stmt = $pdo->prepare($sql);
                $success = $stmt->execute($params);
                
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Product updated successfully' : 'Failed to update product'
                ]);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
    // End the script for AJAX requests
    exit;
}

// Regular form submission handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=DaMeatUp', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get next product code
        $pdo->beginTransaction();
        
        // Get and increment the counter for the specific category
        $category_code = $_POST['category_code'];
        $stmt = $pdo->prepare("SELECT next_value FROM ProductCounter WHERE category_code = ? FOR UPDATE");
        $stmt->execute([$category_code]);
        $next_value = $stmt->fetchColumn();
        $next_value++;
        
        // Update the counter for this category
        $updateStmt = $pdo->prepare("UPDATE ProductCounter SET next_value = ? WHERE category_code = ?");
        $updateStmt->execute([$next_value, $category_code]);
        
        // Format the product code (e.g., 1001, 2001, etc.)
        $prod_code = str_pad($next_value, 4, '0', STR_PAD_LEFT);

        // Handle image upload
        $image_path = null;
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../uploads/products/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'product_' . $prod_code . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                $image_path = 'uploads/products/' . $new_filename;
            } else {
                throw new Exception("Failed to upload image");
            }
        }

        $stmt = $pdo->prepare("INSERT INTO Products 
            (prod_code, prod_name, prod_price, stock_atty, stock_unit, category_code, image_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");

        $success = $stmt->execute([
            $prod_code,
            $_POST['prod_name'],
            $_POST['prod_price'],
            $_POST['stock_atty'],
            $_POST['stock_unit'],
            $_POST['category_code'],
            $image_path
        ]);

        if ($success) {
            $pdo->commit();
            $response['success'] = true;
            $response['message'] = 'Product added successfully! Product Code: ' . $prod_code;
        } else {
            $pdo->rollBack();
            $response['message'] = 'Failed to add product';
        }
        
    } catch(PDOException $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        $response['message'] = 'Database Error: ' . $e->getMessage();
    } catch(Exception $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        $response['message'] = $e->getMessage();
    }

    $_SESSION['form_message'] = $response['message'];
    $_SESSION['form_success'] = $response['success'];
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Store message in a data attribute instead of inline script
$has_message = isset($_SESSION['form_message']);
$message = $has_message ? $_SESSION['form_message'] : '';
$success = isset($_SESSION['form_success']) ? $_SESSION['form_success'] : false;

if ($has_message) {
    unset($_SESSION['form_message']);
    unset($_SESSION['form_success']);
}

// Fetch products from database
$products = [];
try {
    $pdo = new PDO('mysql:host=localhost;dbname=DaMeatUp', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get sort parameters
    $sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'prod_code';
    $sort_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
    
    // Validate sort column to prevent SQL injection
    $allowed_columns = ['prod_code', 'prod_name', 'prod_price', 'stock_atty'];
    if (!in_array($sort_column, $allowed_columns)) {
        $sort_column = 'prod_code';
    }
    
    // Fetch products with category information
    $stmt = $pdo->query("SELECT p.*, c.category_type 
                        FROM Products p 
                        LEFT JOIN Category c ON p.category_code = c.category_code 
                        ORDER BY $sort_column $sort_order");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch categories for filter dropdown
    $stmt = $pdo->query("SELECT * FROM Category ORDER BY category_type");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "<script>alert('Error loading products: " . addslashes($e->getMessage()) . "');</script>";
}

// Function to generate sort URL
function getSortUrl($column, $currentSort, $currentOrder) {
    $newOrder = ($column === $currentSort && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
    return "?sort=$column&order=$newOrder";
}

// Get current sort parameters
$current_sort = isset($_GET['sort']) ? $_GET['sort'] : 'prod_code';
$current_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <link rel="stylesheet" href="../../CSS/ADMIN/styleAdminInventory.css" />
    <link rel="stylesheet" href="../../CSS/ADMIN/logoutModal.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script src="../../JavaScript/ADMIN/admin.js" defer></script>
    <script src="../../JavaScript/ADMIN/adminInventory.js" defer></script>
    <title>Admin Inventory</title>
    <link rel="icon" href="../../pics/logo.png" sizes="any">
</head>
<body <?php if ($has_message): ?>data-message="<?php echo htmlspecialchars($message); ?>" data-success="<?php echo $success ? 'true' : 'false'; ?>"<?php endif; ?>>

<!-- Cropper Modal - Moved outside forms -->
<div id="cropperModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Crop Image</h2>
        <div style="max-height: 400px;">
            <img id="cropperImage" src="" alt="Image to crop">
        </div>
        <div class="button-container">
            <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
            <button type="button" class="btn-primary" onclick="saveCrop()">Save</button>
        </div>
    </div>
</div>

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
                <button class="logbttn" onclick="showLogoutModal()">
                    <img class="logoutlogo" src="../../pics/admin_icons/logout.png" alt="Logout Icon">
                    LOGOUT
                </button>
            </div>
        </div>

        <div class="content-area">
            <div class="tables">
                <div class="AccTable">
                    <h1>All Products</h1>
                    <div class="table-controls">
                        <div class="table-search">
                            <input type="text" id="productSearch" placeholder="Search products..." onkeyup="filterProducts()">
                        </div>
                        <div class="category-filter">
                            <select id="categorySelect" onchange="filterProducts()">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['category_type']) ?>">
                                    <?= htmlspecialchars($category['category_type']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="scrollable-table">
                    <table>
                        <thead>
                        <tr>
                                    <th class="sortable" onclick="window.location='<?php echo getSortUrl('prod_code', $current_sort, $current_order); ?>'">
                                        Code
                                        <?php if($current_sort === 'prod_code'): ?>
                                        <span class="sort-icon"><?php echo $current_order === 'ASC' ? '↑' : '↓'; ?></span>
                                        <?php endif; ?>
                                    </th>
                                    <th class="sortable" onclick="window.location='<?php echo getSortUrl('prod_name', $current_sort, $current_order); ?>'">
                                        Name
                                        <?php if($current_sort === 'prod_name'): ?>
                                        <span class="sort-icon"><?php echo $current_order === 'ASC' ? '↑' : '↓'; ?></span>
                                        <?php endif; ?>
                                    </th>
                                    <th class="sortable" onclick="window.location='<?php echo getSortUrl('prod_price', $current_sort, $current_order); ?>'">
                                        Price
                                        <?php if($current_sort === 'prod_price'): ?>
                                        <span class="sort-icon"><?php echo $current_order === 'ASC' ? '↑' : '↓'; ?></span>
                                        <?php endif; ?>
                                    </th>
                                    <th class="sortable" onclick="window.location='<?php echo getSortUrl('stock_atty', $current_sort, $current_order); ?>'">
                                        Stock
                                        <?php if($current_sort === 'stock_atty'): ?>
                                        <span class="sort-icon"><?php echo $current_order === 'ASC' ? '↑' : '↓'; ?></span>
                                        <?php endif; ?>
                                    </th>
                                    <th>Category</th>
                                    <th>Unit</th>
                        </tr>
                        </thead>
                            <tbody id="productTableBody">
                                <?php foreach ($products as $product): ?>
                                <tr class="clickable-row" onclick="showProductDetails('<?= htmlspecialchars($product['prod_code']) ?>')">
                                    <td><?= htmlspecialchars($product['prod_code']) ?></td>
                                    <td><?= htmlspecialchars($product['prod_name']) ?></td>
                                    <td>₱<?= number_format($product['prod_price'], 2) ?></td>
                                    <td><?= htmlspecialchars($product['stock_atty']) ?></td>
                                    <td><?= htmlspecialchars($product['category_type']) ?></td>
                                    <td><?= htmlspecialchars($product['stock_unit']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="6">No products found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                    </table>
                    </div>
                </div>

                <!-- Add Product Popup -->
                <div id="addProductPopup" class="popup-form" style="display: none;">
                    <div class="form-container">
                        <div class="popup-header">
                            <h2>Add Product</h2>
                            <button type="button" class="close-btn" onclick="closeAddProductForm()">&times;</button>
                        </div>
                        <form id="productForm" method="post" enctype="multipart/form-data">
                            <div class="form-section">
                                <div class="image-upload-container">
                                    <div class="circular-image-upload" onclick="document.getElementById('imageInput').click()">
                                        <img id="previewImage" src="../../pics/admin_icons/inventory.png" alt="Upload Image">
                                        <div class="upload-overlay">
                                            <span>Click to Upload</span>
                                        </div>
                                    </div>
                                    <input type="file" id="imageInput" name="product_image" accept="image/*" style="display: none">
                                </div>

                                <h3>Product Information</h3>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" placeholder="Product name" name="prod_name" required>
                                </div>
                                <div class="form-group">
                                    <label>Price</label>
                                    <input type="number" step="0.01" placeholder="0.00" name="prod_price" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group stock-input-group">
                                    <label>Stock Amount</label>
                                    <div class="stock-input-container">
                                        <input type="number" step="0.01" placeholder="Enter amount" name="stock_atty" required>
                                        <select name="stock_unit" required onchange="updateStockPlaceholder(this)">
                                            <option value="">Select Unit</option>
                                            <option value="kg">Kilograms (kg)</option>
                                            <option value="qty">Quantity (pcs)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="category_code" required>
                                        <option value="">Select Category</option>
                                        <option value="CAT001" data-code-prefix="1">Chicken</option>
                                        <option value="CAT002" data-code-prefix="2">Beef</option>
                                        <option value="CAT003" data-code-prefix="3">Pork</option>
                                        <option value="CAT004" data-code-prefix="4">Processed</option>
                                        <option value="CAT005" data-code-prefix="5">Sari-sari</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-primary">Add Product</button>
                                <button type="button" class="btn-secondary" onclick="closeAddProductForm()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Edit Product Popup -->
                <div id="editProductPopup" class="popup-form" onclick="closePopupOnOutsideClick(event, 'editProductPopup')">
                    <div class="form-container">
                        <div class="popup-header">
                            <h2>Edit Product</h2>
                            <button type="button" class="close-btn" onclick="closeEditProductForm()">&times;</button>
                        </div>
                        <form id="editProductForm" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update">
                            
                            <div class="form-section">
                                <h3>Product Information</h3>
                                <div class="form-group">
                                    <label>Code</label>
                                    <div class="search-code-container">
                                        <input type="text" name="prod_code" id="edit_prod_code" required pattern="\d+" title="Product code must be numbers only" placeholder="Enter product code">
                                        <button type="button" onclick="searchProduct()" class="btn-search">Search</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="edit-form-fields" style="display: none;">
                                <div class="image-upload-container">
                                    <div class="circular-image-upload" onclick="document.getElementById('edit_image_input').click()">
                                        <img id="edit_preview_image" src="../../pics/admin_icons/inventory.png" alt="Upload Image">
                                        <div class="upload-overlay">
                                            <span>Click to Change</span>
                                        </div>
                                    </div>
                                    <input type="file" id="edit_image_input" name="product_image" accept="image/*" style="display: none">
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" placeholder="Type here" name="prod_name" id="edit_prod_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Price</label>
                                        <input type="number" step="0.01" placeholder="Type here" name="prod_price" id="edit_prod_price" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Stock Amount</label>
                                        <input type="number" step="0.01" placeholder="Type here" name="stock_atty" id="edit_stock_atty" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Unit</label>
                                        <select name="stock_unit" id="edit_stock_unit" required onchange="updateStockStep(this)">
                                            <option value="">Select</option>
                                            <option value="kg">Kilograms (kg)</option>
                                            <option value="qty">Quantity (pcs)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Category</label>
                                        <select name="category_code" id="edit_category_code" required>
                                            <option value="">Select Category</option>
                                            <option value="CAT001">Chicken</option>
                                            <option value="CAT002">Beef</option>
                                            <option value="CAT003">Pork</option>
                                            <option value="CAT004">Processed</option>
                                            <option value="CAT005">Sari-sari</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn-primary">Save Changes</button>
                                    <button type="button" class="btn-secondary" onclick="closeEditProductForm()">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Product Popup -->
                <div id="deleteProductPopup" class="popup-form" onclick="closePopupOnOutsideClick(event, 'deleteProductPopup')">
                    <div class="form-container delete-form">
                        <div class="popup-header">
                            <h2>Delete Product</h2>
                            <button type="button" class="close-btn" onclick="closeDeleteProductForm()">&times;</button>
                        </div>
                        <div class="form-section" style="margin-bottom: 0;">
                            <div class="form-group">
                                <label>Enter Product Code to Delete:</label>
                                <div class="search-code-container">
                                    <input type="text" id="delete_prod_code" required pattern="\d+" title="Product code must be numbers only" placeholder="Enter product code">
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-primary" onclick="confirmDeleteProduct()">Delete Product</button>
                            <button type="button" class="btn-secondary" onclick="closeDeleteProductForm()">Cancel</button>
                        </div>
                    </div>
                </div>

                <!-- Product Details Popup -->
                <div id="productDetailsPopup" class="popup-form" onclick="closePopupOnOutsideClick(event, 'productDetailsPopup')">
                    <div class="form-container" style="height: auto;">
                        <div class="popup-header">
                            <h2>Product Details</h2>
                            <button type="button" class="close-btn" onclick="closeDetailsPopup()">&times;</button>
                        </div>
                        <div class="details-container">
                            <div class="product-image-container">
                                <img id="details_product_image" src="../../pics/admin_icons/inventory.png" alt="Product Image">
                            </div>
                            <div class="details-info">
                                <div class="details-row">
                                    <div class="details-label">Product Code:</div>
                                    <div id="details_prod_code" class="details-value"></div>
                                </div>
                                <div class="details-row">
                                    <div class="details-label">Name:</div>
                                    <div id="details_prod_name" class="details-value"></div>
                                </div>
                                <div class="details-row">
                                    <div class="details-label">Price:</div>
                                    <div id="details_prod_price" class="details-value"></div>
                                </div>
                                <div class="details-row">
                                    <div class="details-label">Stock:</div>
                                    <div id="details_stock" class="details-value"></div>
                                </div>
                                <div class="details-row">
                                    <div class="details-label">Unit:</div>
                                    <div id="details_unit" class="details-value"></div>
                                </div>
                                <div class="details-row">
                                    <div class="details-label">Category:</div>
                                    <div id="details_category" class="details-value"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="closeDetailsPopup()">Close</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="sidebar-containerRight">
            <div class="search-container">
                <div class="accounts_button">
                    <div class="acc-button">
                        <button class="Accountbttn" onclick="openAddProductForm()">
                            Add Product
                        </button>
                    </div>

                    <div class="acc-button">
                        <button class="Accountbttn" onclick="editProd()">
                            Edit Product
                        </button>
                    </div>

                    <div class="acc-button">
                        <button class="Accountbttn" onclick="deleteProd()">
                            Delete Product
                        </button>
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