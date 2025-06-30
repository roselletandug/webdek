document.addEventListener('DOMContentLoaded', function () {
    const homeMonthSelect = document.getElementById('homeMonth');
    const homeYearSelect = document.getElementById('homeYear');
    const recentEventsCountEl = document.getElementById('recentEventsCount');
    const approvedEventsCountEl = document.getElementById('approvedEventsCount');
    const declinedEventsCountEl = document.getElementById('declinedEventsCount');
    const monthlyEventsCountEl = document.getElementById('monthlyEventsCount');
    const eventsLineChartCtx = document.getElementById('eventsLineChart').getContext('2d');
    const feedbackDoughnutChartCtx = document.getElementById('feedbackDoughnutChart').getContext('2d');

    let eventsLineChart;
    let feedbackDoughnutChart;

    // Check if all required elements exist
    function validateElements() {
        const elements = {
            homeMonthSelect,
            homeYearSelect,
            recentEventsCountEl,
            approvedEventsCountEl,
            declinedEventsCountEl,
            monthlyEventsCountEl
        };

        for (const [name, element] of Object.entries(elements)) {
            if (!element) {
                console.error(`Element ${name} not found`);
                return false;
            }
        }
        return true;
    }

    async function fetchData() {
        try {
            const month = homeMonthSelect.value;
            const year = homeYearSelect.value;

            console.log(`Fetching data for month: ${month}, year: ${year}`);

            const [bookingsRes, feedbackRes] = await Promise.all([
                fetch(`get_bookings.php?month=${month}&year=${year}`),
                fetch('get_feedback.php')
            ]);

            const bookingsResult = await bookingsRes.json();
            const feedbackResult = await feedbackRes.json();

            console.log('Bookings data received:', bookingsResult.data);

            if (bookingsResult.success && validateElements()) {
                updateSummaryCards(bookingsResult.data);
                updateEventsLineChart(bookingsResult.data);
            } else {
                console.warn('Failed to fetch bookings or elements missing');
            }

            if (feedbackResult.success) {
                updateFeedbackDoughnutChart(feedbackResult.data);
            } else {
                console.warn('Failed to fetch feedback data');
            }
        } catch (error) {
            console.error('Error fetching data:', error);
        }
    }

    function updateSummaryCards(bookings) {
        // Validate inputs
        if (!bookings || !Array.isArray(bookings)) {
            console.error('Invalid bookings data');
            return;
        }

        if (!homeMonthSelect.value || !homeYearSelect.value) {
            console.error('Month or Year not selected');
            return;
        }

        const selectedMonth = parseInt(homeMonthSelect.value);
        const selectedYear = parseInt(homeYearSelect.value);

        console.log(`Filtering for month: ${selectedMonth}, year: ${selectedYear}`);

        // Filter bookings by selected month and year
        const filteredBookings = bookings.filter(booking => {
            if (!booking.date_of_event) {
                console.warn('Booking missing date_of_event:', booking);
                return false;
            }
            
            const bookingDate = new Date(booking.date_of_event);
            const bookingMonth = bookingDate.getMonth() + 1; // JavaScript months are 0-indexed
            const bookingYear = bookingDate.getFullYear();
            
            return bookingMonth === selectedMonth && bookingYear === selectedYear;
        });

        console.log(`Filtered bookings count: ${filteredBookings.length}`);

        // Calculate statistics
        // Recent events: Count of bookings with date_of_event between current date and 7 days from now
        const now = new Date();
        const sevenDaysLater = new Date();
        sevenDaysLater.setDate(now.getDate() + 7);

        const recentEventsBookings = filteredBookings.filter(booking => {
            const bookingDate = new Date(booking.date_of_event);
            return bookingDate >= now && bookingDate <= sevenDaysLater;
        });

        const recentEvents = recentEventsBookings.length;

        // Events for selected month/year
        const approvedEvents = filteredBookings.filter(b => b.status === 'Approved').length;
        const declinedEvents = filteredBookings.filter(b => b.status === 'Declined').length;
        const pendingEvents = filteredBookings.filter(b => b.status === 'Pending').length;
        const monthlyEvents = filteredBookings.length;

        // Update the display elements with error handling
        try {
            recentEventsCountEl.textContent = recentEvents;
            approvedEventsCountEl.textContent = approvedEvents;
            declinedEventsCountEl.textContent = declinedEvents;
            monthlyEventsCountEl.textContent = monthlyEvents;

            console.log('Summary Cards Updated Successfully:', {
                selectedPeriod: `${selectedMonth}/${selectedYear}`,
                recentEvents,
                approvedEvents,
                declinedEvents,
                pendingEvents,
                monthlyEvents
            });
        } catch (error) {
            console.error('Error updating summary cards display:', error);
        }
    }

    function updateEventsLineChart(bookings) {
        if (!homeYearSelect.value) return;

        const selectedYear = parseInt(homeYearSelect.value);
        const monthlyData = Array(12).fill(0);

        bookings.forEach(booking => {
            if (booking.date_of_event) {
                const bookingDate = new Date(booking.date_of_event);
                if (bookingDate.getFullYear() === selectedYear) {
                    monthlyData[bookingDate.getMonth()]++;
                }
            }
        });

        if (eventsLineChart) {
            eventsLineChart.destroy();
        }

        eventsLineChart = new Chart(eventsLineChartCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: `Events in ${selectedYear}`,
                    data: monthlyData,
                    borderColor: '#b8860b',
                    backgroundColor: 'rgba(184, 134, 11, 0.1)',
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    function updateFeedbackDoughnutChart(feedback) {
        const ratingCounts = { '1': 0, '2': 0, '3': 0, '4': 0, '5': 0 };
        
        feedback.forEach(item => {
            if (item.rating && ratingCounts.hasOwnProperty(item.rating)) {
                ratingCounts[item.rating]++;
            }
        });

        if (feedbackDoughnutChart) {
            feedbackDoughnutChart.destroy();
        }

        feedbackDoughnutChart = new Chart(feedbackDoughnutChartCtx, {
            type: 'doughnut',
            data: {
                labels: ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
                datasets: [{
                    data: Object.values(ratingCounts),
                    backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#28a745', '#007bff'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Add event listeners only if elements exist
    if (homeMonthSelect && homeYearSelect) {
        homeMonthSelect.addEventListener('change', function() {
            console.log('Month changed to:', this.value);
            fetchData();
        });

        homeYearSelect.addEventListener('change', function() {
            console.log('Year changed to:', this.value);
            fetchData();
        });
    } else {
        console.error('Month or Year select elements not found');
    }

    // Removed event listener for Generate Report button as it is removed from UI

    // Initial data fetch
    if (homeMonthSelect.value && homeYearSelect.value) {
        fetchData();
    }

    // Force fetch data on page load regardless
    fetchData();
});
