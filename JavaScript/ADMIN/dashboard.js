/**
 * Dashboard.js - Handles dashboard specific functionality
 */

// Initialize the dashboard chart
function initializeChart(labels, series) {
    // Check if we have valid data
    if (!labels || !series || labels.length === 0 || series.length === 0) {
        console.error('No valid chart data provided');
        return;
    }

    // Generate colors for the chart
    const colors = generateChartColors(series.length);
    
    const options = {
        chart: {
            height: 350,
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
            },
            responsive: [{
                breakpoint: 576,
                options: {
                    chart: {
                        height: 300
                    },
                    legend: {
                        position: 'bottom',
                        fontSize: '12px',
                        offsetY: 0
                    }
                }
            }]
        },
        series: series,
        labels: labels,
        colors: colors,
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
            formatter: function(seriesName, opts) {
                // Format legend to show percentage
                const percent = ((opts.w.globals.series[opts.seriesIndex] / opts.w.globals.series.reduce((a, b) => a + b, 0)) * 100).toFixed(1);
                return `${seriesName} (${percent}%)`;
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

    // Render the chart if the element exists
    if (document.getElementById("pie-chart") && typeof ApexCharts !== 'undefined') {
        try {
            const chart = new ApexCharts(document.getElementById("pie-chart"), options);
            chart.render();
        } catch (error) {
            console.error('Error rendering chart:', error);
        }
    }
}

// Generate colors for the chart
function generateChartColors(count) {
    // Base colors
    const baseColors = [
        '#991b1b', // Primary color
        '#b91c1c',
        '#dc2626',
        '#ef4444',
        '#f87171',
        '#7f1d1d',
        '#ECDCBF', // Secondary color
        '#d6c4a5',
        '#c0ad8f',
        '#aa9679'
    ];
    
    // If we have more categories than base colors, generate additional colors
    if (count <= baseColors.length) {
        return baseColors.slice(0, count);
    } else {
        const colors = [...baseColors];
        
        // Generate additional colors
        for (let i = baseColors.length; i < count; i++) {
            // Generate a random color with some hue variation
            const hue = Math.floor(Math.random() * 360);
            const saturation = 70 + Math.floor(Math.random() * 30); // 70-100%
            const lightness = 40 + Math.floor(Math.random() * 20);  // 40-60%
            
            colors.push(`hsl(${hue}, ${saturation}%, ${lightness}%)`);
        }
        
        return colors;
    }
}

// Handle responsive behavior for the dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Adjust chart size on window resize
    window.addEventListener('resize', function() {
        const chartWrapper = document.querySelector('.chart-wrapper');
        if (chartWrapper) {
            // Adjust height based on width for better responsiveness
            if (window.innerWidth < 576) {
                chartWrapper.style.height = '300px';
            } else {
                chartWrapper.style.height = '350px';
            }
        }
    });
    
    // Trigger resize event to set initial sizes
    window.dispatchEvent(new Event('resize'));
});
