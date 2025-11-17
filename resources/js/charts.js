import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

/**
 * Initialize all dashboard charts
 */
export function initDashboardCharts(chartData) {
    if (!chartData) return;

    // Member Growth Chart
    if (chartData.member_growth && chartData.member_growth.length > 0) {
        initMemberGrowthChart(chartData.member_growth);
    }

    // Attendance Trends Chart
    if (chartData.attendance_by_month && chartData.attendance_by_month.length > 0) {
        initAttendanceChart(chartData.attendance_by_month);
    }

    // Daily Attendance Chart
    if (chartData.attendance_daily && chartData.attendance_daily.length > 0) {
        initDailyAttendanceChart(chartData.attendance_daily);
    }

    // Messages Over Time Chart
    if (chartData.messages_over_time && chartData.messages_over_time.length > 0) {
        initMessagesChart(chartData.messages_over_time);
    }

    // Mentorship Status Pie Chart
    if (chartData.mentorship_status && chartData.mentorship_status.length > 0) {
        initMentorshipStatusChart(chartData.mentorship_status);
    }

    // Class Performance Chart
    if (chartData.class_performance && chartData.class_performance.length > 0) {
        initClassPerformanceChart(chartData.class_performance);
    }
}

function initMemberGrowthChart(data) {
    const ctx = document.getElementById('memberGrowthChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.month),
            datasets: [{
                label: 'New Members',
                data: data.map(item => item.count),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
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

function initAttendanceChart(data) {
    const ctx = document.getElementById('attendanceChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(item => item.month),
            datasets: [{
                label: 'Attendance',
                data: data.map(item => item.count),
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function initDailyAttendanceChart(data) {
    const ctx = document.getElementById('dailyAttendanceChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }),
            datasets: [{
                label: 'Daily Attendance',
                data: data.map(item => item.count),
                borderColor: 'rgb(168, 85, 247)',
                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function initMessagesChart(data) {
    const ctx = document.getElementById('messagesChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(item => item.month),
            datasets: [{
                label: 'Messages Sent',
                data: data.map(item => item.count),
                backgroundColor: 'rgba(234, 179, 8, 0.8)',
                borderColor: 'rgb(234, 179, 8)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
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

function initMentorshipStatusChart(data) {
    const ctx = document.getElementById('mentorshipStatusChart');
    if (!ctx) return;

    const colors = [
        'rgba(34, 197, 94, 0.8)',   // Green for active
        'rgba(59, 130, 246, 0.8)',  // Blue for completed
        'rgba(234, 179, 8, 0.8)',   // Yellow for paused
        'rgba(239, 68, 68, 0.8)'    // Red for cancelled
    ];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(item => item.status),
            datasets: [{
                data: data.map(item => item.count),
                backgroundColor: colors.slice(0, data.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            }
        }
    });
}

function initClassPerformanceChart(data) {
    const ctx = document.getElementById('classPerformanceChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(item => item.title.length > 20 ? item.title.substring(0, 20) + '...' : item.title),
            datasets: [{
                label: 'Attendance Rate (%)',
                data: data.map(item => item.attendance_rate),
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                borderColor: 'rgb(99, 102, 241)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

