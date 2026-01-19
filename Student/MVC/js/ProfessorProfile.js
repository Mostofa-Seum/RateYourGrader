document.addEventListener('DOMContentLoaded', () => {
    
    //   Handle "Rate This Professor" Button 
    const rateBtn = document.getElementById('rateBtnLoggedOut');
    const warningBox = document.getElementById('loginWarning');

    if (rateBtn) {
        rateBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Stop any default link behavior
            
            if (warningBox) {
                // Show the yellow warning box
                warningBox.style.display = 'block';
                // Optional: Scroll slightly to ensure it's seen
                warningBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    }

});

//  Helper Functions

// Custom Toast Function
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerText = message;

    // Append to container
    container.appendChild(toast);

    // Remove after 3 seconds
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Check Login for Apply Button 
function checkLoginAndApply() {
    if (typeof isUserLoggedIn !== 'undefined' && isUserLoggedIn === true) {
        window.location.href = "../../../Common/MVC/php/ApplyRole.php";
    } else {
        // Use Toast for Apply button
        showToast("You need to login first to apply for a role!", "error");
    }
}