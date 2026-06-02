@extends('frontEnd.layouts.master')
@section('title','Missed Doses Review')
@section('content')

@include('frontEnd.roster.common.roster_header')

@php
    $pill = 'display:inline-block;padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;line-height:1.6;';
    $codeLabel = ['R' => 'Refused', 'O' => 'Omitted', 'W' => 'Withheld', 'N' => 'Not available'];
    $actionLabels = [
        'no_action' => 'No action required', 'dose_given_late' => 'Dose given late', 'gp_notified' => 'GP notified',
        'nok_notified' => 'Next of kin notified', 'dose_withheld' => 'Dose withheld (intentional)',
        'medication_changed' => 'Medication changed', 'hospitalisation' => 'Hospitalisation', 'other' => 'Other',
    ];
@endphp

<main class="page-content">
    <div class="container-fluid">

        <div style="max-width:920px; margin:0 auto;">

            {{-- Header --}}
            <div class="m-t-30" style="margin-bottom:16px;">
                <h2 style="margin:0; font-weight:700; color:#111827; font-size:24px;"><i class="fa fa-exclamation-triangle" style="color:#ea580c; margin-right:10px;"></i>Missed Doses Review</h2>
                <p style="margin:6px 0 0; color:#6b7280;">Review and resolve doses that were missed or not given as planned</p>
            </div>

            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

            {{-- Date navigation + status filter --}}
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:18px;">
                <a href="{{ route('medication.missed-doses.index', ['date' => $prevDate, 'status' => $statusFilter]) }}" class="btn" style="background:#fff;border:1px solid #d1d5db;color:#374151;border-radius:8px;padding:7px 12px;font-weight:600;">&laquo; Prev day</a>
                <form method="GET" action="{{ route('medication.missed-doses.index') }}" style="margin:0; display:flex; gap:8px;">
                    <input type="date" name="date" value="{{ $date }}" class="form-control" style="width:auto;" onchange="this.form.submit()">
                    <select name="status" class="form-control" style="width:auto;" onchange="this.form.submit()">
                        <option value="outstanding" {{ $statusFilter === 'outstanding' ? 'selected' : '' }}>Outstanding</option>
                        <option value="resolved" {{ $statusFilter === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </form>
                <a href="{{ route('medication.missed-doses.index', ['date' => $nextDate, 'status' => $statusFilter]) }}" class="btn" style="background:#fff;border:1px solid #d1d5db;color:#374151;border-radius:8px;padding:7px 12px;font-weight:600;">Next day &raquo;</a>
                @if($date !== $todayDate)<a href="{{ route('medication.missed-doses.index', ['date' => $todayDate, 'status' => $statusFilter]) }}" class="btn" style="background:#0d9488;color:#fff;border:none;border-radius:8px;padding:7px 12px;font-weight:600;">Today</a>@endif
                <span style="color:#6b7280; font-weight:600; margin-left:auto;">{{ \Carbon\Carbon::parse($date)->format('l, j M Y') }}</span>
            </div>

            {{-- Stat tiles --}}
            <div style="display:flex; gap:14px; flex-wrap:wrap; margin-bottom:20px;">
                @php
                    $tiles = [
                        ['Missed (not recorded)', $stats['missed'], '#b45309'],
                        ['Not given', $stats['not_given'], '#b91c1c'],
                        ['Outstanding', $stats['outstanding'], '#ea580c'],
                        ['Resolved', $stats['resolved'], '#047857'],
                    ];
                @endphp
                @foreach($tiles as $t)
                    <div style="flex:1; min-width:150px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px; text-align:center; box-shadow:0 1px 2px rgba(0,0,0,.04);">
                        <div style="font-size:26px; font-weight:700; color:{{ $t[2] }};">{{ $t[1] }}</div>
                        <div style="font-size:12px; color:#6b7280;">{{ $t[0] }}</div>
                    </div>
                @endforeach
            </div>

            {{-- List --}}
            @if(empty($items))
                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; text-align:center; padding:50px 20px; color:#9ca3af;">
                    <i class="fa fa-check-circle" style="font-size:42px; color:#a7f3d0;"></i>
                    <p style="margin-top:14px; font-weight:600; color:#6b7280;">Nothing to review here</p>
                    <p style="font-size:13px;">No {{ $statusFilter === 'resolved' ? 'resolved items' : 'missed or not-given doses' }} for this day.</p>
                </div>
            @else
                @foreach($items as $i)
                    @php
                        $sheet = $i['sheet']; $review = $i['review']; $admin = $i['admin'];
                        $isMissed = $i['kind'] === 'missed';
                        $kindLabel = $isMissed ? 'Missed (not recorded)' : ($codeLabel[$i['code']] ?? 'Not given');
                        $kindStyle = $isMissed ? 'background:#ffedd5;color:#9a3412;' : 'background:#fee2e2;color:#b91c1c;';
                    @endphp
                    <div style="background:#fff; border:1px solid {{ $review ? '#e5e7eb' : '#fed7aa' }}; border-radius:12px; box-shadow:0 1px 2px rgba(0,0,0,.04); padding:14px 16px; margin-bottom:12px;">
                        <div style="display:flex; align-items:flex-start; gap:12px;">
                            <i class="fa {{ $isMissed ? 'fa-clock-o' : 'fa-exclamation-triangle' }}" style="color:{{ $isMissed ? '#ea580c' : '#dc2626' }}; margin-top:3px;"></i>
                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-bottom:3px;">
                                    <span style="{{ $pill }}{{ $kindStyle }}">{{ $kindLabel }}</span>
                                    <span style="{{ $pill }}background:#f3f4f6;color:#374151;">{{ $i['slot'] }}</span>
                                    @if($sheet->is_controlled)<span style="{{ $pill }}background:#fee2e2;color:#b91c1c;">CD</span>@endif
                                    @if($review)<span style="{{ $pill }}background:#d1fae5;color:#047857;">Resolved</span>@endif
                                </div>
                                <div style="font-weight:700; color:#111827;">{{ $i['resident_name'] }}</div>
                                <div style="color:#4b5563; font-size:14px;">{{ $sheet->medication_name }} · {{ $sheet->dose ?: $sheet->dosage }}</div>
                                @if($admin && $admin->notes)<div style="color:#6b7280; font-size:12px;">Recorded note: {{ $admin->notes }}</div>@endif
                                @if($admin && $admin->administeredByUser)<div style="color:#9ca3af; font-size:11px;">Recorded by {{ $admin->administeredByUser->name }}</div>@endif
                                @if($review)
                                    <div style="margin-top:8px; background:#f0fdf4; border:1px solid #dcfce7; border-radius:8px; padding:8px 10px; font-size:12px; color:#166534;">
                                        <strong>Action:</strong> {{ $actionLabels[$review->clinical_action] ?? $review->clinical_action }}
                                        @if($review->notes)<div><strong>Notes:</strong> {{ $review->notes }}</div>@endif
                                        <div style="color:#6b7280;">Reviewed by {{ $review->reviewedByUser->name ?? '—' }} on {{ $review->updated_at->format('d M Y H:i') }}</div>
                                    </div>
                                @endif
                            </div>
                            <div style="flex-shrink:0;">
                                @if(!$review)
                                    <button type="button" class="btn btn-sm resolve-btn" style="background:#059669;color:#fff;border:none;border-radius:8px;padding:6px 14px;font-weight:600;font-size:13px;"
                                        data-sheet-id="{{ $sheet->id }}" data-date="{{ $date }}" data-slot="{{ $i['slot'] }}" data-kind="{{ $i['kind'] }}" data-code="{{ $i['code'] }}"
                                        data-med="{{ $sheet->medication_name }}" data-resident="{{ $i['resident_name'] }}" data-issue="{{ $kindLabel }}"
                                        data-note="{{ $admin->notes ?? '' }}" data-witness="{{ $admin->witnessed_by ?? '' }}" data-recorder="{{ $admin && $admin->administeredByUser ? $admin->administeredByUser->name : '' }}">Resolve</button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

        </div>{{-- /max-width --}}

    </div>
</main>

{{-- Resolve Modal --}}
<div class="modal fade" id="resolveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('medication.missed-doses.resolve') }}">
            @csrf
            <input type="hidden" name="mar_sheet_id" id="rv-sheet-id">
            <input type="hidden" name="review_date" id="rv-date">
            <input type="hidden" name="time_slot" id="rv-slot">
            <input type="hidden" name="dose_kind" id="rv-kind">
            <input type="hidden" name="code" id="rv-code">
            <div class="modal-content">
                <div class="modal-header" style="background:#ecfdf5;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="color:#065f46;">Resolve Dose</h4>
                </div>
                <div class="modal-body">
                    <div style="background:#f5f5f5; padding:10px; border-radius:8px; margin-bottom:14px;">
                        <strong id="rv-med"></strong>
                        <div class="text-muted" style="font-size:13px;">Resident: <strong id="rv-resident"></strong> · <span id="rv-issue"></span> at <span id="rv-slot-label"></span></div>
                        <div id="rv-note-wrap" style="display:none; font-size:13px; margin-top:6px; color:#374151;"><strong>Recorded note:</strong> <span id="rv-note"></span></div>
                        <div id="rv-witness-wrap" style="display:none; font-size:13px; color:#374151;"><strong>Witnessed by:</strong> <span id="rv-witness"></span></div>
                        <div id="rv-recorder-wrap" style="display:none; font-size:12px; color:#6b7280;">Recorded by <span id="rv-recorder"></span></div>
                    </div>
                    <div class="form-group">
                        <label>Clinical action *</label>
                        <select name="clinical_action" class="form-control" required>
                            <option value="no_action">No action required</option>
                            <option value="dose_given_late">Dose given late</option>
                            <option value="gp_notified">GP notified</option>
                            <option value="nok_notified">Next of kin notified</option>
                            <option value="dose_withheld">Dose withheld (intentional)</option>
                            <option value="medication_changed">Medication changed</option>
                            <option value="hospitalisation">Hospitalisation</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes / outcome</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Describe what happened and the action taken..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background:#059669; color:#fff; border:none;">Resolve</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(function () {
    $(document).on('click', '.resolve-btn', function () {
        var $b = $(this);
        $('#rv-sheet-id').val($b.data('sheet-id'));
        $('#rv-date').val($b.data('date'));
        $('#rv-slot').val($b.data('slot'));
        $('#rv-kind').val($b.data('kind'));
        $('#rv-code').val($b.data('code') || '');
        $('#rv-med').text($b.data('med'));
        $('#rv-resident').text($b.data('resident'));
        $('#rv-issue').text($b.data('issue'));
        $('#rv-slot-label').text($b.data('slot'));

        var note = $b.data('note'), witness = $b.data('witness'), recorder = $b.data('recorder');
        if (note) { $('#rv-note').text(note); $('#rv-note-wrap').show(); } else { $('#rv-note-wrap').hide(); }
        if (witness) { $('#rv-witness').text(witness); $('#rv-witness-wrap').show(); } else { $('#rv-witness-wrap').hide(); }
        if (recorder) { $('#rv-recorder').text(recorder); $('#rv-recorder-wrap').show(); } else { $('#rv-recorder-wrap').hide(); }

        $('#resolveModal').modal('show');
    });
});
</script>

@endsection
