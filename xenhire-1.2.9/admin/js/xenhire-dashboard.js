jQuery(document).ready(function ($) {
    // Data is passed via xenhireDashboardData object from PHP
    if (typeof xenhireDashboardData === 'undefined' || !xenhireDashboardData.success) {
        return;
    }

    const rawData = JSON.parse(xenhireDashboardData.data);

    // Parse Data Sections
    // Assuming standard structure:
    // 0: Stats
    // 1: Chart
    // 2: ? (Maybe Stages)
    // 3: Device Stats
    // 4: City Stats
    // 5: OS Stats
    const stats = rawData[0][0] || {};
    const chartData = rawData[1] || [];
    const devices = rawData[3] || [];
    const cities = rawData[4] || [];
    const os = rawData[5] || [];

    // 1. Populate Stats Cards
    $('#xh-stat-applications').text(stats.JobApplications || 0);
    $('#xh-stat-candidates').text(stats.Candidates || 0);
    $('#xh-stat-jobs').text(stats.Requirements || 0);
    $('#xh-stat-employers').text(stats.Employers || 0);

    // 2. Render Chart
    renderChart(chartData);

    // 3. Populate Lists
    // We try to detect keys automatically or use common ones
    renderList('xh-list-devices', devices, ['Device', 'Name', 'Key'], ['Count', 'Value', 'Total']);
    renderList('xh-list-cities', cities, ['City', 'Name', 'Key'], ['Count', 'Value', 'Total']);
    renderList('xh-list-os', os, ['OS', 'Name', 'Key'], ['Count', 'Value', 'Total']);

    function renderChart(data) {
        const ctx = document.getElementById('xh-applications-chart');
        if (!ctx) return;

        // Extract labels and data
        const labels = data.map(item => (item.Dates || item.Date || '').trim());
        const counts = data.map(item => item.Counts || item.Count || item.Value || 0);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Applications',
                    data: counts,
                    borderColor: '#3b82f6', // Blue
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4, // Smooth curve
                    fill: true,
                    pointRadius: 0, // Hide points by default
                    pointHoverRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2, 4],
                            color: '#f3f4f6',
                            drawBorder: false,
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#9ca3af'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#9ca3af',
                            maxTicksLimit: 7 // Limit x-axis labels
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }

    function renderList(containerId, data, keyNames, valNames) {
        const $container = $('#' + containerId);
        $container.empty();

        if (!data || data.length === 0) {
            $container.append('<div class="xh-d-list-item">No data available</div>');
            return;
        }

        // Convert single strings to arrays
        if (!Array.isArray(keyNames)) keyNames = [keyNames];
        if (!Array.isArray(valNames)) valNames = [valNames];

        data.forEach(item => {
            // Find the first matching key
            let name = 'Unknown';
            for (let k of keyNames) {
                if (item[k] !== undefined) {
                    name = item[k];
                    break;
                }
            }

            // Find the first matching value
            let val = 0;
            for (let v of valNames) {
                if (item[v] !== undefined) {
                    val = item[v];
                    break;
                }
            }

            const html = `
                <div class="xh-d-list-item">
                    <span>${name}</span>
                    <span class="xh-d-list-val">${val}</span>
                </div>
            `;
            $container.append(html);
        });
    }

});
