document.addEventListener('DOMContentLoaded', () => {
    
    // --- Intersection Observer for fade-in animations ---
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-in').forEach(el => {
        observer.observe(el);
    });

    // --- Smooth scrolling for anchor links ---
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const target = document.querySelector(targetId);
            
            if (target) {
                const headerOffset = 80; 
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // --- Navbar scroll effect ---
    const nav = document.querySelector('.navbar');
    if (nav) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                nav.classList.add('shadow-lg');
            } else {
                nav.classList.remove('shadow-lg');
            }
        });
    }
});

// --- HELPER FUNCTIONS ---

// Custom Toast Function
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerText = message;

    // Append to container
    container.appendChild(toast);

    // Remove after 3 seconds (2.5s animation + 0.5s buffer)
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function goToSearch() {
    const searchSection = document.getElementById('search');
    if (searchSection) {
        searchSection.scrollIntoView({ behavior: 'smooth' });
        
        setTimeout(() => {
            const input = document.getElementById('mainSearchInput');
            if(input) input.focus();
        }, 500); 
    }
}

function validateSearch() {
    const input = document.getElementById('mainSearchInput').value;
    if (!input || input.trim() === "") {
        // Replaced alert with Toast
        showToast("Please enter a Professor name, University, or Course to search.", "info");
        return false; 
    }
    return true; 
}

function checkLoginAndApply() {
    if (typeof isUserLoggedIn !== 'undefined' && isUserLoggedIn === true) {
        window.location.href = "ApplyRole.php";
    } else {
        // Replaced alert with Toast + Delayed Redirect
        showToast("You need to login first to apply for a role!", "error");
    }
}