// Handle alert messages on page load
document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;
    const message = body.dataset.message;
    const success = body.dataset.success === 'true';

    if (message) {
        alert(message);
        if (success) {
            document.getElementById('addProductPopup').style.display = 'none';
        }
    }

    // Initialize image upload preview if the element exists
    initializeImageUpload();
});

// Product Search Function
function searchProduct() {
    const prodCode = document.getElementById('edit_prod_code').value;
    if (!prodCode) {
        alert('Please enter a product code');
        return;
    }

    // Disable the search button and show loading state
    const searchButton = document.querySelector('.btn-search');
    searchButton.disabled = true;
    searchButton.textContent = 'Searching...';

    // Create form data
    const formData = new FormData();
    formData.append('action', 'search');
    formData.append('prod_code', prodCode);

    // Create XML HTTP Request
    const xhr = new XMLHttpRequest();
    xhr.open('POST', window.location.href, true);
    
    xhr.onload = function() {
        // Re-enable the search button
        searchButton.disabled = false;
        searchButton.textContent = 'Search';

        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                console.log('Server response:', response); // Debug log
                
                if (response.success) {
                    // Show the form fields
                    const formFields = document.getElementById('edit-form-fields');
                    formFields.style.display = 'block';
                    
                    // Enable all form fields
                    const inputs = formFields.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        input.disabled = false;
                    });
                    
                    // Populate the form fields
                    document.getElementById('edit_prod_name').value = response.data.prod_name;
                    document.getElementById('edit_prod_price').value = response.data.prod_price;
                    document.getElementById('edit_stock_atty').value = response.data.stock_atty;
                    document.getElementById('edit_stock_unit').value = response.data.stock_unit;
                    document.getElementById('edit_category_code').value = response.data.category_code;
                    
                    // Update preview image if product has an image
                    const previewImage = document.getElementById('edit_preview_image');
                    console.log('Image path from server:', response.data.image_path); // Debug log
                    if (response.data.image_path) {
                        const imagePath = '../../' + response.data.image_path;
                        console.log('Full image path:', imagePath); // Debug log
                        previewImage.src = imagePath;
                        // Add error handler for image loading
                        previewImage.onerror = function() {
                            console.error('Failed to load image:', imagePath);
                            this.src = '../../pics/admin_icons/inventory.png';
                        };
                    } else {
                        console.log('No image path provided, using default image'); // Debug log
                        previewImage.src = '../../pics/admin_icons/inventory.png';
                    }
                    
                    // Update stock input step based on unit
                    updateStockStep(document.getElementById('edit_stock_unit'));
                } else {
                    alert(response.message || 'Product not found');
                    hideAndDisableFormFields();
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                console.error('Response text:', this.responseText);
                alert('Error processing response. Please try again.');
                hideAndDisableFormFields();
            }
        } else {
            alert('Error: Server returned status ' + this.status);
            hideAndDisableFormFields();
        }
    };
    
    xhr.onerror = function() {
        // Re-enable the search button
        searchButton.disabled = false;
        searchButton.textContent = 'Search';
        
        alert('Error: Could not connect to the server');
        hideAndDisableFormFields();
    };
    
    xhr.send(formData);
}

// Form Management Functions
function openAddProductForm() {
    document.getElementById('addProductPopup').style.display = 'flex';
}

function closeAddProductForm() {
    document.getElementById('addProductPopup').style.display = 'none';
    document.getElementById('productForm').reset();
    resetImagePreview('add_image_preview');
}

function openEditProductForm() {
    document.getElementById('editProductPopup').style.display = 'flex';
}

function closeEditProductForm() {
    document.getElementById('editProductPopup').style.display = 'none';
    document.getElementById('edit-form-fields').style.display = 'none';
    document.getElementById('editProductForm').reset();
    resetImagePreview('edit_image_preview');
}

function openDeleteProductForm() {
    document.getElementById('deleteProductPopup').style.display = 'flex';
}

function closeDeleteProductForm() {
    document.getElementById('deleteProductPopup').style.display = 'none';
    document.getElementById('delete_prod_code').value = '';
}

// Delete Product Functions
function confirmDeleteProduct() {
    const prodCode = document.getElementById('delete_prod_code').value;
    if (!prodCode) {
        alert('Please enter a product code');
        return;
    }

    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        deleteProduct(prodCode);
    }
}

function deleteProd() {
    document.getElementById('deleteProductPopup').style.display = 'flex';
}

function deleteProduct(prodCode) {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('prod_code', prodCode);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'Inventory.php', true);
    
    xhr.onload = function() {
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                alert(response.message);
                if (response.success) {
                    closeDeleteProductForm();
                    window.location.reload();
                }
            } catch (e) {
                alert('Error processing response');
                console.error(e);
            }
        }
    };
    
    xhr.send(formData);
}

