function showLoginWarning() {
    // Hide reviewer warning if open
    var revMsg = document.getElementById('reviewerWarning');
    if(revMsg) revMsg.style.display = 'none';

    // Show login warning
    var loginMsg = document.getElementById('loginWarning');
    if (loginMsg) {
        loginMsg.style.display = 'block';
    }
}

function showReviewerWarning() {
    // Hide login warning if open
    var loginMsg = document.getElementById('loginWarning');
    if(loginMsg) loginMsg.style.display = 'none';

    // Show reviewer warning
    var revMsg = document.getElementById('reviewerWarning');
    if (revMsg) {
        revMsg.style.display = 'block';
    }
}