function submitApplication() {
    // Get values
    const role = document.getElementById('role').value;
    const reason = document.getElementById('reason').value;
    
    // Basic validation
    if (!role) {
        showMessage('⚠ Please select a role.', 'error');
        return;
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