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
    console.log('Generated colors:', colors); // Debug log
    
    const options = {
        chart: {
            height: 350,
            type: "pie",
            toolbar: {
                show: false
            },
            animations: {
                enabled: true,
                dynamicAnimation: {
                    enabled: true
                }
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
                },
                customScale: 1,
                offsetX: 0,
                offsetY: 0
            }
        },
        dataLabels: {
            enabled: true,
            style: {
                fontSize: '14px',
                fontFamily: 'Poppins, sans-serif',
                fontWeight: 'bold'
            },
            formatter: function(val, opts) {
                return opts.w.config.labels[opts.seriesIndex] + ': ' + val.toFixed(1) + '%';
            }
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

    console.log('Chart options:', options); // Debug log

    // Render the chart if the element exists
    if (document.getElementById("pie-chart") && typeof ApexCharts !== 'undefined') {
        try {
            // Destroy existing chart if it exists
            const existingChart = document.querySelector("#pie-chart apex-charts-canvas");
            if (existingChart) {
                existingChart.remove();
            }

            const chart = new ApexCharts(document.getElementById("pie-chart"), options);
            chart.render();
            console.log('Chart rendered successfully'); // Debug log
        } catch (error) {
            console.error('Error rendering chart:', error);
        }
    }
}

// Generate colors for the chart
function generateChartColors(count) {
    // Base colors with more distinct variations
    const baseColors = [
        '#FF0000', // Pure Red
        '#FF4D00', // Orange-Red
        '#FF9900', // Orange
        '#FFCC00', // Yellow-Orange
        '#E6B800', // Dark Yellow
        '#991b1b', // Dark Red
        '#7f1d1d', // Deeper Red
        '#FF6666', // Light Red
        '#FF3333', // Bright Red
        '#CC0000'  // Dark Red
    ];
    
    console.log('Number of categories:', count); // Debug log
    
    // If we have more categories than base colors, generate additional colors
    if (count <= baseColors.length) {
        return baseColors.slice(0, count);
    } else {
        const colors = [...baseColors];
        
        // Generate additional colors with more variation
        for (let i = baseColors.length; i < count; i++) {
            const hue = (i * 30) % 60; // Create evenly spaced hues in the red-orange range
            const saturation = 80 + Math.floor(Math.random() * 20); // 80-100%
            const lightness = 45 + Math.floor(Math.random() * 15);  // 45-60%
            
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
