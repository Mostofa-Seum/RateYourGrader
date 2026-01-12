// Function to show the "Reviewers cannot review" message
function showReviewerMessage() {
    var msg = document.getElementById("reviewer-warning-msg");
    if (msg) {
        // Show the reviewer warning
        msg.style.display = "block";
        
        // Hide the login warning if it happens to be open
        var loginMsg = document.getElementById("login-warning-msg");
        if(loginMsg) {
            loginMsg.style.display = "none";
        }
        
        // Smooth scroll to the message so the user notices it
        msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}