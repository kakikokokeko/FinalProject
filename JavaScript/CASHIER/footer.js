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

function metric(){
    let scaleElement = document.getElementById("scale");
    scaleElement.textContent = scaleElement.textContent === "kg" ? "g" : "kg";
}

function addToCart() {
    let selectedProduct = document.querySelector(".productType.selected");
    let quantity = parseInt(document.getElementById("scale_number").textContent);
    let ordersContainer = document.querySelector(".orders_container");

    if (!selectedProduct) {
        alert("Please select a product first!");
        return;
    }

    if (quantity === 0) {
        alert("Quantity must be at least 1!");
        return;
    }

    let productName = selectedProduct.querySelector(".product_name").textContent;
    let randomPrice = (Math.random() * (500 - 100) + 100).toFixed(2);
    let totalPrice = (randomPrice * quantity).toFixed(2);

    let orderItem = document.createElement("div");
    orderItem.classList.add("order_item");
    orderItem.innerHTML = `
        <p><strong>${productName}</strong></p>
        <p>Quantity: ${quantity}</p>
        <p>Price: ₱${randomPrice} each</p>
        <p>Total: ₱${totalPrice}</p>
    `;

    ordersContainer.appendChild(orderItem);
}

function showOverlay(type) {
    let overlay = document.getElementById("overlay");
    let modalTitle = document.getElementById("modalTitle");
    let modalContent = document.getElementById("modalContent");

    if (type === "priceList") {
        modalTitle.textContent = "Price List";
        modalContent.innerHTML = `
            <ul>
                <li>Chicken Wings - ₱150/kg</li>
                <li>Beef Sirloin - ₱500/kg</li>
                <li>Pork Belly - ₱350/kg</li>
                <li>Hotdog - ₱200/kg</li>
            </ul>
        `;
    } else if (type === "orderList") {
        modalTitle.textContent = "Order List";
        modalContent.innerHTML = document.querySelector(".orders_container").innerHTML || "<p>No orders yet!</p>";
    }

    overlay.style.display = "flex";
}

function hideOverlay() {
    document.getElementById("overlay").style.display = "none";
}