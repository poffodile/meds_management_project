@extends('frontEnd.layouts.master')
@section('title', 'Payroll Process')
@section('content')

@include('frontEnd.roster.common.roster_header')
<style>
    :root {
        --primary-blue: #2563eb;
        --soft-blue-bg: #eff6ff;
        --border-blue: #dbeafe;
        --text-gray-500: #6b7280;
        --text-gray-900: #111827;
        --green-success: #10b981;
        --orange-warning: #f59e0b;
        --red-danger: #ef4444;
        --purple-accent: #8b5cf6;
    }

    .mainTitlep {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .header-subtitle {
        color: #6b7280;
        font-size: 14px;
    }

    /* Summary Cards */
    .card-row {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .card-col {
        flex: 1;
    }

    .summary-card {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #f3f4f6;
        transition: transform 0.2s;
    }

    .summary-card:hover {
        transform: translateY(-2px);
    }

    .summary-label {
        font-size: 13px;
        font-weight: 500;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .summary-value {
        font-size: 28px;
        font-weight: 800;
        margin: 0;
    }

    .text-blue {
        color: #2563eb;
    }

    .text-orange {
        color: #f59e0b;
    }

    .text-purple {
        color: #8b5cf6;
    }

    .text-green {
        color: #10b981;
    }

    .text-red {
        color: #ef4444;
    }

    /* Filter Bar */
    .filter-bar {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #f3f4f6;
        margin-bottom: 24px;
    }

    .filter-bar .form-control {
        border-radius: 8px;
        height: 42px;
        border-color: #e5e7eb;
    }

    /* Accordion Panels */
    .payRollAcood {
        border: none !important;
        margin-bottom: 20px !important;
        box-shadow: none !important;
    }

    .payRollAcood .panel-heading {
        padding: 0 !important;
        border: none !important;
    }

    .payRollAcood .panel-title a {
        display: flex;
        align-items: center;
        padding: 16px 24px !important;
        border-radius: 12px !important;
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        position: relative;
    }

    .payRollAcood .accIcon {
        position: absolute;
        right: 24px;
        font-size: 20px;
        transition: transform 0.3s;
    }

    .payRollAcood .panel-title a.collapsed .accIcon {
        transform: rotate(-90deg);
    }

    .lightBlueBg {
        background-color: #eff6ff !important;
        color: #1e3a8a !important;
    }

    .lighOrangeBg {
        background-color: #fffaf5 !important;
        color: #9a3412 !important;
    }

    .lightGreeBg {
        background-color: #f0fdf4 !important;
        color: #166534 !important;
    }

    /* Reconciliation Cards */
    .recon-card {
        background: #fff;
        border: 1px solid var(--border-blue);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 16px;
        transition: box-shadow 0.2s;
        position: relative;
    }

    .recon-card:hover {
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.1), 0 2px 4px -1px rgba(37, 99, 235, 0.06);
    }

    .recon-card.needs-adj {
        border-color: #fed7aa;
    }

    .staff-name {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
    }

    .badge-soft {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-pending {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-matched {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-standard {
        background: #f3f4f6;
        color: #374151;
    }

    .badge-overtime {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-adj {
        background: #ffedd5;
        color: #c2410c;
    }

    .badge-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .data-label {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .data-value {
        font-size: 15px;
        font-weight: 600;
        color: #111827;
    }

    .variance-pos {
        color: #10b981;
    }

    .variance-neg {
        color: #ef4444;
    }

    /* Warning Section */
    .warning-box {
        background: #fffbeb;
        border-radius: 8px;
        padding: 12px 16px;
        margin-top: 16px;
        border-left: 4px solid #f59e0b;
    }

    .warning-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fef3c7;
        color: #b45309;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .warning-text {
        font-size: 14px;
        color: #92400e;
        margin: 0;
    }

    /* Buttons */
    .btn-approve {
        background: #10b981;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s;
    }

    .btn-approve:hover {
        background: #059669;
    }

    .btn-adjust {
        background: #fff;
        border: 1px solid #e5e7eb;
        color: #374151;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-adjust:hover {
        background: #f9fafb;
        border-color: #d1d5db;
    }

    .btn-approve-all {
        background: #10b981;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        margin-bottom: 20px;
        border: none;
    }
</style>

<main class="page-content">

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="staffHeaderp">
                    <div>
                        <h1 class="mainTitlep">Timesheet & Shift Reconciliation</h1>
                        <p class="header-subtitle mb-0">Review actual vs planned hours and approve timesheets</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt20">
            <div class="col-lg-12">
                <div class="card-row">
                    <div class="summary-card">
                        <p class="summary-label">Matched</p>
                        <h2 class="summary-value text-blue">{{ $matchedCount }}</h2>
                    </div>
                    <div class="summary-card">
                        <p class="summary-label">Needs Adjustment</p>
                        <h2 class="summary-value text-orange">{{ $needsAdjustmentCount }}</h2>
                    </div>
                    <div class="summary-card">
                        <p class="summary-label">Unscheduled</p>
                        <h2 class="summary-value text-purple">{{ $unscheduledCount }}</h2>
                    </div>
                    <div class="summary-card">
                        <p class="summary-label">Approved</p>
                        <h2 class="summary-value text-green">{{ $approvedCount }}</h2>
                    </div>
                    <div class="summary-card">
                        <p class="summary-label">Rejected</p>
                        <h2 class="summary-value text-red">{{ $rejectedCount }}</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt10">
            <div class="col-lg-12">
                <div class="filter-bar">
                    <div class="row">
                        <div class="col-lg-3">
                            <select class="form-control">
                                <option>All Status</option>
                                <option>Pending</option>
                                <option>Requires Adjustment</option>
                                <option>Approved</option>
                                <option>Rejected</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <input type="date" class="form-control">
                        </div>
                        <div class="col-lg-3">
                            <select class="form-control">
                                <option>All Staff</option>
                                @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <button class="borderBtn w100"><i class="bx bx-filter f18 me-2"></i> Reset Filters</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt20">
            <div class="col-lg-12">
                <div class="panel-group" id="accordion">
                    <!-- pannel 1 -->
                    <div class="panel panel-default payRollAcood">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse1" class="lightBlueBg">
                                    <i class="bx bx-clock f20 me-2" style="color: #2563eb;"></i>
                                    Clocks with Shift Data ({{ $matchedCount }})
                                    <i class="bx bx-chevron-down accIcon"></i>
                                </a>
                            </h4>
                        </div>
                        <div id="collapse1" class="panel-collapse collapse in">
                            <div class="panel-body">
                                @if ($matchedCount > 0)
                                <button class="btn-approve-all">
                                    <i class="bx bx-check-double me-2"></i> Approve All Matched
                                </button>
                                @foreach ($shifts->where('reconciliation_status', 'Matched') as $shift)
                                <div class="recon-card">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="staff-name">{{ $shift->staff ? $shift->staff->name : 'Unknown Staff' }}</span>
                                                <span class="badge-soft badge-pending">pending</span>
                                                <span class="badge-soft badge-standard">standard</span>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn-adjust" data-toggle="modal" data-target="#adjustNodal-{{ $shift->id }}">
                                                <i class="bx bx-show"></i> Adjust
                                            </button>
                                            <button class="btn-approve">
                                                <i class="bx bx-check-circle"></i> Approve
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-2">
                                            <p class="data-label">Date</p>
                                            <p class="data-value">{{ \Carbon\Carbon::parse($shift->start_date)->format('D, M d') }}</p>
                                        </div>
                                        <div class="col-lg-3">
                                            <p class="data-label">Clock Times</p>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bx bx-eye fs18" style="cursor:pointer; color: var(--primary-blue);" data-toggle="modal" data-target="#clockDetails-{{ $shift->id }}"></i>
                                                <p class="data-value mb-0">
                                                    @if($shift->login_activities->count() > 0)
                                                    @php
                                                    $firstIn = \Carbon\Carbon::parse($shift->login_activities->min('check_in_time'));
                                                    $lastOut = $shift->login_activities->max('check_out_time') ? \Carbon\Carbon::parse($shift->login_activities->max('check_out_time')) : null;
                                                    @endphp
                                                    {{ $firstIn->format('H:i') }} - {{ $lastOut ? $lastOut->format('H:i') : 'In Progress' }}
                                                    @else
                                                    No Clock Data
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <p class="data-label">Planned</p>
                                            <p class="data-value">{{ number_format($shift->scheduled_duration_minutes / 60, 2) }}h</p>
                                        </div>
                                        <div class="col-lg-2">
                                            <p class="data-label">Actual</p>
                                            <p class="data-value">{{ number_format($shift->actual_duration_minutes / 60, 2) }}h</p>
                                        </div>
                                        <div class="col-lg-3">
                                            <p class="data-label">Variance</p>
                                            <p class="data-value @if($shift->variance_minutes >= 0) variance-pos @else variance-neg @endif">
                                                {{ $shift->variance_minutes >= 0 ? '+' : '' }}{{ number_format($shift->variance_minutes / 60, 2) }}h
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <p class="textGray500 fs13 text-center py-5 mb-0">No matched timesheets found.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- Panel 2-->
                    <div class="panel panel-default mt-4 payRollAcood p-0">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse2" class="lighOrangeBg">
                                    <i class="bx bx-error-circle f20 me-2" style="color: #f59e0b;"></i>
                                    Requires Adjustment ({{ $needsAdjustmentCount }})
                                    <i class="bx bx-chevron-down accIcon"></i>
                                </a>
                            </h4>
                        </div>
                        <div id="collapse2" class="panel-collapse collapse">
                            <div class="panel-body">
                                @if ($needsAdjustmentCount > 0)
                                @foreach ($shifts->where('reconciliation_status', 'Needs Adjustment') as $shift)
                                <div class="recon-card needs-adj">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="staff-name">{{ $shift->staff ? $shift->staff->name : 'Unknown Staff' }}</span>
                                                <span class="badge-soft badge-adj">requires adjustment</span>
                                                <span class="badge-soft badge-standard">standard</span>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn-adjust" data-toggle="modal" data-target="#adjustNodal-{{ $shift->id }}">
                                                <i class="bx bx-show"></i> Adjust
                                            </button>
                                            <button class="btn-approve">
                                                <i class="bx bx-check-circle"></i> Approve
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-2">
                                            <p class="data-label">Date</p>
                                            <p class="data-value">{{ \Carbon\Carbon::parse($shift->start_date)->format('D, M d') }}</p>
                                        </div>
                                        <div class="col-lg-3">
                                            <p class="data-label">Clock Times</p>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bx bx-eye fs18" style="cursor:pointer; color: var(--primary-blue);" data-toggle="modal" data-target="#clockDetails-{{ $shift->id }}"></i>
                                                <p class="data-value mb-0">
                                                    @if($shift->login_activities->count() > 0)
                                                    @php
                                                    $firstIn = \Carbon\Carbon::parse($shift->login_activities->min('check_in_time'));
                                                    $lastOut = $shift->login_activities->max('check_out_time') ? \Carbon\Carbon::parse($shift->login_activities->max('check_out_time')) : null;
                                                    @endphp
                                                    {{ $firstIn->format('H:i') }} - {{ $lastOut ? $lastOut->format('H:i') : 'In Progress' }}
                                                    @else
                                                    No Clock Data
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <p class="data-label">Planned</p>
                                            <p class="data-value">{{ number_format($shift->scheduled_duration_minutes / 60, 2) }}h</p>
                                        </div>
                                        <div class="col-lg-2">
                                            <p class="data-label">Actual</p>
                                            <p class="data-value">{{ number_format($shift->actual_duration_minutes / 60, 2) }}h</p>
                                        </div>
                                        <div class="col-lg-3">
                                            <p class="data-label">Variance</p>
                                            <p class="data-value @if($shift->variance_minutes >= 0) variance-pos @else variance-neg @endif">
                                                {{ $shift->variance_minutes >= 0 ? '+' : '' }}{{ number_format($shift->variance_minutes / 60, 2) }}h
                                            </p>
                                        </div>
                                    </div>

                                    @if($shift->is_late || $shift->is_early || abs($shift->variance_minutes) > 60)
                                    <div class="warning-box">
                                        <div class="d-flex gap-2 mb-2">
                                            @if($shift->is_late)
                                            <span class="warning-badge"><i class="bx bx-time"></i> Clocked In Late</span>
                                            @endif
                                            @if($shift->is_early)
                                            <span class="warning-badge"><i class="bx bx-time"></i> Clocked Out Early</span>
                                            @endif
                                        </div>
                                        <p class="warning-text">
                                            @if($shift->is_late) Staff clocked in {{ $shift->late_minutes }} minutes late. @endif
                                            @if($shift->is_early) Staff clocked out {{ $shift->early_minutes }} minutes early. @endif
                                            @if(abs($shift->variance_minutes) > 60) Significant hours discrepancy ({{ number_format(abs($shift->variance_minutes)/60, 2) }}h) — manager review required. @endif
                                        </p>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                                @else
                                <p class="textGray500 fs13 text-center py-5 mb-0">No shifts requiring adjustment. </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- Panel 3-->

                    <div class="panel panel-default mt-4 payRollAcood p-0">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse3" class="lightGreeBg">
                                    <i class="bx bx-check-circle f20 me-2" style="color: #10b981;"></i>
                                    Approved ({{ $approvedCount }})
                                    <i class="bx bx-chevron-down accIcon"></i>
                                </a>
                            </h4>
                        </div>
                        <div id="collapse3" class="panel-collapse collapse">
                            <div class="panel-body">
                                @if ($approvedCount > 0)
                                @foreach ($shifts->where('reconciliation_status', 'Approved') as $shift)
                                <div class="recon-card">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="staff-name">{{ $shift->staff ? $shift->staff->name : 'Unknown Staff' }}</span>
                                                <span class="badge-soft badge-approved">approved</span>
                                                <span class="badge-soft badge-standard">standard</span>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn-adjust" data-toggle="modal" data-target="#adjustNodal-{{ $shift->id }}">
                                                <i class="bx bx-show"></i> View Details
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-2">
                                            <p class="data-label">Date</p>
                                            <p class="data-value">{{ \Carbon\Carbon::parse($shift->start_date)->format('D, M d') }}</p>
                                        </div>
                                        <div class="col-lg-3">
                                            <p class="data-label">Clock Times</p>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bx bx-eye fs18" style="cursor:pointer; color: var(--primary-blue);" data-toggle="modal" data-target="#clockDetails-{{ $shift->id }}"></i>
                                                <p class="data-value mb-0">
                                                    @if($shift->login_activities->count() > 0)
                                                    @php
                                                    $firstIn = \Carbon\Carbon::parse($shift->login_activities->min('check_in_time'));
                                                    $lastOut = $shift->login_activities->max('check_out_time') ? \Carbon\Carbon::parse($shift->login_activities->max('check_out_time')) : null;
                                                    @endphp
                                                    {{ $firstIn->format('H:i') }} - {{ $lastOut ? $lastOut->format('H:i') : 'In Progress' }}
                                                    @else
                                                    No Clock Data
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <p class="data-label">Planned</p>
                                            <p class="data-value">{{ number_format($shift->scheduled_duration_minutes / 60, 2) }}h</p>
                                        </div>
                                        <div class="col-lg-2">
                                            <p class="data-label">Actual</p>
                                            <p class="data-value">{{ number_format($shift->actual_duration_minutes / 60, 2) }}h</p>
                                        </div>
                                        <div class="col-lg-3">
                                            <p class="data-label">Variance</p>
                                            <p class="data-value @if($shift->variance_minutes >= 0) variance-pos @else variance-neg @endif">
                                                {{ $shift->variance_minutes >= 0 ? '+' : '' }}{{ number_format($shift->variance_minutes / 60, 2) }}h
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <p class="textGray500 fs13 text-center py-5 mb-0">No approved timesheets found. </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- Panel 4 (Unscheduled) -->
                    <!-- <div class="panel panel-default mt-4 payRollAcood p-0">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse4" class="lightBlueBg">
                                    <i class="bx bx-help-circle f20 purpleTextp me-2"></i>
                                    Unscheduled ({{ $unscheduledCount }})
                                    <i class="bx bx-chevron-down accIcon"></i>
                                </a>
                            </h4>
                        </div>
                        <div id="collapse4" class="panel-collapse collapse">
                            <div class="panel-body">
                                @if ($unscheduledCount > 0)
                                @foreach ($shifts->where('reconciliation_status', 'Unscheduled') as $shift)
                                <div class="bBorderCard mt-4 p-4">
                                    <div class="d-flex justify-content-between">
                                        <div class="flex1">
                                            <div class="d-flex gap-3 mb-3 align-items-center">
                                                <h5 class="h5Head mb-0">Unassigned Shift</h5>
                                                <div><span class="careBadg purpleBages">Unfilled</span></div>
                                            </div>
                                            <div class="d-flex mb-4">
                                                <div class="flex1">
                                                    <p class="mb-2 fs13 textGray500">Date </p>
                                                    <h6 class="h6Head blackText mb-0">{{ \Carbon\Carbon::parse($shift->start_date)->format('D, M d') }}</h6>
                                                </div>
                                                <div class="flex1">
                                                    <p class="mb-2 fs13 textGray500">Scheduled Time</p>
                                                    <h6 class="h6Head blackText mb-0">{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="borderBtn w100"><i class="bx bx-user-plus me-2 f18"></i> Assign Staff</button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <p class="textGray500 fs13 text-center py-5 mb-0">No unassigned shifts found.</p>
                                @endif
                            </div>
                        </div>
                    </div> -->

                    <!-- Panel 5 (Rejected) -->
                    <!-- <div class="panel panel-default mt-4 payRollAcood p-0">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse5" class="lightRedBg">
                                    <i class="bx bx-x-circle f20 redtext me-2"></i>
                                    Rejected ({{ $rejectedCount }})
                                    <i class="bx bx-chevron-down accIcon"></i>
                                </a>
                            </h4>
                        </div>
                        <div id="collapse5" class="panel-collapse collapse">
                            <div class="panel-body">
                                @if ($rejectedCount > 0)
                                @foreach ($shifts->where('reconciliation_status', 'Rejected') as $shift)
                                <div class="bBorderCard mt-4 p-4">
                                    <div class="d-flex justify-content-between">
                                        <div class="flex1">
                                            <div class="d-flex gap-3 mb-3 align-items-center">
                                                <h5 class="h5Head mb-0">{{ $shift->staff ? $shift->staff->name : 'Unknown Staff' }}</h5>
                                                <div><span class="careBadg redbadges">Rejected</span></div>
                                            </div>
                                            <div class="d-flex mb-4">
                                                <div class="flex1">
                                                    <p class="mb-2 fs13 textGray500">Date </p>
                                                    <h6 class="h6Head blackText mb-0">{{ \Carbon\Carbon::parse($shift->start_date)->format('D, M d') }}</h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="borderBtn w100"><i class="bx bx-refresh me-2 f18"></i> Re-assign</button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <p class="textGray500 fs13 text-center py-5 mb-0">No rejected shifts found.</p>
                                @endif
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <!-- modal Adjust reconciliation start -->
    @foreach ($shifts as $shift)
    <div class="modal fade leaveCommunStyle" id="adjustNodal-{{ $shift->id }}" tabindex="1" role="dialog"
        aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modalMd pModalScroll">
            <div class="modal-content">
                <div class="modal-header p24">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Pay Adjustments - {{ $shift->staff ? $shift->staff->name : 'Unknown' }}</h4>
                </div>
                <div class="modal-body heightScrollModal p24" style="height: unset;">
                    <div class="d-flex muteBg rounded5 p-4" style="background: #f9fafb; border-radius: 8px;">
                        <div class="flex1">
                            <p class="fs13 textGray mb-2">Planned Hours </p>
                            <h5 class="h5Head font700">{{ number_format($shift->scheduled_duration_minutes / 60, 2) }}h </h5>
                        </div>
                        <div class="flex1">
                            <p class="fs13 textGray mb-2">Current Actual Hours </p>
                            <h5 class="h5Head font700">{{ number_format($shift->actual_duration_minutes / 60, 2) }}h </h5>
                        </div>
                    </div>
                    <div class="mt20">
                        <h6 class="h5Head">Clock Times</h6>
                        <form action="">
                            <div class="row">
                                <div class="col-md-6 m-t-10">
                                    <label>Clock In</label>
                                    @php
                                    $firstIn = $shift->login_activities->count() > 0 ? \Carbon\Carbon::parse($shift->login_activities->min('check_in_time'))->format('H:i') : '';
                                    $lastOut = ($shift->login_activities->count() > 0 && $shift->login_activities->max('check_out_time')) ? \Carbon\Carbon::parse($shift->login_activities->max('check_out_time'))->format('H:i') : '';
                                    @endphp
                                    <input type="time" name="clock_in" value="{{ $firstIn }}" class="form-control">
                                </div>
                                <div class="col-md-6 m-t-10">
                                    <label>Clock Out</label>
                                    <input type="time" name="clock_out" value="{{ $lastOut }}" class="form-control">
                                </div>
                            </div>
                            <div class="appendContainer-{{ $shift->id }}">
                                <div class="flexBw mt20">
                                    <div>
                                        <h5 class="h5Head">Pay Adjustments </h5>
                                    </div>
                                    <div>
                                        <button class="borderBtn appendBtn" data-target=".appendContainer-{{ $shift->id }}"> <i class="bx bx-plus me-2"></i> Add Row</button>
                                    </div>
                                </div>
                                <div class="flexRow mt-3">
                                    <div class="shadowp rounded8 p-4 lightBorderp appendRow" style="display: none; border: 1px solid #e5e7eb; margin-bottom: 10px;">
                                        <div class="dFlexGap align-item-end">
                                            <div class="flex1">
                                                <label for="">Hours</label>
                                                <input type="text" class="form-control">
                                            </div>
                                            <div class="flex1">
                                                <label for="">Minutes</label>
                                                <input type="text" class="form-control">
                                            </div>
                                            <div class="flex1">
                                                <label for="">Pay Bucket</label>
                                                <select class="form-control">
                                                    <option value="1">Standard</option>
                                                    <option value="1">OverTime</option>
                                                    <option value="1">Weekend</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label for=" " style="visibility: hidden;">delete</label>
                                                <div class="deleteIcon flex1 deleteAppend" style="cursor: pointer; color: #ef4444; padding: 10px;">
                                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt20">
                                <div>
                                    <label>Adjustment Reason</label>
                                    <textarea name="notes" rows="3" placeholder="Enter reason for adjustment..." class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="mt20 lightBorderp bg-blue-50 p-4 rounded8" style="background: #eff6ff; border: 1px solid #dbeafe;">
                                <div class="flexBw d-flex justify-content-between align-items-center">
                                    <h5 class="h6Head mb-0" style="color: #1e40af;">Total Adjusted Hours</h5>
                                    <h3 class="my-0 font700" style="color: #1e40af;">{{ number_format($shift->actual_duration_minutes / 60, 2) }}h</h3>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer d-flex gap-3">
                    <div class="dFlexGap w100 d-flex gap-3">
                        <div class="flex1 w-50">
                            <button class="btn-adjust w100" data-dismiss="modal">Cancel </button>
                        </div>
                        <div class="flex1 w-50">
                            <button class="btn-approve w100"> <i class="bx bx-save f18 me-2"></i> Save Adjustment</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    <!-- modal Adjust reconciliation end -->

    <!-- clocl details -->
    @foreach ($shifts as $shift)
    <div class="modal fade leaveCommunStyle" id="clockDetails-{{ $shift->id }}" tabindex="1" role="dialog"
        aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog  pModalScroll">
            <div class="modal-content">
                <div class="modal-header p24">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Clock Details </h4>
                </div>
                <div class="modal-body heightScrollModal p24" style="height: unset;">

                    <div class="scrollDailyCheck pe-3">
                        @if (count($shift->login_activities) > 0)
                        @foreach ($shift->login_activities as $activity)
                        <div class="lightBorderp mb-3 rounded8 p-3" style="border: 1px solid #e5e7eb; border-radius: 8px;">
                            <div class="d-flex gap-5 mb-3">
                                <div class="flex-grow-1">
                                    <p class="fs13 textGray500 mb-1">Clock In:</p>
                                    <p class="fs13 blackText font600">{{ \Carbon\Carbon::parse($activity->check_in_time)->format('g:i A') }}</p>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="fs13 textGray500 mb-1">Clock Out:</p>
                                    <p class="fs13 blackText font600">{{ $activity->check_out_time ? \Carbon\Carbon::parse($activity->check_out_time)->format('g:i A') : 'In progress' }}</p>
                                </div>
                            </div>
                            <div class="d-flex gap-5">
                                <div class="flex-grow-1">
                                    <p class="fs13 textGray500 mb-1">Clock In Reason:</p>
                                    <p class="fs13 blackText font600">{{ $activity->check_in_reason ?: 'N/A' }}</p>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="fs13 textGray500 mb-1">Clock Out Reason:</p>
                                    <p class="fs13 blackText font600">{{ $activity->check_out_reason ?: 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <div class="lightBorderp mb-3 rounded8 p-3">
                            <p class="textGray500 fs13 text-center mb-0">No Logs Recorded For This Shift</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    <!-- end clock details -->
</main>
<!-- append section -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".appendBtn").forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                const containerSelector = button.getAttribute("data-target");
                const container = document.querySelector(containerSelector);
                if (!container) return;
                const templateRow = container.querySelector(".appendRow");
                if (!templateRow) return;

                // clone the row
                const newRow = templateRow.cloneNode(true);
                newRow.style.display = "block"; // show the row

                // reset inputs/selects
                newRow.querySelectorAll("input").forEach(input => input.value = "");
                newRow.querySelectorAll("select").forEach(select => select.selectedIndex = 0);
                newRow.querySelectorAll("textarea").forEach(txt => txt.value = "");

                // append new row to container
                container.appendChild(newRow);
            });
        });

        // Optional: delete row if you add delete icons
        document.addEventListener("click", function(e) {
            const del = e.target.closest(".deleteIcon");
            if (del) {
                const row = del.closest(".appendRow");
                if (row) row.remove();
            }
        });
    });
</script>
@endsection