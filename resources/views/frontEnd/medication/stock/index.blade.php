@extends('frontEnd.layouts.master')
@section('title','Medication Stock')
@section('content')

@include('frontEnd.roster.common.roster_header')

@php
    $pill = 'display:inline-block;padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;line-height:1.6;';
    $typePill = [
        'received'     => 'background:#dbeafe;color:#1d4ed8;',
        'administered' => 'background:#d1fae5;color:#047857;',
        'disposed'     => 'background:#ffedd5;color:#9a3412;',
        'returned'     => 'background:#f3f4f6;color:#374151;',
        'correction'   => 'background:#fef9c3;color:#854d0e;',
    ];
    $num = fn($v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 2), '0'), '.');
    $today = \Carbon\Carbon::today();
    // Derived lists for the Reorders / Disposals tabs (no extra tables needed).
    $reorders = $sheets->filter(fn($s) => !is_null($s->stock_level) && !is_null($s->reorder_level) && $s->stock_level <= $s->reorder_level);
    $disposals = $transactions->where('transaction_type', 'disposed');
@endphp

<main class="page-content">
    <div class="container-fluid">

        <div style="max-width:1100px; margin:0 auto;">

            {{-- Header --}}
            <div class="m-t-30" style="margin-bottom:18px;">
                <h2 style="margin:0; font-weight:700; color:#111827; font-size:24px;"><i class="fa fa-cube" style="color:#4f46e5; margin-right:10px;"></i>Medication Stock</h2>
                <p style="margin:6px 0 0; color:#6b7280;">Manage inventory, reorder needs and disposals</p>
            </div>

            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if($errors->any())
                <div class="alert alert-danger"><strong>Please fix:</strong>
                    <ul style="margin:8px 0 0 20px;">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            {{-- Stock alerts --}}
            @if($alerts->count() > 0)
                <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:12px; padding:14px 16px; margin-bottom:18px;">
                    <div style="font-weight:700; color:#9a3412; margin-bottom:6px;"><i class="fa fa-exclamation-triangle"></i> Stock Alerts ({{ $alerts->count() }})</div>
                    @foreach($alerts as $a)
                        @php $aExpired = $a->expiry_date && $a->expiry_date->lt($today); @endphp
                        @if($aExpired)
                            <div style="font-size:13px; color:#b91c1c;">✕ {{ $a->medication_name }} ({{ $residentNames[$a->client_id] ?? ('#'.$a->client_id) }}) — expired {{ $a->expiry_date->format('Y-m-d') }}</div>
                        @else
                            <div style="font-size:13px; color:{{ $a->stock_level <= 0 ? '#b91c1c' : '#9a3412' }};">{{ $a->stock_level <= 0 ? '✕' : '⚠' }} {{ $a->medication_name }} ({{ $residentNames[$a->client_id] ?? ('#'.$a->client_id) }}) — {{ $a->stock_level }} remaining (reorder at {{ $a->reorder_level }})</div>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Tabs --}}
            <ul class="nav nav-tabs" role="tablist" style="border-bottom:1px solid #e5e7eb;">
                <li role="presentation" class="active"><a href="#tab-overview" role="tab" data-toggle="tab" style="font-weight:600;">Stock Overview</a></li>
                <li role="presentation"><a href="#tab-transactions" role="tab" data-toggle="tab" style="font-weight:600;">Transactions</a></li>
                <li role="presentation"><a href="#tab-reorders" role="tab" data-toggle="tab" style="font-weight:600;">Reorders <span style="{{ $pill }}background:#ffedd5;color:#9a3412;">{{ $reorders->count() }}</span></a></li>
                <li role="presentation"><a href="#tab-disposals" role="tab" data-toggle="tab" style="font-weight:600;">Disposals</a></li>
            </ul>

            <div class="tab-content" style="padding-top:18px;">

                {{-- Overview --}}
                <div role="tabpanel" class="tab-pane active" id="tab-overview">
                    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 1px 2px rgba(0,0,0,.04); overflow:hidden;">
                        <div style="overflow-x:auto;">
                            <table class="table" style="margin:0; min-width:860px;">
                                <thead>
                                    <tr style="background:#f9fafb;">
                                        <th style="border:none;">Resident</th>
                                        <th style="border:none;">Medication</th>
                                        <th style="border:none;">Stock</th>
                                        <th style="border:none;">Expiry</th>
                                        <th style="border:none;">Status</th>
                                        <th style="border:none; text-align:right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sheets as $s)
                                        @php
                                            $isExpired = $s->expiry_date && $s->expiry_date->lt($today);
                                            $isOut = !is_null($s->stock_level) && $s->stock_level <= 0;
                                            $isLow = !$isOut && !is_null($s->stock_level) && !is_null($s->reorder_level) && $s->stock_level <= $s->reorder_level;
                                        @endphp
                                        <tr style="{{ $isExpired || $isOut || $isLow ? 'background:#fffaf5;' : '' }}">
                                            <td style="font-weight:600;">{{ $residentNames[$s->client_id] ?? ('#'.$s->client_id) }}</td>
                                            <td>
                                                {{ $s->medication_name }}
                                                @if($s->is_controlled)<span style="{{ $pill }}background:#fee2e2;color:#b91c1c;font-size:10px;">CD</span>@endif
                                                <div style="color:#9ca3af; font-size:12px;">{{ $s->dose ?: $s->dosage }}@if($s->route) · {{ $s->route }}@endif</div>
                                            </td>
                                            <td>
                                                <span style="font-weight:700; color:{{ $isOut ? '#b91c1c' : ($isLow ? '#b45309' : '#111827') }};">{{ is_null($s->stock_level) ? '—' : $s->stock_level }}</span>
                                                @if(!is_null($s->reorder_level))<div style="color:#9ca3af; font-size:11px;">Reorder at {{ $s->reorder_level }}</div>@endif
                                            </td>
                                            <td style="font-size:13px; color:{{ $isExpired ? '#b91c1c' : '#6b7280' }};">{{ $s->expiry_date ? $s->expiry_date->format('Y-m-d') : '—' }}</td>
                                            <td>
                                                @if($isExpired)<span style="{{ $pill }}background:#fee2e2;color:#b91c1c;">Expired</span>
                                                @elseif($isOut)<span style="{{ $pill }}background:#fee2e2;color:#b91c1c;">Out of stock</span>
                                                @elseif($isLow)<span style="{{ $pill }}background:#ffedd5;color:#9a3412;">Low stock</span>
                                                @elseif(is_null($s->stock_level))<span style="{{ $pill }}background:#f3f4f6;color:#6b7280;">Not tracked</span>
                                                @else<span style="{{ $pill }}background:#d1fae5;color:#047857;">OK</span>@endif
                                            </td>
                                            <td style="text-align:right;">
                                                <button type="button" class="btn btn-sm adjust-btn" style="background:#4f46e5;color:#fff;border:none;border-radius:8px;padding:5px 12px;font-weight:600;font-size:13px;"
                                                    data-sheet-id="{{ $s->id }}" data-med="{{ $s->medication_name }}" data-resident="{{ $residentNames[$s->client_id] ?? '' }}"
                                                    data-stock="{{ $s->stock_level }}" data-expiry="{{ $s->expiry_date ? $s->expiry_date->format('Y-m-d') : '' }}"
                                                    data-controlled="{{ $s->is_controlled ? 1 : 0 }}" data-schedule="{{ $s->cd_schedule }}">Adjust</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" style="text-align:center; padding:40px; color:#9ca3af;">No active medications.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Transactions --}}
                <div role="tabpanel" class="tab-pane" id="tab-transactions">
                    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 1px 2px rgba(0,0,0,.04); overflow:hidden;">
                        <div style="overflow-x:auto;">
                            <table class="table" style="margin:0; min-width:820px;">
                                <thead>
                                    <tr style="background:#f9fafb;">
                                        <th style="border:none;">Date</th><th style="border:none;">Resident</th><th style="border:none;">Medication</th>
                                        <th style="border:none;">Type</th><th style="border:none;">Qty</th><th style="border:none;">Balance</th><th style="border:none;">By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $t)
                                        <tr>
                                            <td style="font-size:12px; color:#6b7280;">{{ $t->transaction_date ? $t->transaction_date->format('Y-m-d H:i') : '—' }}</td>
                                            <td>{{ $t->client_name ?: '—' }}</td>
                                            <td>{{ $t->medication_name }}</td>
                                            <td><span style="{{ $pill }}{{ $typePill[$t->transaction_type] ?? 'background:#f3f4f6;color:#374151;' }}">{{ ucfirst($t->transaction_type) }}</span></td>
                                            <td>{{ $num($t->quantity) }}</td>
                                            <td style="font-size:12px;"><span style="color:#9ca3af;">{{ $num($t->balance_before) }} &rarr;</span> <strong>{{ $num($t->balance_after) }}</strong></td>
                                            <td style="color:#6b7280;">
                                                {{ $t->performedByUser->name ?? '—' }}
                                                <div><a href="#" class="txn-detail-toggle" data-target="txn-{{ $t->id }}" style="font-size:12px; color:#6b7280;"><i class="fa fa-sticky-note-o"></i> Details</a></div>
                                            </td>
                                        </tr>
                                        <tr class="txn-detail-row" id="txn-{{ $t->id }}" style="display:none;">
                                            <td colspan="7" style="background:#f9fafb;">
                                                <div style="font-size:12px; color:#4b5563; padding:4px 2px;">
                                                    @if($t->reason)<div><strong>Reason:</strong> {{ $t->reason }}</div>@endif
                                                    @if($t->disposal_method)<div><strong>Disposal method:</strong> {{ str_replace('_', ' ', $t->disposal_method) }}</div>@endif
                                                    @if($t->witness_name)<div><strong>Witness:</strong> {{ $t->witness_name }}</div>@endif
                                                    <div><strong>Quantity:</strong> {{ $num($t->quantity) }}@if($t->unit) {{ $t->unit }}@endif</div>
                                                    <div><strong>Balance:</strong> {{ $num($t->balance_before) }} &rarr; {{ $num($t->balance_after) }}</div>
                                                    <div><strong>Recorded by:</strong> {{ $t->performedByUser->name ?? '—' }}@if($t->transaction_date) on {{ $t->transaction_date->format('d M Y H:i') }}@endif</div>
                                                    @if($t->notes)<div><strong>Notes:</strong> {{ $t->notes }}</div>@else<div style="color:#9ca3af;">No notes</div>@endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" style="text-align:center; padding:40px; color:#9ca3af;">No stock movements recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Reorders (derived from low/out stock) --}}
                <div role="tabpanel" class="tab-pane" id="tab-reorders">
                    @if($reorders->count() === 0)
                        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; text-align:center; padding:40px; color:#9ca3af;">Nothing needs reordering right now.</div>
                    @else
                        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 1px 2px rgba(0,0,0,.04); overflow:hidden;">
                            <table class="table" style="margin:0; min-width:600px;">
                                <thead><tr style="background:#f9fafb;"><th style="border:none;">Resident</th><th style="border:none;">Medication</th><th style="border:none;">Current</th><th style="border:none;">Reorder at</th></tr></thead>
                                <tbody>
                                    @foreach($reorders as $s)
                                        <tr>
                                            <td style="font-weight:600;">{{ $residentNames[$s->client_id] ?? ('#'.$s->client_id) }}</td>
                                            <td>{{ $s->medication_name }} @if($s->is_controlled)<span style="{{ $pill }}background:#fee2e2;color:#b91c1c;font-size:10px;">CD</span>@endif</td>
                                            <td><span style="font-weight:700; color:{{ $s->stock_level <= 0 ? '#b91c1c' : '#b45309' }};">{{ $s->stock_level }}</span></td>
                                            <td>{{ $s->reorder_level }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Disposals (derived from history) --}}
                <div role="tabpanel" class="tab-pane" id="tab-disposals">
                    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 1px 2px rgba(0,0,0,.04); overflow:hidden;">
                        <table class="table" style="margin:0; min-width:700px;">
                            <thead><tr style="background:#f9fafb;"><th style="border:none;">Date</th><th style="border:none;">Medication</th><th style="border:none;">Qty</th><th style="border:none;">Reason</th><th style="border:none;">Method</th><th style="border:none;">Witness</th></tr></thead>
                            <tbody>
                                @forelse($disposals as $d)
                                    <tr>
                                        <td style="font-size:12px; color:#6b7280;">{{ $d->transaction_date ? $d->transaction_date->format('Y-m-d') : '—' }}</td>
                                        <td>{{ $d->medication_name }} <span style="color:#9ca3af;">({{ $d->client_name }})</span></td>
                                        <td>{{ $num($d->quantity) }}</td>
                                        <td>{{ $d->reason ?: '—' }}</td>
                                        <td>{{ $d->disposal_method ? str_replace('_', ' ', $d->disposal_method) : '—' }}</td>
                                        <td>{{ $d->witness_name ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" style="text-align:center; padding:40px; color:#9ca3af;">No disposals recorded yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>{{-- /max-width --}}

    </div>
</main>

{{-- Adjust Stock Modal --}}
<div class="modal fade" id="stockModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('medication.stock.adjust') }}">
            @csrf
            <input type="hidden" name="mar_sheet_id" id="st-sheet-id">
            <div class="modal-content">
                <div class="modal-header" style="background:#eef2ff;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="color:#3730a3;">Adjust Stock</h4>
                </div>
                <div class="modal-body" style="max-height:72vh; overflow-y:auto;">
                    <div style="background:#f5f5f5; padding:10px; border-radius:8px; margin-bottom:14px;">
                        <strong id="st-med"></strong>
                        <div class="text-muted" style="font-size:13px;">Resident: <strong id="st-resident"></strong> · Current stock: <strong id="st-current"></strong></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6"><div class="form-group">
                            <label>Action</label>
                            <select name="transaction_type" id="st-type" class="form-control">
                                <option value="received">Received (add)</option>
                                <option value="disposed">Disposed (remove)</option>
                                <option value="returned">Returned to pharmacy (remove)</option>
                                <option value="correction">Correction (set exact count)</option>
                            </select>
                        </div></div>
                        <div class="col-md-6"><div class="form-group">
                            <label id="st-qty-label">Quantity to add</label>
                            <input type="number" step="any" min="0" name="quantity" class="form-control" placeholder="Leave blank to only update details below">
                        </div></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6"><div class="form-group">
                            <label>Expiry date</label>
                            <input type="date" name="expiry_date" id="st-expiry" class="form-control">
                        </div></div>
                        <div class="col-md-6"><div class="form-group">
                            <label style="display:block;">Controlled drug?</label>
                            <label style="font-weight:normal;"><input type="checkbox" name="is_controlled" id="st-controlled" value="1"> Mark as CD</label>
                            <select name="cd_schedule" id="st-schedule" class="form-control" style="margin-top:6px; display:none;">
                                <option value="schedule_2">Schedule 2</option>
                                <option value="schedule_3">Schedule 3</option>
                                <option value="schedule_4">Schedule 4</option>
                                <option value="schedule_5">Schedule 5</option>
                            </select>
                        </div></div>
                    </div>

                    <div class="form-group" id="st-reason-group">
                        <label>Reason</label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g. expired, damaged, recount">
                    </div>

                    <div class="form-group" id="st-method-group" style="display:none;">
                        <label>Disposal method</label>
                        <select name="disposal_method" class="form-control">
                            <option value="">—</option>
                            <option value="pharmacy_return">Pharmacy return</option>
                            <option value="doop_service">DOOP service</option>
                            <option value="chemical_destruction">Chemical destruction</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Witness <span class="text-muted">(required for controlled drugs)</span></label>
                        <input type="text" name="witness_name" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background:#4f46e5; color:#fff; border:none;">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(function () {
    // Expand/collapse a transaction's full details.
    $(document).on('click', '.txn-detail-toggle', function (e) {
        e.preventDefault();
        $('#' + $(this).data('target')).toggle();
    });

    $(document).on('click', '.adjust-btn', function () {
        var $b = $(this);
        $('#st-sheet-id').val($b.data('sheet-id'));
        $('#st-med').text($b.data('med'));
        $('#st-resident').text($b.data('resident') || '—');
        var stock = $b.data('stock');
        $('#st-current').text((stock === '' || stock === undefined) ? 'Not tracked' : stock);
        $('#st-expiry').val($b.data('expiry') || '');
        var isCd = String($b.data('controlled')) === '1';
        $('#st-controlled').prop('checked', isCd);
        $('#st-schedule').toggle(isCd);
        if ($b.data('schedule')) $('#st-schedule').val($b.data('schedule'));
        $('#stockModal').modal('show');
    });

    $('#st-controlled').on('change', function () { $('#st-schedule').toggle($(this).is(':checked')); });

    function updateTypeUI() {
        var t = $('#st-type').val();
        $('#st-method-group').toggle(t === 'disposed');
        $('#st-qty-label').text(t === 'received' ? 'Quantity to add' : t === 'correction' ? 'New exact count' : 'Quantity to remove');
    }
    $('#st-type').on('change', updateTypeUI);
    updateTypeUI();
});
</script>

@endsection
