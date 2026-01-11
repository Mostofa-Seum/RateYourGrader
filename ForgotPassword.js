document.addEventListener('DOMContentLoaded', function() {
    
    // Select the reset form if it exists
    const resetForm = document.getElementById('resetForm');

    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Clear previous alerts if you want a dynamic JS approach, 
            // but here we just prevent submission if invalid.
            
            if (password.length < 6) {
                e.preventDefault();
                alert("Password must be at least 6 characters long.");
                return;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert("Passwords do not match. Please try again.");
                return;
            }
            
            // If checks pass, the form submits to PHP
        });
    }
});