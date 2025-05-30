// Initialize DataTable and Chart when the document is ready
let salesTable;
let salesChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeDataTable();
    initializeChart();
    initializeFilters();
    loadSummaryCards();
    loadQuickSummary();
});

// Initialize DataTable with configuration
function initializeDataTable() {
    salesTable = $('#salesTable').DataTable({
        pageLength: 10,
        order: [[0, 'desc']],
        responsive: true,
        ajax: {
            url: '../../PHP/ADMIN/get_sales_data.php',
            dataSrc: ''
        },
        columns: [
            { data: 'date' },
            { data: 'order_id' },
            { data: 'product' },
            { data: 'quantity' },
            { 
                data: 'unit_price',
                render: function(data) {
                    return '₱' + parseFloat(data).toFixed(2);
                }
            },
            { 
                data: 'total',
                render: function(data) {
                    return '₱' + parseFloat(data).toFixed(2);
                }
            },
            { data: 'payment_method' }
        ]
    });
}

// Initialize Chart.js
function initializeChart() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Daily Sales',
                data: [],
                backgroundColor: '#4CAF50',
                borderColor: '#45a049',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value;
                        }
                    }
                }
            }
        }
    });
    updateChartData();
}

// Initialize filter event listeners
function initializeFilters() {
    const dateFilter = document.querySelector('.date-filter');
    const categoryFilter = document.querySelector('.category-filter');
    const paymentFilter = document.querySelector('.payment-filter');
    const applyFiltersBtn = document.querySelector('.apply-filters');

    applyFiltersBtn.addEventListener('click', function() {
        applyFilters();
    });
}

// Apply filters to the data
function applyFilters() {
    const dateFilter = document.querySelector('.date-filter').value;
    const categoryFilter = document.querySelector('.category-filter').value;
    const paymentFilter = document.querySelector('.payment-filter').value;

    // Create filter parameters
    const params = new URLSearchParams({
        date_filter: dateFilter,
        category: categoryFilter,
        payment_method: paymentFilter
    });

    // Fetch filtered data
    fetch('../../PHP/ADMIN/get_filtered_sales.php?' + params.toString())
        .then(response => response.json())
        .then(data => {
            updateAllDisplays(data);
        })
        .catch(error => console.error('Error:', error));
}

// Update all displays with new data
function updateAllDisplays(data) {
    updateTable(data);
    updateChartData();
    loadSummaryCards();
    loadQuickSummary();
}

// Update the sales table with new data
function updateTable(data) {
    salesTable.clear();
    salesTable.rows.add(data);
    salesTable.draw();
}

// Update chart with new data
function updateChartData() {
    fetch('../../PHP/ADMIN/get_chart_data.php')
        .then(response => response.json())
        .then(data => {
            salesChart.data.labels = data.labels;
            salesChart.data.datasets[0].data = data.values;
            salesChart.update();
        })
        .catch(error => console.error('Error:', error));
}

// Load and update summary cards
function loadSummaryCards() {
    fetch('../../PHP/ADMIN/get_summary_data.php')
        .then(response => response.json())
        .then(data => {
            updateSummaryCard('today', data.today);
            updateSummaryCard('weekly', data.weekly);
            updateSummaryCard('monthly', data.monthly);
            updateSummaryCard('orders', data.orders);
        })
        .catch(error => console.error('Error:', error));
}

// Update individual summary card
function updateSummaryCard(type, data) {
    const cards = {
        today: document.querySelector('.summary-card:nth-child(1)'),
        weekly: document.querySelector('.summary-card:nth-child(2)'),
        monthly: document.querySelector('.summary-card:nth-child(3)'),
        orders: document.querySelector('.summary-card:nth-child(4)')
    };

    if (cards[type]) {
        const numberElement = cards[type].querySelector('.number');
        const changeElement = cards[type].querySelector('.change');

        numberElement.textContent = type === 'orders' 
            ? data.value 
            : '₱' + parseFloat(data.value).toFixed(2);

        const changeClass = data.change > 0 ? 'positive' : data.change < 0 ? 'negative' : 'neutral';
        changeElement.className = `change ${changeClass}`;
        changeElement.textContent = type === 'orders'
            ? data.change === 0 ? 'Today' : `${data.change > 0 ? '+' : ''}${data.change}% from yesterday`
            : `${data.change > 0 ? '+' : ''}${data.change}% from previous period`;
    }
}

// Load and update quick summary
function loadQuickSummary() {
    fetch('../../PHP/ADMIN/get_quick_summary.php')
        .then(response => response.json())
        .then(data => {
            document.querySelector('.summary-item:nth-child(1) strong').textContent = data.bestSelling;
            document.querySelector('.summary-item:nth-child(2) strong').textContent = '₱' + parseFloat(data.averageOrder).toFixed(2);
            document.querySelector('.summary-item:nth-child(3) strong').textContent = data.totalItems + ' items';
        })
        .catch(error => console.error('Error:', error));
} 