let reviewIdToDelete = null;
let reviewTypeToDelete = null;

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

function showSuccessMessage(message) {
    const successMessage = document.getElementById('successMessage');
    if (!successMessage) return;

    successMessage.textContent = message;
    successMessage.classList.add('active');
    
    setTimeout(() => {
        successMessage.classList.remove('active');
    }, 3000);
}

//  PROFILE UPDATES 

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

    fetch('UserDashboard.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.querySelector('.profile-name').textContent = username;
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

    fetch('UserDashboard.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showMessage(msgId, '✔ ' + data.message, 'success');
            document.getElementById('current-password').value = '';
            document.getElementById('new-password').value = '';
            document.getElementById('confirm-password').value = '';
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

//  REVIEWS MODAL 

function openReviewsModal(status) {
    const modal = document.getElementById('reviewsModal');
    const title = document.getElementById('reviews-modal-title');
    const list = document.getElementById('reviews-list');

    modal.classList.add('active');
    title.textContent = status.charAt(0).toUpperCase() + status.slice(1) + ' Reviews';

    // Access data safely
    const reviews = (window.reviewsData && window.reviewsData[status]) ? window.reviewsData[status] : [];
    
    list.innerHTML = '';

    if (reviews.length === 0) {
        list.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p>No ${status} reviews at the moment.</p>
            </div>
        `;
        return;
    }

    reviews.forEach(review => {
        const rejectionReason = status === 'rejected' && review.rejectionReason
            ? `<div class="review-meta"><strong>Rejection reason:</strong> ${review.rejectionReason}</div>`
            : '';

        const item = document.createElement('div');
        item.className = 'review-item';
        // Add ID just in case, though we rely on reload now
        item.id = `review-card-${review.id}`;
        
        item.innerHTML = `
            <div class="review-header">
                <div class="review-title">${review.title}</div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <div class="review-status ${status}">${status.toUpperCase()}</div>
                    <button class="delete-icon-btn" onclick="initiateDelete(${review.id}, '${status}')" title="Delete Review">
                        🗑️
                    </button>
                </div>
            </div>
            <div class="review-meta">
                <strong>Rating:</strong> ⭐ ${review.rating} | <strong>Review ID:</strong> ${review.date}
            </div>
            ${rejectionReason}
            <div class="review-content">${review.content}</div>
        `;
        list.appendChild(item);
    });
}

function closeReviewsModal() {
    document.getElementById('reviewsModal').classList.remove('active');
}

//  DELETE LOGIC  & PAGE RELOAD

function initiateDelete(id, type) {
    reviewIdToDelete = id;
    reviewTypeToDelete = type;
    document.getElementById('deleteConfirmationModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteConfirmationModal').classList.remove('active');
    reviewIdToDelete = null;
    reviewTypeToDelete = null;
}

function confirmDelete() {
    if (!reviewIdToDelete || !reviewTypeToDelete) return;

    const formData = new FormData();
    formData.append('action', 'delete_review');
    formData.append('r_id', reviewIdToDelete);
    formData.append('type', reviewTypeToDelete);

    fetch('UserDashboard.php', { 
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        closeDeleteModal(); 

        if (data.status === 'success') {
            //  Show Success Message
            showSuccessMessage('✔ Review deleted succesfully!');
            
            //  Reload Page after a short delay so user sees the message
            setTimeout(() => {
                location.reload(); 
            }, 1000); 

        } else {
            showSuccessMessage('⚠ ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        closeDeleteModal();
        showSuccessMessage('⚠ Network error.');
    });
}

//  INITIALIZATION 

function renderRecentReviews() {
    const list = document.getElementById('recent-reviews-list');
    if (!list) return;

    // Use empty object default if window.reviewsData is missing
    const data = window.reviewsData || { accepted: [], pending: [], rejected: [] };

    let allReviews = [];
    if (data.accepted) allReviews = allReviews.concat(data.accepted.map(r => ({...r, status: 'accepted'})));
    if (data.pending) allReviews = allReviews.concat(data.pending.map(r => ({...r, status: 'pending'})));
    if (data.rejected) allReviews = allReviews.concat(data.rejected.map(r => ({...r, status: 'rejected'})));

    // Sort by ID descending (newest first)
    allReviews.sort((a, b) => b.id - a.id);

    const recent = allReviews.slice(0, 6);
    list.innerHTML = '';

    if (recent.length === 0) {
        list.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p>No reviews yet.</p>
            </div>`;
        return;
    }

    recent.forEach(review => {
        const item = document.createElement('div');
        item.className = 'recent-review-item';
        item.innerHTML = `
            <div class="recent-review-header">
                <div class="recent-review-title">${review.title}</div>
                <div class="recent-review-status ${review.status}">${review.status.toUpperCase()}</div>
            </div>
            <div class="recent-review-meta">
                <strong>Rating:</strong> ⭐ ${review.rating} | <strong>Review ID:</strong> ${review.date}
            </div>
        `;
        list.appendChild(item);
    });
}

// Events
document.addEventListener('DOMContentLoaded', () => {
    renderRecentReviews();
    
    // Close modals on outside click
    window.addEventListener('click', (e) => {
        const reviewsModal = document.getElementById('reviewsModal');
        const deleteModal = document.getElementById('deleteConfirmationModal');

        if (e.target === reviewsModal) closeReviewsModal();
        if (e.target === deleteModal) closeDeleteModal();
    });
});