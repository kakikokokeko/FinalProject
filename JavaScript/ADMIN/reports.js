// Initialize DataTable and Chart when the document is ready
let salesTable;
let salesChart;

// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu functionality
    initializeMobileMenu();
    
    // Initialize other features
    initializeDataTable();
    initializeChart();
    initializeFilters();
    loadSummaryCards();
    loadQuickSummary();
});

// Mobile menu initialization
function initializeMobileMenu() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const sidebar = document.querySelector('.sidebar-container');
    const closeSidebar = document.getElementById('close-sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    if(mobileMenuButton && sidebar && closeSidebar && sidebarOverlay) {
        mobileMenuButton.addEventListener('click', function() {
            console.log('Mobile menu clicked'); // Debug log
            sidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
}

function initializeDataTable() {
    salesTable = $('#salesTable').DataTable({
        pageLength: 10,
        order: [[0, 'desc']],
        responsive: true,
        ajax: {
            url: 'Reports.php?action=get_sales_data',
            dataSrc: ''
        },
        columns: [
            { data: 'date' },
            { data: 'order_id' },
            { data: 'product' },
            { 
                data: null,
                render: function(data) {
                    return parseFloat(data.quantity).toFixed(2) + ' ' + data.unit;
                }
            },
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
            }
        ],
        dom: '<"top"<"search-container"f>>rt<"bottom"p><"clear">',
        language: {
            search: "Search sales:",
            emptyTable: "No sales records found"
        },
        initComplete: function() {
            $('.dataTables_filter input').attr('placeholder', 'Type to search...');
        }
    });
}

function initializeChart() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Sales',
                data: [],
                backgroundColor: '#4CAF50',
                borderColor: '#45a049',
                borderWidth: 1,
                barThickness: 20,
                fill: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 0,
                    bottom: 0
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        drawBorder: true,
                        drawOnChartArea: true,
                        drawTicks: true,
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₱' + context.parsed.x.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Add click handlers for toggle buttons
    document.querySelectorAll('.chart-toggle-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            document.querySelectorAll('.chart-toggle-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            // Add active class to clicked button
            this.classList.add('active');
            // Update chart with selected period
            updateChartData(this.dataset.period);
        });
    });

    // Initial chart data load
    updateChartData('daily');
}

function initializeFilters() {
    const applyFiltersBtn = document.querySelector('.apply-filters');
    if (!applyFiltersBtn) return;

    applyFiltersBtn.addEventListener('click', function() {
        const dateFilter = document.querySelector('.date-filter').value;
        const categoryFilter = document.querySelector('.category-filter').value;

        // Show loading state
        applyFiltersBtn.textContent = 'Loading...';
        applyFiltersBtn.disabled = true;

        fetch(`Reports.php?action=get_filtered_sales&date_filter=${dateFilter}&category=${categoryFilter}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                salesTable.clear();
                salesTable.rows.add(data);
                salesTable.draw();
                updateChartData();
                loadSummaryCards();
                loadQuickSummary();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error applying filters. Please try again.');
            })
            .finally(() => {
                // Reset button state
                applyFiltersBtn.textContent = 'Apply Filters';
                applyFiltersBtn.disabled = false;
            });
    });
}

function updateChartData(period = 'daily') {
    fetch(`Reports.php?action=get_chart_data&period=${period}`)
        .then(response => response.json())
        .then(data => {
            const chartConfig = getChartConfig(period);
            
            // Update chart type and options
            salesChart.config.type = chartConfig.type;
            salesChart.options = chartConfig.options;
            
            // Update data
            salesChart.data.labels = data.map(item => item.label);
            salesChart.data.datasets[0] = {
                ...salesChart.data.datasets[0],
                ...chartConfig.datasetOptions,
                data: data.map(item => parseFloat(item.total))
            };
            
            salesChart.update();
        })
        .catch(error => console.error('Error:', error));
}

function getChartConfig(period) {
    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: {
                left: 10,
                right: 25,
                top: 0,
                bottom: 0
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '₱' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    };

    const configs = {
        daily: {
            type: 'bar',
            options: {
                ...baseOptions,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            display: true
                        },
                        ticks: {
                            callback: value => '₱' + value.toLocaleString()
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            },
            datasetOptions: {
                backgroundColor: '#4CAF50',
                borderColor: '#45a049',
                borderWidth: 1,
                barThickness: 20
            }
        },
        weekly: {
            type: 'line',
            options: {
                ...baseOptions,
                tension: 0.4,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '₱' + value.toLocaleString()
                        }
                    }
                }
            },
            datasetOptions: {
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                borderColor: '#4CAF50',
                borderWidth: 2,
                pointBackgroundColor: '#4CAF50',
                pointRadius: 4,
                tension: 0.4
            }
        },
        monthly: {
            type: 'line',
            options: {
                ...baseOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '₱' + value.toLocaleString()
                        }
                    }
                }
            },
            datasetOptions: {
                backgroundColor: 'rgba(76, 175, 80, 0.2)',
                borderColor: '#4CAF50',
                borderWidth: 2,
                pointBackgroundColor: '#4CAF50',
                pointRadius: 4,
                fill: true,
                tension: 0.4
            }
        },
        annual: {
            type: 'bar',
            options: {
                ...baseOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '₱' + value.toLocaleString()
                        }
                    }
                }
            },
            datasetOptions: {
                backgroundColor: 'rgba(76, 175, 80, 0.8)',
                borderColor: '#45a049',
                borderWidth: 1,
                borderRadius: 4,
                maxBarThickness: 50
            }
        }
    };

    return configs[period];
}

function loadSummaryCards() {
    fetch('Reports.php?action=get_summary_data')
        .then(response => response.json())
        .then(data => {
            updateSummaryCard('today', data.today);
            updateSummaryCard('weekly', data.weekly);
            updateSummaryCard('monthly', data.monthly);
            updateSummaryCard('orders', data.orders);
        })
        .catch(error => console.error('Error:', error));
}

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

function loadQuickSummary() {
    const totalItems = document.querySelector('.summary-item strong');

    // Show loading state
    if (totalItems) totalItems.textContent = 'Loading...';

    // Get current filter values
    const dateFilter = document.querySelector('.date-filter').value;
    const categoryFilter = document.querySelector('.category-filter').value;

    // Fetch with filter parameters
    fetch(`Reports.php?action=get_quick_summary&date_filter=${dateFilter}&category=${categoryFilter}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (totalItems) {
                totalItems.textContent = parseInt(data.totalItems).toLocaleString() + ' items';
            }
        })
        .catch(error => {
            console.error('Error loading quick summary:', error);
            if (totalItems) totalItems.textContent = 'Error loading data';
        });
} 