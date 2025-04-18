document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("achievementForm");
    
    form.addEventListener("submit", function (event) {
        const fileInput = document.getElementById("certificate");
        const fileSize = fileInput.files[0]?.size || 0;
        
        if (fileSize > 5242880) { // 5MB limit
            alert("File size exceeds 5MB limit. Please upload a smaller file.");
            event.preventDefault();
        }
    });

    const statusForms = document.querySelectorAll(".admin-panel form");
    
    statusForms.forEach(form => {
        form.addEventListener("submit", function (event) {
            event.preventDefault();
            
            const formData = new FormData(this);
            fetch("update_status.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload(); // Refresh the page to reflect changes
            })
            .catch(error => console.error("Error:", error));
        });
    });
});
