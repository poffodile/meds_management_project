function esc(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

var csrfToken = $('meta[name="csrf-token"]').attr('content');
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });

var allWorkflows = [];

$(document).ready(function () {
    loadWorkflows();
    loadExecutions();
    loadTemplates();
    initGalleryToggle();
    showTriggerFields();
    showActionFields();
});

function loadWorkflows() {
    $.get(baseUrl + '/roster/workflows/list', function (res) {
        if (!res.status) return;
        allWorkflows = res.workflows || [];
        renderWorkflows(allWorkflows);
        renderStats(res.stats || {});
    }).fail(function () {
        $('#wf-list').html('<div class="wf-empty">Failed to load workflows.</div>');
    });
}

function renderStats(s) {
    $('#stat-active').text(s.active || 0);
    $('#stat-total').text(s.total || 0);
    $('#stat-executed').text(s.executed_today || 0);
    $('#stat-failed').text(s.failed_today || 0);
}

function renderWorkflows(workflows) {
    if (!workflows.length) {
        $('#wf-list').html('<div class="wf-empty">No workflows configured. Create one to automate notifications and alerts.</div>');
        return;
    }

    var grouped = {};
    for (var i = 0; i < workflows.length; i++) {
        var w = workflows[i];
        var cat = w.category || 'other';
        if (!grouped[cat]) grouped[cat] = [];
        grouped[cat].push(w);
    }

    var html = '';
    var catOrder = ['scheduling', 'compliance', 'clinical', 'training', 'hr', 'engagement', 'reporting'];
    for (var ci = 0; ci < catOrder.length; ci++) {
        var cat = catOrder[ci];
        if (!grouped[cat]) continue;
        html += '<div class="wf-group-header">' + esc(cat) + '</div>';
        for (var j = 0; j < grouped[cat].length; j++) {
            html += renderWorkflowCard(grouped[cat][j]);
        }
    }

    $('#wf-list').html(html);
}

function renderWorkflowCard(w) {
    var isActive = w.is_active ? true : false;
    var cls = isActive ? '' : ' inactive';
    var triggerBadge = '<span class="wf-badge wf-badge-' + esc(w.trigger_type) + '">' + esc(w.trigger_type) + '</span>';
    var actionBadge = w.action_type === 'send_notification'
        ? '<span class="wf-badge wf-badge-notification">notification</span>'
        : '<span class="wf-badge wf-badge-email">email</span>';

    var triggerSummary = buildTriggerSummary(w);
    var actionSummary = buildActionSummary(w);

    var lastRun = '';
    if (w.last_execution) {
        var ex = w.last_execution;
        var resBadge = ex.action_result === 'success'
            ? '<span class="wf-badge wf-badge-success">Success</span>'
            : '<span class="wf-badge wf-badge-failed">Failed</span>';
        lastRun = 'Last run: ' + esc(ex.executed_at) + ' ' + resBadge;
    } else {
        lastRun = 'Last run: Never';
    }

    var pausedLabel = isActive ? '' : ' <span class="wf-badge wf-badge-paused">Paused</span>';
    var toggleIcon = isActive ? '&#10074;&#10074;' : '&#9654;';
    var toggleTitle = isActive ? 'Pause' : 'Activate';

    return '<div class="wf-card' + cls + '">' +
        '<div class="wf-card-body">' +
            '<div class="wf-card-name">' + esc(w.workflow_name) + pausedLabel + '</div>' +
            '<div class="wf-card-detail">' + triggerBadge + ' ' + actionBadge + ' &mdash; ' + esc(triggerSummary) + '</div>' +
            '<div class="wf-card-detail">' + esc(actionSummary) + '</div>' +
            '<div class="wf-card-detail">' + lastRun + '</div>' +
        '</div>' +
        '<div class="wf-card-actions">' +
            '<button class="wf-btn-icon wf-btn-run" title="Run Now" onclick="runSingleWorkflow(' + w.id + ', this)"><i class="fa fa-play"></i></button>' +
            '<button class="wf-btn-icon" title="Edit" onclick="openEditModal(' + w.id + ')"><i class="fa fa-pencil"></i></button>' +
            '<button class="wf-btn-icon" title="' + toggleTitle + '" onclick="toggleWorkflow(' + w.id + ')">' + toggleIcon + '</button>' +
            '<button class="wf-btn-icon danger" title="Delete" onclick="deleteWorkflow(' + w.id + ')"><i class="fa fa-trash"></i></button>' +
        '</div>' +
    '</div>';
}

