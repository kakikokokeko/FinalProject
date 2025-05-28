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

document.getElementById('accountForm').addEventListener('submit', function(e) {
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
            document.getElementById('accountForm').reset();
            location.reload(); 
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
});

function closeAddAccountForm() {
    document.getElementById('addAccountPopup').style.display = 'none';
}

// Open popup function (you can call this from your button)
function openAddAccountForm() {
    document.getElementById('addAccountPopup').style.display = 'flex';
}

document.getElementById('accountForm').addEventListener('submit', function(e) {
});

function closeAddAccountForm() {
    document.getElementById('addAccountPopup').style.display = 'none';
}



//-----------------------------------------------------------------EDIT ACCOUNT------------------------------------------------------------------

function closeEditAccountForm() {
    document.getElementById('editAccountPopup').style.display = 'none';
}

// Open popup function (you can call this from your button)
function openEditAccountForm() {
    document.getElementById('editAccountPopup').style.display = 'flex';
}
