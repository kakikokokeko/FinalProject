function dashboard(){
    window.location.href = "../../HTML/ADMIN/Dashboard.php";
}

function account(){
    window.location.href = "../../HTML/ADMIN/Accounts.php";
}

function inventory(){
    window.location.href = "../../HTML/ADMIN/Inventory.php";
}

function reports(){
    window.location.href = "../../HTML/ADMIN/Reports.php";
}

//-----------------------------------------------------------------ADD ACCOUNT------------------------------------------------------------------

// Check if element exists before adding event listener
const accountForm = document.getElementById('accountForm');
if (accountForm) {
    accountForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch('', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); 
            if (data.success) {
                closeAddAccountForm();
                accountForm.reset();
                location.reload(); 
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    });
}

function closeAddAccountForm() {
    const addAccountPopup = document.getElementById('addAccountPopup');
    if (addAccountPopup) {
        addAccountPopup.style.display = 'none';
    }
}

function openAddAccountForm() {
    const addAccountPopup = document.getElementById('addAccountPopup');
    if (addAccountPopup) {
        addAccountPopup.style.display = 'flex';
    }
}

//-----------------------------------------------------------------EDIT ACCOUNT------------------------------------------------------------------

function closeEditAccountForm() {
    const editAccountPopup = document.getElementById('editAccountPopup');
    if (editAccountPopup) {
        editAccountPopup.style.display = 'none';
    }
}

function openEditAccountForm() {
    const editAccountPopup = document.getElementById('editAccountPopup');
    if (editAccountPopup) {
        editAccountPopup.style.display = 'flex';
    }
}

//-----------------------------------------------------------------EDIT PRODUCT------------------------------------------------------------------

function editProd() {
    const editProductPopup = document.getElementById('editProductPopup');
    if (editProductPopup) {
        editProductPopup.style.display = 'flex';
    }
}

// Dashboard Chart Configuration
function initializeChart(labels, series) {
    const options = {
        chart: {
            height: 350,
            width: 500,
            type: "pie",
            toolbar: {
                show: false
            },
            events: {
                dataPointSelection: function() {
                    return false;  // Disable slice selection
                },
                legendClick: function() {
                    return false;  // Disable legend clicks
                }
            }
        },
        series: series,
        labels: labels,
        theme: {
            monochrome: {
                enabled: false
            }
        },
        plotOptions: {
            pie: {
                expandOnClick: false,  // Disable expanding on click
                donut: {
                    size: '65%'  // Make the chart slightly thicker
                }
            }
        },
        dataLabels: {
            enabled: false
        },
        tooltip: {
            enabled: true,
            theme: 'light',
            style: {
                fontFamily: 'Poppins, sans-serif'
            },
            y: {
                formatter: function(value) {
                    return value.toFixed(0) + ' units';
                }
            }
        },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            show: true,
            fontSize: '14px',
            fontFamily: 'Poppins, sans-serif',
            markers: {
                width: 12,
                height: 12,
                radius: 6
            },
            itemMargin: {
                horizontal: 8,
                vertical: 8
            },
            onItemClick: {
                toggleDataSeries: false  // Disable legend item clicks
            },
            onItemHover: {
                highlightDataSeries: false  // Disable highlight on hover
            },
            labels: {
                colors: '#333333'
            }
        },
        states: {
            hover: {
                filter: {
                    type: 'none'  // Disable hover effects
                }
            },
            active: {
                filter: {
                    type: 'none'  // Disable active state effects
                }
            }
        }
    };

    if (document.getElementById("pie-chart") && typeof ApexCharts !== 'undefined') {
        try {
            const chart = new ApexCharts(document.getElementById("pie-chart"), options);
            chart.render();
        } catch (error) {
            console.error('Error rendering chart:', error);
        }
    }
}

// Remove the old chart initialization code
if (document.getElementById("pie-chart") && typeof ApexCharts !== 'undefined') {
    const chart = new ApexCharts(document.getElementById("pie-chart"), getChartOptions());
    chart.render();
}
  