document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const BASE_URL = document
        .querySelector('meta[name="base-url"]')
        .getAttribute('content');
    const calendar = new FullCalendar.Calendar(calendarEl, {

        schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        initialView: 'resourceTimelineWeek',
        resourceOrder: 'order',
        height: 'calc(100vh - 250px)',
        expandRows: true,

        datesSet(info) {
            // Update date range text
            document.getElementById('dateRange').innerText =
                '📅 ' + formatDateRange(info.view);

            // Toggle active button
            document.getElementById('btnDay').classList.toggle(
                'active',
                info.view.type === 'resourceTimelineDay'
            );

            document.getElementById('btnWeek').classList.toggle(
                'active',
                info.view.type === 'resourceTimelineWeek'
            );

            updateDateRange(info.view);
            updateStats(); // optional but recommended
        },

        headerToolbar: false,
        footerToolbar: false,


        resourceLabelContent: function (arg) {
            if (arg.resource.id === 'open') {
                return {
                    html: `
                    <div style="display:flex;align-items:center;gap:6px;color:#ea580c;font-size:14px;font-weight:600;padding:6px 4px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Open Shifts
                    </div>`
                };
            }

            const initials = arg.resource.title.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            // Try to extract hours or default
            const hoursLogged = 0;
            const hoursTotal = 40;
            const progress = (hoursLogged / hoursTotal) * 100;

            return {
                html: `
                <div style="display:flex;gap:12px;align-items:center;padding:8px 4px;">
                    <div style="
                        width:32px;
                        height:32px;
                        border-radius:50%;
                        background:#3b82f6;
                        color:#fff;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        font-size:13px;
                        font-weight:600">
                        ${initials}
                    </div>
                    <div style="flex:1;">
                        <div style="font-weight:600;color:#1f2937;font-size:13px;">${arg.resource.title}</div>
                        <div style="display:flex;align-items:center;gap:6px;margin-top:2px;">
                            <small style="color:#6b7280;font-size:11px;">0.0h / 40h</small>
                            <div style="flex:1;height:4px;background:#e5e7eb;border-radius:2px;position:relative;">
                                <div style="width:${progress}%;height:100%;background:#3b82f6;border-radius:2px;"></div>
                            </div>
                            <small style="color:#9ca3af;font-size:9px;font-weight:700;">FT</small>
                        </div>
                    </div>
                </div>`
            }
        },

        eventContent: function (arg) {
            const isAssigned = arg.event.getResources().length > 0 && arg.event.getResources()[0].id !== 'open';
            const title = arg.event.title;

            // Format time safely
            const startStr = arg.event.start ? arg.event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false }) : '';
            const endStr = arg.event.end ? arg.event.end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false }) : '';
            const timeStr = startStr && endStr ? `${startStr} - ${endStr}` : '';

            if (!isAssigned) {
                // Open shift styling (yellow bg implies brownish text)
                return {
                    html: `
                    <div style="padding: 4px; color: #92400e; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; font-size: 13px; font-weight: 500; line-height: 1.2;">
                        <div style="display:flex; align-items:center; gap: 4px;">
                            ${title}
                        </div>
                        ${timeStr ? `<div style="font-size: 11px; opacity: 0.8; margin-top: 2px; font-weight: normal;">${timeStr}</div>` : ''}
                    </div>`
                };
            }

            // Assigned shift styling (green bg implies dark green text)
            return {
                html: `
                <div style="padding: 4px; color: #065f46; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; font-size: 13px; font-weight: 500; line-height: 1.2;">
                    <div style="display:flex; align-items:center; gap: 4px;">
                        <span style="height: 6px; width: 6px; background-color: #059669; border-radius: 50%; display: inline-block; flex-shrink: 0;"></span>
                        <span style="overflow: hidden; text-overflow: ellipsis;">${title}</span>
                    </div>
                    ${timeStr ? `<div style="font-size: 11px; opacity: 0.8; margin-top: 2px; font-weight: normal;">${timeStr}</div>` : ''}
                </div>`
            };
        },

        eventAllow: function (dropInfo, draggedEvent) {
            if (draggedEvent.extendedProps.resourceId === 'open') {
                return true;
            }
            return true;
        },

        /* ===== RESOURCE SETTINGS ===== */
        resourceAreaHeaderContent: function () {
            return {
                html: `<div style="display:flex;align-items:center;gap:8px;color:#4b5563;font-size:14px;font-weight:600;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Staff
                </div>`
            }
        },

        /* ===== INTERACTION ===== */
        editable: true,
        selectable: true,

        /* ===== MONTH VIEW CONFIG ===== */
        views: {
            resourceTimelineWeek: {
                type: 'resourceTimeline',
                duration: { weeks: 1 },

                // 👇 THIS removes hours
                slotDuration: { days: 1 },

                slotLabelContent: function (arg) {
                    const date = arg.date;
                    const weekd = date.toLocaleDateString('en-US', { weekday: 'short' });
                    const dayNum = date.getDate();

                    const isToday = date.toDateString() === new Date().toDateString();
                    const topColor = isToday ? '#3b82f6' : '#6b7280';
                    const botColor = isToday ? '#3b82f6' : '#111827';

                    return {
                        html: `<div style="text-align:center;line-height:1.2;padding:4px 0;">
                            <div style="font-size:12px;color:${topColor};font-weight:500;">${weekd}</div>
                            <div style="font-size:16px;font-weight:600;margin-top:2px;color:${botColor};">${dayNum}</div>
                        </div>`
                    };
                }
            },

            resourceTimelineDay: {
                type: 'resourceTimeline',
                duration: { days: 1 },

                // 👇 Keep hours ONLY for day view
                slotDuration: { hours: 1 },

                slotLabelFormat: {
                    hour: 'numeric',
                    meridiem: 'short'
                }
            }
        },
        /* ===== CLICK MONTH DAY → OPEN WEEK ===== */
        dateClick: function (info) {
            if (calendar.view.type === 'dayGridMonth') {
                calendar.changeView('resourceTimelineWeek', info.dateStr);
            }
        },

        resources: {
            url: `${BASE_URL}/roster/carer/shift-resources`,  // 👈 your Laravel route
            method: 'GET',
            failure() {
                alert('Failed to load resources');
            }
        },
        /* ===== SHIFTS (EVENTS) ===== */
        events: {
            url: `${BASE_URL}/roster/carer/shifts`,
            method: 'GET',
            success: function (data) {
                console.log("🔥 Successfully fetched shifts data: ", data);
            },
            failure: function () {
                console.error("❌ Failed to load shifts!");
                alert('Failed to load shifts');
            }
        }
    });

    calendar.render();


    function formatDateRange(view) {
        const start = view.currentStart;
        const end = new Date(view.currentEnd - 1); // inclusive

        const options = { day: 'numeric', month: 'short' };

        if (view.type === 'resourceTimelineDay') {
            return `${start.toLocaleDateString('en-GB', options)} ${start.getFullYear()}`;
        }

        return `${start.toLocaleDateString('en-GB', options)} - ${end.toLocaleDateString('en-GB', options)} ${end.getFullYear()}`;
    }

    function updateStats() {
        const events = calendar.getEvents();

        const total = events.length;
        const open = events.filter(e => e.extendedProps.resourceId === 'open').length;
        const filled = total - open;

        document.querySelector('.stat strong').innerText = total;
        document.querySelector('.stat.open strong').innerText = open;
        document.querySelector('.stat.filled strong').innerText = filled;
    }

    // Navigation
    document.getElementById('btnPrev').onclick = () => calendar.prev();
    document.getElementById('btnNext').onclick = () => calendar.next();
    document.getElementById('btnToday').onclick = () => calendar.today();

    // View switch
    document.getElementById('btnDay').onclick = () =>
        calendar.changeView('resourceTimelineDay');

    document.getElementById('btnWeek').onclick = () =>
        calendar.changeView('resourceTimelineWeek');

    function updateDateRange(view) {
        document.getElementById('dateRange').innerText =
            '📅 ' + formatDateRange(view);
    }

    if (window.ResizeObserver) {
        new ResizeObserver(function () {
            calendar.updateSize();
        }).observe(calendarEl);
    }

});

function formatTime(date) {
    return date.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit'
    });
}