// Image Upload Functions
function initializeImageUpload() {
    const imageInputs = document.querySelectorAll('input[type="file"][accept="image/*"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                const previewId = this.dataset.previewId;
                
                reader.onload = function(e) {
                    const preview = document.getElementById(previewId);
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                
                reader.readAsDataURL(file);
            }
        });
    });
}

function resetImagePreview(previewId) {
    const preview = document.getElementById(previewId);
    if (preview) {
        preview.src = '';
        preview.style.display = 'none';
    }
}

// Popup Close Functions
function closePopupOnOutsideClick(event, popupId) {
    if (event.target.id === popupId) {
        if (popupId === 'addProductPopup') {
            closeAddProductForm();
        }
    }
}

// Table Filter Function
function filterTable() {
    const input = document.getElementById('tableSearch');
    const filter = input.value.toLowerCase();
    const tbody = document.getElementById('productTableBody');
    const rows = tbody.getElementsByTagName('tr');

    for (let row of rows) {
        const cells = row.getElementsByTagName('td');
        let shouldShow = false;
        
        for (let cell of cells) {
            const text = cell.textContent || cell.innerText;
            if (text.toLowerCase().indexOf(filter) > -1) {
                shouldShow = true;
                break;
            }
        }
        
        row.style.display = shouldShow ? '' : 'none';
    }
}

// Category Filter Function
function filterByCategory(category) {
    const rows = document.getElementById('productTableBody').getElementsByTagName('tr');
    const showAll = category === 'all';
    
    for (let row of rows) {
        const categoryCell = row.cells[row.cells.length - 1]; // Assuming category is the last column
        row.style.display = (showAll || categoryCell.textContent === category) ? '' : 'none';
    }
}

// Navigation Functions
function dashboard() {
    window.location.href = "Dashboard.php";
}

function accounts() {
    window.location.href = "Accounts.php";
}

function reports() {
    window.location.href = "Reports.php";
}

// Update the filterProducts function
function filterProducts() {
    const input = document.getElementById('productSearch');
    const filter = input.value.toLowerCase();
    const category = document.getElementById('categorySelect').value.toLowerCase();
    const tbody = document.getElementById('productTableBody');
    const rows = tbody.getElementsByTagName('tr');

    for (let row of rows) {
        const cells = row.getElementsByTagName('td');
        if (cells.length === 0) continue; // Skip if no cells (like in "no products found" row)
        
        let shouldShow = true;
        const rowCategory = cells[4].textContent.toLowerCase(); // Category is in the 5th column

        // Check category filter
        if (category && rowCategory !== category) {
            shouldShow = false;
        }

        // Check text filter
        if (shouldShow) {
            shouldShow = false;
            for (let i = 0; i < cells.length; i++) {
                const cell = cells[i];
                if (cell.textContent.toLowerCase().indexOf(filter) > -1) {
                    shouldShow = true;
                    break;
                }
            }
        }

        row.style.display = shouldShow ? '' : 'none';
    }
}

// Image cropping functions
let cropper = null;

function closeModal() {
    const modal = document.getElementById('cropperModal');
    modal.style.display = 'none';
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
}

// Initialize image upload handlers
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cropper modal elements
    const modal = document.getElementById('cropperModal');
    const cropperImage = document.getElementById('cropperImage');

    // Handle image upload for add form
    const addImageInput = document.getElementById('imageInput');
    if (addImageInput) {
        addImageInput.addEventListener('change', function(e) {
            handleImageUpload(e, 'previewImage');
        });
    }

    // Handle image upload for edit form
    const editImageInput = document.getElementById('edit_image_input');
    if (editImageInput) {
        editImageInput.addEventListener('change', function(e) {
            handleImageUpload(e, 'edit_preview_image');
        });
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
});

function handleImageUpload(e, previewId) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const modal = document.getElementById('cropperModal');
        const cropperImage = document.getElementById('cropperImage');
        
        cropperImage.src = e.target.result;
        modal.style.display = 'block';

        if (cropper) {
            cropper.destroy();
        }

        cropper = new Cropper(cropperImage, {
            aspectRatio: 1,
            viewMode: 2,
            dragMode: 'move',
            autoCropArea: 1,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });

        // Store the target preview ID for use in saveCrop
        modal.dataset.targetPreview = previewId;
    };
    reader.readAsDataURL(file);
}

