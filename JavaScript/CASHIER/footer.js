let count = 0;
function increase() {
    count++;
    document.getElementById("scale_number").textContent = count;
}

function decrease() {
    if (count > 0) {
        count--;
    }
    document.getElementById("scale_number").textContent = count;
}

function metric() {
    const selectedProduct = document.querySelector('.productType.selected');
    const scaleElement = document.getElementById('scale');
    const currentMetric = scaleElement.textContent;
    
    if (selectedProduct) {
        const unit = selectedProduct.getAttribute('data-unit');
        if (unit === 'qty') {
            // For quantity-based products, only use qty
            scaleElement.textContent = 'qty';
        } else if (unit === 'kg') {
            // For weight-based products, only cycle between kg and g
            switch (currentMetric) {
                case 'kg':
                    scaleElement.textContent = 'g';
                    break;
                case 'g':
                    scaleElement.textContent = 'kg';
                    break;
                default:
                    scaleElement.textContent = 'kg';
            }
        }
    } else {
        // If no product selected, only cycle between kg and g
        switch (currentMetric) {
            case 'kg':
                scaleElement.textContent = 'g';
                break;
            case 'g':
                scaleElement.textContent = 'kg';
                break;
            default:
                scaleElement.textContent = 'kg';
        }
    }
}

function selectProduct(button) {
    // Remove selected class from all products
    const products = document.querySelectorAll('.productType');
    products.forEach(prod => prod.classList.remove('selected'));
    
    // Add selected class to clicked product
    button.classList.add('selected');
    
    // Update metric button based on product unit
    const unit = button.getAttribute('data-unit');
    const scaleElement = document.getElementById('scale');
    
    // Always set to 'qty' for quantity-based products, 'kg' for weight-based products
    scaleElement.textContent = unit === 'qty' ? 'qty' : 'kg';
}

function addToCart() {
    const selectedProduct = document.querySelector('.productType.selected');
    if (!selectedProduct) {
        alert('Please select a product first!');
        return;
    }
    
    const quantity = parseFloat(document.getElementById('scale_number').textContent);
    if (quantity <= 0) {
        alert('Quantity must be at least 1!');
        return;
    }

    const productName = selectedProduct.querySelector('.product_name').textContent;
    const basePrice = parseFloat(selectedProduct.getAttribute('data-price'));
    const unit = selectedProduct.getAttribute('data-unit');
    const scaleType = document.getElementById('scale').textContent;
    
    let finalQuantity = quantity;
    let displayUnit = unit;
    let pricePerUnit = basePrice;

    // Handle unit conversion if needed
    if (unit === 'kg') {
        if (scaleType === 'g') {
            finalQuantity = quantity / 1000; // Convert grams to kilograms
            displayUnit = 'g';
            pricePerUnit = basePrice / 1000; // Price per gram
        } else if (scaleType === 'qty') {
            displayUnit = 'pcs';
            pricePerUnit = basePrice; // Price per piece
        } else {
            displayUnit = 'kg';
            pricePerUnit = basePrice; // Price per kg
        }
    } else {
        displayUnit = 'pcs'; // For quantity items
        pricePerUnit = basePrice; // Price per piece
    }

    const totalPrice = pricePerUnit * quantity;

    const ordersContainer = document.querySelector('.orders_container');
    const orderDiv = document.createElement('div');
    orderDiv.className = 'order-item';
    orderDiv.innerHTML = `
        <p><strong>${productName}</strong></p>
        <p>${quantity} ${displayUnit}</p>
        <p>₱${pricePerUnit.toFixed(2)} per ${displayUnit}</p>
        <p>₱${totalPrice.toFixed(2)}</p>
    `;

    ordersContainer.appendChild(orderDiv);
    calculateTotal();

    // Reset selection and quantity
    selectedProduct.classList.remove('selected');
    document.getElementById('scale_number').textContent = '0';
    count = 0; // Reset the counter
}

function calculateTotal() {
    const orders = document.querySelectorAll('.order-item');
    let total = 0;
    
    orders.forEach(order => {
        const price = parseFloat(order.querySelector('p:last-child').textContent.replace('₱', ''));
        total += price;
    });
    
    document.getElementById('TOTAL').textContent = `TOTAL: ₱${total.toFixed(2)}`;
}

