function toggleColor(button) {
    let nameElement = button.parentElement.querySelector(".button_name");
    if (nameElement.style.color === "rgb(216, 64, 64)" || nameElement.style.color === "#D84040") {
        nameElement.style.color = "white";
    } else {
        nameElement.style.color = "#D84040";
    }
}

// Calculate total function
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