// Initialize star ratings
function initializeStarRatings() {
    const ratingContainers = document.querySelectorAll('.star-rating');
    
    ratingContainers.forEach(container => {
        const stars = container.querySelectorAll('.star');
        const containerId = container.id;
        
        stars.forEach(star => {
            star.addEventListener('click', () => {
                const rating = star.dataset.value;
                stars.forEach(s => s.classList.remove('active'));
                
                for (let i = 0; i < rating; i++) {
                    stars[i].classList.add('active');
                }
                
                // Update the rating value display
                const valueElement = document.getElementById(containerId === 'overallRating' ? 'overallValue' : 
                                                            containerId === 'fairnessRating' ? 'fairnessValue' : 
                                                            'feedbackValue');
                valueElement.textContent = rating;
                
                // Clear error
                const errorId = containerId === 'overallRating' ? 'overallError' : 
                                containerId === 'fairnessRating' ? 'fairnessError' : 
                                'feedbackError';
                document.getElementById(errorId).classList.remove('show');
            });
        });
    });
}

// Update choice items styling
function updateChoice(input) {
    const allChoices = input.parentElement.parentElement.querySelectorAll('.choice-item');
    allChoices.forEach(choice => choice.classList.remove('selected'));
    input.parentElement.classList.add('selected');
    
    // Clear error
    const errorId = input.name === 'takeAgain' ? 'takeAgainError' : 'difficultyError';
    if (document.getElementById(errorId)) {
        document.getElementById(errorId).classList.remove('show');
    }
}

// Form submission
function submitReview(event) {
    event.preventDefault();
    
    // Validation
    const overallValue = document.getElementById('overallValue').textContent;
    const fairnessValue = document.getElementById('fairnessValue').textContent;
    const feedbackValue = document.getElementById('feedbackValue').textContent;
    const takeAgainValue = document.querySelector('input[name="takeAgain"]:checked');
    const difficultyValue = document.querySelector('input[name="difficulty"]:checked');
    
    let isValid = true;
    
    if (!overallValue || overallValue === '0') {
        document.getElementById('overallError').classList.add('show');
        isValid = false;
    }
    
    if (!fairnessValue || fairnessValue === '0') {
        document.getElementById('fairnessError').classList.add('show');
        isValid = false;
    }
    
    if (!feedbackValue || feedbackValue === '0') {
        document.getElementById('feedbackError').classList.add('show');
        isValid = false;
    }
    
    if (!takeAgainValue) {
        document.getElementById('takeAgainError').classList.add('show');
        isValid = false;
    }
    
    if (!difficultyValue) {
        document.getElementById('difficultyError').classList.add('show');
        isValid = false;
    }
    
    if (!isValid) {
        return;
    }
    
    // Collect form data
    const formData = {
        professorName: document.getElementById('professorName').textContent,
        overallRating: overallValue,
        fairnessRating: fairnessValue,
        feedbackRating: feedbackValue,
        takeAgain: takeAgainValue.value,
        courseName: document.querySelector('input[name="courseName"]').value,
        semester: document.querySelector('input[name="semester"]').value,
        review: document.querySelector('textarea[name="review"]').value,
        difficulty: difficultyValue.value,
        email: document.querySelector('input[name="email"]').value,
        timestamp: new Date().toISOString()
    };
    
    // Log the data (in a real app, this would be sent to a server)
    console.log('Review submitted:', formData);
    
    // Show success message
    const successMessage = document.getElementById('successMessage');
    successMessage.classList.add('show');
    
    // Reset form
    document.getElementById('reviewForm').reset();
    
    // Clear stars and values
    document.querySelectorAll('.star').forEach(star => star.classList.remove('active'));
    document.querySelectorAll('.rating-value').forEach(val => val.textContent = '0');
    document.querySelectorAll('.choice-item').forEach(item => item.classList.remove('selected'));
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Hide success message after 5 seconds
    setTimeout(() => {
        successMessage.classList.remove('show');
    }, 5000);
}

// Go back function
function goBack() {
    window.history.back();
}

