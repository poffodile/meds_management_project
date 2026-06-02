@extends('frontEnd.layouts.master')
@section('title','Shift Handover')
@section('content')

@include('frontEnd.roster.common.roster_header')

<main class="page-content">
    <div class="container-fluid">

            <div style="max-width:900px; margin:0 auto;">

            {{-- Header --}}
            <div class="m-t-30" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:22px;">
                <div>
                    <h2 style="margin:0; font-weight:700; color:#111827; font-size:24px;"><i class="fa fa-file-text-o" style="color:#0d9488; margin-right:10px;"></i>Shift Handover</h2>
                    <p style="margin:6px 0 0; color:#6b7280;">Record and review handover notes between shifts</p>
                </div>
                <button type="button" id="newHandoverBtn" class="btn" style="background:#0d9488; color:#fff; border:none; border-radius:8px; padding:10px 18px; font-weight:600;" data-toggle="modal" data-target="#shiftHandoverModal">
                    <i class="fa fa-plus"></i> New Handover
                </button>
            </div>

            {{-- Date navigation (handover log by day) --}}
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:18px;">
                <a href="{{ route('medication.shift-handover.index', ['date' => $prevDate]) }}" class="btn" style="background:#fff;border:1px solid #d1d5db;color:#374151;border-radius:8px;padding:7px 12px;font-weight:600;">&laquo; Previous day</a>
                <form method="GET" action="{{ route('medication.shift-handover.index') }}" style="margin:0;">
                    <input type="date" name="date" value="{{ $selectedDate }}" class="form-control" style="display:inline-block;width:auto;" onchange="this.form.submit()">
                </form>
                <a href="{{ route('medication.shift-handover.index', ['date' => $nextDate]) }}" class="btn" style="background:#fff;border:1px solid #d1d5db;color:#374151;border-radius:8px;padding:7px 12px;font-weight:600;">Next day &raquo;</a>
                @if($selectedDate !== $todayDate)
                    <a href="{{ route('medication.shift-handover.index', ['date' => $todayDate]) }}" class="btn" style="background:#0d9488;color:#fff;border:none;border-radius:8px;padding:7px 12px;font-weight:600;">Today</a>
                @endif
                <span style="color:#6b7280; font-weight:600; margin-left:auto;">{{ \Carbon\Carbon::parse($selectedDate)->format('l, j M Y') }}</span>
            </div>

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="row"><div class="col-md-12"><div class="alert alert-success">{{ session('success') }}</div></div></div>
            @endif
            @if($errors->any())
                <div class="row"><div class="col-md-12"><div class="alert alert-danger">
                    <strong>Please fix the following:</strong>
                    <ul style="margin:8px 0 0 20px;">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div></div></div>
            @endif

            {{-- Handover list --}}
            @if($handovers->isEmpty())
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; text-align:center; padding:60px 20px; color:#9ca3af;">
                    <i class="fa fa-file-text-o" style="font-size:48px; opacity:.2;"></i>
                    <p style="margin-top:16px; font-weight:600; color:#6b7280;">No handovers recorded for {{ \Carbon\Carbon::parse($selectedDate)->format('j M Y') }}</p>
                    <p style="font-size:13px;">Use the date arrows to look at another day, or create a new handover.</p>
                </div>
            @else
                @foreach($handovers as $h)
                    @php
                        $hasUrgent = collect($h->priority_alerts ?? [])->contains(fn($a) => ($a['priority'] ?? '') === 'urgent');
                        $clientUpdates = $h->client_updates ?? [];
                        $medConcerns = $h->medication_concerns ?? [];
                        $alerts = $h->priority_alerts ?? [];
                        $pill = 'display:inline-block;padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;line-height:1.6;';
                        $statusStyle = $h->status === 'acknowledged' ? 'background:#d1fae5;color:#047857;' : ($h->status === 'submitted' ? 'background:#dbeafe;color:#1d4ed8;' : 'background:#f3f4f6;color:#6b7280;');
                        $canEdit = $h->canBeEditedBy(Auth::user());
                    @endphp
                    <div style="background:#fff; border:1px solid {{ $hasUrgent ? '#fecaca' : '#e5e7eb' }}; border-radius:12px; box-shadow:0 1px 2px rgba(0,0,0,.04); margin-bottom:16px; overflow:hidden;">
                        {{-- collapsed summary (click to expand) --}}
                        <div style="cursor:pointer; padding:16px 18px; display:flex; align-items:center; gap:12px;" data-toggle="collapse" data-target="#ho-body-{{ $h->id }}">
                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:5px;">
                                    <span style="font-weight:700; color:#111827; font-size:15px;">{{ $h->handover_date->format('Y-m-d') }} at {{ \Carbon\Carbon::parse($h->handover_time)->format('H:i') }}</span>
                                    <span style="{{ $pill }}{{ $statusStyle }}">{{ $h->status }}</span>
                                    @if($hasUrgent)<span style="{{ $pill }}background:#fee2e2;color:#b91c1c;">Urgent Alerts</span>@endif
                                </div>
                                <div style="color:#4b5563; font-size:13px;">{{ $h->location ?: '—' }} · {{ $h->from_carer_name ?: '?' }} &rarr; {{ $h->to_carer_name ?: '?' }}</div>
                                <div style="color:#9ca3af; font-size:12px; margin-top:2px;">{{ count($clientUpdates) }} client updates · {{ count($medConcerns) }} medication concerns</div>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                @if($canEdit)
                                    <button type="button" class="edit-handover-btn" style="background:#fff;border:1px solid #d1d5db;color:#374151;border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;" data-id="{{ $h->id }}">
                                        <i class="fa fa-pencil"></i> Edit
                                    </button>
                                @endif
                                @if($h->status === 'acknowledged')
                                    <span style="color:#047857;font-weight:600;font-size:13px;white-space:nowrap;" title="Acknowledged by {{ $h->acknowledgedByUser->name ?? '' }}">
                                        <i class="fa fa-check-circle"></i> Acknowledged
                                    </span>
                                @elseif($h->status === 'submitted')
                                    <button type="button" class="acknowledge-btn" style="background:#0d9488;color:#fff;border:none;border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;" data-id="{{ $h->id }}" data-url="{{ route('medication.shift-handover.acknowledge', $h->id) }}">
                                        <i class="fa fa-square-o"></i> Acknowledge
                                    </button>
                                @endif
                                <i class="fa fa-chevron-down" style="color:#9ca3af;"></i>
                            </div>
                        </div>
                        {{-- expanded detail --}}
                        <div id="ho-body-{{ $h->id }}" class="collapse" style="border-top:1px solid #f0f0f0; padding:16px 18px;">
                            @if($h->general_notes)
                                <p style="font-weight:600;color:#374151;margin:0 0 6px;">General Notes</p>
                                <div style="background:#f9fafb;padding:12px;border-radius:8px;margin-bottom:16px;white-space:pre-wrap;color:#4b5563;">{{ $h->general_notes }}</div>
                            @endif
                            @if(count($alerts) > 0)
                                <p style="font-weight:600;color:#b91c1c;margin:0 0 8px;"><i class="fa fa-exclamation-circle"></i> Priority Alerts</p>
                                <div style="margin-bottom:16px;">
                                    @foreach($alerts as $a)
                                        <div style="display:flex;align-items:center;gap:8px;background:#fef2f2;padding:9px 12px;border-radius:8px;margin-bottom:6px;">
                                            <span style="{{ $pill }}{{ ($a['priority'] ?? '')==='urgent' ? 'background:#fecaca;color:#991b1b;' : 'background:#fed7aa;color:#9a3412;' }}">{{ $a['priority'] ?? '—' }}</span>
                                            <span style="color:#374151;font-size:14px;">{{ $a['alert'] ?? '' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if(count($clientUpdates) > 0)
                                <p style="font-weight:600;color:#374151;margin:0 0 8px;">Client Updates</p>
                                <div style="margin-bottom:16px;">
                                    @foreach($clientUpdates as $u)
                                        @php
                                            $prio = $u['priority'] ?? '';
                                            $prioStyle = $prio === 'urgent' ? 'background:#fee2e2;color:#b91c1c;' : ($prio === 'high' ? 'background:#fed7aa;color:#9a3412;' : ($prio === 'medium' ? 'background:#fef9c3;color:#854d0e;' : 'background:#f3f4f6;color:#6b7280;'));
                                            $isUrgentUpdate = in_array($prio, ['urgent','high'], true);
                                        @endphp
                                        <div style="background:{{ $isUrgentUpdate ? '#fef2f2' : '#eff6ff' }};padding:12px;border-radius:8px;margin-bottom:8px;">
                                            <p style="margin:0 0 2px;font-weight:600;color:#1e3a8a;">{{ $u['client_name'] ?? '—' }} @if($prio)<span style="{{ $pill }}{{ $prioStyle }}font-size:10px;">{{ $prio }}</span>@endif</p>
                                            <p style="margin:0;color:#1d4ed8;font-size:14px;">{{ $u['update'] ?? '' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if(count($medConcerns) > 0)
                                <p style="font-weight:600;color:#9a3412;margin:0 0 8px;">Medication Concerns</p>
                                <div>
                                    @foreach($medConcerns as $c)
                                        <div style="background:#fff7ed;padding:12px;border-radius:8px;margin-bottom:8px;">
                                            <p style="margin:0 0 2px;font-weight:600;color:#9a3412;">{{ $c['client_name'] ?? '—' }} @if(!empty($c['action_required']))<span style="{{ $pill }}background:#fed7aa;color:#9a3412;font-size:10px;">Action Required</span>@endif</p>
                                            <p style="margin:0;color:#c2410c;font-size:14px;">{{ $c['concern'] ?? '' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <div style="color:#9ca3af;font-size:11px;margin-top:10px;">
                                Created by {{ $h->createdByUser->name ?? '—' }} on {{ $h->created_at->format('Y-m-d H:i') }}
                                @if($h->status === 'acknowledged' && $h->acknowledgedByUser)
                                    · Acknowledged by {{ $h->acknowledgedByUser->name }} on {{ $h->acknowledged_at->format('Y-m-d H:i') }}
                                @endif
                            </div>
                            @if(!empty($h->edit_log))
                                <div style="margin-top:10px; border-top:1px dashed #e5e7eb; padding-top:8px;">
                                    <p style="font-weight:600; color:#6b7280; font-size:12px; margin:0 0 6px;"><i class="fa fa-history"></i> Edit history</p>
                                    @foreach(array_reverse($h->edit_log) as $log)
                                        <div style="font-size:11px; color:#9ca3af; margin-bottom:5px;">
                                            <strong style="color:#6b7280;">{{ $log['user_name'] ?? '—' }}</strong> · {{ $log['at'] ?? '' }}
                                            @if(!empty($log['changes']))
                                                <ul style="margin:2px 0 0 16px; padding:0;">@foreach($log['changes'] as $c)<li>{{ $c }}</li>@endforeach</ul>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif

            </div>{{-- /max-width wrapper --}}

    </div>
</main>

{{-- New Handover Modal --}}
<div class="modal fade" id="shiftHandoverModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <form id="handoverForm" method="POST" action="{{ route('medication.shift-handover.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background:#e6f4f3;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="handoverModalTitle">New Shift Handover</h4>
                </div>
                <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="row">
                        <div class="col-md-6"><div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Main Building">
                        </div></div>
                        <div class="col-md-6"><div class="form-group">
                            <label>Date *</label>
                            <input type="date" name="handover_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div></div>
                        <div class="col-md-6"><div class="form-group">
                            <label>Time *</label>
                            <input type="time" name="handover_time" class="form-control" value="{{ now()->format('H:i') }}" required>
                        </div></div>
                        <div class="col-md-6"></div>
                        <div class="col-md-6"><div class="form-group">
                            <label>From (Outgoing Carer)</label>
                            <input type="text" name="from_carer_name" class="form-control" value="{{ Auth::user()->name }}">
                        </div></div>
                        <div class="col-md-6"><div class="form-group">
                            <label>To (Incoming Carer)</label>
                            <input type="text" name="to_carer_name" class="form-control">
                        </div></div>
                    </div>
                    <div class="form-group">
                        <label>General Notes</label>
                        <textarea name="general_notes" class="form-control" rows="3" placeholder="Summary of the shift..."></textarea>
                    </div>

                    {{-- Client Updates --}}
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong>Client Updates</strong></div>
                        <div class="panel-body">
                            <div id="client-updates-list"></div>
                            <div class="row">
                                <div class="col-md-4"><select id="cu-client" class="form-control">
                                    <option value="">Resident...</option>
                                    @foreach($serviceUsers as $su)<option value="{{ $su->id }}" data-name="{{ $su->name }}">{{ $su->name }}</option>@endforeach
                                </select></div>
                                <div class="col-md-3"><select id="cu-priority" class="form-control">
                                    <option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option>
                                </select></div>
                                <div class="col-md-4"><input type="text" id="cu-update" class="form-control" placeholder="Update..."></div>
                                <div class="col-md-1"><button type="button" class="btn btn-default" id="cu-add">Add</button></div>
                            </div>
                        </div>
                    </div>

                    {{-- Medication Concerns --}}
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong style="color:#e8881a;">Medication Concerns</strong></div>
                        <div class="panel-body">
                            <div id="med-concerns-list"></div>
                            <div class="row">
                                <div class="col-md-4"><select id="mc-client" class="form-control">
                                    <option value="">Resident...</option>
                                    @foreach($serviceUsers as $su)<option value="{{ $su->id }}" data-name="{{ $su->name }}">{{ $su->name }}</option>@endforeach
                                </select></div>
                                <div class="col-md-5"><input type="text" id="mc-concern" class="form-control" placeholder="Concern..."></div>
                                <div class="col-md-2"><label style="font-weight:normal; margin-top:8px;"><input type="checkbox" id="mc-action"> Action req'd</label></div>
                                <div class="col-md-1"><button type="button" class="btn btn-default" id="mc-add">Add</button></div>
                            </div>
                        </div>
                    </div>

                    {{-- Priority Alerts --}}
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong style="color:#d9534f;">Priority Alerts</strong></div>
                        <div class="panel-body">
                            <div id="alerts-list"></div>
                            <div class="row">
                                <div class="col-md-3"><select id="al-priority" class="form-control">
                                    <option value="medium">Medium</option><option value="high">High</option><option value="urgent">Urgent</option>
                                </select></div>
                                <div class="col-md-8"><input type="text" id="al-alert" class="form-control" placeholder="Alert..."></div>
                                <div class="col-md-1"><button type="button" class="btn btn-default" id="al-add">Add</button></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit_action" value="draft" class="btn btn-default">Save as Draft</button>
                    <button type="submit" name="submit_action" value="submitted" class="btn btn-success">Submit Handover</button>
                </div>
            </div>
        </form>
    </div>
</div>

@php
    $handoversJs = $handovers->mapWithKeys(function ($h) {
        return [$h->id => [
            'location'            => $h->location,
            'handover_date'       => $h->handover_date->format('Y-m-d'),
            'handover_time'       => \Carbon\Carbon::parse($h->handover_time)->format('H:i'),
            'from_carer_name'     => $h->from_carer_name,
            'to_carer_name'       => $h->to_carer_name,
            'general_notes'       => $h->general_notes,
            'client_updates'      => $h->client_updates ?? [],
            'medication_concerns' => $h->medication_concerns ?? [],
            'priority_alerts'     => $h->priority_alerts ?? [],
        ]];
    });
@endphp
<script>
    window.HANDOVERS = @json($handoversJs);
    window.SH_STORE_URL = '{{ route('medication.shift-handover.store') }}';
    window.SH_UPDATE_BASE = '{{ url('/medication/shift-handover') }}';
    window.SH_AUTH_NAME = @json(Auth::user()->name);
</script>
<script>
$(function() {
    var cuIdx = 0, mcIdx = 0, alIdx = 0;

    function escapeHtml(s) { return $('<div/>').text(s == null ? '' : s).html(); }

    // ---- reusable row builders (used by both the Add buttons and edit pre-fill) ----
    function addClientUpdate(d) {
        d = d || {};
        var priority = d.priority || 'low';
        var i = cuIdx++;
        $('#client-updates-list').append(
            '<div class="row" style="background:#eaf3fb; padding:6px; border-radius:4px; margin-bottom:6px;">' +
                '<div class="col-md-11"><strong>' + escapeHtml(d.client_name || '—') + '</strong> <span class="label label-info" style="font-size:10px;">' + escapeHtml(priority) + '</span> ' + escapeHtml(d.update) + '</div>' +
                '<div class="col-md-1"><button type="button" class="btn btn-xs btn-link remove-row">&times;</button></div>' +
                '<input type="hidden" name="client_updates[' + i + '][client_id]" value="' + escapeHtml(d.client_id) + '">' +
                '<input type="hidden" name="client_updates[' + i + '][client_name]" value="' + escapeHtml(d.client_name) + '">' +
                '<input type="hidden" name="client_updates[' + i + '][update]" value="' + escapeHtml(d.update) + '">' +
                '<input type="hidden" name="client_updates[' + i + '][priority]" value="' + escapeHtml(priority) + '">' +
            '</div>'
        );
    }

    function addMedConcern(d) {
        d = d || {};
        var action = d.action_required ? 1 : 0;
        var i = mcIdx++;
        $('#med-concerns-list').append(
            '<div class="row" style="background:#fef5e7; padding:6px; border-radius:4px; margin-bottom:6px;">' +
                '<div class="col-md-11"><strong>' + escapeHtml(d.client_name || '—') + '</strong> ' + escapeHtml(d.concern) + (action ? ' <span class="label label-warning" style="font-size:10px;">Action Required</span>' : '') + '</div>' +
                '<div class="col-md-1"><button type="button" class="btn btn-xs btn-link remove-row">&times;</button></div>' +
                '<input type="hidden" name="medication_concerns[' + i + '][client_id]" value="' + escapeHtml(d.client_id) + '">' +
                '<input type="hidden" name="medication_concerns[' + i + '][client_name]" value="' + escapeHtml(d.client_name) + '">' +
                '<input type="hidden" name="medication_concerns[' + i + '][concern]" value="' + escapeHtml(d.concern) + '">' +
                '<input type="hidden" name="medication_concerns[' + i + '][action_required]" value="' + action + '">' +
            '</div>'
        );
    }

    function addAlert(d) {
        d = d || {};
        var priority = d.priority || 'medium';
        var i = alIdx++;
        $('#alerts-list').append(
            '<div class="row" style="background:#fdf2f2; padding:6px; border-radius:4px; margin-bottom:6px;">' +
                '<div class="col-md-11"><span class="label label-' + (priority === 'urgent' ? 'danger' : 'warning') + '" style="font-size:10px;">' + escapeHtml(priority) + '</span> ' + escapeHtml(d.alert) + '</div>' +
                '<div class="col-md-1"><button type="button" class="btn btn-xs btn-link remove-row">&times;</button></div>' +
                '<input type="hidden" name="priority_alerts[' + i + '][priority]" value="' + escapeHtml(priority) + '">' +
                '<input type="hidden" name="priority_alerts[' + i + '][alert]" value="' + escapeHtml(d.alert) + '">' +
            '</div>'
        );
    }

    // ---- Add buttons ----
    $('#cu-add').on('click', function() {
        var $sel = $('#cu-client'), update = $('#cu-update').val().trim();
        if (!update) return;
        addClientUpdate({ client_id: $sel.val(), client_name: $sel.find(':selected').data('name') || '', update: update, priority: $('#cu-priority').val() });
        $('#cu-update').val(''); $('#cu-client').val(''); $('#cu-priority').val('low');
    });

    $('#mc-add').on('click', function() {
        var $sel = $('#mc-client'), concern = $('#mc-concern').val().trim();
        if (!concern) return;
        addMedConcern({ client_id: $sel.val(), client_name: $sel.find(':selected').data('name') || '', concern: concern, action_required: $('#mc-action').is(':checked') });
        $('#mc-concern').val(''); $('#mc-client').val(''); $('#mc-action').prop('checked', false);
    });

    $('#al-add').on('click', function() {
        var alert = $('#al-alert').val().trim();
        if (!alert) return;
        addAlert({ alert: alert, priority: $('#al-priority').val() });
        $('#al-alert').val(''); $('#al-priority').val('medium');
    });

    $(document).on('click', '.remove-row', function() { $(this).closest('.row').remove(); });

    // ---- create / edit mode handling ----
    function clearLists() {
        $('#client-updates-list, #med-concerns-list, #alerts-list').empty();
        cuIdx = 0; mcIdx = 0; alIdx = 0;
    }

    function f(name) { return $('#handoverForm [name="' + name + '"]'); }

    $('#newHandoverBtn').on('click', function() {
        var now = new Date();
        var date = now.getFullYear() + '-' + ('0' + (now.getMonth() + 1)).slice(-2) + '-' + ('0' + now.getDate()).slice(-2);
        var time = ('0' + now.getHours()).slice(-2) + ':' + ('0' + now.getMinutes()).slice(-2);
        $('#handoverForm').attr('action', window.SH_STORE_URL);
        $('#handoverModalTitle').text('New Shift Handover');
        clearLists();
        f('location').val(''); f('handover_date').val(date); f('handover_time').val(time);
        f('from_carer_name').val(window.SH_AUTH_NAME); f('to_carer_name').val(''); f('general_notes').val('');
    });

    $('.edit-handover-btn').on('click', function(e) {
        e.preventDefault(); e.stopPropagation();
        var id = $(this).data('id');
        var d = (window.HANDOVERS || {})[id];
        if (!d) { alert('Could not load this handover to edit.'); return; }
        $('#handoverForm').attr('action', window.SH_UPDATE_BASE + '/' + id + '/update');
        $('#handoverModalTitle').text('Edit Shift Handover');
        clearLists();
        f('location').val(d.location || '');
        f('handover_date').val(d.handover_date || '');
        f('handover_time').val(d.handover_time || '');
        f('from_carer_name').val(d.from_carer_name || '');
        f('to_carer_name').val(d.to_carer_name || '');
        f('general_notes').val(d.general_notes || '');
        (d.client_updates || []).forEach(addClientUpdate);
        (d.medication_concerns || []).forEach(addMedConcern);
        (d.priority_alerts || []).forEach(addAlert);
        $('#shiftHandoverModal').modal('show');
    });

    // ---- acknowledge ----
    $('.acknowledge-btn').on('click', function(e) {
        e.preventDefault(); e.stopPropagation();
        var $btn = $(this), url = $btn.data('url');
        if (!confirm('Acknowledge this handover? Once acknowledged, carers can no longer edit it.')) return;
        $btn.prop('disabled', true);
        $.ajax({
            url: url, method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function() { location.reload(); },
            error: function(xhr) { alert('Failed to acknowledge: ' + (xhr.responseJSON?.message || xhr.statusText)); $btn.prop('disabled', false); }
        });
    });
});
</script>

@endsection