function buildTriggerSummary(w) {
    var tc = w.trigger_config || {};
    if (w.trigger_type === 'scheduled') {
        var freq = tc.frequency || 'daily';
        var time = tc.time || '08:00';
        var dayStr = '';
        if (freq === 'weekly' && tc.day !== null && tc.day !== undefined) {
            var days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            dayStr = ' on ' + (days[tc.day] || tc.day);
        }
        if (freq === 'monthly' && tc.day) {
            dayStr = ' on day ' + tc.day;
        }
        return freq.charAt(0).toUpperCase() + freq.slice(1) + dayStr + ' at ' + time;
    }
    if (w.trigger_type === 'condition') {
        var cond = (tc.condition || '').replace(/_/g, ' ');
        return (tc.entity || '') + ' ' + cond + ' ' + (tc.threshold || 0) + ' in last ' + (tc.lookback_days || 7) + ' days';
    }
    if (w.trigger_type === 'event') {
        return (tc.entity || '') + ' with status "' + (tc.status || '') + '" >= ' + (tc.min_count || 1);
    }
    return '';
}

function buildActionSummary(w) {
    var ac = w.action_config || {};
    if (w.action_type === 'send_notification') {
        var msg = ac.message || '';
        if (msg.length > 60) msg = msg.substring(0, 60) + '...';
        return 'Notify: "' + msg + '"' + (ac.is_sticky ? ' (sticky)' : '');
    }
    if (w.action_type === 'send_email') {
        var recip = ac.recipients || '';
        var count = recip.split(',').filter(function (e) { return e.trim(); }).length;
        return 'Email ' + count + ' recipient(s): ' + (ac.subject || '');
    }
    return '';
}

function showTriggerFields() {
    $('.trigger-fields').removeClass('visible');
    var type = $('#wf-trigger-type').val();
    if (type === 'scheduled') $('#tf-scheduled').addClass('visible');
    else if (type === 'condition') $('#tf-condition').addClass('visible');
    else if (type === 'event') $('#tf-event').addClass('visible');
}

function showActionFields() {
    $('.action-fields').removeClass('visible');
    var type = $('#wf-action-type').val();
    if (type === 'send_notification') $('#af-notification').addClass('visible');
    else if (type === 'send_email') $('#af-email').addClass('visible');
}

function openCreateModal() {
    $('#wf-id').val('');
    $('#wf-name').val('');
    $('#wf-category').val('scheduling');
    $('#wf-trigger-type').val('scheduled');
    $('#wf-action-type').val('send_notification');
    $('#wf-cooldown').val(24);
    $('#wf-active').prop('checked', true);

    $('#tf-frequency').val('daily');
    $('#tf-day').val('');
    $('#tf-time').val('08:00');
    $('#tf-cond-entity').val('incidents');
    $('#tf-cond-condition').val('count_exceeds');
    $('#tf-cond-threshold').val(5);
    $('#tf-cond-lookback').val(7);
    $('#tf-evt-entity').val('shifts');
    $('#tf-evt-status').val('');
    $('#tf-evt-min').val(1);

    $('#af-notif-message').val('');
    $('#af-notif-sticky').prop('checked', false);
    $('#af-email-recipients').val('');
    $('#af-email-subject').val('');
    $('#af-email-message').val('');

    showTriggerFields();
    showActionFields();
    $('#modalTitle').text('New Workflow');
    $('#workflowModal').modal('show');
}

