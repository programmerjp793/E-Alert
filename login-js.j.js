$(document).ready(function() {
    $("#login-form").submit(function(event) {
        event.preventDefault(); // Prevent the form from submitting via the browser

        var formData = {
            email: $("#email").val(),
            password: $("#password").val(),
            login: true
        };

        $.ajax({
            type: "POST",
            url: "login.php",
            data: formData,
            dataType: "json",
            encode: true
        })
        .done(function(data) {
            if (data.status === "success") {
                window.location.href = data.redirect;
            } else {
                alert(data.message);
            }
        })
        .fail(function(data) {
            alert("An error occurred. Please try again.");
        });
    });

    $("#verify-otp-btn").click(function(event) {
        event.preventDefault(); // Prevent the form from submitting via the browser

        var formData = {
            email: $("#email").val(),
            otp: $("#otp").val(),
            verify_otp: true
        };

        $.ajax({
            type: "POST",
            url: "verify_otp.php",
            data: formData,
            dataType: "json",
            encode: true
        })
        .done(function(data) {
            if (data.status === "success") {
                window.location.href = "change_password.php?email=" + encodeURIComponent(data.email);
            } else {
                alert(data.message);
            }
        })
        .fail(function(data) {
            alert("An error occurred. Please try again.");
        });
    });
});


const logregBox = document.querySelector('.logreg-box');
const loginLink = document.querySelector('.login-link');
const registerLink = document.querySelector('.register-link');


document.addEventListener("DOMContentLoaded", function() {
    //PASSWORD FIELD VISABILITY
    document.getElementById('toggle-password').addEventListener('click', function () {
        const passwordField = document.getElementById('password');
        const icon = this.querySelector('i');

        // PASSWORD TOGGLE - VISABILITY
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('bxs-show');
            icon.classList.add('bxs-hide');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('bxs-hide');
            icon.classList.add('bxs-show');
        }
    });

    // CONFIRM PASSWORD TOGGLE
    document.getElementById('toggle-confirm-password').addEventListener('click', function () {
        const confirmPasswordField = document.getElementById('confirm-password');
        const icon = this.querySelector('i');

        // CONFIRM PASSWORD TOGGLE - VISABILITY
        if (confirmPasswordField.type === 'password') {
            confirmPasswordField.type = 'text';
            icon.classList.remove('bxs-show');
            icon.classList.add('bxs-hide');
        } else {
            confirmPasswordField.type = 'password';
            icon.classList.remove('bxs-hide');
            icon.classList.add('bxs-show');
        }
    });

    // Password match checker
    document.querySelector('.btn').addEventListener('click', function (event) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        if (password !== confirmPassword) {
            event.preventDefault(); // Prevents the form submission
            alert("Password is not the same"); // Alert message from browser
        }
    });
});


