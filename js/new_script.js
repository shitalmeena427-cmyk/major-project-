// Enhanced JavaScript with Dark Mode, AJAX, and Modern Features

// Dark Mode Toggle
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            themeToggle.textContent = newTheme === 'dark' ? '☀️' : '🌙';
        });

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        themeToggle.textContent = savedTheme === 'dark' ? '☀️' : '🌙';
    }
});

// Timer functionality with callback
function startTimer(duration, display, onTimeout) {
    var timer = duration, minutes, seconds;
    var interval = setInterval(function () {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);
        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;
        display.textContent = minutes + ":" + seconds;
        if (--timer < 0) {
            clearInterval(interval);
            display.textContent = "00:00";
            if (onTimeout) onTimeout();
        }
    }, 1000);
    return interval;
}

// Prevent page refresh during quiz
window.onbeforeunload = function() {
    if (document.getElementById('quizForm')) {
        return "Are you sure you want to leave? Your progress will be lost.";
    }
};

// AJAX utility function
function ajaxRequest(url, method = 'GET', data = null, callback, errorCallback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    callback(response);
                } catch (e) {
                    callback(xhr.responseText);
                }
            } else {
                if (errorCallback) {
                    errorCallback(xhr.status, xhr.responseText);
                } else {
                    console.error('AJAX Error:', xhr.status, xhr.responseText);
                }
            }
        }
    };
    xhr.send(data ? JSON.stringify(data) : null);
}

// Auto-save quiz answers
function autoSaveAnswers(quizId, answers) {
    const data = { quiz_id: quizId, answers: answers };
    ajaxRequest('auto_save.php', 'POST', data, function(response) {
        console.log('Auto-saved:', response);
        showNotification('Progress saved automatically', 'success');
    }, function(status, error) {
        console.error('Auto-save failed:', error);
    });
}

// Live leaderboard update
function updateLeaderboard() {
    ajaxRequest('get_leaderboard.php', 'GET', null, function(data) {
        const leaderboardEl = document.getElementById('leaderboard');
        if (leaderboardEl) {
            leaderboardEl.innerHTML = data.html;
            fadeIn('leaderboard');
        }
    });
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        ${message}
        <span class="close" onclick="this.parentElement.remove()">&times;</span>
    `;
    document.body.appendChild(notification);
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Mark question for review
function markForReview(questionId) {
    const questionEl = document.querySelector(`[data-question="${questionId}"]`);
    if (questionEl) {
        questionEl.classList.toggle('marked');
        const marked = questionEl.classList.contains('marked');
        ajaxRequest('mark_review.php', 'POST', { question_id: questionId, marked: marked }, function(response) {
            showNotification(marked ? 'Marked for review' : 'Unmarked', 'info');
        });
    }
}

// Initialize on page load
window.onload = function () {
    var display = document.querySelector('#timer');
    if (display) {
        var fiveMinutes = 60 * 5; // 5 minutes, can be dynamic
        startTimer(fiveMinutes, display, function() {
            showNotification("Time's up! Submitting quiz.", 'warning');
            setTimeout(() => {
                document.getElementById('quizForm').submit();
            }, 2000);
        });
    }

    // Start leaderboard polling
    if (document.getElementById('leaderboard')) {
        updateLeaderboard();
        setInterval(updateLeaderboard, 30000); // Update every 30 seconds
    }

    // Auto-save for quiz
    const quizForm = document.getElementById('quizForm');
    if (quizForm) {
        const inputs = quizForm.querySelectorAll('input[type="radio"]');
        let saveTimeout;
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => {
                    const answers = {};
                    inputs.forEach(inp => {
                        if (inp.checked) {
                            answers[inp.name.replace('q', '')] = inp.value;
                        }
                    });
                    autoSaveAnswers(quizForm.quiz_id.value, answers);
                }, 1000); // Save after 1 second of inactivity
            });
        });
    }

    // Add mark for review buttons
    const markButtons = document.querySelectorAll('.mark-review');
    markButtons.forEach(button => {
        button.addEventListener('click', function() {
            const questionId = this.dataset.question;
            markForReview(questionId);
        });
    });
};

// Chart.js integration
function createChart(canvasId, type, data, options = {}) {
    const ctx = document.getElementById(canvasId);
    if (ctx) {
        new Chart(ctx, {
            type: type,
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                family: 'Inter'
                            }
                        }
                    }
                },
                ...options
            }
        });
    }
}

// Utility functions
function showSpinner(elementId) {
    const el = document.getElementById(elementId);
    if (el) {
        el.innerHTML = '<div class="spinner"></div>';
    }
}

function hideSpinner(elementId) {
    const el = document.getElementById(elementId);
    if (el) {
        el.innerHTML = '';
    }
}

function fadeIn(elementId) {
    const el = document.getElementById(elementId);
    if (el) {
        el.classList.add('fade-in');
    }
}

// Progress bar update
function updateProgress(current, total) {
    const progressBar = document.querySelector('.progress-fill');
    if (progressBar) {
        const percentage = (current / total) * 100;
        progressBar.style.width = percentage + '%';
    }
}

// PWA Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('ServiceWorker registered: ', registration);
            })
            .catch(function(registrationError) {
                console.log('ServiceWorker registration failed: ', registrationError);
            });
    });
}

// Install PWA prompt
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    showInstallPrompt();
});

function showInstallPrompt() {
    const installBtn = document.getElementById('install-btn');
    if (installBtn) {
        installBtn.style.display = 'block';
        installBtn.addEventListener('click', () => {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the install prompt');
                }
                deferredPrompt = null;
            });
        });
    }
}