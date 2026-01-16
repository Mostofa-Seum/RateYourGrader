// --- HELPER FUNCTIONS (Same as UserDashboard) ---

function toggleEditProfileSection() {
    const section = document.getElementById('edit-profile-section');
    if (!section) return;

    if (section.style.display === 'none' || section.style.display === '') {
        section.style.display = 'block';
        section.scrollIntoView({ behavior: 'smooth' });
    } else {
        section.style.display = 'none';
    }
}

function showMessage(elementId, message, type) {
    const messageBox = document.getElementById(elementId);
    if (!messageBox) return;
    
    // Reset animation
    messageBox.className = 'message-box';
    void messageBox.offsetWidth; 
    
    messageBox.className = `message-box active ${type}`;
    messageBox.textContent = message;

    setTimeout(() => {
        messageBox.className = 'message-box'; 
        messageBox.textContent = '';
    }, 5000);
}

// --- PROFILE UPDATES (Same as UserDashboard) ---

function updateUsername() {
    const username = document.getElementById('username').value;
    const msgId = 'username-message';

    if (!username) {
        showMessage(msgId, '⚠ Please enter a username.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update_username');
    formData.append('username', username);

    fetch('UniversityRepDashboard.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.querySelector('.profile-name').textContent = 'Rep: ' + username;
            showMessage(msgId, '✔ ' + data.message, 'success');
        } else {
            showMessage(msgId, '⚠ ' + data.message, 'error');
        }
    })
    .catch(() => showMessage(msgId, '⚠ Server error.', 'error'));
}

function updatePassword() {
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const msgId = 'password-message';

    if (!currentPassword || !newPassword || !confirmPassword) {
        showMessage(msgId, '⚠ Please fill all fields.', 'error');
        return;
    }
    if (newPassword !== confirmPassword) {
        showMessage(msgId, '⚠ Passwords mismatch.', 'error');
        return;
    }
    if (newPassword.length < 6) {
        showMessage(msgId, '⚠ Min length 6 chars.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update_password');
    formData.append('current_password', currentPassword);
    formData.append('new_password', newPassword);
    formData.append('confirm_password', confirmPassword);

    fetch('UniversityRepDashboard.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showMessage(msgId, '✔ ' + data.message, 'success');
            resetPasswordForm();
        } else {
            showMessage(msgId, '⚠ ' + data.message, 'error');
        }
    })
    .catch(() => showMessage(msgId, '⚠ Server error.', 'error'));
}

function resetPasswordForm() {
    document.getElementById('current-password').value = '';
    document.getElementById('new-password').value = '';
    document.getElementById('confirm-password').value = '';
}