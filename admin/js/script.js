/* JavaScript for sidebar dropdown and other interactions */

document.addEventListener('DOMContentLoaded', function () {
    // Sidebar menu click handling for dynamic content display
    const sidebarLinks = document.querySelectorAll('.sidebar-menu li a');
    const homeContent = document.getElementById('homeContent');
    const reservationContent = document.getElementById('reservationContent');
    const availabilityContent = document.getElementById('availabilityContent');
    const feedbackContent = document.getElementById('feedbackContent');
    const settingContent = document.getElementById('settingContent');

    // Add this block
    const sections = {
        homeContent,
        reservationContent,
        availabilityContent,
        feedbackContent,
        settingContent
    };

    sidebarLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            console.log('Sidebar link clicked:', this.textContent.trim());

            // Remove active class from all links
            sidebarLinks.forEach(l => l.classList.remove('active'));

            // Add active class to clicked link
            this.classList.add('active');

            // Hide all content sections
            for (const section in sections) {
                if (sections[section]) {
                    sections[section].style.display = 'none';
                }
            }

            // Show content based on clicked link
            const sectionToShow = this.getAttribute('data-section');
            if (sectionToShow && sectionToShow !== 'logout') {
                const sectionElement = document.getElementById(sectionToShow);
                if (sectionElement) {
                    sectionElement.style.display = 'block';
                    console.log('Showing section:', sectionToShow);
                }
            } else if (sectionToShow === 'logout') {
                console.log('Logout clicked');
                window.location.href = '../login/index.html';
            }
        });
    });


    // Modal for viewing images
    const modal = document.createElement('div');
    modal.classList.add('modal');
    modal.style.cssText = `
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.9);
    `;
    
    modal.innerHTML = `
        <span class="close" style="position: absolute; top: 15px; right: 35px; color: #f1f1f1; font-size: 40px; font-weight: bold; cursor: pointer;">&times;</span>
        <img class="modal-content" id="modalImage" style="margin: auto; display: block; width: 80%; max-width: 700px; margin-top: 50px;" />
    `;
    document.body.appendChild(modal);

    const modalImage = modal.querySelector('#modalImage');
    const closeBtn = modal.querySelector('.close');

    // Close modal on clicking close button or outside the image
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
        modalImage.src = '';
    });
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
            modalImage.src = '';
        }
    });

    // Event delegation for view links
    document.body.addEventListener('click', (e) => {
        if (e.target.classList.contains('view-image-payment') || e.target.classList.contains('view-sample-event-design')) {
            e.preventDefault();
            const src = e.target.getAttribute('data-src');
            if (src) {
                modalImage.src = src;
                modal.style.display = 'block';
            }
        }
    });

    // Fetch and display feedback in the feedback section
    const feedbackContainer = document.getElementById('feedbackContainer');

    if (feedbackContainer) {
        fetch('get_feedback.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && Array.isArray(data.data)) {
                    feedbackContainer.innerHTML = ''; // Clear existing content
                    data.data.forEach(feedback => {
                        const feedbackItem = document.createElement('div');
                        feedbackItem.className = 'feedback-item';
                        // Display last name, first name, rating, and comment
                        feedbackItem.innerHTML = `
                            <strong>${feedback.lastName} ${feedback.firstName}</strong><br/>
                            Rating: ${feedback.rating}<br/>
                            Comment: ${feedback.comment || 'No comment'}
                        `;
                        feedbackContainer.appendChild(feedbackItem);
                    });
                } else {
                    feedbackContainer.innerHTML = '<p>No feedback available.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching feedback:', error);
                feedbackContainer.innerHTML = '<p>Error loading feedback.</p>';
            });
    }
});
