// Initialize Charts
function initCharts() {
    // Statistics Chart
    const statsCtx = document.getElementById('statisticsChart').getContext('2d');
    new Chart(statsCtx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Inquiries',
                data: chartData.inquiries,
                borderColor: '#3498db'
            }, {
                label: 'Bookings',
                data: chartData.bookings,
                borderColor: '#2ecc71'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                intersect: false,
            }
        }
    });

    // User Activity Chart
    const userCtx = document.getElementById('userActivityChart').getContext('2d');
    new Chart(userCtx, {
        type: 'bar',
        data: {
            labels: chartData.userLabels,
            datasets: [{
                label: 'Active Users',
                data: chartData.userActivity,
                backgroundColor: '#3498db'
            }]
        }
    });
}