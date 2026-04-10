function animateValue(element, target) {
    let current = 0;
    const suffix = element.dataset.suffix || '';
    const increment = Math.max(1, Math.ceil(target / 30));

    const timer = setInterval(() => {
        current += increment;

        if (current >= target) {
            element.textContent = target + suffix;
            clearInterval(timer);
            return;
        }

        element.textContent = current + suffix;
    }, 30);
}

function initDashboard() {
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach((item) => {
        const targetAttr = item.dataset.target;
        if (typeof targetAttr === 'undefined' || targetAttr === '') {
            return;
        }

        const target = Number(targetAttr || 0);
        animateValue(item, target);
    });

    const todayEl = document.getElementById('todayDate');
    if (todayEl) {
        const now = new Date();
        todayEl.textContent = now.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    document.querySelectorAll('.chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('.chip').forEach((item) => item.classList.remove('active'));
            chip.classList.add('active');
        });
    });

    document.querySelectorAll('.menu-item').forEach((item) => {
        item.addEventListener('click', () => {
            document.querySelectorAll('.menu-item').forEach((link) => link.classList.remove('active'));
            item.classList.add('active');
        });
    });
}

if (document.body.dataset.page === 'admin-dashboard') {
    initDashboard();

    if (typeof revenueData !== 'undefined') {
        const ctx = document.getElementById('revenueChart');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: revenueData.Year,
                datasets: [{
                    label: 'Total Revenue (₱)',
                    data: revenueData.Revenue,
                    borderColor: '#ff6a2b',
                    backgroundColor: 'rgba(255, 106, 43, 0.12)',
                    pointBackgroundColor: '#ff6a2b',
                    pointRadius: 5,
                    tension: 0.35,
                    fill: true,
                    borderWidth: 2.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return '₱' + ctx.parsed.y.toLocaleString(undefined, { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function (v) {
                                return '₱' + (v / 1000).toFixed(0) + 'k';
                            }
                        },
                        grid: { color: '#eef2f8' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2.1 Monthly Revenue — bar chart
    if (typeof monthlyRevenueData !== 'undefined') {
        new Chart(document.getElementById('monthlyRevenueChart'), {
            type: 'bar',
            data: {
                labels: monthlyRevenueData.labels,
                datasets: [{
                    label: 'Monthly Revenue (₱)',
                    data: monthlyRevenueData.values,
                    backgroundColor: '#3b82f6',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return '₱' + ctx.parsed.y.toLocaleString(undefined, { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function (v) { return '₱' + (v / 1000).toFixed(0) + 'k'; }
                        },
                        grid: { color: '#eef2f8' }
                    },
                    x: {
                        ticks: { maxRotation: 90, minRotation: 45 },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2.2 Revenue by Product — horizontal bar chart
    if (typeof productRevenueData !== 'undefined') {
        new Chart(document.getElementById('productRevenueChart'), {
            type: 'bar',
            data: {
                labels: productRevenueData.labels,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: productRevenueData.values,
                    backgroundColor: '#16a34a',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return '₱' + ctx.parsed.x.toLocaleString(undefined, { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            callback: function (v) { return '₱' + (v / 1000).toFixed(0) + 'k'; }
                        },
                        grid: { color: '#eef2f8' }
                    },
                    y: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    if (typeof topCustomersData !== 'undefined') {
        const topCustomersCanvas = document.getElementById('topCustomersChart');
        const topCustomersCtx = topCustomersCanvas.getContext('2d');
        const violetGradient = topCustomersCtx.createLinearGradient(0, 0, 400, 0);
        violetGradient.addColorStop(0, '#a855f7');
        violetGradient.addColorStop(1, '#6d28d9');

        new Chart(topCustomersCanvas, {
            type: 'bar',
            data: {
                labels: topCustomersData.labels,
                datasets: [{
                    label: 'Total Spent (₱)',
                    data: topCustomersData.values,
                    backgroundColor: violetGradient,
                    borderRadius: 10,
                    borderSkipped: false,
                    barThickness: 18
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        right: 12
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return '₱' + ctx.parsed.x.toLocaleString(undefined, { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (v) { return '₱' + (v / 1000).toFixed(0) + 'k'; }
                        },
                        grid: { color: '#eef2f8' }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            color: '#4b5668',
                            font: {
                                size: 12,
                                weight: 600
                            }
                        }
                    }
                }
            }
        });
    }
}

if (document.body.dataset.page === 'calendar-page') {
    initDashboard();

    if (typeof FullCalendar !== 'undefined' && typeof calendarEvents !== 'undefined') {
        const calendarEl = document.getElementById('orderCalendar');

        if (calendarEl) {
            const monthPicker = document.getElementById('calendarMonthPicker');
            const oldestOrdersBtn = document.getElementById('oldestOrdersBtn');
            const latestOrdersBtn = document.getElementById('latestOrdersBtn');
            const initialDate = (typeof calendarRange !== 'undefined' && calendarRange.initial)
                ? calendarRange.initial
                : new Date().toISOString().slice(0, 10);

            const syncMonthPicker = (dateValue) => {
                if (monthPicker && dateValue) {
                    monthPicker.value = String(dateValue).slice(0, 7);
                }
            };

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                initialDate,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                height: 'auto',
                dayMaxEvents: 2,
                navLinks: true,
                events: calendarEvents,
                eventDisplay: 'block',
                datesSet(info) {
                    syncMonthPicker(info.view.currentStart);
                },
                eventDidMount(info) {
                    const { orderId, customer, total } = info.event.extendedProps;
                    const orderLabel = orderId ? `Order #${orderId}` : '';
                    const totalLabel = typeof total !== 'undefined'
                        ? '₱' + Number(total).toLocaleString(undefined, { minimumFractionDigits: 2 })
                        : '';

                    info.el.title = [orderLabel, customer, totalLabel].filter(Boolean).join(' • ');
                }
            });

            calendar.render();
            syncMonthPicker(initialDate);

            if (monthPicker) {
                monthPicker.addEventListener('change', () => {
                    if (monthPicker.value) {
                        calendar.gotoDate(`${monthPicker.value}-01`);
                    }
                });
            }

            if (oldestOrdersBtn) {
                oldestOrdersBtn.addEventListener('click', () => {
                    if (typeof calendarRange !== 'undefined' && calendarRange.oldest) {
                        calendar.gotoDate(calendarRange.oldest);
                        syncMonthPicker(calendarRange.oldest);
                    }
                });
            }

            if (latestOrdersBtn) {
                latestOrdersBtn.addEventListener('click', () => {
                    if (typeof calendarRange !== 'undefined' && calendarRange.latest) {
                        calendar.gotoDate(calendarRange.latest);
                        syncMonthPicker(calendarRange.latest);
                    }
                });
            }
        }
    }
}