function openEditModal(id) {
    var w = null;
    for (var i = 0; i < allWorkflows.length; i++) {
        if (allWorkflows[i].id === id) { w = allWorkflows[i]; break; }
    }
    if (!w) return;

    $('#wf-id').val(w.id);
    $('#wf-name').val(w.workflow_name);
    $('#wf-category').val(w.category);
    $('#wf-trigger-type').val(w.trigger_type);
    $('#wf-action-type').val(w.action_type);
    $('#wf-cooldown').val(w.cooldown_hours);
    $('#wf-active').prop('checked', w.is_active ? true : false);

    var tc = w.trigger_config || {};
    if (w.trigger_type === 'scheduled') {
        $('#tf-frequency').val(tc.frequency || 'daily');
        $('#tf-day').val(tc.day !== null && tc.day !== undefined ? tc.day : '');
        $('#tf-time').val(tc.time || '08:00');
    } else if (w.trigger_type === 'condition') {
        $('#tf-cond-entity').val(tc.entity || 'incidents');
        $('#tf-cond-condition').val(tc.condition || 'count_exceeds');
        $('#tf-cond-threshold').val(tc.threshold || 0);
        $('#tf-cond-lookback').val(tc.lookback_days || 7);
    } else if (w.trigger_type === 'event') {
        $('#tf-evt-entity').val(tc.entity || 'shifts');
        $('#tf-evt-status').val(tc.status || '');
        $('#tf-evt-min').val(tc.min_count || 1);
    }

    var ac = w.action_config || {};
    if (w.action_type === 'send_notification') {
        $('#af-notif-message').val(ac.message || '');
        $('#af-notif-sticky').prop('checked', ac.is_sticky ? true : false);
    } else if (w.action_type === 'send_email') {
        $('#af-email-recipients').val(ac.recipients || '');
        $('#af-email-subject').val(ac.subject || '');
        $('#af-email-message').val(ac.message || '');
    }

    showTriggerFields();
    showActionFields();
    $('#modalTitle').text('Edit Workflow');
    $('#workflowModal').modal('show');
}

function buildTriggerConfig() {
    var type = $('#wf-trigger-type').val();
    if (type === 'scheduled') {
        var dayVal = $('#tf-day').val();
        return {
            frequency: $('#tf-frequency').val(),
            day: dayVal !== '' ? parseInt(dayVal) : null,
            time: $('#tf-time').val()
        };
    }
    if (type === 'condition') {
        return {
            entity: $('#tf-cond-entity').val(),
            condition: $('#tf-cond-condition').val(),
            threshold: parseInt($('#tf-cond-threshold').val()) || 0,
            lookback_days: parseInt($('#tf-cond-lookback').val()) || 7
        };
    }
    if (type === 'event') {
        return {
            entity: $('#tf-evt-entity').val(),
            status: $('#tf-evt-status').val(),
            min_count: parseInt($('#tf-evt-min').val()) || 1
        };
    }
    return {};
}

function buildActionConfig() {
    var type = $('#wf-action-type').val();
    if (type === 'send_notification') {
        return {
            message: $('#af-notif-message').val(),
            is_sticky: $('#af-notif-sticky').is(':checked')
        };
    }
    if (type === 'send_email') {
        return {
            recipients: $('#af-email-recipients').val(),
            subject: $('#af-email-subject').val(),
            message: $('#af-email-message').val()
        };
    }
    return {};
}

function saveWorkflow() {
    var id = $('#wf-id').val();
    var url = id ? baseUrl + '/roster/workflows/update' : baseUrl + '/roster/workflows/store';

    var data = {
        workflow_name: $('#wf-name').val(),
        category: $('#wf-category').val(),
        trigger_type: $('#wf-trigger-type').val(),
        trigger_config: JSON.stringify(buildTriggerConfig()),
        action_type: $('#wf-action-type').val(),
        action_config: JSON.stringify(buildActionConfig()),
        cooldown_hours: parseInt($('#wf-cooldown').val()) || 24
    };

    if (id) data.id = parseInt(id);

    $.post(url, data, function (res) {
        if (res.status) {
            $('#workflowModal').modal('hide');
            loadWorkflows();
            loadExecutions();
        } else {
            alert(res.message || 'Failed to save workflow.');
        }
    }).fail(function (xhr) {
        var msg = 'Failed to save workflow.';
        if (xhr.responseJSON) {
            if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
            if (xhr.responseJSON.errors) {
                var errs = xhr.responseJSON.errors;
                var parts = [];
                for (var k in errs) { parts.push(errs[k].join(', ')); }
                msg = parts.join('\n');
            }
        }
        alert(msg);
    });
}

