document.addEventListener('DOMContentLoaded', function () {
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
});
