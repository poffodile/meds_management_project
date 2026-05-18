@extends('frontEnd.layouts.master')
@section('title','Safeguarding Referrals')
@section('content')

@include('frontEnd.roster.common.roster_header')
<main class="page-content">
    <style>
        .sg-badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; }
        .sg-badge-low { background:#d4edda; color:#155724; }
        .sg-badge-medium { background:#fff3cd; color:#856404; }
        .sg-badge-high { background:#ffe0cc; color:#c35a00; }
        .sg-badge-critical { background:#f8d7da; color:#721c24; }
        .sg-badge-reported { background:#cce5ff; color:#004085; }
        .sg-badge-under_investigation { background:#fff3cd; color:#856404; }
        .sg-badge-safeguarding_plan { background:#e8d5f5; color:#6f42c1; }
        .sg-badge-closed { background:#e2e3e5; color:#383d41; }
        .sg-card { background:#fff; border:1px solid #e3e6ea; border-radius:8px; padding:16px; margin-bottom:12px; cursor:pointer; transition:box-shadow 0.2s; }
        .sg-card:hover { box-shadow:0 2px 8px rgba(0,0,0,0.08); }
        .sg-ref { font-weight:700; color:#333; font-size:14px; }
        .sg-meta { font-size:12px; color:#888; margin-top:4px; }
        .sg-type-tags { margin-top:6px; }
        .sg-type-tag { display:inline-block; padding:2px 8px; border-radius:4px; font-size:11px; background:#f0f0f0; color:#555; margin-right:4px; margin-bottom:4px; }
        .sg-detail-section { margin-bottom:20px; }
        .sg-detail-section h5 { font-size:14px; font-weight:700; color:#333; border-bottom:1px solid #eee; padding-bottom:6px; margin-bottom:10px; }
        .sg-detail-row { display:flex; margin-bottom:6px; }
        .sg-detail-label { width:180px; font-size:13px; color:#888; flex-shrink:0; }
        .sg-detail-value { font-size:13px; color:#333; flex:1; }
        .sg-ongoing { display:inline-block; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600; background:#f8d7da; color:#721c24; }
        .sg-empty { text-align:center; padding:60px 20px; color:#999; }
        .sg-empty i { font-size:48px; margin-bottom:12px; display:block; color:#ddd; }
        .sg-form-section { margin-bottom:20px; padding:15px; background:#f9f9fb; border-radius:6px; }
        .sg-form-section h5 { font-size:14px; font-weight:600; margin-bottom:12px; color:#333; }
        .sg-witness-row { border:1px solid #e3e6ea; border-radius:6px; padding:10px; margin-bottom:8px; background:#fff; }
        .topHeaderCont { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .topHeaderCont h1 { margin:0; font-size:22px; }
        .header-subtitle { margin:0; font-size:13px; color:#888; }
    </style>

    <div class="container-fluid">
        <div class="topHeaderCont">
            <div>
                <h1>Safeguarding Referrals</h1>
                <p class="header-subtitle">Raise and track safeguarding concerns</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-danger" id="btn-new-referral"><i class="fa fa-plus"></i> Raise Concern</button>
            </div>
        </div>

        <!-- Filters -->
        <div class="row" style="margin-bottom:15px;">
            <div class="col-md-3">
                <label>Status</label>
                <select id="filter-status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="reported">Reported</option>
                    <option value="under_investigation">Under Investigation</option>
                    <option value="safeguarding_plan">Safeguarding Plan</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Risk Level</label>
                <select id="filter-risk" class="form-control">
                    <option value="">All Risk Levels</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Search</label>
                <input type="text" id="filter-search" class="form-control" placeholder="Search by reference, details, location...">
            </div>
            <div class="col-md-2" style="padding-top:24px;">
                <button class="btn btn-default" id="btn-filter"><i class="fa fa-filter"></i> Filter</button>
                <button class="btn btn-default" id="btn-clear-filter"><i class="fa fa-times"></i> Clear</button>
            </div>
        </div>

        <!-- Referral List -->
        <div id="sg-list">
            <div class="text-center" style="padding:40px;">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
            </div>
        </div>

        <!-- Pagination -->
        <div id="sg-pagination" style="text-align:center;margin-top:10px;"></div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="sg-form-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="sg-form-title">Raise Safeguarding Concern</h4>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" style="max-height:70vh;overflow-y:auto;">
                    <form id="sg-form">
                        @csrf
                        <input type="hidden" id="sg-edit-id" value="">

                        <!-- Section 1: Concern Details -->
                        <div class="sg-form-section">
                            <h5><i class="fa fa-exclamation-triangle"></i> Concern Details</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date &amp; Time of Concern <span class="text-danger">*</span></label>
                                        <input type="datetime-local" id="sg-date-of-concern" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Location of Incident</label>
                                        <input type="text" id="sg-location" class="form-control" maxlength="500" placeholder="Where did it happen?">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Details of Concern <span class="text-danger">*</span></label>
                                <textarea id="sg-details" class="form-control" rows="4" maxlength="5000" required placeholder="Describe what happened, what was observed or reported..."></textarea>
                            </div>
                            <div class="form-group">
                                <label>Immediate Action Taken</label>
                                <textarea id="sg-immediate-action" class="form-control" rows="3" maxlength="5000" placeholder="What steps were taken immediately?"></textarea>
                            </div>
                        </div>

                        <!-- Section 2: Classification -->
                        <div class="sg-form-section">
                            <h5><i class="fa fa-tags"></i> Classification</h5>
                            <div class="form-group">
                                <label>Safeguarding Type(s) <span class="text-danger">*</span></label>
                                <div id="sg-type-checkboxes">
                                    @foreach($safeguardingTypes as $type)
                                        <label style="display:inline-block;margin-right:12px;font-weight:normal;">
                                            <input type="checkbox" name="safeguarding_type[]" value="{{ $type }}"> {{ $type }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Risk Level <span class="text-danger">*</span></label>
                                        <select id="sg-risk-level" class="form-control" required>
                                            <option value="">Select...</option>
                                            <option value="low">Low</option>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                            <option value="critical">Critical</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ongoing Risk?</label>
                                        <select id="sg-ongoing-risk" class="form-control">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: People Involved -->
                        <div class="sg-form-section">
                            <h5><i class="fa fa-users"></i> People Involved</h5>
                            <div class="form-group">
                                <label>Alleged Perpetrator</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="text" id="sg-perp-name" class="form-control" placeholder="Name" maxlength="200">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" id="sg-perp-relationship" class="form-control" placeholder="Relationship" maxlength="200">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" id="sg-perp-details" class="form-control" placeholder="Details" maxlength="500">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Witnesses</label>
                                <div id="sg-witnesses-container"></div>
                                <button type="button" class="btn btn-sm btn-default" id="btn-add-witness"><i class="fa fa-plus"></i> Add Witness</button>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Capacity to Make Decisions?</label>
                                        <select id="sg-capacity" class="form-control">
                                            <option value="">Unknown</option>
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Client Wishes</label>
                                <textarea id="sg-client-wishes" class="form-control" rows="2" maxlength="5000" placeholder="What does the person at risk want to happen?"></textarea>
                            </div>
                        </div>

                        <!-- Section 4: Notifications -->
                        <div class="sg-form-section">
                            <h5><i class="fa fa-bell"></i> Multi-Agency Notifications</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><input type="checkbox" id="sg-police-notified"> Police Notified</label>
                                        <input type="text" id="sg-police-ref" class="form-control" placeholder="Reference" maxlength="100" style="display:none;margin-top:6px;">
                                        <input type="datetime-local" id="sg-police-date" class="form-control" style="display:none;margin-top:6px;">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><input type="checkbox" id="sg-la-notified"> Local Authority Notified</label>
                                        <input type="text" id="sg-la-ref" class="form-control" placeholder="Reference" maxlength="100" style="display:none;margin-top:6px;">
                                        <input type="datetime-local" id="sg-la-date" class="form-control" style="display:none;margin-top:6px;">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><input type="checkbox" id="sg-cqc-notified"> CQC Notified</label>
                                        <input type="datetime-local" id="sg-cqc-date" class="form-control" style="display:none;margin-top:6px;">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><input type="checkbox" id="sg-family-notified"> Family Notified</label>
                                        <textarea id="sg-family-details" class="form-control" rows="2" maxlength="2000" placeholder="Details..." style="display:none;margin-top:6px;"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><input type="checkbox" id="sg-advocate-involved"> Advocate Involved</label>
                                        <textarea id="sg-advocate-details" class="form-control" rows="2" maxlength="2000" placeholder="Details..." style="display:none;margin-top:6px;"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="btn-save-referral"><i class="fa fa-save"></i> Save Referral</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="sg-detail-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="sg-detail-title">Referral Details</h4>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" id="sg-detail-body" style="max-height:70vh;overflow-y:auto;"></div>
                <div class="modal-footer" id="sg-detail-footer"></div>
            </div>
        </div>
    </div>
</main>

<script src="{{ url('public/js/roster/safeguarding.js') }}"></script>
@endsection
