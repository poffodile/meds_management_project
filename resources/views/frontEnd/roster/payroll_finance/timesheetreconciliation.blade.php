@extends('frontEnd.layouts.master')
@section('title', 'Timesheet Reconciliation')
@section('content')

@include('frontEnd.roster.common.roster_header')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<style>
    /* :root {
        --primary-blue: #2563eb;
        --soft-blue-bg: #eff6ff;
        --border-blue: #dbeafe;
        --text-gray-500: #6b7280;
        --text-gray-900: #111827;
        --green-success: #10b981;
        --orange-warning: #f59e0b;
        --red-danger: #ef4444;
        --purple-accent: #8b5cf6;
    } */

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

    .flex-grow-1 {
        flex-grow: 1;
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
        /* font-size: 28px; */
        font-weight: 700;
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
        /* box-shadow: none !important; */
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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

    .lightPurpleBg {
        background-color: #f5f3ff !important;
        color: #5b21b6 !important;
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
        background: rgb(23 165 75);
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

    /* Modal Styling Classes */
    .modal-body-unset {
        height: unset !important;
    }

    .bg-muted-box {
        background: #f9fafb;
        border-radius: 8px;
    }

    .text-blue-dark {
        color: #1e40af !important;
    }

    .delete-btn-trash {
        cursor: pointer;
        color: #ef4444;
        padding: 10px;
    }

    .p24 {
        padding: 24px !important;
    }

    .gap-3 {
        gap: 12px !important;
    }

    /* Manual Entry Styles */
    .calc-hours-box {
        margin-top: 20px;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        padding: 20px;
        border-radius: 8px;
    }

    .calc-hours-label {
        color: #1e40af;
        font-weight: 600;
        margin-bottom: 0;
    }

    .calc-hours-value {
        color: #1e40af;
        font-weight: 700;
        margin-top: 0;
        margin-bottom: 0;
    }

    /* Scroller Design for Large Data */
    .payRollAcood .panel-body {
        max-height: 600px;
        overflow-y: auto;
        padding: 20px 24px !important;
        scroll-behavior: smooth;
    }

    .payRollAcood .panel-body::-webkit-scrollbar {
        width: 6px;
    }

    .payRollAcood .panel-body::-webkit-scrollbar-track {
        background: #f9fafb;
        border-radius: 10px;
    }

    .payRollAcood .panel-body::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
    }

    .payRollAcood .panel-body::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    .tab-search-wrapper {
        position: sticky;
        top: -20px;
        background: #fff;
        z-index: 10;
        padding-bottom: 15px;
        margin-bottom: 20px;
        border-bottom: 1px solid #f3f4f6;
    }

    .inner-tab-search {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding-left: 40px !important;
        height: 42px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .search-icon-inner {
        position: absolute;
        left: 15px;
        top: 12px;
        color: #9ca3af;
        font-size: 18px;
    }
</style>

<main class="page-content">

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="staffHeaderp d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mainTitlep">Timesheet & Shift Reconciliation</h1>
                        <p class="header-subtitle mb-0">Review actual vs planned hours and approve timesheets</p>
                    </div>
                    <div class="header-btn">
                        <button class="btn allBtnUseColor" data-toggle="modal" data-target="#addTimesheetModal">+ Add Timesheet</button>
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
                    <form action="{{ route('roster.payroll.finance.reconciliation') }}" method="GET" id="filter-form">
                        <div class="row">
                            <div class="col-lg-3">
                                <select class="form-control" name="status" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Matched" {{ request('status') == 'Matched' ? 'selected' : '' }}>Matched</option>
                                    <option value="Needs Adjustment" {{ request('status') == 'Needs Adjustment' ? 'selected' : '' }}>Needs Adjustment</option>
                                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <input type="date" class="form-control" name="date" value="{{ request('date') }}" onchange="this.form.submit()">
                            </div>
                            <div class="col-lg-3">
                                <select class="form-control" name="staff_id" onchange="this.form.submit()">
                                    <option value="">All Staff</option>
                                    @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ request('staff_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <a href="{{ route('roster.payroll.finance.reconciliation') }}" class="borderBtn w100 text-center" style="display: block; line-height: 34px;"><i class="bx bx-filter f18 me-2"></i> Reset Filters</a>
                            </div>
                        </div>
                    </form>
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
                                <div class="tab-search-wrapper">
                                    <div class="position-relative">
                                        <i class="bx bx-search search-icon-inner"></i>
                                        <input type="text" class="form-control inner-tab-search" placeholder="Search by staff name or date..." onkeyup="filterTabContent(this, '#collapse1')">
                                    </div>
                                </div>
                                @if ($matchedCount > 0)
                                <button class="bgBtn pgreenBtn" id="approve-all-matched">
                                    <i class="bx bx-check-double me-2"></i> Approve All Matched
                                </button>
                                @foreach ($shifts->where('reconciliation_status', 'Matched') as $shift)
                                <div class="recon-card" style="border-color: {{ $shift->shiftCategory->color ?? '#e2e8f0' }};">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="h5Head">{{ $shift->staff ? $shift->staff->name : 'Unknown Staff' }}</span>
                                                <span class="badge-soft badge-pending">pending</span>
                                                @if($shift->shiftCategory)
                                                <span class="badge-soft" style="background-color: {{ $shift->shiftCategory->color }}20; color: {{ $shift->shiftCategory->color }};">{{ $shift->shiftCategory->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn-adjust" data-toggle="modal" data-target="#adjustNodal-{{ $shift->id }}">
                                                <i class="bx bx-show"></i> Adjust
                                            </button>
                                            <button class="bgBtn pgreenBtn approve-shift-btn" data-id="{{ $shift->id }}">
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
                                    <i class="bx bx-alert-triangle f20 me-2" style="color: #f59e0b;"></i>
                                    Requires Adjustment ({{ $needsAdjustmentCount }})
                                    <i class="bx bx-chevron-down accIcon"></i>
                                </a>
                            </h4>
                        </div>
                        <div id="collapse2" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="tab-search-wrapper">
                                    <div class="position-relative">
                                        <i class="bx bx-search search-icon-inner"></i>
                                        <input type="text" class="form-control inner-tab-search" placeholder="Search by staff name or date..." onkeyup="filterTabContent(this, '#collapse2')">
                                    </div>
                                </div>
                                @if ($needsAdjustmentCount > 0)
                                @foreach ($shifts->where('reconciliation_status', 'Needs Adjustment') as $shift)
                                <div class="recon-card needs-adj" style="border-color: {{ $shift->shiftCategory->color ?? '#e2e8f0' }};">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="h5Head">{{ $shift->staff ? $shift->staff->name : 'Unknown Staff' }}</span>
                                                <span class="badge-soft badge-adj">requires adjustment</span>
                                                @if($shift->shiftCategory)
                                                <span class="badge-soft" style="background-color: {{ $shift->shiftCategory->color }}20; color: {{ $shift->shiftCategory->color }};">{{ $shift->shiftCategory->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn-adjust" data-toggle="modal" data-target="#adjustNodal-{{ $shift->id }}">
                                                <i class="bx bx-show"></i> Adjust
                                            </button>
                                            <button class="bgBtn pgreenBtn approve-shift-btn" data-id="{{ $shift->id }}">
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
                                <div class="tab-search-wrapper">
                                    <div class="position-relative">
                                        <i class="bx bx-search search-icon-inner"></i>
                                        <input type="text" class="form-control inner-tab-search" placeholder="Search by staff name or date..." onkeyup="filterTabContent(this, '#collapse3')">
                                    </div>
                                </div>
                                @if ($approvedCount > 0)
                                @foreach ($shifts->where('reconciliation_status', 'Approved') as $shift)
                                <div class="recon-card" style="border-color: {{ $shift->shiftCategory->color ?? '#e2e8f0' }};">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="h5Head">{{ $shift->staff ? $shift->staff->name : 'Unknown Staff' }}</span>
                                                <span class="badge-soft badge-approved">approved</span>
                                                @if($shift->shiftCategory)
                                                <span class="badge-soft" style="background-color: {{ $shift->shiftCategory->color }}20; color: {{ $shift->shiftCategory->color }};">{{ $shift->shiftCategory->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn-adjust" data-toggle="modal" data-target="#adjustNodal-{{ $shift->id }}">
                                                <i class="bx bx-edit"></i> Adjust
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
                                                    @if($shift->timesheet)
                                                    {{ $shift->timesheet->clock_in }} - {{ $shift->timesheet->clock_out }}
                                                    @elseif($shift->login_activities->count() > 0)
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

                                @foreach ($manual_timesheets as $m)
                                <div class="recon-card" style="border-color: {{ $m->category->color ?? '#e2e8f0' }};">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="h5Head">{{ $m->staff ? $m->staff->name : 'Unknown Staff' }}</span>
                                                <span class="badge-soft badge-approved">manual record (approved)</span>
                                                @if($m->category)
                                                <span class="badge-soft" style="background-color: {{ $m->category->color }}20; color: {{ $m->category->color }};">{{ $m->category->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn-adjust" data-toggle="modal" data-target="#adjustManualModal-{{ $m->id }}">
                                                <i class="bx bx-edit"></i> Adjust
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-2">
                                            <p class="data-label">Created Date</p>
                                            <p class="data-value">{{ $m->created_at->format('D, M d') }}</p>
                                        </div>
                                        <div class="col-lg-3">
                                            <p class="data-label">Clock Times</p>
                                            <p class="data-value">{{ $m->clock_in }} - {{ $m->clock_out }}</p>
                                        </div>
                                        <div class="col-lg-2">
                                            <p class="data-label">Planned</p>
                                            <p class="data-value">N/A</p>
                                        </div>
                                        <div class="col-lg-2">
                                            <p class="data-label">Actual</p>
                                            @php
                                            $duration = 0;
                                            if ($m->clock_in && $m->clock_out) {
                                            $in = \Carbon\Carbon::parse($m->clock_in);
                                            $out = \Carbon\Carbon::parse($m->clock_out);
                                            if ($out < $in) $out->addDay();
                                                $duration = $in->diffInMinutes($out);
                                                }
                                                @endphp
                                                <p class="data-value">{{ number_format($duration / 60, 2) }}h</p>
                                        </div>
                                        <div class="col-lg-3">
                                            <p class="data-label">Variance</p>
                                            <p class="data-value">N/A</p>
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

                    <!-- Panel 4: Unscheduled Logs -->
                    <div class="panel panel-default mt-4 payRollAcood p-0">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse4" class="lightPurpleBg collapsed">
                                    <i class="bx bx-time-five f20 me-2" style="color: #8b5cf6;"></i>
                                    Unscheduled Shifts ({{ count($unscheduled_logs) }})
                                    <i class="bx bx-chevron-down accIcon"></i>
                                </a>
                            </h4>
                        </div>
                        <div id="collapse4" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="tab-search-wrapper">
                                    <div class="position-relative">
                                        <i class="bx bx-search search-icon-inner"></i>
                                        <input type="text" class="form-control inner-tab-search" placeholder="Search by staff name or date..." onkeyup="filterTabContent(this, '#collapse4')">
                                    </div>
                                </div>
                                @if (count($unscheduled_logs) > 0)
                                @foreach ($unscheduled_logs as $log)
                                <div class="recon-card" style="border-left: 4px solid #8b5cf6;">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="h5Head">{{ $log->staff ? $log->staff->name : 'Unknown Staff' }}</span>
                                                <span class="badge-soft badge-standard">unscheduled log</span>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="bgBtn pgreenBtn approve-unscheduled-btn"
                                                data-staff-id="{{ $log->staff_id }}"
                                                data-date="{{ $log->start_date }}">
                                                <i class="bx bx-check-circle"></i> Approve
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <p class="data-label">Date</p>
                                            <p class="data-value">{{ \Carbon\Carbon::parse($log->start_date)->format('D, M d') }}</p>
                                        </div>
                                        <div class="col-lg-4">
                                            <p class="data-label">Clock Activity</p>
                                            <div class="d-flex align-items-center gap-2">
                                                <p class="data-value mb-0">
                                                    {{ $log->login_activities->count() }} Sessions Recorded
                                                </p>
                                                <i class="fa fa-eye fs18 text-purple" style="cursor:pointer;" data-toggle="modal" data-target="#logDetails-{{ $log->id }}"></i>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <p class="data-label">Scheduled</p>
                                            <p class="data-value">0.00h</p>
                                        </div>
                                        <div class="col-lg-3">
                                            <p class="data-label">Actual Worked</p>
                                            <p class="data-value text-purple">{{ number_format($log->actual_duration_minutes / 60, 2) }}h</p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <p class="textGray500 fs13 text-center py-5 mb-0">No unscheduled staff logs found.</p>
                                @endif
                            </div>
                        </div>
                    </div>
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
                <div class="modal-body heightScrollModal p24 modal-body-unset">
                    <div class="d-flex muteBg rounded5 p-4 bg-muted-box">
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
                        <form action="{{ route('timesheet.save') }}" method="POST">
                            @csrf
                            <input type="hidden" name="shift_id" value="{{ $shift->id }}">
                            <div class="row">
                                <div class="col-md-6 m-t-10">
                                    <label>Clock In</label>
                                    @php
                                    if ($shift->timesheet) {
                                    $firstIn = $shift->timesheet->clock_in;
                                    $lastOut = $shift->timesheet->clock_out;
                                    } else {
                                    $firstIn = $shift->login_activities->count() > 0 ? \Carbon\Carbon::parse($shift->login_activities->min('check_in_time'))->format('H:i') : '';
                                    $lastOut = ($shift->login_activities->count() > 0 && $shift->login_activities->max('check_out_time')) ? \Carbon\Carbon::parse($shift->login_activities->max('check_out_time'))->format('H:i') : '';
                                    }
                                    @endphp
                                    <input type="time" id="clock-in-{{ $shift->id }}" name="clock_in" value="{{ $firstIn }}" class="form-control" onchange="calculateAdjustedHours('{{ $shift->id }}')">
                                </div>
                                <div class="col-md-6 m-t-10">
                                    <label>Clock Out</label>
                                    <input type="time" id="clock-out-{{ $shift->id }}" name="clock_out" value="{{ $lastOut }}" class="form-control" onchange="calculateAdjustedHours('{{ $shift->id }}')">
                                </div>
                            </div>
                            <div class="appendContainer-{{ $shift->id }}">
                                <div class="flexBw mt20">
                                    <!-- <div>
                                        <h5 class="h5Head">Pay Adjustments </h5>
                                    </div>
                                    <div>
                                        <button class="borderBtn appendBtn" data-target=".appendContainer-{{ $shift->id }}"> <i class="bx bx-plus me-2"></i> Add Row</button>
                                    </div> -->
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
                                                <div class="deleteIcon flex1 deleteAppend delete-btn-trash">
                                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="mt20"> -->
                            <div>
                                <label>Adjustment Reason</label>
                                <textarea name="notes" rows="3" placeholder="Enter reason for adjustment..." class="form-control">{{ $shift->timesheet ? $shift->timesheet->notes : '' }}</textarea>
                            </div>
                            <!-- </div> -->
                            <div class="mt20 lightBorderp rounded8 calc-hours-box">
                                <div class="flexBw d-flex justify-content-between align-items-center">
                                    <h5 class="h6Head mb-0 text-blue-dark">Total Adjusted Hours</h5>
                                    <h3 class="my-0 font700 text-blue-dark" id="total-adjusted-{{ $shift->id }}">{{ number_format($shift->actual_duration_minutes / 60, 2) }}h</h3>
                                </div>
                            </div>
                    </div>
                </div>
                <div class="modal-footer d-flex gap-3">
                    <div class="dFlexGap w100 d-flex gap-3">
                        <div class="flex1 w-50">
                            <button type="button" class="btn-adjust w100" data-dismiss="modal">Cancel </button>
                        </div>
                        <div class="flex1 w-50">
                            <button type="submit" class="btn-approve w100"> <i class="bx bx-save f18 me-2"></i> Save Adjustment</button>
                        </div>
                    </div>
                </div>
                </form>
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

    <!-- unscheduled log details -->
    @foreach ($unscheduled_logs as $log)
    <div class="modal fade leaveCommunStyle" id="logDetails-{{ $log->id }}" tabindex="1" role="dialog" aria-hidden="true">
        <div class="modal-dialog pModalScroll">
            <div class="modal-content">
                <div class="modal-header p24">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Unscheduled Activity Logs - {{ $log->staff ? $log->staff->name : 'Unknown' }}</h4>
                </div>
                <div class="modal-body heightScrollModal p24" style="height: unset;">
                    <div class="mb-4">
                        <p class="fs14 font600 mb-1">Date: {{ \Carbon\Carbon::parse($log->start_date)->format('l, M d, Y') }}</p>
                        <p class="textGray mb-0">Total Time: {{ number_format($log->actual_duration_minutes / 60, 2) }}h</p>
                    </div>
                    <div class="scrollDailyCheck pe-3">
                        @foreach ($log->login_activities as $activity)
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
                                    <p class="fs13 textGray500 mb-1">Check In Reason:</p>
                                    <p class="fs13 blackText font600">{{ $activity->check_in_reason ?: 'N/A' }}</p>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="fs13 textGray500 mb-1">Check Out Reason:</p>
                                    <p class="fs13 blackText font600">{{ $activity->check_out_reason ?: 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    <!-- end unscheduled log details -->
</main>
<!-- append section -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        window.filterTabContent = function(input, collapseId) {
            const filter = input.value.toLowerCase();
            const cards = document.querySelectorAll(collapseId + ' .recon-card');

            cards.forEach(card => {
                const text = card.innerText.toLowerCase();
                if (text.indexOf(filter) > -1) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        }

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

    window.calculateAdjustedHours = function(shiftId) {
        let elClockIn = document.getElementById('clock-in-' + shiftId);
        let elClockOut = document.getElementById('clock-out-' + shiftId);
        let elTotal = document.getElementById('total-adjusted-' + shiftId);

        if (elClockIn && elClockOut && elTotal) {
            let clockIn = elClockIn.value;
            let clockOut = elClockOut.value;

            if (clockIn && clockOut) {
                let clockInTime = new Date('1970-01-01T' + clockIn + 'Z');
                let clockOutTime = new Date('1970-01-01T' + clockOut + 'Z');

                if (clockOutTime < clockInTime) {
                    clockOutTime.setDate(clockOutTime.getDate() + 1);
                }

                let diffMs = clockOutTime - clockInTime;
                let diffHrs = diffMs / (1000 * 60 * 60);

                let roundedHours = isNaN(diffHrs) ? '0.00' : diffHrs.toFixed(2);
                elTotal.innerText = roundedHours + 'h';
            } else {
                elTotal.innerText = '0.00h';
            }
        }
    };

    const addShiftOptions = @json($shift_options);

    window.filterAddShiftsByStaff = function(staffId) {
        const select = document.getElementById('add-shift-select');
        select.innerHTML = '<option value="">Select Shift</option>';

        const filtered = addShiftOptions.filter(s => s.staff_id === parseInt(staffId));
        filtered.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = `${s.date} (${s.time}) — ${s.category}`;
            select.appendChild(opt);
        });
    };
</script>

<div class="modal fade leaveCommunStyle" id="addTimesheetModal" tabindex="1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modalMd pModalScroll">
        <div class="modal-content">
            <div class="modal-header p24">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Create Manual Timesheet</h4>
            </div>
            <form action="{{ route('timesheet.save') }}" method="POST">
                @csrf
                <div class="modal-body heightScrollModal p24 modal-body-unset">
                    <div class="mb-3">
                        <label>Select Staff Member</label>
                        <select class="form-control" name="staff_id" required>
                            <option value="">Choose Staff...</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Shift Category</label>
                        <select class="form-control" name="category_id" required>
                            <option value="">Choose Category...</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label>Clock In</label>
                            <input type="time" name="clock_in" id="clock-in-manual" class="form-control" required onchange="calculateAdjustedHours('manual')">
                        </div>
                        <div class="col-md-6">
                            <label>Clock Out</label>
                            <input type="time" name="clock_out" id="clock-out-manual" class="form-control" required onchange="calculateAdjustedHours('manual')">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label>Reason / Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Explain the manual entry..."></textarea>
                    </div>

                    <div class="calc-hours-box">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="calc-hours-label">Calculated Hours</h5>
                            <h3 class="calc-hours-value" id="total-adjusted-manual">0.00h</h3>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p24">
                    <!-- <button type="submit" class="btn btn-primary btn-save-records">Save Records</button> -->
                    <button type="submit" class="btn allBtnUseColor btn-save-records">Save Records</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Manual Adjustment Modals -->
@foreach ($manual_timesheets as $m)
<div class="modal fade leaveCommunStyle" id="adjustManualModal-{{ $m->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modalMd pModalScroll">
        <div class="modal-content">
            <div class="modal-header p24">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Adjust Manual Timesheet</h4>
            </div>
            <form action="{{ route('timesheet.save') }}" method="POST">
                @csrf
                <input type="hidden" name="timesheet_id" value="{{ $m->id }}">
                <div class="modal-body heightScrollModal p24 modal-body-unset">
                    <div class="mb-3">
                        <label>Select Staff Member</label>
                        <select class="form-control" name="staff_id" required>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $m->staff_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Shift Category</label>
                        <select class="form-control" name="category_id" required>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $m->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label>Clock In</label>
                            <input type="time" name="clock_in" value="{{ $m->clock_in }}" class="form-control" required onchange="calculateAdjustedHours('manual-{{ $m->id }}')">
                        </div>
                        <div class="col-md-6">
                            <label>Clock Out</label>
                            <input type="time" name="clock_out" value="{{ $m->clock_out }}" class="form-control" required onchange="calculateAdjustedHours('manual-{{ $m->id }}')">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label>Adjustment Reason / Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ $m->notes }}</textarea>
                    </div>

                    <div class="calc-hours-box">
                        <div class="d-flex justify-content-between align-items-center">
                            @php
                            $in = \Carbon\Carbon::parse($m->clock_in);
                            $out = \Carbon\Carbon::parse($m->clock_out);
                            if ($out < $in) $out->addDay();
                                $currentDuration = $in->diffInMinutes($out);
                                @endphp
                                <h5 class="calc-hours-label">Calculated Hours</h5>
                                <h3 class="calc-hours-value" id="total-adjusted-manual-{{ $m->id }}">{{ number_format($currentDuration / 60, 2) }}h</h3>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p24">
                    <button type="submit" class="btn btn-primary btn-save-records">Update Record</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
    function calculateAdjustedHours(id) {
        let clockIn, clockOut, targetId;

        if (id === 'manual') {
            clockIn = document.getElementById('clock-in-manual').value;
            clockOut = document.getElementById('clock-out-manual').value;
            targetId = 'total-adjusted-manual';
        } else if (id.startsWith('manual-')) {
            // For adjusted manual records
            const form = event.target.closest('form');
            clockIn = form.querySelector('input[name="clock_in"]').value;
            clockOut = form.querySelector('input[name="clock_out"]').value;
            targetId = 'total-adjusted-' + id;
        } else {
            clockIn = document.getElementById('clock-in-' + id).value;
            clockOut = document.getElementById('clock-out-' + id).value;
            targetId = 'total-adjusted-' + id;
        }

        if (clockIn && clockOut) {
            let start = new Date("2000-01-01 " + clockIn);
            let end = new Date("2000-01-01 " + clockOut);

            if (end < start) {
                end.setDate(end.getDate() + 1);
            }

            let diff = (end - start) / (1000 * 60 * 60);
            document.getElementById(targetId).innerText = diff.toFixed(2) + "h";
        }
    }
    $(document).on('click', '.approve-shift-btn', function() {
        const shiftId = $(this).data('id');
        const btn = $(this);

        if (confirm('Are you sure you want to approve this shift?')) {
            btn.prop('disabled', true).html('<i class="bx bx-loader fs-18 bx-spin"></i>');

            $.ajax({
                url: "{{ route('timesheet.approve') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    shift_id: shiftId
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(response.message || 'Something went wrong');
                        console.error('Approve Error:', response);
                        btn.prop('disabled', false).html('<i class="bx bx-check-circle"></i> Approve');
                    }
                },
                error: function(xhr) {
                    let msg = 'Error approving shift';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toastr.error(msg);
                    console.error('AJAX Error:', xhr);
                    btn.prop('disabled', false).html('<i class="bx bx-check-circle"></i> Approve');
                }
            });
        }
    });

    $('#approve-all-matched').on('click', function() {
        const btn = $(this);
        const shiftIds = [];
        $('.approve-shift-btn').each(function() {
            shiftIds.push($(this).data('id'));
        });

        if (shiftIds.length === 0) {
            toastr.info('No shifts to approve');
            return;
        }

        if (confirm(`Are you sure you want to approve all ${shiftIds.length} matched shifts?`)) {
            btn.prop('disabled', true).html('<i class="bx bx-loader fs-18 bx-spin"></i> Approving All...');

            // We can either loop or send all IDs. Let's add a bulk method or just loop for now.
            // For simplicity and to avoid complexity in controller, I'll just use the same endpoint in a loop or send all.
            // Sending all is better.

            $.ajax({
                url: "{{ route('timesheet.approve') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    shift_id: shiftIds // Send array
                },
                success: function(response) {
                    toastr.success('Bulk approval processed');
                    setTimeout(() => location.reload(), 1500);
                },
                error: function() {
                    toastr.error('Error in bulk approval');
                    btn.prop('disabled', false).html('<i class="bx bx-check-double me-2"></i> Approve All Matched');
                }
            });
        }
    });

    $(document).on('click', '.approve-unscheduled-btn', function() {
        const staffId = $(this).data('staff-id');
        const date = $(this).data('date');
        const btn = $(this);

        if (confirm('Are you sure you want to approve this unscheduled work? It will be added to payroll.')) {
            btn.prop('disabled', true).html('<i class="bx bx-loader fs-18 bx-spin"></i>');

            $.ajax({
                url: "{{ route('timesheet.approve.unscheduled') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    staff_id: staffId,
                    date: date
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(response.message || 'Error occurred');
                        btn.prop('disabled', false).html('<i class="bx bx-check-circle"></i> Approve');
                    }
                },
                error: function(xhr) {
                    toastr.error('Failed to approve unscheduled work');
                    btn.prop('disabled', false).html('<i class="bx bx-check-circle"></i> Approve');
                }
            });
        }
    });
</script>
@endsection