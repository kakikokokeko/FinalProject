function toggleColor(button) {
    let nameElement = button.parentElement.querySelector(".button_name");
    if (nameElement.style.color === "rgb(216, 64, 64)" || nameElement.style.color === "#D84040") {
        nameElement.style.color = "white";
    } else {
        nameElement.style.color = "#D84040";
    }
}

function toggleSelection(button) {
    button.classList.toggle("selected");
}

function calculateTotal() {
    let orderItems = document.querySelectorAll(".order_item");
    let totalAmount = 0;

    orderItems.forEach(order => {
        let priceText = order.querySelector("p:nth-child(3)").textContent;
        let price = parseFloat(priceText.replace("Price: ₱", "").replace(" each", ""));

        totalAmount += price;
    });

    document.getElementById("TOTAL").textContent = `TOTAL: ₱${totalAmount.toFixed(2)}`;
}