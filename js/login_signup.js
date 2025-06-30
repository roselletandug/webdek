document.addEventListener('DOMContentLoaded', function () {
    const loginToggle = document.getElementById('login-toggle');
    const signupToggle = document.getElementById('signup-toggle');
    const loginForm = document.getElementById('login-form');
    const forgotPasswordForm = document.getElementById('forgot-password-form');

    loginToggle.addEventListener('click', function () {
        loginToggle.classList.add('active');
        signupToggle.classList.remove('active');
        loginForm.style.display = 'flex';
        document.getElementById('signup-form').style.display = 'none';
    });

    signupToggle.addEventListener('click', function () {
        signupToggle.classList.add('active');
        loginToggle.classList.remove('active');
        document.getElementById('signup-form').style.display = 'flex';
        loginForm.style.display = 'none';
    });

    // Show/hide password toggle for login and signup using toggle attribute and classList toggle
    const togglePasswordElements = document.querySelectorAll('.toggle-password');

    togglePasswordElements.forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const icon = this.querySelector('i');
            if (icon) {
                const inputSelector = this.getAttribute('toggle');
                const input = document.querySelector(inputSelector);
                if (input) {
                    if (input.getAttribute('type') === 'password') {
                        input.setAttribute('type', 'text');
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fas', 'fa-eye');
                    } else {
                        input.setAttribute('type', 'password');
                        icon.classList.remove('fas', 'fa-eye');
                        icon.classList.add('fas', 'fa-eye-slash');
                    }
                }
            }
        });
    });

    // Handle login form submission via AJAX
    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const username = document.getElementById('login-username').value.trim();
        const password = document.getElementById('login-password').value;

        if (!username || !password) {
            alert('Please enter both username and password.');
            return;
        }

        fetch('authenticate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.role === 'admin') {
                        window.location.href = '../admin/index.html';
                    } else if (data.role === 'customer') {
                        window.location.href = '../customer/index.html';
                    } else {
                        alert('Unknown user role.');
                    }
                } else {
                    alert(data.message || 'Login failed.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during login.');
            });
    });

    // Added signup form password confirmation validation
    const signupForm = document.getElementById('signup-form');
    if (signupForm) {
        signupForm.addEventListener('submit', function (e) {
            const password = document.getElementById('signup-password').value;
            const confirmPassword = document.getElementById('signup-confirm-password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password and Confirm Password do not match.');
                return false;
            }
        });
    }

    // Handle forgot password form password confirmation validation
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function (e) {
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New Password and Confirm Password do not match.');
                return false;
            }
        });
    }
});