function showOverlay(type) {
    let overlay = document.getElementById("overlay");
    let modalTitle = document.getElementById("modalTitle");
    let modalContent = document.getElementById("modalContent");

    if (type === "priceList") {
        modalTitle.textContent = "Price List";
        
        // Create controls
        let controlsHTML = `
            <div class="price-list-controls" style="margin-bottom: 15px;">
                <div class="price-list-search">
                    <input type="text" id="priceListSearch" placeholder="Search products..." onkeyup="filterPriceList()">
                </div>
                <div class="price-list-category">
                    <select id="categoryFilter" onchange="filterPriceList()">
                        <option value="all">All Products</option>
                        <option value="chicken">Chicken</option>
                        <option value="beef">Beef</option>
                        <option value="pork">Pork</option>
                        <option value="processed">Processed</option>
                        <option value="sari-sari">Sari-sari</option>
                    </select>
                </div>
            </div>
        `;
        
        // Create table structure with fixed height and sticky header
        let tableHTML = `
            <div style="
                max-height: 350px;
                overflow-y: auto;
                border: 1px solid #ddd;
                border-radius: 5px;
            ">
                <table class="price-list-table" style="width: 100%; border-collapse: collapse;">
                    <thead style="
                        position: sticky;
                        top: 0;
                        background: #2c3e50;
                        color: white;
                        z-index: 1;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    ">
                        <tr>
                            <th style="padding: 15px; text-align: left; border-bottom: 2px solid #34495e; font-weight: bold;">Product Name</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 2px solid #34495e; font-weight: bold;">Category</th>
                            <th style="padding: 15px; text-align: right; border-bottom: 2px solid #34495e; font-weight: bold;">Price</th>
                            <th style="padding: 15px; text-align: center; border-bottom: 2px solid #34495e; font-weight: bold;">Unit</th>
                        </tr>
                    </thead>
                    <tbody id="priceListTableBody" style="background: white;">
        `;
        
        // Add products to table
        const products = document.querySelectorAll('.productType');
        const productArray = Array.from(products);
        productArray.sort((a, b) => {
            const nameA = a.querySelector('.product_name').textContent.toLowerCase();
            const nameB = b.querySelector('.product_name').textContent.toLowerCase();
            return nameA.localeCompare(nameB);
        });
        
        productArray.forEach(product => {
            const name = product.querySelector('.product_name').textContent;
            const price = product.getAttribute('data-price');
            const category = product.getAttribute('data-category');
            const unit = product.getAttribute('data-unit');
            
            tableHTML += `
                <tr data-category="${category}" style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; text-align: left;">${name}</td>
                    <td style="padding: 12px; text-align: left;">${category.charAt(0).toUpperCase() + category.slice(1)}</td>
                    <td style="padding: 12px; text-align: right;">₱${parseFloat(price).toFixed(2)}</td>
                    <td style="padding: 12px; text-align: center;">${unit}</td>
                </tr>
            `;
        });
        
        tableHTML += `
                    </tbody>
                </table>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <button onclick="hideOverlay()" style="
                    background: #4CAF50;
                    color: white;
                    border: none;
                    padding: 10px 30px;
                    border-radius: 5px;
                    font-size: 16px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                " onmouseover="this.style.background='#45a049'; this.style.transform='translateY(-2px)';"
                  onmouseout="this.style.background='#4CAF50'; this.style.transform='translateY(0)';">
                    Close
                </button>
            </div>
        `;
        
        modalContent.innerHTML = controlsHTML + tableHTML;
    } else if (type === "orderList") {
        modalTitle.textContent = "Order List";
        modalContent.innerHTML = `
            <div class="orders-list-content">
                ${document.querySelector(".orders_container").innerHTML || "<p>No orders yet!</p>"}
                <div style="text-align: center; margin-top: 20px;">
                    <button onclick="hideOverlay()" style="
                        background: #4CAF50;
                        color: white;
                        border: none;
                        padding: 10px 30px;
                        border-radius: 5px;
                        font-size: 16px;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                    " onmouseover="this.style.background='#45a049'; this.style.transform='translateY(-2px)';"
                      onmouseout="this.style.background='#4CAF50'; this.style.transform='translateY(0)';">
                        Close
                    </button>
                </div>
            </div>
        `;
    }

    overlay.style.display = "flex";
    
    // Add click event listener for outside click
    overlay.onclick = function(event) {
        if (event.target === overlay) {
            hideOverlay();
        }
    };
}

function filterPriceList() {
    const searchText = document.getElementById('priceListSearch').value.toLowerCase();
    const selectedCategory = document.getElementById('categoryFilter').value.toLowerCase();
    const rows = document.getElementById('priceListTableBody').getElementsByTagName('tr');
    
    Array.from(rows).forEach(row => {
        const name = row.cells[0].textContent.toLowerCase();
        const category = row.getAttribute('data-category');
        const matchesSearch = name.includes(searchText);
        const matchesCategory = selectedCategory === 'all' || category === selectedCategory;
        
        row.style.display = matchesSearch && matchesCategory ? '' : 'none';
    });
}

function hideOverlay() {
    document.getElementById("overlay").style.display = "none";
}