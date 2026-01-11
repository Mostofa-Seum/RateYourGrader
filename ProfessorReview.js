document.addEventListener('DOMContentLoaded', () => {
    
    // --- 0. REDIRECT LOGIC START ---
    console.log("JS Loaded. Checking for redirect signal...");
    
    const signalDiv = document.getElementById('redirect-signal');

    if (signalDiv) {
        console.log("Redirect Signal Found! Redirecting in 2 seconds...");
        
        // Optional: Change button text to show user something is happening
        const submitBtn = document.querySelector('.btn-primary');
        if(submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = "Success! Redirecting...";
            submitBtn.style.backgroundColor = "#16a34a"; // Green
        }

        // Wait 2 seconds then go
        setTimeout(function() {
            window.location.href = "SearchOutput.php";
        }, 2000);
    } else {
        console.log("No redirect signal found. Staying on page.");
    }
    // --- REDIRECT LOGIC END ---


    // --- 1. STAR RATING LOGIC ---
    const ratingContainers = document.querySelectorAll('.star-rating');

    ratingContainers.forEach(container => {
        const stars = container.querySelectorAll('.star');
        const containerId = container.id;

        stars.forEach(star => {
            star.addEventListener('click', () => {
                const rating = star.dataset.value;
                
                // Color the stars
                stars.forEach(s => s.classList.remove('active'));
                for (let i = 0; i < rating; i++) {
                    stars[i].classList.add('active');
                }

                // Update Hidden Input
                let inputId = "";
                if (containerId === 'overallRating') inputId = 'inputOverall';
                if (containerId === 'fairnessRating') inputId = 'inputFairness';
                if (containerId === 'feedbackRating') inputId = 'inputFeedback';

                const hiddenInput = document.getElementById(inputId);
                if (hiddenInput) hiddenInput.value = rating;

                // Hide Errors
                const errorId = 
                    containerId === 'overallRating' ? 'overallError' : 
                    containerId === 'fairnessRating' ? 'fairnessError' : 
                    'feedbackError';
                
                const errorElement = document.getElementById(errorId);
                if(errorElement) errorElement.style.display = 'none';
            });
        });
    });

    // --- 2. RADIO BUTTON STYLING ---
    const radioInputs = document.querySelectorAll('.choice-item input[type="radio"]');
    radioInputs.forEach(input => {
        input.addEventListener('change', function() {
            const group = this.closest('.choice-group');
            group.querySelectorAll('.choice-item').forEach(item => item.classList.remove('selected'));
            this.closest('.choice-item').classList.add('selected');
        });
    });
});

// --- 3. VALIDATION ---
function validateForm() {
    // If the redirect signal is present, do not validate (we are already done)
    if (document.getElementById('redirect-signal')) {
        return false;
    }

    let overall = document.getElementById('inputOverall').value;
    let fairness = document.getElementById('inputFairness').value;
    let feedback = document.getElementById('inputFeedback').value;

    if(overall === "0" || fairness === "0" || feedback === "0") {
        if(overall === "0") document.getElementById('overallError').style.display = 'block';
        if(fairness === "0") document.getElementById('fairnessError').style.display = 'block';
        if(feedback === "0") document.getElementById('feedbackError').style.display = 'block';
        
        alert("Please make sure to rate all star categories!");
        return false; 
    }
    return true; 
}