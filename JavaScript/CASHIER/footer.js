let count = 0;
function increase() {
    count++;
    document.getElementById("scale_number").value = count;
}

function decrease() {
    if (count > 0) {
        count--;
    }
    document.getElementById("scale_number").value = count;
}

function validateInput(input) {
    // Get the current unit
    const scaleElement = document.getElementById('scale');
    const currentUnit = scaleElement.textContent;
    
    // Convert input to number
    let value = parseFloat(input.value) || 0;
    
    // Ensure value is not negative
    if (value < 0) {
        value = 0;
    }
    
    // For 'qty' unit, ensure it's a whole number
    if (currentUnit === 'qty') {
        value = Math.floor(value);
    }
    
    // Update both the input value and count variable
    input.value = value;
    count = value;
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
    
    const quantity = parseFloat(document.getElementById('scale_number').value);
    if (quantity <= 0) {
        alert('Quantity must be at least 1!');
        return;
    }

    const productName = selectedProduct.querySelector('.product_name').textContent;
    const basePrice = parseFloat(selectedProduct.getAttribute('data-price'));
    const unit = selectedProduct.getAttribute('data-unit');
    const scaleType = document.getElementById('scale').textContent;
    const prodCode = selectedProduct.getAttribute('data-code');
    
    let finalQuantity = quantity;
    let displayUnit = unit;
    let pricePerUnit = basePrice;
    let actualQuantity = quantity; // This will be the quantity used for stock deduction

    // Handle unit conversion if needed
    if (unit === 'kg') {
        if (scaleType === 'g') {
            finalQuantity = quantity; // Keep display in grams
            displayUnit = 'g';
            pricePerUnit = basePrice / 1000; // Price per gram
            actualQuantity = quantity / 1000; // Convert to kg for stock deduction
        } else if (scaleType === 'qty') {
            displayUnit = 'pcs';
            pricePerUnit = basePrice; // Price per piece
            actualQuantity = quantity; // No conversion needed
        } else {
            displayUnit = 'kg';
            pricePerUnit = basePrice; // Price per kg
            actualQuantity = quantity; // No conversion needed
        }
    } else {
        displayUnit = 'pcs'; // For quantity items
        pricePerUnit = basePrice; // Price per piece
        actualQuantity = quantity; // No conversion needed
    }

    const totalPrice = pricePerUnit * quantity;

    // Create new table row
    const tbody = document.getElementById('ordersTableBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>${productName}</td>
        <td data-actual-quantity="${actualQuantity}" data-unit="${unit}">${quantity} ${displayUnit}</td>
        <td>₱${totalPrice.toFixed(2)}</td>
    `;
    
    // Make the row selectable before adding it to the table
    makeRowSelectable(tr);
    tbody.appendChild(tr);

    calculateTotal();

    // Reset selection and quantity
    selectedProduct.classList.remove('selected');
    document.getElementById('scale_number').value = '0';
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