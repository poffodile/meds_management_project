@extends('frontEnd.layouts.master')
@section('title','Controlled Drugs Register')
@section('content')

@include('frontEnd.roster.common.roster_header')

@php
    $pill = 'display:inline-block;padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;line-height:1.6;';
    $actionPill = [
        'administered' => 'background:#d1fae5;color:#047857;',
        'received'     => 'background:#dbeafe;color:#1d4ed8;',
        'disposed'     => 'background:#ffedd5;color:#9a3412;',
        'returned'     => 'background:#f3f4f6;color:#374151;',
        'adjustment'   => 'background:#fef9c3;color:#854d0e;',
    ];
    $scheduleLabel = fn($s) => $s ? ('Schedule ' . str_replace('schedule_', '', $s)) : '';
@endphp

<main class="page-content">
    <div class="container-fluid">

        <div style="max-width:1100px; margin:0 auto;">

            {{-- Header --}}
            <div class="m-t-30" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
                <div>
                    <h2 style="margin:0; font-weight:700; color:#111827; font-size:24px;"><i class="fa fa-shield" style="color:#dc2626; margin-right:10px;"></i>Controlled Drugs Register</h2>
                    <p style="margin:6px 0 0; color:#6b7280;">Immutable record of all controlled drug transactions</p>
                </div>
                <button type="button" id="cdAddBtn" class="btn" style="background:#dc2626; color:#fff; border:none; border-radius:8px; padding:10px 18px; font-weight:600;" data-toggle="modal" data-target="#cdModal">
                    <i class="fa fa-plus"></i> Add Entry
                </button>
            </div>

            {{-- Flash / errors --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following:</strong>
                    <ul style="margin:8px 0 0 20px;">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            {{-- Filters --}}
            <form method="GET" action="{{ route('medication.controlled-drugs.index') }}" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px;">
                <input type="text" name="q" value="{{ $filterQ }}" class="form-control" placeholder="Search medications..." style="width:auto; flex:1; min-width:200px; max-width:320px;">
                <select name="client_id" class="form-control" style="width:auto;" onchange="this.form.submit()">
                    <option value="">All residents</option>
                    @foreach($residents as $r)
                        <option value="{{ $r->id }}" {{ (string)$filterClient === (string)$r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-default">Search</button>
                @if($filterQ || $filterClient)
                    <a href="{{ route('medication.controlled-drugs.index') }}" class="btn btn-link">Clear</a>
                @endif
            </form>

            {{-- Register table --}}
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 1px 2px rgba(0,0,0,.04); overflow:hidden;">
                <div style="overflow-x:auto;">
                    <table class="table" style="margin:0; min-width:840px;">
                        <thead>
                            <tr style="background:#f9fafb;">
                                <th style="border:none;">Date / Time</th>
                                <th style="border:none;">Resident</th>
                                <th style="border:none;">Medication</th>
                                <th style="border:none;">Action</th>
                                <th style="border:none;">Dose</th>
                                <th style="border:none;">Balance</th>
                                <th style="border:none;">Witness</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $e)
                                <tr>
                                    <td style="font-size:12px; color:#6b7280;">{{ $e->entry_date->format('Y-m-d') }}<br>{{ \Carbon\Carbon::parse($e->entry_time)->format('H:i') }}</td>
                                    <td style="font-weight:600;">{{ $e->client_name ?: '—' }}</td>
                                    <td>
                                        {{ $e->medication_name }}
                                        @if($e->cd_schedule)<div><span style="{{ $pill }}background:#fee2e2;color:#b91c1c;font-size:10px;">{{ $scheduleLabel($e->cd_schedule) }}</span></div>@endif
                                    </td>
                                    <td><span style="{{ $pill }}{{ $actionPill[$e->action_type] ?? 'background:#f3f4f6;color:#374151;' }}">{{ ucfirst($e->action_type) }}</span></td>
                                    <td style="font-weight:600;">{{ $e->dose_quantity !== null ? rtrim(rtrim(number_format($e->dose_quantity, 2), '0'), '.') : '—' }} {{ $e->unit }}</td>
                                    <td style="font-size:12px;">
                                        <span style="color:#9ca3af;">{{ $e->balance_before !== null ? rtrim(rtrim(number_format($e->balance_before, 2), '0'), '.') : '?' }} &rarr;</span>
                                        <strong style="margin-left:3px;">{{ rtrim(rtrim(number_format($e->balance_after, 2), '0'), '.') }}</strong>
                                    </td>
                                    <td>
                                        @if($e->witness_name)
                                            {{ $e->witness_name }}
                                        @else
                                            <span style="color:#dc2626; font-size:12px; font-weight:600;">No witness</span>
                                        @endif
                                        <div style="color:#9ca3af; font-size:11px;">by {{ $e->createdByUser->name ?? '—' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" style="text-align:center; padding:40px; color:#9ca3af;">No controlled drug entries found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>{{-- /max-width --}}

    </div>
</main>

{{-- Add Entry Modal --}}
<div class="modal fade" id="cdModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('medication.controlled-drugs.store') }}" id="cdForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background:#fef2f2;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="color:#991b1b;">Add CD Register Entry</h4>
                </div>
                <div class="modal-body" style="max-height:72vh; overflow-y:auto;">
                    <div class="form-group">
                        <label>Resident *</label>
                        <select name="client_id" id="cd-client" class="form-control" required>
                            <option value="">Select resident</option>
                            @foreach($residents as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Controlled Drug *</label>
                        <select id="cd-med" class="form-control">
                            <option value="">Select resident first…</option>
                        </select>
                        <input type="text" id="cd-med-other" class="form-control" placeholder="Type the drug name" style="display:none; margin-top:6px;">
                        <input type="hidden" name="medication_name" id="cd-med-name">
                        <input type="hidden" name="mar_sheet_id" id="cd-med-sheet">
                    </div>

                    <div class="row">
                        <div class="col-md-6"><div class="form-group">
                            <label>CD Schedule</label>
                            <select name="cd_schedule" class="form-control">
                                <option value="schedule_2">Schedule 2</option>
                                <option value="schedule_3">Schedule 3</option>
                                <option value="schedule_4">Schedule 4</option>
                                <option value="schedule_5">Schedule 5</option>
                            </select>
                        </div></div>
                        <div class="col-md-6"><div class="form-group">
                            <label>Action *</label>
                            <select name="action_type" id="cd-action" class="form-control" required>
                                <option value="administered">Administered</option>
                                <option value="received">Received from pharmacy</option>
                                <option value="disposed">Disposed</option>
                                <option value="returned">Returned</option>
                                <option value="adjustment">Adjustment</option>
                            </select>
                        </div></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6"><div class="form-group">
                            <label>Date *</label>
                            <input type="date" name="entry_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div></div>
                        <div class="col-md-6"><div class="form-group">
                            <label>Time *</label>
                            <input type="time" name="entry_time" class="form-control" value="{{ now()->format('H:i') }}" required>
                        </div></div>
                    </div>

                    <div class="row">
                        <div class="col-md-4"><div class="form-group">
                            <label>Dose qty</label>
                            <input type="number" step="any" name="dose_quantity" id="cd-dose" class="form-control" placeholder="e.g. 5">
                        </div></div>
                        <div class="col-md-2"><div class="form-group">
                            <label>Unit</label>
                            <input type="text" name="unit" id="cd-unit" class="form-control" value="mg">
                        </div></div>
                        <div class="col-md-3"><div class="form-group">
                            <label>Balance before</label>
                            <input type="number" step="any" name="balance_before" id="cd-bbefore" class="form-control">
                        </div></div>
                        <div class="col-md-3"><div class="form-group">
                            <label>Balance after *</label>
                            <input type="number" step="any" name="balance_after" id="cd-bafter" class="form-control" required>
                        </div></div>
                    </div>

                    <div class="form-group">
                        <label>Witness name * <span class="text-muted">(second nurse or senior carer)</span></label>
                        <input type="text" name="witness_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background:#dc2626; color:#fff; border:none;">Save Entry</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    window.CD_MEDS = @json($medsByClient);
    window.CD_LASTBAL = @json($lastBalances);
</script>
<script>
$(function () {
    var OTHER = '__other__';

    function currentMedName() {
        var v = $('#cd-med').val();
        if (v === OTHER) return $('#cd-med-other').val().trim();
        return $('#cd-med option:selected').data('name') || '';
    }

    // Populate the drug dropdown when a resident is picked.
    $('#cd-client').on('change', function () {
        var meds = (window.CD_MEDS || {})[$(this).val()] || [];
        var $sel = $('#cd-med').empty();
        $sel.append('<option value="">Select drug…</option>');
        meds.forEach(function (m) {
            $sel.append($('<option>').attr('value', m.id).attr('data-name', m.name).text(m.name));
        });
        $sel.append('<option value="' + OTHER + '">— Type a different drug —</option>');
        $('#cd-med-other').hide().val('');
        $('#cd-med-name').val(''); $('#cd-med-sheet').val('');
        $('#cd-bbefore').val(''); $('#cd-bafter').val('');
    });

    function refreshBalanceBefore() {
        var clientId = $('#cd-client').val();
        var name = currentMedName();
        var key = clientId + '|' + name;
        var last = (window.CD_LASTBAL || {})[key];
        if (last !== undefined && last !== null && $('#cd-bbefore').val() === '') {
            $('#cd-bbefore').val(last);
        }
        recalcAfter();
    }

    // Pick a drug from the list (or choose to type one).
    $('#cd-med').on('change', function () {
        if ($(this).val() === OTHER) {
            $('#cd-med-other').show().focus();
            $('#cd-med-sheet').val('');
        } else {
            $('#cd-med-other').hide();
            $('#cd-med-sheet').val($(this).val());
        }
        $('#cd-med-name').val(currentMedName());
        refreshBalanceBefore();
    });

    $('#cd-med-other').on('input', function () { $('#cd-med-name').val(currentMedName()); refreshBalanceBefore(); });

    // Auto-calculate balance after = before ± dose, based on the action.
    function recalcAfter() {
        var action = $('#cd-action').val();
        if (action === 'adjustment') return; // leave manual
        var before = parseFloat($('#cd-bbefore').val());
        var dose = parseFloat($('#cd-dose').val());
        if (isNaN(before) || isNaN(dose)) return;
        var after = (action === 'received') ? before + dose : before - dose;
        $('#cd-bafter').val(Math.round(after * 100) / 100);
    }
    $('#cd-dose, #cd-bbefore, #cd-action').on('input change', recalcAfter);

    // Make sure the hidden drug name is set before submitting.
    $('#cdForm').on('submit', function (e) {
        $('#cd-med-name').val(currentMedName());
        if (!$('#cd-med-name').val()) {
            e.preventDefault();
            alert('Please choose or type the controlled drug.');
        }
    });
});
</script>

@endsection
