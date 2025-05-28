const productsData = {
    chicken: [
        { name: "Chicken Wings", img: "../../pics/category_products/chickenwings.jpg" },
        { name: "Chicken Drumsticks", img: "../../pics/category_products/chickendrums.webp" },
        { name: "Chicken Neck", img: "../../pics/category_products/chickenneck.avif" },
        { name: "Chicken Feet", img: "../../pics/category_products/chickenfeet.webp" },
        { name: "Whole Chicken", img: "../../pics/category_products/wholechicken.webp" }
    ],
    beef: [
        { name: "Beef Sirloin", img: "../../pics/category_products/beefsirloin.jpg" },
        { name: "Beef Ribs", img: "../../pics/category_products/beefribs.webp" },
        { name: "Beef Brisket", img: "../../pics/category_products/beefbrisket.avif" }

    ],
    pork: [
        { name: "Pork Belly", img: "../../pics/category_products/porkbelly.jpg" },
        { name: "Pork Ribs", img: "../../pics/category_products/porkribs.webp" },
        { name: "Pork Shoulder", img: "../../pics/category_products/porkshoulder.avif" }
    ],
    processed: [
        { name: "Hotdog", img: "../../pics/category_products/hotdog.jpg" },
        { name: "Bacon", img: "../../pics/category_products/bacon.webp" },
        { name: "Ham", img: "../../pics/category_products/ham.avif" }
    ],
    "sari-sari": [
        { name: "Canned Goods", img: "../../pics/category_products/cannedgoods.jpg" },
        { name: "Instant Noodles", img: "../../pics/category_products/noodles.webp" },
        { name: "Powdered Milk", img: "../../pics/category_products/milk.avif" }
    ]
};

// Function to select category and filter products
function selectCategory(button, category) {
    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.Category .items button');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    // Add active class to clicked button
    button.classList.add('active');
    
    // Update category text
    document.getElementById('category_selected').textContent = 
        category === 'all' ? 'All Products' : category.charAt(0).toUpperCase() + category.slice(1);
    
    // Filter products
    filterProducts();
}

// Function to filter products based on search input and selected category
function filterProducts() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const selectedCategory = document.querySelector('.Category .items button.active').getAttribute('onclick').match(/['"]([^'"]+)['"]/)[1];
    const products = document.querySelectorAll('.productType');
    
    products.forEach(product => {
        const productName = product.querySelector('.product_name').textContent.toLowerCase();
        const productCategory = product.getAttribute('data-category').toLowerCase();
        
        const matchesSearch = productName.includes(searchInput);
        const matchesCategory = selectedCategory === 'all' || productCategory === selectedCategory;
        
        product.style.display = matchesSearch && matchesCategory ? '' : 'none';
    });
}

// Function to select a product
function selectProduct(button) {
    // Remove selected class from all products
    const products = document.querySelectorAll('.productType');
    products.forEach(prod => prod.classList.remove('selected'));
    
    // Add selected class to clicked product
    button.classList.add('selected');
    
    // Update metric button based on product unit
    const unit = button.getAttribute('data-unit');
    const scaleElement = document.getElementById('scale');
    if (unit === 'kg') {
        scaleElement.textContent = 'kg';
    } else {
        scaleElement.textContent = 'qty';
    }
}

// Function to add selected product to cart
function addToCart() {
    const selectedProduct = document.querySelector('.productType.selected');
    if (!selectedProduct) {
        alert('Please select a product first');
        return;
    }
    
    const quantity = parseFloat(document.getElementById('scale_number').textContent);
    if (quantity <= 0) {
        alert('Please enter a valid quantity');
        return;
    }
    
    const productName = selectedProduct.querySelector('.product_name').textContent;
    const productPrice = parseFloat(selectedProduct.getAttribute('data-price'));
    const total = quantity * productPrice;
    
    const ordersContainer = document.querySelector('.orders_container');
    const orderDiv = document.createElement('div');
    orderDiv.className = 'order-item';
    orderDiv.innerHTML = `
        <p>${productName}</p>
        <p>${quantity} ${selectedProduct.getAttribute('data-unit')}</p>
        <p>₱${total.toFixed(2)}</p>
    `;
    
    ordersContainer.appendChild(orderDiv);
    calculateTotal();
    
    // Reset selection
    selectedProduct.classList.remove('selected');
    document.getElementById('scale_number').textContent = '0';
}

// Function to calculate total
function calculateTotal() {
    const orders = document.querySelectorAll('.order-item');
    let total = 0;
    
    orders.forEach(order => {
        const price = parseFloat(order.querySelector('p:last-child').textContent.replace('₱', ''));
        total += price;
    });
    
    document.getElementById('TOTAL').textContent = `TOTAL: ₱${total.toFixed(2)}`;
}

// Initialize the page with "All" category selected
document.addEventListener('DOMContentLoaded', function() {
    const allButton = document.querySelector('.Category .items button[onclick*="all"]');
    if (allButton) {
        selectCategory(allButton, 'all');
    }
});
