// ====================
// THEME MANAGEMENT
// ====================

// Prevent FOUC (Flash of Unstyled Content) on page load
function initializeThemeBeforeRender() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
    } else {
        // Set light theme by default
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
    }
}

// Call immediately to prevent flickering
initializeThemeBeforeRender();

function setTheme(theme) {
    console.log('setTheme called with theme:', theme);
    document.documentElement.setAttribute('data-theme', theme);
    
    const toggle = document.getElementById('theme-toggle');
    if (toggle) {
        toggle.textContent = theme === 'dark' ? '☀️' : '🌙';
        
        // Add rotation animation
        toggle.classList.add('rotating');
        setTimeout(() => toggle.classList.remove('rotating'), 500);
    }
    
    localStorage.setItem('theme', theme);
    console.log('Theme set to:', theme);
}

function toggleTheme() {
    console.log('toggleTheme function called');
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    console.log('Current theme:', currentTheme);
    setTheme(currentTheme === 'dark' ? 'light' : 'dark');
}

function startTimer(duration, display, onTimeout) {
    let timer = duration;
    const interval = setInterval(function () {
        const minutes = String(Math.floor(timer / 60)).padStart(2, '0');
        const seconds = String(timer % 60).padStart(2, '0');
        display.textContent = `${minutes}:${seconds}`;

        if (--timer < 0) {
            clearInterval(interval);
            if (typeof onTimeout === 'function') {
                onTimeout();
            }
        }
    }, 1000);
}

function initQuizTimer() {
    const display = document.querySelector('#timer');
    if (display) {
        const fiveMinutes = 60 * 5;
        startTimer(fiveMinutes, display, function() {
            alert("Time's up! Submitting quiz.");
            const form = document.getElementById('quizForm');
            if (form) {
                form.submit();
            }
        });
    }
}

function initQuestionNavigation() {
    const questionCards = Array.from(document.querySelectorAll('.question-card'));
    if (!questionCards.length) {
        return;
    }

    const stepButtons = Array.from(document.querySelectorAll('.question-step'));
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const reviewToggle = document.getElementById('reviewToggle');
    const reviewInfo = document.getElementById('reviewInfo');
    let activeIndex = 0;

    function updateStepButtons() {
        stepButtons.forEach((button, index) => {
            button.classList.toggle('active', index === activeIndex);
            const card = questionCards[index];
            const isReviewed = card.dataset.review === 'true';
            button.classList.toggle('reviewed', isReviewed);
        });
    }

    function showQuestion(index) {
        questionCards.forEach((card, i) => {
            card.classList.toggle('active', i === index);
        });
        activeIndex = index;
        updateStepButtons();
        if (reviewInfo) {
            const card = questionCards[activeIndex];
            reviewInfo.textContent = card.dataset.review === 'true' ? 'Marked for review' : 'Not marked';
        }
    }

    function toggleReview() {
        const card = questionCards[activeIndex];
        const isReviewed = card.dataset.review === 'true';
        card.dataset.review = isReviewed ? 'false' : 'true';
        updateStepButtons();
        if (reviewInfo) {
            reviewInfo.textContent = card.dataset.review === 'true' ? 'Marked for review' : 'Not marked';
        }
    }

    stepButtons.forEach((button, index) => {
        button.addEventListener('click', () => showQuestion(index));
    });

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            showQuestion(Math.max(0, activeIndex - 1));
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            showQuestion(Math.min(questionCards.length - 1, activeIndex + 1));
        });
    }

    if (reviewToggle) {
        reviewToggle.addEventListener('click', toggleReview);
    }

    showQuestion(activeIndex);
}

window.onbeforeunload = function () {
    if (document.getElementById('quizForm')) {
        return 'Are you sure you want to leave? Your progress will be lost.';
    }
};

document.addEventListener('DOMContentLoaded', function () {
    // Theme is already initialized before render
    // Just set up the event listeners
    
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        console.log('Theme toggle button found:', themeToggle);
        themeToggle.addEventListener('click', function(e) {
            console.log('Theme toggle clicked');
            toggleTheme();
        });
        
        // Add ripple effect on click
        themeToggle.addEventListener('click', function (e) {
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    } else {
        console.log('Theme toggle button not found');
    }

    initQuizTimer();
    initQuestionNavigation();
});

// For charts, if using Chart.js
// Include Chart.js from CDN in HTML
// Then, for example in result page:
// var ctx = document.getElementById('myChart').getContext('2d');
// var myChart = new Chart(ctx, { ... });