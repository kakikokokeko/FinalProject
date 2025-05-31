// Main initialization
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu functionality
    initializeMobileMenu();
    
    // Handle alert messages
    handleAlertMessages();
});

// Mobile menu initialization
function initializeMobileMenu() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const sidebar = document.querySelector('.sidebar-container');
    const closeSidebar = document.getElementById('close-sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    if(mobileMenuButton && sidebar && closeSidebar && sidebarOverlay) {
        mobileMenuButton.addEventListener('click', function() {
            console.log('Mobile menu clicked'); // Debug log
            sidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    } else {
        console.error('Mobile menu elements not found:', {
            mobileMenuButton: !!mobileMenuButton,
            sidebar: !!sidebar,
            closeSidebar: !!closeSidebar,
            sidebarOverlay: !!sidebarOverlay
        });
    }
}

// Handle alert messages
function handleAlertMessages() {
    const body = document.body;
    const message = body.dataset.message;
    const success = body.dataset.success === 'true';

    if (message) {
        alert(message);
        if (success) {
            document.getElementById('addAccountPopup').style.display = 'none';
        }
    }
}

// Account Search Function
function searchAccount() {
    const accCode = document.getElementById('edit_acc_code').value;
    if (!accCode) {
        alert('Please enter an account code');
        return;
    }

    // Create form data
    const formData = new FormData();
    formData.append('action', 'search');
    formData.append('acc_code', accCode);

    // Create XML HTTP Request
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'Accounts.php', true);
    
    xhr.onload = function() {
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                if (response.success) {
                    // Show the form fields
                    document.getElementById('edit-form-fields').style.display = 'block';
                    
                    // Populate the form fields
                    document.getElementById('edit_first_name').value = response.data.first_name;
                    document.getElementById('edit_last_name').value = response.data.last_name;
                    document.getElementById('edit_acc_address').value = response.data.acc_address;
                    document.getElementById('edit_acc_contact').value = response.data.acc_contact;
                    document.getElementById('edit_username').value = response.data.username;
                    document.getElementById('edit_gender').value = response.data.gender;
                    document.getElementById('edit_acc_position').value = response.data.acc_position;
                } else {
                    alert('Account not found');
                    document.getElementById('edit-form-fields').style.display = 'none';
                }
            } catch (e) {
                alert('Error processing response');
                console.error(e);
            }
        }
    };
    
    xhr.send(formData);
}

// Form Management Functions
function openAddAccountForm() {
    document.getElementById('addAccountPopup').style.display = 'flex';
}

function closeAddAccountForm() {
    document.getElementById('addAccountPopup').style.display = 'none';
    document.getElementById('accountForm').reset();
}

function openEditAccountForm() {
    document.getElementById('editAccountPopup').style.display = 'flex';
}

function closeEditAccountForm() {
    document.getElementById('editAccountPopup').style.display = 'none';
    document.getElementById('edit-form-fields').style.display = 'none';
    document.getElementById('editAccountForm').reset();
}

function openDeleteAccountForm() {
    document.getElementById('deleteAccountPopup').style.display = 'flex';
}

function closeDeleteAccountForm() {
    document.getElementById('deleteAccountPopup').style.display = 'none';
    document.getElementById('delete_acc_code').value = '';
}

// Delete Account Functions
function confirmDelete() {
    const accCode = document.getElementById('delete_acc_code').value;
    if (!accCode) {
        alert('Please enter an account code');
        return;
    }

    if (confirm('Are you sure you want to delete this account? This action cannot be undone.')) {
        deleteAccount(accCode);
    }
}

function deleteAccount(accCode) {
    // Create form data
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('acc_code', accCode);

    // Create XML HTTP Request
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'Accounts.php', true);
    
    xhr.onload = function() {
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                alert(response.message);
                if (response.success) {
                    closeDeleteAccountForm();
                    // Reload the page to refresh the account list
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

// Account Details Functions
function showAccountDetails(accCode) {
    // Create form data
    const formData = new FormData();
    formData.append('action', 'search');
    formData.append('acc_code', accCode);

    // Create XML HTTP Request
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'Accounts.php', true);
    
    xhr.onload = function() {
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                if (response.success) {
                    const data = response.data;
                    document.getElementById('details_acc_code').textContent = data.acc_code;
                    document.getElementById('details_full_name').textContent = data.first_name + ' ' + data.last_name;
                    document.getElementById('details_position').textContent = data.acc_position;
                    document.getElementById('details_address').textContent = data.acc_address;
                    document.getElementById('details_gender').textContent = data.gender;
                    document.getElementById('details_contact').textContent = data.acc_contact;
                    document.getElementById('details_username').textContent = data.username;
                    
                    document.getElementById('accountDetailsPopup').style.display = 'flex';
                } else {
                    alert('Account not found');
                }
            } catch (e) {
                alert('Error processing response');
                console.error(e);
            }
        }
    };
    
    xhr.send(formData);
}

function closeDetailsPopup() {
    document.getElementById('accountDetailsPopup').style.display = 'none';
}

// Popup Close Functions
function closePopupOnOutsideClick(event, popupId) {
    if (event.target.id === popupId) {
        switch(popupId) {
            case 'editAccountPopup':
                closeEditAccountForm();
                break;
            case 'deleteAccountPopup':
                closeDeleteAccountForm();
                break;
            case 'accountDetailsPopup':
                closeDetailsPopup();
                break;
        }
    }
}

// Table Filter Function
function filterTable() {
    const input = document.getElementById('tableSearch');
    const filter = input.value.toLowerCase();
    const tbody = document.getElementById('accountTableBody');
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

// Navigation Functions
function dashboard() {
    window.location.href = "Dashboard.php";
}

function inventory() {
    window.location.href = "Inventory.php";
}

function reports() {
    window.location.href = "Reports.php";
} 