// --- GLOBAL FUNCTIONS ---

function toggleEditProfileSection() {
    const section = document.getElementById('edit-profile-section');
    if (section.style.display === 'none' || section.style.display === '') {
        section.style.display = 'block';
        section.scrollIntoView({ behavior: 'smooth' });
    } else {
        section.style.display = 'none';
    }
}

function showMessage(elementId, message, type) {
    const messageBox = document.getElementById(elementId);
    messageBox.className = 'message-box active';
    messageBox.classList.add(type);
    messageBox.textContent = message;
    setTimeout(() => {
        messageBox.className = 'message-box';
        messageBox.textContent = '';
    }, 5000);
}

// --- UPDATE USERNAME FUNCTION ---
function updateUsername() {
    const username = document.getElementById('username').value;
    document.getElementById('username-message').textContent = '';

    if (!username) {
        showMessage('username-message', '⚠ Please enter a username.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update_username');
    formData.append('username', username);

    // FETCH PATH UPDATED TO POINT TO COMMON MODULE
    fetch('../../Common/MVC/php/update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.querySelector('.profile-name').textContent = username;
            showMessage('username-message', '✔ ' + data.message, 'success');
        } else {
            showMessage('username-message', '⚠ ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('username-message', '⚠ An error occurred connecting to the server.', 'error');
    });
}

function updatePassword() {
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    document.getElementById('password-message').textContent = '';

    if (!currentPassword || !newPassword || !confirmPassword) {
        showMessage('password-message', '⚠ Please fill in all password fields.', 'error');
        return;
    }

    if (newPassword !== confirmPassword) {
        showMessage('password-message', '⚠ New passwords do not match.', 'error');
        return;
    }

    if (newPassword.length < 6) {
        showMessage('password-message', '⚠ Password min length is 6 characters.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update_password');
    formData.append('current_password', currentPassword);
    formData.append('new_password', newPassword);
    formData.append('confirm_password', confirmPassword);

    // FETCH PATH UPDATED TO POINT TO COMMON MODULE
    fetch('../../Common/MVC/php/update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showMessage('password-message', '✔ ' + data.message, 'success');
            document.getElementById('current-password').value = '';
            document.getElementById('new-password').value = '';
            document.getElementById('confirm-password').value = '';
        } else {
            showMessage('password-message', '⚠ ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('password-message', '⚠ An error occurred connecting to the server.', 'error');
    });
}

function resetPasswordForm() {
    document.getElementById('current-password').value = '';
    document.getElementById('new-password').value = '';
    document.getElementById('confirm-password').value = '';
}

function openReviewsModal(status) {
    document.getElementById('reviewsModal').classList.add('active');
    document.getElementById('reviews-modal-title').textContent = 
        status.charAt(0).toUpperCase() + status.slice(1) + ' Reviews';

    const reviews = reviewsData[status] || [];
    const reviewsList = document.getElementById('reviews-list');
    reviewsList.innerHTML = '';

    if (reviews.length === 0) {
        reviewsList.innerHTML = `
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
        const reviewHTML = `
            <div class="review-item">
                <div class="review-header">
                    <div class="review-title">${review.title}</div>
                    <div class="review-status ${status}">${status.toUpperCase()}</div>
                </div>
                <div class="review-meta">
                    <strong>Rating:</strong> ⭐ ${review.rating} | <strong>Review ID:</strong> ${review.date}
                </div>
                ${rejectionReason}
                <div class="review-content">${review.content}</div>
            </div>
        `;
        reviewsList.innerHTML += reviewHTML;
    });
}

function closeReviewsModal() {
    document.getElementById('reviewsModal').classList.remove('active');
}

function showSuccessMessage(message) {
    const successMessage = document.getElementById('successMessage');
    successMessage.textContent = message;
    successMessage.classList.add('active');
    setTimeout(() => { successMessage.classList.remove('active'); }, 3000);
}

function renderRecentReviews() {
    const recentReviewsList = document.getElementById('recent-reviews-list');
    if (!recentReviewsList) return;

    const allReviews = [
        ...reviewsData.accepted.map(review => ({ ...review, status: 'accepted' })),
        ...reviewsData.pending.map(review => ({ ...review, status: 'pending' })),
        ...reviewsData.rejected.map(review => ({ ...review, status: 'rejected' }))
    ];

    const recentReviews = allReviews.slice(0, 6);

    if (recentReviews.length === 0) {
        recentReviewsList.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p>No reviews yet.</p>
            </div>
        `;
        return;
    }

    recentReviewsList.innerHTML = '';
    recentReviews.forEach(review => {
        const reviewHTML = `
            <div class="recent-review-item">
                <div class="recent-review-header">
                    <div class="recent-review-title">${review.title}</div>
                    <div class="recent-review-status ${review.status}">${review.status.toUpperCase()}</div>
                </div>
                <div class="recent-review-meta">
                    <strong>Rating:</strong> ⭐ ${review.rating} | <strong>Review ID:</strong> ${review.date}
                </div>
            </div>
        `;
        recentReviewsList.innerHTML += reviewHTML;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    renderRecentReviews();
    window.addEventListener('click', (e) => {
        const reviewsModal = document.getElementById('reviewsModal');
        if (e.target === reviewsModal) { closeReviewsModal(); }
    });
});

function resetUsernameForm() {
    location.reload(); 
}