function toggleWorkflow(id) {
    $.post(baseUrl + '/roster/workflows/toggle', { id: id }, function (res) {
        if (res.status) {
            loadWorkflows();
        } else {
            alert(res.message || 'Failed to toggle workflow.');
        }
    });
}

function deleteWorkflow(id) {
    if (!confirm('Are you sure you want to delete this workflow?')) return;

    $.post(baseUrl + '/roster/workflows/delete', { id: id }, function (res) {
        if (res.status) {
            loadWorkflows();
            loadExecutions();
        } else {
            alert(res.message || 'Failed to delete workflow.');
        }
    });
}

function runSingleWorkflow(id, btn) {
    var $btn = $(btn);
    var origHtml = $btn.html();
    $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

    $.post(baseUrl + '/roster/workflows/run-single', { id: id }, function (res) {
        if (res.status && res.result) {
            var r = res.result;
            if (r.status === 'success') {
                $btn.html('<i class="fa fa-check"></i>').css('color', '#27ae60');
            } else {
                $btn.html('<i class="fa fa-times"></i>').css('color', '#e74c3c');
            }
            loadWorkflows();
            loadExecutions();
        } else {
            $btn.html('<i class="fa fa-times"></i>').css('color', '#e74c3c');
            alert(res.message || 'Run failed.');
        }
        setTimeout(function () { $btn.html(origHtml).css('color', '').prop('disabled', false); }, 2000);
    }).fail(function (xhr) {
        $btn.html('<i class="fa fa-times"></i>').css('color', '#e74c3c');
        var msg = 'Run failed.';
        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
        alert(msg);
        setTimeout(function () { $btn.html(origHtml).css('color', '').prop('disabled', false); }, 2000);
    });
}

function runAllWorkflows() {
    var $btn = $('#btn-run-all');
    var origHtml = $btn.html();
    $btn.html('<i class="fa fa-spinner fa-spin"></i> Running...').prop('disabled', true);

    $.post(baseUrl + '/roster/workflows/run-all', {}, function (res) {
        if (res.status) {
            var results = res.results || [];
            var success = 0, failed = 0, skipped = 0;
            for (var i = 0; i < results.length; i++) {
                if (results[i].status === 'success') success++;
                else if (results[i].status === 'failed') failed++;
                else skipped++;
            }
            $btn.html('<i class="fa fa-check"></i> Done: ' + success + ' triggered, ' + skipped + ' skipped' + (failed ? ', ' + failed + ' failed' : ''));
            loadWorkflows();
            loadExecutions();
        } else {
            $btn.html('<i class="fa fa-times"></i> Failed');
        }
        setTimeout(function () { $btn.html(origHtml).prop('disabled', false); }, 4000);
    }).fail(function () {
        $btn.html('<i class="fa fa-times"></i> Failed');
        setTimeout(function () { $btn.html(origHtml).prop('disabled', false); }, 3000);
    });
}

function loadExecutions() {
    $.get(baseUrl + '/roster/workflows/executions', function (res) {
        if (!res.status) return;
        renderExecutions(res.executions || []);
    });
}

function renderExecutions(execs) {
    if (!execs.length) {
        $('#wf-exec-list').html('<div class="wf-empty" style="padding:20px;">No executions recorded yet.</div>');
        return;
    }

    var html = '<table class="wf-exec-table"><thead><tr>' +
        '<th>Time</th><th>Workflow</th><th>Trigger</th><th>Action</th><th>Result</th><th>Details</th>' +
        '</tr></thead><tbody>';

    for (var i = 0; i < execs.length; i++) {
        var e = execs[i];
        var resCls = e.action_result === 'success' ? 'wf-badge-success' : 'wf-badge-failed';
        var details = '';
        if (e.error_message) {
            details = esc(e.error_message);
        } else if (e.trigger_data) {
            var td = typeof e.trigger_data === 'string' ? JSON.parse(e.trigger_data) : e.trigger_data;
            if (td.count !== undefined) details = 'Count: ' + td.count;
            else if (td.scheduled_for) details = 'Scheduled: ' + esc(td.scheduled_for);
        }

        html += '<tr>' +
            '<td>' + esc(e.executed_at) + '</td>' +
            '<td>' + esc(e.workflow_name || '#' + e.workflow_id) + '</td>' +
            '<td>' + esc(e.trigger_type) + '</td>' +
            '<td>' + esc(e.action_type) + '</td>' +
            '<td><span class="wf-badge ' + resCls + '">' + esc(e.action_result) + '</span></td>' +
            '<td>' + details + '</td>' +
            '</tr>';
    }

    html += '</tbody></table>';
    $('#wf-exec-list').html(html);
}

