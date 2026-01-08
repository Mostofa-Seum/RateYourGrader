document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. STAR RATING LOGIC ---
    const ratingContainers = document.querySelectorAll('.star-rating');

    ratingContainers.forEach(container => {
        const stars = container.querySelectorAll('.star');
        const containerId = container.id;

        stars.forEach(star => {
            star.addEventListener('click', () => {
                const rating = star.dataset.value;
                
                // A. VISUAL: Color the stars yellow
                stars.forEach(s => s.classList.remove('active'));
                for (let i = 0; i < rating; i++) {
                    stars[i].classList.add('active');
                }

                // B. DATA: Update the Hidden Input ID so PHP can read it
                let inputId = "";
                if (containerId === 'overallRating') inputId = 'inputOverall';
                if (containerId === 'fairnessRating') inputId = 'inputFairness';
                if (containerId === 'feedbackRating') inputId = 'inputFeedback';

                const hiddenInput = document.getElementById(inputId);
                if (hiddenInput) {
                    hiddenInput.value = rating;
                }

                // C. HIDE ERRORS: Remove the error message if it was showing
                const errorId = 
                    containerId === 'overallRating' ? 'overallError' : 
                    containerId === 'fairnessRating' ? 'fairnessError' : 
                    'feedbackError';
                
                const errorElement = document.getElementById(errorId);
                if(errorElement) {
                    errorElement.style.display = 'none';
                }
            });
        });
    });

    // --- 2. RADIO BUTTON STYLING ---
    // This adds the 'selected' class to the parent label when clicked
    const radioInputs = document.querySelectorAll('.choice-item input[type="radio"]');
    
    radioInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Remove 'selected' class from all items in this specific group
            const group = this.closest('.choice-group');
            const allItems = group.querySelectorAll('.choice-item');
            allItems.forEach(item => item.classList.remove('selected'));

            // Add 'selected' class to the parent of the clicked input
            this.closest('.choice-item').classList.add('selected');
        });
    });
});

// --- 3. FORM VALIDATION ---
function validateForm() {
    let overall = document.getElementById('inputOverall').value;
    let fairness = document.getElementById('inputFairness').value;
    let feedback = document.getElementById('inputFeedback').value;

    // Check if any of the hidden inputs are still "0"
    if(overall === "0" || fairness === "0" || feedback === "0") {
        
        // Show specific error messages
        if(overall === "0") document.getElementById('overallError').style.display = 'block';
        if(fairness === "0") document.getElementById('fairnessError').style.display = 'block';
        if(feedback === "0") document.getElementById('feedbackError').style.display = 'block';
        
        alert("Please make sure to rate all star categories!");
        return false; // Stop submission
    }
    return true; // Allow submission
}