// Load professor data (simulated from search)
function loadProfessorData() {
    // This would normally come from URL parameters or session storage
    const professorData = {
        name: 'Dr. Michael Johnson',
        department: 'Department of Computer Science',
        university: 'State University',
        rating: '4.5',
        reviews: '247',
        level: 'Advanced'
    };
    
    // Update the page with professor data
    document.getElementById('professorName').textContent = professorData.name;
    document.getElementById('professorDept').textContent = professorData.department;
    document.getElementById('professorUniversity').textContent = professorData.university;
    document.getElementById('currentRating').textContent = professorData.rating + '/5.0';
    document.getElementById('reviewCount').textContent = professorData.reviews;
    document.getElementById('courseLevel').textContent = professorData.level;
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    initializeStarRatings();
    loadProfessorData();
});

// Smooth scroll for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        if (this.getAttribute('href') !== '#' && !this.classList.contains('back-btn')) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });
});
        function validateForm() {
            let overall = document.getElementById('inputOverall').value;
            let fairness = document.getElementById('inputFairness').value;
            let feedback = document.getElementById('inputFeedback').value;

            if(overall == "0" || fairness == "0" || feedback == "0") {
                alert("Please make sure to rate all star categories!");
                return false;
            }
            return true;
        }
        
        // This connects the JS logic to the PHP hidden inputs
        // When star is clicked in JS, we need to update the hidden input name for PHP to see it
        document.addEventListener('DOMContentLoaded', () => {
            const overallStars = document.querySelectorAll('#overallRating .star');
            const fairnessStars = document.querySelectorAll('#fairnessRating .star');
            const feedbackStars = document.querySelectorAll('#feedbackRating .star');

            // Helper to add click listeners
            const addListeners = (stars, inputId) => {
                stars.forEach(star => {
                    star.addEventListener('click', () => {
                        document.getElementById(inputId).value = star.dataset.value;
                    });
                });
            };

            addListeners(overallStars, 'inputOverall');
            addListeners(fairnessStars, 'inputFairness');
            addListeners(feedbackStars, 'inputFeedback');
        });
        function initializeStarRatings() {
    const ratingContainers = document.querySelectorAll('.star-rating');
    
    ratingContainers.forEach(container => {
        const stars = container.querySelectorAll('.star');
        const containerId = container.id;
        
        stars.forEach(star => {
            star.addEventListener('click', () => {
                const rating = star.dataset.value;
                
                // 1. Visual: Color the stars
                stars.forEach(s => s.classList.remove('active'));
                for (let i = 0; i < rating; i++) {
                    stars[i].classList.add('active');
                }
                
                // 2. Visual: Update the text number (e.g. "4")
                // Determine which text span to update based on the container ID
                let valueSpanId = "";
                if (containerId === 'overallRating') valueSpanId = 'overallValue';
                else if (containerId === 'fairnessRating') valueSpanId = 'fairnessValue';
                else if (containerId === 'feedbackRating') valueSpanId = 'feedbackValue';
                
                const valueElement = document.getElementById(valueSpanId);
                if(valueElement) valueElement.textContent = rating;

                // 3. CRITICAL DATA UPDATE: Update the hidden input for PHP validation
                // Determine which hidden input to update
                let hiddenInputId = "";
                if (containerId === 'overallRating') hiddenInputId = 'inputOverall';
                else if (containerId === 'fairnessRating') hiddenInputId = 'inputFairness';
                else if (containerId === 'feedbackRating') hiddenInputId = 'inputFeedback';

                const hiddenInput = document.getElementById(hiddenInputId);
                if(hiddenInput) {
                    hiddenInput.value = rating; // <--- THIS WAS MISSING
                }

                // 4. Clear error message
                let errorId = "";
                if (containerId === 'overallRating') errorId = 'overallError';
                else if (containerId === 'fairnessRating') errorId = 'fairnessError';
                else if (containerId === 'feedbackRating') errorId = 'feedbackError';
                
                const errorEl = document.getElementById(errorId);
                if(errorEl) errorEl.classList.remove('show');
            });
        });
    });
}