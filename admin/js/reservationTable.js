let allBookings = [];

document.addEventListener('DOMContentLoaded', function () {
    const bookingTableBody = document.querySelector('#bookingTable tbody');
    const searchInput = document.getElementById('searchName');
    const reportMonth = document.getElementById('reportMonth');
    const reportYear = document.getElementById('reportYear');
    const generateReportBtn = document.getElementById('generateReportBtn');

    async function fetchBookings(search = '') {
        try {
            const url = `get_bookings.php?search=${encodeURIComponent(search)}`;
            const response = await fetch(url);
            const result = await response.json();
            if (result.success) {
                return result.data;
            } else {
                alert('Failed to fetch bookings: ' + result.message);
                return [];
            }
        } catch (error) {
            alert('Error fetching bookings: ' + error);
            return [];
        }
    }

    async function loadBookings() {
        const search = searchInput.value.trim();
        const bookings = await fetchBookings(search);
        allBookings = bookings;  // Assign to global variable for edit modal
        renderBookings(bookings);
    }
    loadBookings();

    function renderBookings(bookings) {
        bookingTableBody.innerHTML = '';
        bookings.forEach(booking => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${booking.last_name}</td>
                <td>${booking.first_name}</td>
                <td>${booking.contact_no}</td>
                <td>${booking.address}</td>
                <td>${booking.theme}</td>
                <td>${booking.color_theme}</td>
                <td>${booking.date_of_event}</td>
                <td>${booking.time_of_event}</td>
                <td>${booking.venue_address}</td>
                <td>${booking.name_of_celebrant}</td>
                <td>${booking.age_of_celebrant}</td>
                <td>${booking.package}</td>
                <td>${booking.sample_event_design ? `<a href="#" class="view-sample-event-design" data-src="${booking.sample_event_design}">View</a>` : ''}</td>
                <td>${booking.payment ?? ''}</td>
                <td>${booking.image_payment ? `<a href="#" class="view-image-payment" data-src="${booking.image_payment}">View</a>` : ''}</td>
                <td class="status-cell" style="cursor:pointer;">${booking.status}</td>
                <td class="reason-cell">${booking.reason || ''}</td>
                <td>${booking.remarks ? booking.remarks.replace(/<\/?[^>]+(>|$)/g, "") : ''}</td>
                <td>
                    <span class="action-icon edit-icon" title="Edit" data-id="${booking.customer_id}" style="cursor:pointer; margin-right: 8px;">✏️</span>
                    <span class="action-icon approve-icon" title="Approve" data-id="${booking.customer_id}" style="cursor:pointer; margin-right: 8px;">✅</span>
                    <span class="action-icon decline-icon" title="Decline" data-id="${booking.customer_id}" style="cursor:pointer; margin-right: 8px;">❌</span>
                    <span class="action-icon delete-icon" title="Delete" data-id="${booking.customer_id}" style="cursor:pointer; color: red;">🗑️</span>
                </td>
            `;
            bookingTableBody.appendChild(row);
        });
    }

    searchInput.addEventListener('input', () => {
        loadBookings();
    });

    if (generateReportBtn) {
        generateReportBtn.addEventListener('click', () => {
            loadBookings();
        });
    }


    bookingTableBody.addEventListener('click', async (e) => {
        const target = e.target.closest('.action-icon');
        if (!target) return;
        
        console.log('Clicked icon:', target.className, 'Customer ID:', target.getAttribute('data-id'));
        const customerId = target.getAttribute('data-id');

        if (!customerId) {
            alert('Customer ID not found');
            return;
        }

        if (target.classList.contains('approve-icon')) {
            console.log('Approve icon clicked');
            if (!confirm('Are you sure you want to approve this booking?')) return;
            
            const newStatus = 'Approved';
            try {
                const formData = new FormData();
                formData.append('customer_id', customerId);
                formData.append('status', newStatus);
                formData.append('remarks', ''); // Clear remarks for approved bookings

                const response = await fetch('update_booking_status.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                if (result.success) {
                    await loadBookings();
                    alert('Booking approved successfully!');
                } else {
                    alert('Failed to approve booking: ' + result.message);
                }
            } catch (error) {
                console.error('Error approving booking:', error);
                alert('Error approving booking: ' + error.message);
            }
        } 
        else if (target.classList.contains('decline-icon')) {
            console.log('Decline icon clicked');
            const reason = prompt('Please provide a reason for declining this booking:');
            if (reason === null) return; // User cancelled
            if (reason.trim() === '') {
                alert('Reason is required for declining a booking.');
                return;
            }
            
            const newStatus = 'Declined';
            try {
                const formData = new FormData();
                formData.append('customer_id', customerId);
                formData.append('status', newStatus);
                formData.append('remarks', reason);

                const response = await fetch('update_booking_status.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                if (result.success) {
                    await loadBookings();
                    alert('Booking declined successfully!');
                } else {
                    alert('Failed to decline booking: ' + result.message);
                }
            } catch (error) {
                console.error('Error declining booking:', error);
                alert('Error declining booking: ' + error.message);
            }
        } 
        else if (target.classList.contains('delete-icon')) {
            console.log('Delete icon clicked');
            if (!confirm('Are you sure you want to delete this booking? This action cannot be undone.')) return;
            
            try {
                const formData = new FormData();
                formData.append('customer_id', customerId);

                const response = await fetch('delete_booking.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                if (result.success) {
                    await loadBookings();
                    alert('Booking deleted successfully!');
                } else {
                    alert('Failed to delete booking: ' + result.message);
                }
            } catch (error) {
                console.error('Error deleting booking:', error);
                alert('Error deleting booking: ' + error.message);
            }
        } 
        else if (target.classList.contains('edit-icon')) {
            console.log('Edit icon clicked');
            e.preventDefault();
            const booking = allBookings.find(b => b.customer_id == customerId);
            if (!booking) {
                alert('Booking data not found');
                return;
            }
            
            // Populate edit form
            const editBookingId = document.getElementById('editBookingId');
            const editLastName = document.getElementById('editLastName');
            const editFirstName = document.getElementById('editFirstName');
            const editContactNo = document.getElementById('editContactNo');
            const editAddress = document.getElementById('editAddress');
            const editTheme = document.getElementById('editTheme');
            const editSpecificTheme = document.getElementById('editSpecificTheme');
            const editColorTheme = document.getElementById('editColorTheme');
            const editDateOfEvent = document.getElementById('editDateOfEvent');
            const editTimeOfEvent = document.getElementById('editTimeOfEvent');
            const editNameOfCelebrant = document.getElementById('editNameOfCelebrant');
            const editAgeOfCelebrant = document.getElementById('editAgeOfCelebrant');
            const editVenueAddress = document.getElementById('editVenueAddress');
            const editPackage = document.getElementById('editPackage');
            const editStatus = document.getElementById('editStatus');
            const editRemarks = document.getElementById('editRemarks');
            
            if (editBookingId) editBookingId.value = booking.customer_id;
            if (editLastName) editLastName.value = booking.last_name || '';
            if (editFirstName) editFirstName.value = booking.first_name || '';
            if (editContactNo) editContactNo.value = booking.contact_no || '';
            if (editAddress) editAddress.value = booking.address || '';
            if (editTheme) editTheme.value = booking.theme || '';
            if (editSpecificTheme) editSpecificTheme.value = booking.specific_theme || '';
            if (editColorTheme) editColorTheme.value = booking.color_theme || '';
            if (editDateOfEvent) editDateOfEvent.value = booking.date_of_event || '';
            if (editTimeOfEvent) editTimeOfEvent.value = booking.time_of_event || '';
            if (editNameOfCelebrant) editNameOfCelebrant.value = booking.name_of_celebrant || '';
            if (editAgeOfCelebrant) editAgeOfCelebrant.value = booking.age_of_celebrant || '';
            if (editVenueAddress) editVenueAddress.value = booking.venue_address || '';
            if (editPackage) editPackage.value = booking.package || '';
            if (editStatus) editStatus.value = booking.status || '';
            if (editRemarks) editRemarks.value = booking.remarks || '';
            
            const modal = document.getElementById('editBookingModal');
            if (modal) {
                modal.style.display = 'block';
            }
        }
    });

    // Status cell click handler for viewing decline reason
    bookingTableBody.addEventListener('click', (e) => {
        if (e.target.classList.contains('status-cell')) {
            const row = e.target.closest('tr');
            const status = e.target.textContent.trim();
            if (status === 'Declined') {
                const reasonCell = row.querySelector('td:nth-child(17)'); // Reason column
                const reason = reasonCell ? reasonCell.textContent.trim() : '';
                alert('Reason for decline: ' + (reason || 'No reason provided'));
            }
        }
    });

    // Modal handling
    const editModal = document.getElementById('editBookingModal');
    const editModalCloseBtn = document.getElementById('editModalClose');
    if (editModalCloseBtn) {
        editModalCloseBtn.addEventListener('click', () => {
            editModal.style.display = 'none';
        });
    }

    // Edit form submission
    const editBookingForm = document.getElementById('editBookingForm');
    if (editBookingForm) {
        editBookingForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(editBookingForm);
            
            // Make sure we're using customer_id
            const bookingId = document.getElementById('editBookingId').value;
            formData.set('customer_id', bookingId);
            
            try {
                const response = await fetch('edit_booking.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    await loadBookings();
                    editModal.style.display = 'none';
                    alert('Booking updated successfully!');
                } else {
                    alert('Failed to update booking: ' + result.message);
                }
            } catch (error) {
                console.error('Error updating booking:', error);
                alert('Error updating booking: ' + error.message);
            }
        });
    }
});