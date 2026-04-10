document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const BASE_URL = document.querySelector('meta[name="base-url"]').getAttribute('content');
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
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
            // updateStats(); // optional but recommended
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

            const hoursLogged = arg.resource.extendedProps.hours_scheduled || 0;
            const hoursTotal = arg.resource.extendedProps.max_hours || 40;
            const progress = Math.min((hoursLogged / hoursTotal) * 100, 100);

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
                            <small style="color:#6b7280;font-size:11px;">${hoursLogged.toFixed(1)}h / ${hoursTotal}h</small>
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

            // Calculate duration
            let durationStr = '';
            if (arg.event.start && arg.event.end) {
                const diffMs = arg.event.end - arg.event.start;
                const hours = diffMs / (1000 * 60 * 60);
                durationStr = hours.toFixed(1) + 'h';
            }

            if (!isAssigned) {
                // Open shift styling
                return {
                    html: `
                    <div style="padding: 4px; color: #92400e; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; font-size: 13px; font-weight: 500; line-height: 1.2;">
                        <div style="display:flex; align-items:center; gap: 4px; justify-content: space-between;">
                            <span>${title}</span>
                            ${durationStr ? `<span style="font-size: 10px; background: rgba(251, 191, 36, 0.2); padding: 1px 4px; border-radius: 4px;">${durationStr}</span>` : ''}
                        </div>
                        ${timeStr ? `<div style="font-size: 11px; opacity: 0.8; margin-top: 2px; font-weight: normal;">${timeStr}</div>` : ''}
                    </div>`
                };
            }

            // Assigned shift styling
            return {
                html: `
                <div style="padding: 4px; color: #065f46; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; font-size: 13px; font-weight: 500; line-height: 1.2;">
                    <div style="display:flex; align-items:center; gap: 4px; justify-content: space-between;">
                        <div style="display:flex; align-items:center; gap: 4px; overflow: hidden;">
                            <span style="height: 6px; width: 6px; background-color: #059669; border-radius: 50%; display: inline-block; flex-shrink: 0;"></span>
                            <span style="overflow: hidden; text-overflow: ellipsis;">${title}</span>
                        </div>
                        ${durationStr ? `<span style="font-size: 10px; background: rgba(16, 185, 129, 0.1); padding: 1px 4px; border-radius: 4px;">${durationStr}</span>` : ''}
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

        eventClick: function (info) {
            const ev = info.event;
            const props = ev.extendedProps;
            const form = $('#createShiftForm');

            // Change form action to update
            form.attr('action', BASE_URL + '/roster/schedule-shift/update/' + props.shift_id);
            form.closest('.modal-content').find('.modal-title').text('Edit Shift');
            form.find('button[type="submit"]').html('Update Shift');
            form.find('#edit_shift_id').val(props.shift_id);
            console.log('shiftId3', props.shift_id);
            // Populate fields
            if (props.client_id) {
                form.find('[name="client_id"]').val(props.client_id).trigger('change');
                // Allow the change event a tick to resolve the option text
                setTimeout(() => {
                    $('#assignedClientTo').text(form.find('[name="client_id"] option:selected').text());
                }, 10);
            } else {
                form.find('[name="client_id"]').val('').trigger('change');
                $('#assignedClientTo').text('Not assigned');
            }

            form.find('[name="start_date"]').val(props.start_date);
            form.find('[name="start_time"]').val(props.start_time_raw);
            form.find('[name="end_time"]').val(props.end_time_raw);

            const staffName = props.staff_name || '';

            if (props.staff_id) {
                form.find('[name="carer_id"]').val(props.staff_id).trigger('change');
                $('#selected_carer_id').val(props.staff_id);
                $('#selectedCarerName').text(staffName);
                $('#selectedCarerCard').show();
                $('#carerSuggestionsWrapper').hide();
                $('#toggleSuggestionsBtn').show().text('Show Suggestions');
            } else {
                form.find('[name="carer_id"]').val('').trigger('change');
                $('#selected_carer_id').val('');
                $('#selectedCarerCard').hide();
                $('#carerSuggestionsWrapper').show();
                $('#toggleSuggestionsBtn').hide();
            }

            form.find('[name="shift_type"]').val(props.shift_type_raw).trigger('change');
            form.find('[name="shift_category"]').val(props.shift_category_id).trigger('change');
            form.find('[name="home_area_id"]').val(props.home_area_id).trigger('change');
            form.find('[name="property_id"]').val(props.property_id).trigger('change');
            form.find('[name="location_name"]').val(props.location_name);
            form.find('[name="location_address"]').val(props.location_address);

            if (props.care_type) {
                form.find('[name="care_type"]').val(props.care_type).trigger('change');
            }
            if (props.assignment) {
                form.find('[name="assignment"]').val(props.assignment).trigger('change');
                let assignLower = props.assignment.toLowerCase();
                if (assignLower === 'location' || assignLower === 'home area') $('#locationTab').click();
                else if (assignLower === 'client') $('#clientTab').click();
                else if (assignLower === 'property') $('#propertyTab').click();
            } else {
                form.find('[name="assignment"]').val('Client').trigger('change');
                $('#clientTab').click();
            }
            form.find('[name="notes"]').val(props.notes);

            if (props.tasks) {
                form.find('[name="tasks"]').val(props.tasks).trigger('change');
            } else {
                form.find('[name="tasks"]').val('').trigger('change');
            }

            // Populate Recurrence
            if (props.is_recurring == "1" || props.is_recurring === true) {
                form.find('#recurringClientToggle').prop('checked', true);
                if (props.recurrence) {
                    form.find('[name="frequency"]').val(props.recurrence.frequency).trigger('change');
                    form.find('[name="end_date"]').val(props.recurrence.end_recurring_date || '');

                    const daysRow = form.find('.weeklyDaysSelect').closest('.col-md-12');
                    if (props.recurrence.frequency === 'weekly') {
                        daysRow.show();
                        if (props.recurrence.week_days) {
                            try {
                                let days = [];
                                if (props.recurrence.week_days.startsWith('[') || props.recurrence.week_days.startsWith('{')) {
                                    days = JSON.parse(props.recurrence.week_days);
                                } else {
                                    days = props.recurrence.week_days.split(',').map(d => d.trim());
                                }

                                form.find('.weeklyDaysSelect span').removeClass('active');
                                form.find('.weeklyDaysSelect span').each(function () {
                                    if (days.includes($(this).text().trim())) {
                                        $(this).addClass('active');
                                    }
                                });
                                form.find('#week_days').val(Array.isArray(days) ? days.join(',') : props.recurrence.week_days);
                            } catch (e) {
                                console.error('Failed to parse week_days', e);
                            }
                        }
                    } else {
                        daysRow.hide();
                        form.find('.weeklyDaysSelect span').removeClass('active');
                        form.find('.weeklyDaysSelect span').first().addClass('active');
                        form.find('#week_days').val('');
                    }
                }
                $('#recurringClientDiv').slideDown();
            } else {
                form.find('#recurringClientToggle').prop('checked', false);
                form.find('[name="frequency"]').val('daily').trigger('change');
                form.find('[name="end_date"]').val('');
                form.find('.weeklyDaysSelect span').removeClass('active');
                form.find('.weeklyDaysSelect span').first().addClass('active');
                form.find('#week_days').val('');
                $('#recurringClientDiv').slideUp();
            }

            // Populate Documents
            $('.pendingCard').remove();
            let hasDocs = false;
            if (props.documents && props.documents.length > 0) {
                hasDocs = true;
                props.documents.forEach(doc => {
                    let isForm = doc.form_id ? true : false;
                    let today = new Date().toISOString().split('T')[0];

                    // Improved title logic: use doc_name, fallback to filename or form_id
                    let title = doc.doc_name;
                    if (!title) {
                        if (isForm) {
                            title = 'System Form #' + doc.form_id;
                        } else if (doc.doc_file) {
                            title = doc.doc_file.split('/').pop();
                        } else {
                            title = 'Unnamed Document';
                        }
                    }

                    if (!isForm && doc.doc_file) {
                        // Correct path: BASE_URL + '/' + doc_file
                        title = `<a href="${BASE_URL}/${doc.doc_file}" target="_blank" style="color:var(--primary-color); text-decoration:underline;">${title}</a>`;
                    }

                    let newSection = `
                        <div class="card pendingCard" data-doc-id="${doc.id}">
                            <input type="hidden" name="existing_document_ids[]" value="${doc.id}">
                            <div class="left">
                                <div class="icon blueText"><i class='bx bx-file'></i></div>
                                <div class="info">
                                    <div class="title">${title}</div>
                                    <div class="meta">
                                        <div class="inactive roundTag">${isForm ? 'System Form' : 'Attachment'}</div>
                                        ${doc.doc_required == 1 ? '<div class="inactive roundTag" style="background:#fee2e2;color:#991b1b;">Required</div>' : ''}
                                        <span class="date">${today}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="actions">
                                <span class="approve"><i class='bx bx-check-circle'></i></span>
                                <span class="delete" onclick="$(this).closest('.pendingCard').remove(); $('#pendingHeader').text('Pending Completion (' + $('.pendingCard').length + ')');"><i class='bx bx-trash'></i></span>
                            </div>
                        </div>
                    `;
                    $('#pendingCompletion').append(newSection);
                });
            }

            if (hasDocs) {
                $('#pendingCompletionSection').show();
                $('#attachDocumentSection').hide();
                $('#pendingHeader').text('Pending Completion (' + $('.pendingCard').length + ')');
                $('#close_document').hide();
                $('#attach_document').show();
            } else {
                $('#pendingCompletionSection').hide();
                $('#attachDocumentSection').show();
            }

            // Populate Assessments
            $('#assessmentList').empty();
            if (props.assessments && props.assessments.length > 0) {
                props.assessments.forEach((ass, index) => {
                    let itemId = 'assessment-item-edit-' + index;
                    let fileNameRaw = ass.assessment_doc ? ass.assessment_doc.split('/').pop() : 'Assessment ' + ass.id;

                    let fileNameHtml = fileNameRaw;
                    if (ass.assessment_doc) {
                        // Correct path: BASE_URL + '/' + ass.assessment_doc
                        fileNameHtml = `<a href="${BASE_URL}/${ass.assessment_doc}" target="_blank" style="color:var(--primary-color); text-decoration:underline;">${fileNameRaw}</a>`;
                    }

                    let itemHtml = `
                        <div class="assessment-item" id="${itemId}">
                            <input type="hidden" name="existing_assessment_ids[]" value="${ass.id}">
                            <div class="assessment-item-left">
                                <i class="fa fa-file-text-o"></i>
                                <span class="assessment-item-name" title="${fileNameRaw}">${fileNameHtml}</span>
                            </div>
                            <div class="assessment-item-right">
                                <select class="assessment-type-select" name="existing_assessment_types[${ass.id}]">
                                    <option value="other" ${ass.assessment_type === 'other' || !ass.assessment_type ? 'selected' : ''}>Other</option>
                                    <option value="supervision" ${ass.assessment_type === 'supervision' ? 'selected' : ''}>Supervision Form</option>
                                    <option value="care_plan" ${ass.assessment_type === 'care_plan' ? 'selected' : ''}>Care Plan</option>
                                    <option value="risk" ${ass.assessment_type === 'risk' ? 'selected' : ''}>Risk Assessment</option>
                                    <option value="medication" ${ass.assessment_type === 'medication' ? 'selected' : ''}>Medication Chart</option>
                                </select>
                                <button type="button" class="assessment-item-delete" title="Remove" onclick="$(this).closest('.assessment-item').remove()">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    $('#assessmentList').append(itemHtml);
                });
            }

            $('#assessment_card').show();
            $('#addShiftModal').modal('show');
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
                // Get current view's visible range
                const view = calendar.view;
                const viewStart = view.activeStart;
                const viewEnd = view.activeEnd;

                // Initialize/Reset hours per resource
                const hoursByResource = {};
                let totalVisibleHours = 0;
                let openShifts = 0;
                let filledShifts = 0;

                data.forEach(ev => {
                    const start = new Date(ev.start);
                    const end = new Date(ev.end);

                    // Only count if it's within the currently visible timeframe
                    if (start >= viewStart && start < viewEnd) {
                        const hours = (end - start) / (1000 * 60 * 60);

                        if (ev.resourceId && ev.resourceId !== 'open') {
                            hoursByResource[ev.resourceId] = (hoursByResource[ev.resourceId] || 0) + hours;
                            totalVisibleHours += hours;
                            filledShifts++;
                        } else {
                            openShifts++;
                        }
                    }
                });

                // Update resource props
                calendar.getResources().forEach(res => {
                    if (res.id !== 'open') {
                        const hours = hoursByResource[res.id] || 0;
                        res.setExtendedProp('hours_scheduled', hours);
                    }
                });

                // Update dashboard stats
                $('.stat strong').first().text(filledShifts + openShifts);
                $('.stat.filled strong').text(filledShifts);
                $('.stat.open strong').text(openShifts);
                $('.stat.hours strong').text(Math.round(totalVisibleHours) + 'h');

                console.log(`🔥 Updated for ${view.type}:`, totalVisibleHours, "hours");
            },
            failure: function () {
                console.error("❌ Failed to load shifts!");
                alert('Failed to load shifts');
            }
        },

        /* ================= DRAG & DROP ================= */
        eventDrop: function (info) {
            const event = info.event;
            const shiftId = event.extendedProps.shift_id;

            const oldResource = info.oldResource?.id;
            const newResource = event.getResources()[0]?.id;

            console.log('Old:', oldResource, 'New:', newResource);

            // 🚫 Prevent staff → open
            if (oldResource !== 'open' && newResource === 'open') {
                alert('Cannot move assigned shift back to open');
                info.revert();
                return;
            }

            // Get new dates/times in local time
            const year = event.start.getFullYear();
            const month = String(event.start.getMonth() + 1).padStart(2, '0');
            const day = String(event.start.getDate()).padStart(2, '0');
            const startDate = `${year}-${month}-${day}`;
            
            const hours = String(event.start.getHours()).padStart(2, '0');
            const mins = String(event.start.getMinutes()).padStart(2, '0');
            const startTime = `${hours}:${mins}:00`;

            let endTime = startTime;
            if (event.end) {
                const eHours = String(event.end.getHours()).padStart(2, '0');
                const eMins = String(event.end.getMinutes()).padStart(2, '0');
                endTime = `${eHours}:${eMins}:00`;
            }

            // ✅ Assign OR Reassign
            assignShift(shiftId, newResource, info, startDate, startTime, endTime);
        },

        eventResize: function (info) {
            const event = info.event;
            const shiftId = event.extendedProps.shift_id;
            const resourceId = event.getResources()[0]?.id;

            // Get new dates/times in local time
            const year = event.start.getFullYear();
            const month = String(event.start.getMonth() + 1).padStart(2, '0');
            const day = String(event.start.getDate()).padStart(2, '0');
            const startDate = `${year}-${month}-${day}`;
            
            const hours = String(event.start.getHours()).padStart(2, '0');
            const mins = String(event.start.getMinutes()).padStart(2, '0');
            const startTime = `${hours}:${mins}:00`;

            let endTime = startTime;
            if (event.end) {
                const eHours = String(event.end.getHours()).padStart(2, '0');
                const eMins = String(event.end.getMinutes()).padStart(2, '0');
                endTime = `${eHours}:${eMins}:00`;
            }

            assignShift(shiftId, resourceId, info, startDate, startTime, endTime);
        },

    });

    calendar.render();


    /* ================= FUNCTION ================= */
    function assignShift(shiftId, staffId, info, startDate = null, startTime = null, endTime = null) {

        fetch(`${BASE_URL}/roster/schedule-shift/assign-shift`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                shift_id: shiftId,
                staff_id: staffId,
                start_date: startDate,
                start_time: startTime,
                end_time: endTime
            })
        })
            .then(res => res.json())
            .then(data => {

                if (!data.success) {
                    alert(data.message || 'Assignment failed');
                    info.revert();
                    return;
                }

                // ✅ Optional: refresh calendar
                calendar.refetchEvents();

            })
            .catch(err => {
                console.error(err);
                info.revert();
            });
    }



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
        /*
        const events = calendar.getEvents();

        const total = events.length;
        const open = events.filter(e => e.extendedProps.resourceId === 'open').length;
        const filled = total - open;

        document.querySelector('.stat strong').innerText = total;
        document.querySelector('.stat.open strong').innerText = open;
        document.querySelector('.stat.filled strong').innerText = filled;
        */
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






