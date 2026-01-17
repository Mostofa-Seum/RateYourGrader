function submitApplication() {
    // Get values
    const role = document.getElementById('role').value;
    const reasonField = document.getElementById('reason');
    const reason = reasonField.value.trim(); // Trim removes leading/trailing spaces
    
    // 1. Validation: Check Role
    if (!role) {
        showMessage('⚠ Please select a role.', 'error');
        return;
    }

    // 2. Validation: Check Reason (The requested change)
    if (!reason) {
        showMessage('⚠ The reason field cannot be empty. Please explain why you want this role.', 'error');
        reasonField.focus(); // Move cursor to the text box
        return; // Stop execution
    }

    const formData = new FormData();
    formData.append('action', 'submit_application');
    formData.append('role', role);
    formData.append('reason', reason);

    // Disable button to indicate loading
    const btn = document.querySelector('.btn-primary');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Submitting...';

    fetch('ApplyRole.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = originalText;

        if (data.status === 'success') {
            showMessage('✔ ' + data.message, 'success');
            // Clear form
            document.getElementById('applyForm').reset();
            // Optional: Redirect after a few seconds
            // setTimeout(() => window.location.href = 'HomePage.php', 2000);
        } else {
            showMessage('⚠ ' + data.message, 'error');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.textContent = originalText;
        showMessage('⚠ System Error. Please try again later.', 'error');
        console.error('Error:', error);
    });
}

function showMessage(message, type) {
    const box = document.getElementById('response-message');
    
    // Reset animation
    box.className = 'message-box';
    void box.offsetWidth; 
    
    box.className = `message-box active ${type}`;
    box.textContent = message;

    // Auto hide after 5 seconds if success, keep if error
    if (type === 'success') {
        setTimeout(() => {
            box.className = 'message-box';
            box.textContent = '';
        }, 5000);
    }
}