// --- GLOBAL VARIABLES ---
// window.reviewerData is set in the PHP file

// --- HELPER FUNCTIONS (From UserDashboard) ---

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

// --- PROFILE UPDATES (From UserDashboard) ---

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

    fetch('ReviewerDashboard.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update profile name in the card immediately
            const profileNameEl = document.querySelector('.profile-name');
            if(profileNameEl) profileNameEl.textContent = 'Reviewer: ' + username;
            
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

    fetch('ReviewerDashboard.php', { method: 'POST', body: formData })
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

// --- REVIEWER SPECIFIC FUNCTIONS ---

function handleCardClick(type) {
    if (type === 'pending') {
        // Redirect to decision page
        window.location.href = 'ReviewerDecision.php';
    } 
    else if (type === 'accepted' || type === 'rejected') {
        // Show history in modal
        openHistoryModal(type);
    }
}

function openHistoryModal(status) {
    const modal = document.getElementById('historyModal');
    const title = document.getElementById('modal-title');
    const list = document.getElementById('modal-list');

    modal.classList.add('active');
    
    title.textContent = status.charAt(0).toUpperCase() + status.slice(1) + ' Reviews';

    // Access data safely
    const reviews = (window.reviewerData && window.reviewerData[status]) ? window.reviewerData[status] : [];
    
    list.innerHTML = '';

    if (reviews.length === 0) {
        list.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📂</div>
                <p>No ${status} reviews found.</p>
            </div>
        `;
        return;
    }

    reviews.forEach(review => {
        const rejectionHtml = status === 'rejected' && review.rejectionReason
            ? `<div class="review-meta" style="color: #e74c3c;"><strong>Cause:</strong> ${review.rejectionReason}</div>`
            : '';

        const item = document.createElement('div');
        item.className = 'review-item';
        
        item.innerHTML = `
            <div class="review-header">
                <div class="review-title">${review.title}</div>
                <div class="review-status ${status}">${status.toUpperCase()}</div>
            </div>
            <div class="review-meta">
                <strong>Rating:</strong> ⭐ ${review.rating} | <strong>Review ID:</strong> ${review.date}
            </div>
            ${rejectionHtml}
            <div class="review-content">${review.content}</div>
        `;
        list.appendChild(item);
    });
}

function closeHistoryModal() {
    document.getElementById('historyModal').classList.remove('active');
}

function renderRecentActivity() {
    const list = document.getElementById('recent-reviews-list');
    if (!list) return;

    // Show recently Accepted reviews
    const reviews = (window.reviewerData && window.reviewerData['accepted']) ? window.reviewerData['accepted'] : [];
    
    const recent = reviews.slice(0, 5);
    list.innerHTML = '';

    if (recent.length === 0) {
        list.innerHTML = `
            <div class="empty-state">
                <p>No recently approved reviews.</p>
            </div>`;
        return;
    }

    recent.forEach(review => {
        const item = document.createElement('div');
        item.className = 'recent-review-item';
        item.innerHTML = `
            <div class="recent-review-header">
                <div class="recent-review-title">${review.title}</div>
                <div class="recent-review-status accepted">APPROVED</div>
            </div>
            <div class="recent-review-meta">
                 ${review.date}
            </div>
        `;
        list.appendChild(item);
    });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    renderRecentActivity();
    
    // Close modal on outside click
    window.addEventListener('click', (e) => {
        const modal = document.getElementById('historyModal');
        if (e.target === modal) closeHistoryModal();
    });
});