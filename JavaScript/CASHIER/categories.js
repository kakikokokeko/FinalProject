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


function selectCategory(clickedButton, category) {
    let productList = document.getElementById("product_list");
    productList.style.display="flex";
    document.querySelectorAll(".items button").forEach(button => {
        button.classList.remove("selected");
    });

    clickedButton.classList.add("selected");


    document.getElementById("category_selected").textContent = clickedButton.textContent;


    updateProducts(category);
}


function updateProducts(category) {
    let productList = document.getElementById("product_list");

    productList.innerHTML = "";


    productsData[category].forEach(product => {

        let productElement = document.createElement("button");
        productElement.classList.add("productType");
        productElement.innerHTML = `
            <div class="product_img">
                <img src="${product.img}" alt="${product.name}" class="img_prod">
            </div>
            <p class="product_name">${product.name}</p>
        `;


        productElement.onclick = function () {
            selectProduct(productElement);
        };

        productList.appendChild(productElement);
    });
}

function selectProduct(selectedButton) {
    document.querySelectorAll(".productType").forEach(button => {
        button.classList.remove("selected");
    });

    selectedButton.classList.add("selected");
}

document.addEventListener("DOMContentLoaded", () => {
    updateProducts("chicken");
});