function saveCrop() {
    if (!cropper) return;

    const canvas = cropper.getCroppedCanvas({
        width: 300,
        height: 300
    });

    // Get the target preview ID from the modal's dataset
    const modal = document.getElementById('cropperModal');
    const targetPreviewId = modal.dataset.targetPreview;
    const previewImage = document.getElementById(targetPreviewId);
    
    if (!previewImage) {
        console.error('Preview image element not found:', targetPreviewId);
        return;
    }

    // Update preview image
    previewImage.src = canvas.toDataURL('image/jpeg');

    // Convert canvas to blob and update file input
    canvas.toBlob(function(blob) {
        const file = new File([blob], 'cropped_image.jpg', { type: 'image/jpeg' });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        
        // Update the appropriate file input based on which form is active
        const inputId = targetPreviewId === 'edit_preview_image' ? 'edit_image_input' : 'imageInput';
        const fileInput = document.getElementById(inputId);
        if (fileInput) {
            fileInput.files = dataTransfer.files;
        }
    }, 'image/jpeg');

    closeModal();
}

function updateStockPlaceholder(selectElement) {
    const stockInput = selectElement.parentElement.querySelector('input[name="stock_atty"]');
    if (selectElement.value === 'kg') {
        stockInput.placeholder = 'Enter weight in kg';
        stockInput.step = '0.01';
    } else if (selectElement.value === 'qty') {
        stockInput.placeholder = 'Enter quantity';
        stockInput.step = '1';
        stockInput.value = Math.floor(stockInput.value); // Round to whole number for quantity
    }
}

// Form submission handlers
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editProductForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'update');

            // Get the cropped image if it exists
            const previewImage = document.getElementById('edit_preview_image');
            if (previewImage.src && previewImage.src.startsWith('data:image')) {
                // Convert base64 to file
                fetch(previewImage.src)
                    .then(res => res.blob())
                    .then(blob => {
                        const file = new File([blob], 'product_image.jpg', { type: 'image/jpeg' });
                        formData.append('product_image', file);
                        submitForm(formData);
                    });
            } else {
                submitForm(formData);
            }
        });
    }
});

function submitForm(formData) {
    const submitButton = document.querySelector('#editProductForm button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = 'Saving...';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', window.location.href, true);
    
    xhr.onload = function() {
        submitButton.disabled = false;
        submitButton.textContent = 'Save Changes';
        
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                alert(response.message);
                if (response.success) {
                    closeEditProductForm();
                    location.reload();
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                alert('Error processing response. Please try again.');
            }
        } else {
            alert('Error: Server returned status ' + this.status);
        }
    };
    
    xhr.onerror = function() {
        submitButton.disabled = false;
        submitButton.textContent = 'Save Changes';
        alert('Error: Could not connect to the server');
    };
    
    xhr.send(formData);
}

// Add these missing functions
function hideAndDisableFormFields() {
    const formFields = document.getElementById('edit-form-fields');
    formFields.style.display = 'none';
    
    // Disable all form fields
    const inputs = formFields.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.disabled = true;
    });
}

function updateStockStep(selectElement) {
    if (!selectElement) return;
    
    const stockInput = selectElement.closest('.form-row').querySelector('input[name="stock_atty"]');
    if (!stockInput) return;

    if (selectElement.value === 'kg') {
        stockInput.step = '0.01';
        stockInput.placeholder = 'Enter weight in kg';
    } else if (selectElement.value === 'qty') {
        stockInput.step = '1';
        stockInput.placeholder = 'Enter quantity';
        stockInput.value = Math.floor(stockInput.value); // Round to whole number for quantity
    }
}

// Product Details Functions
function showProductDetails(prodCode) {
    const formData = new FormData();
    formData.append('action', 'search');
    formData.append('prod_code', prodCode);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', window.location.href, true);
    
    xhr.onload = function() {
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                if (response.success) {
                    const data = response.data;
                    
                    // Populate the details
                    document.getElementById('details_prod_code').textContent = data.prod_code;
                    document.getElementById('details_prod_name').textContent = data.prod_name;
                    document.getElementById('details_prod_price').textContent = '₱' + parseFloat(data.prod_price).toFixed(2);
                    document.getElementById('details_stock').textContent = data.stock_atty;
                    document.getElementById('details_unit').textContent = data.stock_unit;
                    document.getElementById('details_category').textContent = data.category_type;

                    // Show product image
                    const productImage = document.getElementById('details_product_image');
                    if (data.image_path) {
                        productImage.src = '../../' + data.image_path;
                        productImage.style.display = 'block';
                    } else {
                        productImage.src = '../../pics/admin_icons/inventory.png';
                        productImage.style.display = 'block';
                    }

                    // Show the popup
                    document.getElementById('productDetailsPopup').style.display = 'flex';
                } else {
                    alert('Product not found');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                alert('Error loading product details');
            }
        }
    };
    
    xhr.send(formData);
}

function closeDetailsPopup() {
    document.getElementById('productDetailsPopup').style.display = 'none';
} 