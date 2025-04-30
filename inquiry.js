document.getElementById("inquiryForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent default form submission

    let formData = new FormData(this);

    fetch("process_inquiry.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert("Inquiry submitted successfully!");
        location.reload(); // Refresh page to update carousel
    })
    .catch(error => console.error("Error:", error));
});
