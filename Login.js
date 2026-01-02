const authContainer = document.getElementById('authContainer');
const slider = document.getElementById('slider');
const sliderBtn = document.getElementById('sliderBtn');
const sliderTitle = document.getElementById('sliderTitle');
const sliderText = document.getElementById('sliderText');

// Check initial state from HTML class
let isSignupMode = authContainer.classList.contains('signup-mode');

sliderBtn.addEventListener('click', () => {
    isSignupMode = !isSignupMode;

    if (isSignupMode) {
        authContainer.classList.add('signup-mode');
        slider.classList.add('active');

        setTimeout(() => {
            sliderTitle.textContent = 'Hello!';
            sliderText.textContent = 'Enter your personal details and start your journey with us';
            sliderBtn.textContent = 'Sign In';
        }, 200);
    } else {
        authContainer.classList.remove('signup-mode');
        slider.classList.remove('active');

        setTimeout(() => {
            sliderTitle.textContent = 'Welcome Back!';
            sliderText.textContent =
                'To keep connected with us please login with your personal info';
            sliderBtn.textContent = 'Sign Up';
        }, 200);
    }
});