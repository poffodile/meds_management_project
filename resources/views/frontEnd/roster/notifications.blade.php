@extends('frontEnd.layouts.master')
@section('title','Notifications')
@section('content')

@include('frontEnd.roster.common.roster_header')
<main class="page-content">
    <style>
        .notif-card { display:flex; align-items:flex-start; padding:12px 15px; border-bottom:1px solid #eee; transition:background 0.2s; }
        .notif-card:hover { background:#f9f9f9; }
        .notif-unread { background:#f0f7ff; border-left:3px solid #3498db; }
        .notif-read { background:#fff; border-left:3px solid transparent; opacity:0.75; }
        .notif-icon { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; flex-shrink:0; font-size:14px; }
        .notif-body { flex:1; padding:0 12px; min-width:0; }
        .notif-header { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:3px; }
        .notif-type { font-weight:600; font-size:13px; color:#333; }
        .notif-action { font-size:11px; color:#888; text-transform:uppercase; }
        .notif-time { font-size:11px; color:#999; margin-left:auto; }
        .notif-message { font-size:13px; color:#555; line-height:1.4; word-break:break-word; }
        .notif-actions { flex-shrink:0; padding-top:2px; }
    </style>
    <div class="container-fluid">

        <div class="topHeaderCont">
            <div>
                <h1>Notification Centre</h1>
                <p class="header-subtitle">View and manage all notifications</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" id="mark-all-read-btn"><i class="fa fa-check-circle"></i> Mark All as Read</button>
            </div>
        </div>

        <!-- Filters -->
        <div class="row" style="margin-bottom:15px;">
            <div class="col-md-3">
                <label>Event Type</label>
                <select id="filter-type" class="form-control">
                    <option value="">All Types</option>
                    @foreach($eventTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>From Date</label>
                <input type="date" id="filter-start-date" class="form-control">
            </div>
            <div class="col-md-3">
                <label>To Date</label>
                <input type="date" id="filter-end-date" class="form-control">
            </div>
            <div class="col-md-3" style="padding-top:24px;">
                <button class="btn btn-default" id="filter-btn"><i class="fa fa-filter"></i> Filter</button>
                <button class="btn btn-default" id="clear-filter-btn"><i class="fa fa-times"></i> Clear</button>
            </div>
        </div>

        <!-- Notification List -->
        <div id="notification-list">
            <div class="text-center" style="padding:40px;">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
                <p>Loading notifications...</p>
            </div>
        </div>

        <!-- Pagination -->
        <div id="notification-pagination" class="text-center" style="margin-top:15px;display:none;">
            <button class="btn btn-default" id="load-more-btn"><i class="fa fa-arrow-down"></i> Load More</button>
        </div>

        <script>
            var baseUrl = "{{ url('') }}";
            var csrfToken = "{{ csrf_token() }}";
        </script>
        <script src="{{ url('public/js/roster/notifications.js') }}"></script>

    </div>
    @endsection
</main>
</div>
