function showCheckoutModal() {
    const tbody = document.getElementById('ordersTableBody');
    if (tbody.children.length === 0) {
        alert('Please add items to cart before checkout');
        return;
    }

    // Copy orders to checkout table
    const checkoutBody = document.getElementById('checkoutTableBody');
    checkoutBody.innerHTML = '';  // Clear existing content

    // Copy each row with data attributes
    Array.from(tbody.children).forEach(row => {
        const newRow = document.createElement('tr');
        
        // Copy the product name cell
        const nameCell = document.createElement('td');
        nameCell.textContent = row.cells[0].textContent;
        newRow.appendChild(nameCell);
        
        // Copy the quantity cell with data attributes
        const qtyCell = document.createElement('td');
        qtyCell.textContent = row.cells[1].textContent;
        qtyCell.setAttribute('data-actual-quantity', row.cells[1].getAttribute('data-actual-quantity'));
        qtyCell.setAttribute('data-unit', row.cells[1].getAttribute('data-unit'));
        newRow.appendChild(qtyCell);
        
        // Copy the price cell
        const priceCell = document.createElement('td');
        priceCell.textContent = row.cells[2].textContent;
        newRow.appendChild(priceCell);
        
        checkoutBody.appendChild(newRow);
    });

    // Set total amount
    const total = document.getElementById('TOTAL').textContent.replace('₱', '');
    document.getElementById('checkoutTotal').textContent = `₱${total}`;

    // Reset cash and change amounts
    document.getElementById('cashAmount').value = '';
    document.getElementById('changeAmount').textContent = '₱0.00';

    // Show modal
    document.getElementById('checkoutModal').style.display = 'flex';

    // Add event listener for cash amount input
    document.getElementById('cashAmount').addEventListener('input', calculateChange);

    // Focus on the cash amount input
    document.getElementById('cashAmount').focus();
}

function hideCheckoutModal() {
    document.getElementById('checkoutModal').style.display = 'none';
}

function calculateChange() {
    const totalAmount = parseFloat(document.getElementById('checkoutTotal').textContent.replace('₱', ''));
    const cashAmount = parseFloat(document.getElementById('cashAmount').value) || 0;
    const change = cashAmount - totalAmount;
    
    document.getElementById('changeAmount').textContent = change >= 0 ? `₱${change.toFixed(2)}` : '₱0.00';
}

function processPayment() {
    const cashAmount = parseFloat(document.getElementById('cashAmount').value) || 0;
    const totalAmount = parseFloat(document.getElementById('checkoutTotal').textContent.replace('₱', ''));
    
    if (cashAmount === 0) {
        alert('Please enter payment amount');
        document.getElementById('cashAmount').focus();
        return;
    }
    
    if (cashAmount < totalAmount) {
        const remaining = (totalAmount - cashAmount).toFixed(2);
        alert(`Insufficient payment amount. Please add ₱${remaining} more`);
        document.getElementById('cashAmount').focus();
        return;
    }

    // Collect order data
    const orders = [];
    const rows = document.getElementById('checkoutTableBody').getElementsByTagName('tr');
    
    for (let row of rows) {
        const productName = row.cells[0].textContent;
        const quantityCell = row.cells[1];
        const actualQuantity = parseFloat(quantityCell.getAttribute('data-actual-quantity'));
        const unit = quantityCell.getAttribute('data-unit');
        const price = parseFloat(row.cells[2].textContent.replace('₱', ''));
        
        orders.push({
            product_name: productName,
            quantity: actualQuantity,
            unit: unit,
            price: price
        });
    }

    // Send to server
    fetch('process_transaction.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            orders: orders,
            total_amount: totalAmount,
            cash_amount: cashAmount,
            change_amount: cashAmount - totalAmount
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Transaction completed successfully!');
            // Clear the orders table
            document.getElementById('ordersTableBody').innerHTML = '';
            // Reset the total
            document.getElementById('TOTAL').textContent = '₱0.00';
            // Hide the modal
            hideCheckoutModal();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the transaction');
    });
} 