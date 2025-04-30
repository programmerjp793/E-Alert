document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('signup-form').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent page reload

        const formData = new FormData(this); // Collect form data

        // Basic validation
        if (!formData.get("username") || !formData.get("email") || !formData.get("password") || !formData.get("confirm-password")) {
            alert("All fields are required!");
            return;
        }

        if (formData.get("password").length < 8) {
            alert("Password must be at least 8 characters long.");
            return;
        }

        if (formData.get("password") !== formData.get("confirm-password")) {
            alert("Passwords do not match!");
            return;
        }

        // Send signup request to PHP
        fetch('signup.php', {
            method: 'POST',
            body: formData // Send form data directly
        })
        .then(response => response.json()) // Expecting JSON response
        .then(data => {
            if (data.status === "success") {
                alert("Signup successful! Redirecting to Home.");
                window.location.href = 'E-Alert-Home.php'; // Redirect on success
            } else {
                alert(data.message); // Show error message
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred. Please try again.");
        });
    });
});
