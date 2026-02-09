document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const BASE_URL = document
    .querySelector('meta[name="base-url"]')
    .getAttribute('content');
    const calendar = new FullCalendar.Calendar(calendarEl, {

        schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        initialView: 'resourceTimelineWeek',
        resourceOrder: 'order',
        height: 'auto',
        expandRows: false,
        contentHeight: 'auto',

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
            return {
                html: `
            <div style="display:flex;gap:10px;align-items:center">
                <div style="
                    width:36px;
                    height:36px;
                    border-radius:50%;
                    background:#3b82f6;
                    color:#fff;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    font-weight:600">
                    ${arg.resource.title.substring(0, 2)}
                </div>
                <div>
                    <strong>${arg.resource.title}</strong><br>
                    <small style="color:#6b7280">0h / 40h</small>
                </div>
            </div>
        `
            }
        },

        // eventContent: function (arg) {
        //     const isWeek = arg.view.type === 'resourceTimelineWeek';

        //     return {
        //         html: `
        //     <div class="shift-card">
        //         <strong>${arg.event.title}</strong>
        //         ${!isWeek ? `<div class="time">
        //             ${arg.timeText}
        //         </div>` : ''}
        //     </div>
        // `
        //     };
        // },

        eventContent: function (arg) {
            if (arg.event.extendedProps.type === 'open') {
                return {
                    html: `
                    <div class="open-shift-box">
                        <div class="dot"></div>
                        <div class="details">
                            <strong>${arg.event.extendedProps.location}</strong>
                            <div class="time">
                                ${formatTime(arg.event.start)} - ${formatTime(arg.event.end)}
                            </div>
                        </div>
                    </div>
                `
                };
            }

            return { html: `<div class="normal-event">${arg.event.title}</div>` };
        },

        eventAllow: function (dropInfo, draggedEvent) {
            if (draggedEvent.extendedProps.resourceId === 'open') {
                return true;
            }
            return true;
        },

        /* ===== RESOURCE SETTINGS ===== */
        resourceAreaHeaderContent: 'Staff',

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

                slotLabelFormat: [
                    { weekday: 'short', day: 'numeric' } // Mon 9
                ]
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


        /* ===== STAFF (RESOURCES) ===== */
        // resources: [{
        //     id: 'open',
        //     title: '🟡 Open Shifts',
        //     order: 0 // 👈 always first
        // },
        // {
        //     id: '1',
        //     title: 'Alex Sheffield',
        //     order: 1 // 👈 always first
        // },
        // {
        //     id: '2',
        //     title: 'Becky Harrison',
        //     order: 2
        // },
        // {
        //     id: '3',
        //     title: 'Emma Wilson',
        //     order: 3
        // },
        // {
        //     id: '4',
        //     title: 'Alex Sheffield',
        //     order: 4 // 👈 always first
        // },
        // {
        //     id: '5',
        //     title: 'Becky Harrison',
        //     order: 5
        // },
        // {
        //     id: '6',
        //     title: 'Emma Wilson',
        //     order: 6
        // }
        // ],

        resources: {
            url: `${BASE_URL}/roster/carer/shift-resources`,  // 👈 your Laravel route
            method: 'GET',
            failure() {
                alert('Failed to load resources');
            }
        },
        /* ===== SHIFTS (EVENTS) ===== */
        events: [
            // 🟡 OPEN SHIFTS
            {
                id: '101',
                title: 'South Wing',
                start: '2026-01-22T09:00:00',
                end: '2026-01-22T13:00:00',
                resourceId: 'open',
                backgroundColor: '#fde68a'
            },
            {
                id: '102',
                title: 'Night Shift',
                start: '2026-01-23T20:00:00',
                end: '2026-01-24T06:00:00',
                resourceId: 'open',
                backgroundColor: '#fde68a'
            },
            {
                id: '103',
                title: 'East Wing',
                start: '2026-01-24T10:00:00',
                end: '2026-01-24T18:00:00',
                resourceId: 'open',
                backgroundColor: '#fde68a'
            },
            // 🟢 ASSIGNED SHIFTS
            {
                id: '104',
                title: 'South Wing',
                start: '2026-01-22T09:00:00',
                end: '2026-01-22T13:00:00',
                resourceId: '1',
                backgroundColor: '#d1fae5'
            },
            {
                id: '105',
                title: 'North Wing',
                start: '2026-01-23T09:00:00',
                end: '2026-01-23T17:00:00',
                resourceId: '2',
                backgroundColor: '#bbf7d0'
            },
            {
                id: '106',
                title: 'East Wing',
                start: '2026-01-24T10:00:00',
                end: '2026-01-24T18:00:00',
                resourceId: '3',
                backgroundColor: '#a7f3d0'
            },
            {
                id: '107',
                title: 'South Wing',
                start: '2026-01-22T09:00:00',
                end: '2026-01-22T13:00:00',
                resourceId: '1',
                backgroundColor: '#d1fae5'
            },
            {
                id: '108',
                title: 'North Wing',
                start: '2026-01-23T09:00:00',
                end: '2026-01-23T17:00:00',
                resourceId: '2',
                backgroundColor: '#bbf7d0'
            },
            {
                id: '109',
                title: 'East Wing',
                start: '2026-01-24T10:00:00',
                end: '2026-01-24T18:00:00',
                resourceId: '3',
                backgroundColor: '#a7f3d0'
            }
        ]
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


});

calendar.on('datesSet', function (info) {
    document.getElementById('dateRange').innerText =
        '📅 ' + formatDateRange(info.view);
});

function formatTime(date) {
    return date.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit'
    });
}