// ==================== TEMPLATE GALLERY ====================

function loadTemplates() {
    $.get(baseUrl + '/roster/workflows/templates', function (res) {
        if (!res.status) return;
        renderTemplates(res.templates || []);
    });
}

function renderTemplates(templates) {
    if (!templates.length) {
        $('#tpl-gallery').html('<div class="wf-empty" style="padding:20px;">No templates available.</div>');
        return;
    }

    var grouped = {};
    var catOrder = ['compliance', 'scheduling', 'clinical', 'training', 'hr', 'engagement', 'reporting'];
    for (var i = 0; i < templates.length; i++) {
        var t = templates[i];
        var cat = t.category || 'other';
        if (!grouped[cat]) grouped[cat] = [];
        grouped[cat].push(t);
    }

    var html = '';
    for (var ci = 0; ci < catOrder.length; ci++) {
        var cat = catOrder[ci];
        if (!grouped[cat]) continue;
        html += '<div class="tpl-cat-header">' + esc(cat) + '</div>';
        for (var j = 0; j < grouped[cat].length; j++) {
            html += renderTemplateCard(grouped[cat][j]);
        }
    }

    $('#tpl-gallery').html(html);
}

function renderTemplateCard(t) {
    var triggerBadge = '<span class="wf-badge wf-badge-' + esc(t.trigger_type) + '">' + esc(t.trigger_type) + '</span>';
    var actionBadge = t.action_type === 'send_notification'
        ? '<span class="wf-badge wf-badge-notification">notification</span>'
        : '<span class="wf-badge wf-badge-email">email</span>';

    var installBtn = t.installed
        ? '<span class="btn-installed">Installed &#10003;</span>'
        : '<button class="btn-install" onclick="installTemplate(\'' + esc(t.template_id) + '\')">Install</button>';

    return '<div class="tpl-card" id="tpl-' + esc(t.template_id) + '">' +
        '<div class="tpl-card-icon"><i class="bx ' + esc(t.icon || 'bx-zap') + '"></i></div>' +
        '<div class="tpl-card-body">' +
            '<div class="tpl-card-name">' + esc(t.workflow_name) + '</div>' +
            '<div class="tpl-card-desc">' + esc(t.description) + '</div>' +
            '<div class="tpl-card-badges">' + triggerBadge + ' ' + actionBadge + '</div>' +
        '</div>' +
        installBtn +
    '</div>';
}

function installTemplate(templateId) {
    $.post(baseUrl + '/roster/workflows/install-template', { template_id: templateId }, function (res) {
        if (res.status) {
            var card = $('#tpl-' + templateId);
            card.find('.btn-install').replaceWith('<span class="btn-installed">Installed &#10003;</span>');
            loadWorkflows();

            if (res.needs_config) {
                alert('Workflow installed! Please edit it to add email recipients before it can send.');
            }
        } else {
            alert(res.message || 'Failed to install template.');
        }
    }).fail(function (xhr) {
        var msg = 'Failed to install template.';
        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
        alert(msg);
    });
}

function toggleGallery() {
    var gallery = $('#tpl-gallery');
    var btn = $('#tpl-toggle-btn');
    if (gallery.hasClass('collapsed')) {
        gallery.removeClass('collapsed');
        btn.html('&#9650; Hide');
        localStorage.setItem('wf_gallery_collapsed', '0');
    } else {
        gallery.addClass('collapsed');
        btn.html('&#9660; Show');
        localStorage.setItem('wf_gallery_collapsed', '1');
    }
}

function initGalleryToggle() {
    if (localStorage.getItem('wf_gallery_collapsed') === '1') {
        $('#tpl-gallery').addClass('collapsed');
        $('#tpl-toggle-btn').html('&#9660; Show');
    }
}
