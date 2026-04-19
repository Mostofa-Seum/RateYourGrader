function showLoginMessage() {
    // Get the warning message element
    var msg = document.getElementById("login-warning-msg");
    
    // Check if element exists to prevent errors
    if (msg) {
        // Make the message visible
        msg.style.display = "block";
        
        // Smooth scroll to the message so the user notices it
        msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// --- Custom Toast Function (Matches Homepage) ---
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerText = message;

    // Append to container
    container.appendChild(toast);

    // Remove after 3 seconds (2.5s animation + 0.5s buffer)
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// --- Check Login for Apply Button ---
function checkLoginAndApply() {
    if (typeof isUserLoggedIn !== 'undefined' && isUserLoggedIn === true) {
        window.location.href = "../../../Common/MVC/php/ApplyRole.php";
    } else {
        // Now uses the toast instead of alert
        showToast("You need to login first to apply for a role!", "error");
    }
}