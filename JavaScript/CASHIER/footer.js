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

    // Create new table row
    const tbody = document.getElementById('ordersTableBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>${productName}</td>
        <td>${quantity} ${displayUnit}</td>
        <td>₱${totalPrice.toFixed(2)}</td>
    `;
    tbody.appendChild(tr);

    calculateTotal();

    // Reset selection and quantity
    selectedProduct.classList.remove('selected');
    document.getElementById('scale_number').textContent = '0';
    count = 0;
}

function calculateTotal() {
    const rows = document.querySelectorAll('#ordersTableBody tr td:last-child');
    let total = 0;
    
    rows.forEach(cell => {
        const price = parseFloat(cell.textContent.replace('₱', ''));
        total += price;
    });
    
    document.getElementById('TOTAL').textContent = `₱${total.toFixed(2)}`;
}

function hideOverlay() {
    document.getElementById("overlay").style.display = "none";
}