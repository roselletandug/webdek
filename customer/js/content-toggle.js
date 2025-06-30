// Use event delegation on sidebar-menu to handle clicks properly
document.querySelector('.sidebar-menu').addEventListener('click', function(e) {
    if (e.target && e.target.tagName === 'A') {
        e.preventDefault();
        const text = e.target.textContent.trim().toLowerCase();

        const homeSection = document.getElementById('home-section');
        const bookSection = document.getElementById('book-section');
        const gallerySection = document.getElementById('gallery-section');
        const feedbackSection = document.getElementById('feedback-section');
        const packagesSection = document.getElementById('packages-section');
        const aboutSection = document.getElementById('about-section');
        const menuLinks = document.querySelectorAll('.sidebar-menu li a');

        // Hide all sections
        if (homeSection) homeSection.style.display = 'none';
        if (bookSection) bookSection.style.display = 'none';
        if (packagesSection) packagesSection.style.display = 'none';
        if (aboutSection) aboutSection.style.display = 'none';
        if (gallerySection) gallerySection.style.display = 'none';
        if (feedbackSection) feedbackSection.style.display = 'none';

        // Remove active class from all links
        menuLinks.forEach(l => l.classList.remove('active'));

        // Add active class to clicked link
        e.target.classList.add('active');

        // Show the selected section
        if (text === 'home') {
            if (homeSection) homeSection.style.display = 'block';
            if (packagesSection) packagesSection.style.display = 'block';
            if (aboutSection) aboutSection.style.display = 'block';
        } else if (text === 'book') {
            if (bookSection) bookSection.style.display = 'block';
        } else if (text === 'gallery') {
            if (gallerySection) gallerySection.style.display = 'block';
        } else if (text === 'feedback') {
            if (feedbackSection) feedbackSection.style.display = 'block';
        }
    }
});

// Booking form image payment preview and validation
const imagePaymentInput = document.getElementById('imagePayment');
const paymentQRPreview = document.getElementById('paymentQRPreview');
const bookingForm = document.getElementById('booking-form');

if (imagePaymentInput) {
    imagePaymentInput.addEventListener('change', () => {
        const file = imagePaymentInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                paymentQRPreview.src = e.target.result;
                paymentQRPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            paymentQRPreview.src = '';
            paymentQRPreview.style.display = 'none';
        }
    });
}

if (bookingForm) {
    bookingForm.addEventListener('submit', (e) => {
        const contactNo = document.getElementById('contactNo').value.trim();
        const ageOfCelebrant = parseInt(document.getElementById('ageOfCelebrant').value, 10);

        // Validate contact number length (11 digits)
        const contactNoPattern = /^\d{11}$/;
        if (!contactNoPattern.test(contactNo)) {
            alert('Contact No. must be exactly 11 digits.');
            e.preventDefault();
            return;
        }

        // Validate age of celebrant max 122
        if (isNaN(ageOfCelebrant) || ageOfCelebrant < 0 || ageOfCelebrant > 122) {
            alert('Age of Celebrant must be a number between 0 and 122.');
            e.preventDefault();
            return;
        }
    });

    // Restrict contactNo input to digits only
    const contactNoInput = document.getElementById('contactNo');
    if (contactNoInput) {
        contactNoInput.addEventListener('input', (e) => {
            contactNoInput.value = contactNoInput.value.replace(/\D/g, '');
        });

        // Prevent typing non-digit characters
        contactNoInput.addEventListener('keypress', (e) => {
            const char = String.fromCharCode(e.which);
            if (!/[0-9]/.test(char)) {
                e.preventDefault();
            }
        });
    }
}
