// Functions for managing orders
function deleteSelected() {
    const selectedRows = document.getElementsByClassName('selected');
    
    // Convert to array because we'll be modifying the live HTMLCollection
    const rowsToDelete = Array.from(selectedRows);
    
    if (rowsToDelete.length === 0) {
        alert('Please select items to delete');
        return;
    }

    if (confirm('Are you sure you want to delete the selected items?')) {
        // Remove selected rows
        rowsToDelete.forEach(row => row.remove());
        // Recalculate total
        calculateTotal();
    }
}

function deleteAll() {
    const tbody = document.getElementById('ordersTableBody');
    if (tbody.children.length === 0) {
        alert('No items to delete');
        return;
    }

    if (confirm('Are you sure you want to delete all items?')) {
        tbody.innerHTML = '';
        calculateTotal();
    }
}

function editSelected() {
    const selectedRows = document.getElementsByClassName('selected');
    
    if (selectedRows.length === 0) {
        alert('Please select an item to edit');
        return;
    }
    
    if (selectedRows.length > 1) {
        alert('Please select only one item to edit');
        return;
    }

    const row = selectedRows[0];
    const currentQty = row.cells[1].textContent.split(' ')[0]; // Get the number part
    const unit = row.cells[1].textContent.split(' ')[1]; // Get the unit part
    
    const newQty = prompt(`Enter new quantity (current: ${currentQty} ${unit}):`, currentQty);
    
    if (newQty === null) return; // User clicked cancel
    
    const parsedQty = parseFloat(newQty);
    
    if (isNaN(parsedQty) || parsedQty <= 0) {
        alert('Please enter a valid quantity greater than 0');
        return;
    }

    // Update quantity
    row.cells[1].textContent = `${parsedQty} ${unit}`;
    
    // Update price
    const pricePerUnit = parseFloat(row.cells[2].textContent.replace('₱', '')) / parseFloat(currentQty);
    const newTotal = pricePerUnit * parsedQty;
    row.cells[2].textContent = `₱${newTotal.toFixed(2)}`;
    
    // Recalculate total
    calculateTotal();
}

// Initialize order table functionality when the document loads
document.addEventListener('DOMContentLoaded', function() {
    const ordersTableBody = document.getElementById('ordersTableBody');
    if (!ordersTableBody) return;

    // Add click handler using event delegation
    ordersTableBody.addEventListener('click', function(event) {
        const row = event.target.closest('tr');
        if (!row) return;
        
        // Toggle selection
        row.classList.toggle('selected');
    });
});

// Function to make a row selectable
function makeRowSelectable(row) {
    row.style.cursor = 'pointer';
}

function calculateTotal() {
    const rows = document.querySelectorAll('#ordersTableBody tr td:last-child');
    let total = 0;
    
    rows.forEach(cell => {
        const price = parseFloat(cell.textContent.replace('₱', ''));
        if (!isNaN(price)) {
            total += price;
        }
    });
    
    document.getElementById('TOTAL').textContent = `₱${total.toFixed(2)}`;
}

// Show order history modal
function showOrderHistory() {
    document.getElementById('orderHistoryModal').style.display = 'flex';
    document.getElementById('orderHistoryFilter').value = 'today'; // Set default filter
    fetchOrderHistory('today'); // Fetch with default filter
}

// Hide order history modal
function hideOrderHistory() {
    document.getElementById('orderHistoryModal').style.display = 'none';
}

// Hide order details modal
function hideOrderDetails() {
    document.getElementById('orderDetailsModal').style.display = 'none';
}

// Filter order history
function filterOrderHistory() {
    const filter = document.getElementById('orderHistoryFilter').value;
    fetchOrderHistory(filter);
}

// Fetch order history
function fetchOrderHistory(filter = 'today') {
    const url = filter === 'all' ? 'get_order_history.php' : `get_order_history.php?filter=${filter}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('orderHistoryTableBody');
                tbody.innerHTML = '';

                data.data.forEach(order => {
                    const row = document.createElement('tr');
                    const date = new Date(order.transaction_date);
                    row.innerHTML = `
                        <td>${date.toLocaleString()}</td>
                        <td>${order.sale_id}</td>
                        <td>₱${parseFloat(order.total_amount).toFixed(2)}</td>
                        <td>
                            <button onclick="showOrderDetails(${order.sale_id})">View Details</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                alert('Error loading order history: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading order history');
        });
}

// Show order details
function showOrderDetails(orderId) {
    fetch(`get_order_history.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const details = data.data;
                if (details.length > 0) {
                    const firstItem = details[0];
                    
                    // Set order info
                    document.getElementById('detailOrderId').textContent = firstItem.sale_id;
                    document.getElementById('detailDate').textContent = new Date(firstItem.transaction_date).toLocaleString();
                    document.getElementById('detailCashier').textContent = firstItem.cashier_name;
                    document.getElementById('detailTotal').textContent = `₱${parseFloat(firstItem.total_amount).toFixed(2)}`;
                    document.getElementById('detailCash').textContent = `₱${parseFloat(firstItem.cash_amount).toFixed(2)}`;
                    document.getElementById('detailChange').textContent = `₱${parseFloat(firstItem.change_amount).toFixed(2)}`;

                    // Fill items table
                    const tbody = document.getElementById('orderDetailsTableBody');
                    tbody.innerHTML = '';

                    details.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.prod_name}</td>
                            <td>${item.quantity}</td>
                            <td>₱${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td>₱${parseFloat(item.item_total).toFixed(2)}</td>
                        `;
                        tbody.appendChild(row);
                    });

                    // Show the modal
                    document.getElementById('orderDetailsModal').style.display = 'flex';
                }
            } else {
                alert('Error loading order details: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading order details');
        });
} 