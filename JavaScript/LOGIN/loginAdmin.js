document.addEventListener("DOMContentLoaded", function () {
    //Show/Hide Password
    document.getElementById("show-password").addEventListener("change", function () {
        const passwordInput = document.getElementById("adminPassword");
        passwordInput.type = this.checked ? "text" : "password";
    });

    //Validation
    document.querySelector("form").addEventListener("submit", function (e) {
        e.preventDefault(); 

        let isUsernameValid = false;
        let isPasswordValid = false;

        const username = document.getElementById("adminUsername");
        const password = document.getElementById("adminPassword");

        if (username.value.trim() === "Admin123") {
            username.style.borderColor = "";
            isUsernameValid = true;
        } else {
            username.style.borderColor = "red";
            isUsernameValid = false;
        }

        if (password.value.trim() === "admin123") {
            password.style.borderColor = "";
            isPasswordValid = true;
        } else {
            password.style.borderColor = "red";
            isPasswordValid = false;
        }

        if (!isUsernameValid || !isPasswordValid) {
            alert("Invalid username or password!");
            return;
        }

        window.location.href = "../../HTML/ADMIN/Dashboard.php";
    });
});
