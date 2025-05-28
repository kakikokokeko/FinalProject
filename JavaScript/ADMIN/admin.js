function dashboard(){
    window.location.href = "../../HTML/ADMIN/Dashboard.php";
}

function account(){
    window.location.href = "../../HTML/ADMIN/Accounts.php";
}

function inventory(){
    window.location.href = "../../HTML/ADMIN/Inventory.php";
}

function reports(){
    window.location.href = "../../HTML/ADMIN/Reports.php";
}

//-----------------------------------------------------------------ADD ACCOUNT------------------------------------------------------------------

// Check if element exists before adding event listener
const accountForm = document.getElementById('accountForm');
if (accountForm) {
    accountForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch('', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); 
            if (data.success) {
                closeAddAccountForm();
                accountForm.reset();
                location.reload(); 
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    });
}

function closeAddAccountForm() {
    const addAccountPopup = document.getElementById('addAccountPopup');
    if (addAccountPopup) {
        addAccountPopup.style.display = 'none';
    }
}

function openAddAccountForm() {
    const addAccountPopup = document.getElementById('addAccountPopup');
    if (addAccountPopup) {
        addAccountPopup.style.display = 'flex';
    }
}

//-----------------------------------------------------------------EDIT ACCOUNT------------------------------------------------------------------

function closeEditAccountForm() {
    const editAccountPopup = document.getElementById('editAccountPopup');
    if (editAccountPopup) {
        editAccountPopup.style.display = 'none';
    }
}

function openEditAccountForm() {
    const editAccountPopup = document.getElementById('editAccountPopup');
    if (editAccountPopup) {
        editAccountPopup.style.display = 'flex';
    }
}

//-----------------------------------------------------------------EDIT PRODUCT------------------------------------------------------------------

function editProd() {
    const editProductPopup = document.getElementById('editProductPopup');
    if (editProductPopup) {
        editProductPopup.style.display = 'flex';
    }
}
