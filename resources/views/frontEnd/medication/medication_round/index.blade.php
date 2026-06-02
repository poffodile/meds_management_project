@extends('frontEnd.layouts.master')
@section('title','Medication Round')
@section('content')

@include('frontEnd.roster.common.roster_header')

@php
    // MAR code -> readable label and soft pill colours
    $codeLabel = ['A' => 'Given', 'S' => 'Sleeping', 'R' => 'Refused', 'W' => 'Withheld', 'N' => 'Not available', 'O' => 'Omitted'];
    $codePill = [
        'A' => 'background:#d1fae5;color:#047857;',
        'S' => 'background:#f3f4f6;color:#6b7280;',
        'R' => 'background:#fee2e2;color:#b91c1c;',
        'W' => 'background:#fed7aa;color:#9a3412;',
        'N' => 'background:#fed7aa;color:#9a3412;',
        'O' => 'background:#fde68a;color:#92400e;',
    ];
    $pill = 'display:inline-block;padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;line-height:1.6;';
@endphp

<main class="page-content">
    <div class="container-fluid">

        <div style="max-width:1000px; margin:0 auto;">

            {{-- Header --}}
            <div class="m-t-30" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
                <div>
                    <h2 style="margin:0; font-weight:700; color:#111827; font-size:24px;"><i class="fa fa-medkit" style="color:#0d9488; margin-right:10px;"></i>Medication Round</h2>
                    <p style="margin:6px 0 0; color:#6b7280;">Administer and record medications · pick a round below</p>
                </div>
                <form method="GET" action="{{ route('medication.medication-round.index') }}" style="margin:0;">
                    <label style="font-weight:600; color:#6b7280; margin-right:6px;"><i class="fa fa-calendar"></i> Date</label>
                    <input type="date" name="date" value="{{ $date }}" class="form-control" style="display:inline-block; width:auto;" onchange="this.form.submit()">
                </form>
            </div>

            {{-- Round tabs --}}
            <ul class="nav nav-tabs" role="tablist" style="border-bottom:1px solid #e5e7eb;">
                @foreach($rounds as $key => $cfg)
                    @php
                        $rows = collect($grid[$key])->flatMap(fn($r) => $r['rows']);
                        $tabTotal = $rows->count();
                        $tabDone = $rows->filter(fn($x) => $x['admin'])->count();
                    @endphp
                    <li role="presentation" class="{{ $key === $currentRound ? 'active' : '' }}">
                        <a href="#round-{{ $key }}" aria-controls="round-{{ $key }}" role="tab" data-toggle="tab" style="font-weight:600;">
                            <i class="fa {{ $cfg['icon'] }}"></i> {{ $cfg['label'] }}
                            <span style="color:#9ca3af; font-size:11px; font-weight:400;">{{ $cfg['window'] }}</span>
                            <span style="{{ $pill }}{{ $tabDone === $tabTotal && $tabTotal > 0 ? 'background:#d1fae5;color:#047857;' : 'background:#f3f4f6;color:#6b7280;' }} margin-left:4px;">{{ $tabDone }}/{{ $tabTotal }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content" style="padding-top:20px;">
                @foreach($rounds as $key => $cfg)
                    @php
                        $residents = $grid[$key];
                        $allRows = collect($residents)->flatMap(fn($r) => $r['rows']);
                        $total = $allRows->count();
                        $done = $allRows->filter(fn($x) => $x['admin'])->count();
                        $pending = $total - $done;
                        $pct = $total > 0 ? round($done / $total * 100) : 0;
                    @endphp
                    <div role="tabpanel" class="tab-pane {{ $key === $currentRound ? 'active' : '' }}" id="round-{{ $key }}">

                        {{-- Stat tiles --}}
                        <div style="display:flex; gap:14px; flex-wrap:wrap; margin-bottom:20px;">
                            @php
                                $tiles = [
                                    ['Total Doses', $total, '#2563eb', 'all'],
                                    ['Administered', $done, '#059669', 'done'],
                                    ['Pending', $pending, '#ea580c', 'pending'],
                                    ['Complete', $pct . '%', '#111827', 'all'],
                                ];
                            @endphp
                            @foreach($tiles as $tile)
                                <div class="stat-tile" data-filter="{{ $tile[3] }}" title="Click to filter this round" style="flex:1; min-width:140px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px; text-align:center; box-shadow:0 1px 2px rgba(0,0,0,.04); cursor:pointer;">
                                    <div style="font-size:26px; font-weight:700; color:{{ $tile[2] }};">{{ $tile[1] }}</div>
                                    <div style="font-size:12px; color:#6b7280;">{{ $tile[0] }}</div>
                                </div>
                            @endforeach
                        </div>

                        @if(empty($residents))
                            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; text-align:center; padding:50px 20px; color:#9ca3af;">
                                <i class="fa fa-medkit" style="font-size:42px; opacity:.2;"></i>
                                <p style="margin-top:14px; font-weight:600; color:#6b7280;">No medications scheduled for this round</p>
                                <p style="font-size:13px;">Medications appear here based on their administration times</p>
                            </div>
                        @else
                            @foreach($residents as $resident)
                                @php
                                    $rRows = collect($resident['rows']);
                                    $rTotal = $rRows->count();
                                    $rDone = $rRows->filter(fn($x) => $x['admin'])->count();
                                    $rPct = $rTotal > 0 ? round($rDone / $rTotal * 100) : 0;
                                    $initial = strtoupper(mb_substr($resident['name'], 0, 1));
                                @endphp
                                <div class="resident-card" style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 1px 2px rgba(0,0,0,.04); margin-bottom:14px; overflow:hidden;">
                                    <div style="cursor:pointer; padding:14px 16px; display:flex; align-items:center; gap:12px;" data-toggle="collapse" data-target="#res-{{ $key }}-{{ $resident['client_id'] }}">
                                        <div style="width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#38bdf8,#2563eb); color:#fff; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;">{{ $initial }}</div>
                                        <div style="flex:1; min-width:0;">
                                            <div style="font-weight:700; color:#111827;">{{ $resident['name'] }}</div>
                                            <div style="color:#6b7280; font-size:13px;">{{ $rTotal }} medication{{ $rTotal === 1 ? '' : 's' }} · {{ $rDone }}/{{ $rTotal }} done</div>
                                        </div>
                                        <div style="width:90px; height:8px; background:#e5e7eb; border-radius:9999px; overflow:hidden;">
                                            <div style="height:100%; width:{{ $rPct }}%; background:#10b981; border-radius:9999px;"></div>
                                        </div>
                                        <span style="{{ $pill }}{{ $rDone === $rTotal ? 'background:#d1fae5;color:#047857;' : 'background:#ffedd5;color:#9a3412;' }}">{{ $rDone === $rTotal ? 'Complete' : 'Pending' }}</span>
                                        <i class="fa fa-chevron-down" style="color:#9ca3af;"></i>
                                    </div>

                                    <div id="res-{{ $key }}-{{ $resident['client_id'] }}" class="collapse in" style="border-top:1px solid #f0f0f0;">
                                        @foreach($resident['rows'] as $row)
                                            @php
                                                $sheet = $row['sheet'];
                                                $slot  = $row['slot'];
                                                $admin = $row['admin'];
                                                $stockLow = !is_null($sheet->stock_level) && !is_null($sheet->reorder_level) && $sheet->stock_level <= $sheet->reorder_level;
                                                $stockOut = !is_null($sheet->stock_level) && $sheet->stock_level <= 0;
                                            @endphp
                                            <div class="med-row" data-state="{{ $admin ? 'done' : 'pending' }}" style="padding:12px 16px; {{ !$loop->first ? 'border-top:1px solid #f3f4f6;' : '' }}">
                                                <div style="display:flex; align-items:flex-start; gap:12px;">
                                                    <span style="{{ $pill }}background:#f3f4f6;color:#374151;">{{ $slot ?: 'Any time' }}</span>
                                                    <div style="flex:1; min-width:0;">
                                                        <div>
                                                            <strong style="color:#111827;">{{ $sheet->medication_name }}</strong>
                                                            @if($sheet->is_controlled)<span style="{{ $pill }}background:#fee2e2;color:#b91c1c;font-size:10px;">CD</span>@endif
                                                            @if($sheet->as_required)<span style="{{ $pill }}background:#ede9fe;color:#6d28d9;font-size:10px;">PRN</span>@endif
                                                            @if($stockOut)<span style="{{ $pill }}background:#fee2e2;color:#991b1b;font-size:10px;">OUT OF STOCK</span>
                                                            @elseif($stockLow)<span style="{{ $pill }}background:#fef3c7;color:#92400e;font-size:10px;">LOW STOCK</span>@endif
                                                        </div>
                                                        <div style="color:#6b7280; font-size:12px;">{{ $sheet->dose ?: $sheet->dosage }}@if($sheet->route) · {{ $sheet->route }}@endif @if($sheet->frequency) · {{ $sheet->frequency }}@endif</div>
                                                        @if($sheet->as_required && $sheet->prn_details)
                                                            <div style="font-size:12px; color:#7c3aed;">PRN: {{ $sheet->prn_details }}</div>
                                                        @endif
                                                        @if(!is_null($sheet->stock_level))
                                                            <div style="color:#9ca3af; font-size:11px;">Stock: {{ $sheet->stock_level }}</div>
                                                        @endif
                                                    </div>
                                                    <div style="text-align:right; flex-shrink:0;">
                                                        @if($admin)
                                                            <span style="{{ $pill }}{{ $codePill[$admin->code] ?? 'background:#f3f4f6;color:#6b7280;' }}">{{ $codeLabel[$admin->code] ?? 'Recorded' }}</span>
                                                            <div style="color:#9ca3af; font-size:11px;">{{ $admin->administeredByUser->name ?? '' }}</div>
                                                            <div style="margin-top:2px;">
                                                                <a href="#" class="adm-detail-toggle" data-detail="adm-{{ $admin->id }}" style="color:#6b7280; font-size:12px;"><i class="fa fa-sticky-note-o"></i> Details</a>
                                                                <span style="color:#d1d5db;">·</span>
                                                                <button type="button" class="record-btn" style="background:none; border:none; color:#2563eb; font-size:12px; padding:2px 0; cursor:pointer;"
                                                                    data-sheet-id="{{ $sheet->id }}" data-slot="{{ $slot }}" data-med="{{ $sheet->medication_name }}" data-resident="{{ $resident['name'] }}" data-dose="{{ $sheet->dose ?: $sheet->dosage }}">Edit</button>
                                                            </div>
                                                        @else
                                                            <button type="button" class="record-btn" style="background:#2563eb; color:#fff; border:none; border-radius:8px; padding:6px 14px; font-size:13px; font-weight:600; {{ $stockOut ? 'opacity:.5; cursor:not-allowed;' : '' }}"
                                                                data-sheet-id="{{ $sheet->id }}" data-slot="{{ $slot }}" data-med="{{ $sheet->medication_name }}" data-resident="{{ $resident['name'] }}" data-dose="{{ $sheet->dose ?: $sheet->dosage }}" {{ $stockOut ? 'disabled' : '' }}>Record</button>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if($admin)
                                                    <div id="adm-{{ $admin->id }}" class="adm-detail" style="display:none; margin-top:10px; background:#f9fafb; border:1px solid #eef2f7; border-radius:8px; padding:10px; font-size:12px; color:#4b5563;">
                                                        @if($admin->dose_given)<div><strong>Dose given:</strong> {{ $admin->dose_given }}</div>@endif
                                                        <div><strong>Recorded by:</strong> {{ $admin->administeredByUser->name ?? '—' }}@if($admin->updated_at) at {{ $admin->updated_at->format('d M Y H:i') }}@endif</div>
                                                        @if($admin->witnessed_by)<div><strong>Witnessed by:</strong> {{ $admin->witnessed_by }}</div>@endif
                                                        @if($admin->notes)<div><strong>Notes:</strong> {{ $admin->notes }}</div>@else<div style="color:#9ca3af;">No notes recorded</div>@endif
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>

        </div>{{-- /max-width wrapper --}}

    </div>
</main>

{{-- Record Administration Modal --}}
<div class="modal fade" id="recordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#e6f4f3;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Record Administration</h4>
            </div>
            <div class="modal-body">
                <div style="background:#f5f5f5; padding:10px; border-radius:4px; margin-bottom:14px;">
                    <strong id="rec-med"></strong>
                    <div class="text-muted" style="font-size:13px;">Resident: <strong id="rec-resident"></strong> · Time slot: <span id="rec-slot-label"></span></div>
                </div>

                <input type="hidden" id="rec-sheet-id">
                <input type="hidden" id="rec-slot">
                <input type="hidden" id="rec-code">

                <div class="form-group">
                    <label>Outcome *</label>
                    <div class="btn-group btn-group-justified" role="group">
                        <div class="btn-group" role="group"><button type="button" class="btn btn-default code-btn" data-code="A">Given</button></div>
                        <div class="btn-group" role="group"><button type="button" class="btn btn-default code-btn" data-code="R">Refused</button></div>
                        <div class="btn-group" role="group"><button type="button" class="btn btn-default code-btn" data-code="O">Omitted</button></div>
                    </div>
                    <div style="margin-top:6px;">
                        <small class="text-muted">Other codes:</small>
                        <button type="button" class="btn btn-xs btn-default code-btn" data-code="S">Sleeping</button>
                        <button type="button" class="btn btn-xs btn-default code-btn" data-code="W">Withheld</button>
                        <button type="button" class="btn btn-xs btn-default code-btn" data-code="N">Not available</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Dose given</label>
                    <input type="text" id="rec-dose" class="form-control" placeholder="e.g. 1 tablet">
                </div>

                <div class="form-group">
                    <label>Witnessed by <span class="text-muted">(required for controlled drugs)</span></label>
                    <input type="text" id="rec-witness" class="form-control" placeholder="Witness full name">
                </div>

                <div class="form-group">
                    <label>Notes / reason</label>
                    <textarea id="rec-notes" class="form-control" rows="2" placeholder="e.g. reason for refusal / omission, observations..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="rec-save">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    var CURRENT_DATE = '{{ $date }}';

    function nowHHMM() {
        var d = new Date();
        return ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
    }

    // Open modal, prefill from the clicked row.
    $(document).on('click', '.record-btn', function () {
        var $b = $(this);
        $('#rec-sheet-id').val($b.data('sheet-id'));
        $('#rec-slot').val($b.data('slot') || '');
        $('#rec-med').text($b.data('med'));
        $('#rec-resident').text($b.data('resident'));
        $('#rec-slot-label').text($b.data('slot') || 'Any time');
        $('#rec-dose').val($b.data('dose') || '');
        $('#rec-witness').val('');
        $('#rec-notes').val('');
        $('#rec-code').val('');
        $('.code-btn').removeClass('btn-success btn-danger btn-warning btn-info active').addClass('btn-default');
        $('#recordModal').modal('show');
    });

    // Toggle the saved administration details (dose, witness, notes, who/when).
    $(document).on('click', '.adm-detail-toggle', function (e) {
        e.preventDefault();
        $('#' + $(this).data('detail')).slideToggle(120);
    });

    // Stat tiles act as filters within their round (all / done / pending).
    $(document).on('click', '.stat-tile', function () {
        var $pane = $(this).closest('.tab-pane');
        var filter = $(this).data('filter');
        $pane.find('.stat-tile').css({ 'outline': 'none', 'border-color': '#e5e7eb' });
        $(this).css({ 'outline': '2px solid #0d9488', 'border-color': '#0d9488' });

        function matches(row) { return filter === 'all' || $(row).data('state') === filter; }

        $pane.find('.med-row').each(function () {
            $(this).toggle(matches(this));
        });
        // Decide each card from its rows' STATE (not current visibility), so cards can re-appear.
        $pane.find('.resident-card').each(function () {
            var hasMatch = $(this).find('.med-row').toArray().some(matches);
            $(this).toggle(hasMatch);
        });
    });

    // Pick an outcome code.
    $(document).on('click', '.code-btn', function () {
        var code = $(this).data('code');
        $('#rec-code').val(code);
        $('.code-btn').removeClass('btn-success btn-danger btn-warning btn-info active').addClass('btn-default');
        var cls = (code === 'A' || code === 'S') ? 'btn-success' : (code === 'R' ? 'btn-danger' : 'btn-warning');
        $(this).removeClass('btn-default').addClass(cls + ' active');
    });

    // Save -> existing MAR administer endpoint.
    $('#rec-save').on('click', function () {
        var code = $('#rec-code').val();
        if (!code) { alert('Please choose an outcome.'); return; }
        var slot = $('#rec-slot').val() || nowHHMM();

        var $btn = $(this).prop('disabled', true);
        $.ajax({
            url: '{{ route('medication.medication-round.record') }}',
            method: 'POST',
            data: {
                mar_sheet_id: $('#rec-sheet-id').val(),
                date: CURRENT_DATE,
                time_slot: slot,
                code: code,
                dose_given: $('#rec-dose').val(),
                witnessed_by: $('#rec-witness').val(),
                notes: $('#rec-notes').val()
            },
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function () {
                window.location = '{{ route('medication.medication-round.index') }}?date=' + encodeURIComponent(CURRENT_DATE);
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : xhr.statusText;
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).map(function (e) { return e[0]; }).join('\n');
                }
                alert('Could not save: ' + msg);
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>

@endsection
