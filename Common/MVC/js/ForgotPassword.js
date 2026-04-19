document.addEventListener('DOMContentLoaded', function() {
    const resetForm = document.getElementById('resetForm');

    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            

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
            
        });
    }
});