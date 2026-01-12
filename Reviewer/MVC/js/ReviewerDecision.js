// Function to Open Modal and set the Review ID
function openRejectModal(reviewId) {
    // 1. Show the overlay
    const modal = document.getElementById('rejectModal');
    modal.style.display = 'flex';

    // 2. Set the ID in the hidden input field so PHP knows which one to delete
    const hiddenInput = document.getElementById('modal_review_id');
    hiddenInput.value = reviewId;
    
    // 3. Focus on the textarea for better UX
    setTimeout(() => {
        document.getElementById('rejection_reason').focus();
    }, 100);
}

// Function to Close Modal
function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.style.display = 'none';
    
    // Clear the textarea
    document.getElementById('rejection_reason').value = '';
}

// Close modal if user clicks outside the content area
window.onclick = function(event) {
    const modal = document.getElementById('rejectModal');
    if (event.target === modal) {
        closeRejectModal();
    }
}