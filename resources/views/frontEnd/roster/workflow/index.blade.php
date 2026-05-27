@extends('frontEnd.layouts.master')
@section('title','Workflow Automation')
@section('content')

@include('frontEnd.roster.common.roster_header')
<main class="page-content">

<style>
    .wf-container { padding: 20px 30px; }
    .wf-title { font-size: 22px; font-weight: 600; color: #2c3e50; margin-bottom: 5px; }
    .wf-subtitle { color: #777; font-size: 14px; margin-bottom: 20px; }

    .wf-stats { display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; }
    .wf-stat-card {
        background: #fff; border-radius: 10px; padding: 18px 22px; min-width: 140px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1); text-align: center;
    }
    .wf-stat-val { font-size: 28px; font-weight: 700; color: #2c3e50; }
    .wf-stat-lbl { font-size: 12px; color: #999; font-weight: 600; text-transform: uppercase; margin-top: 4px; }
    .wf-stat-active .wf-stat-val { color: #27ae60; }
    .wf-stat-failed .wf-stat-val { color: #e74c3c; }

    .wf-toolbar { display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px; }
    .btn-new-wf {
        background: #3498db; color: #fff; border: none; border-radius: 8px;
        padding: 9px 20px; font-size: 14px; font-weight: 600; cursor: pointer;
    }
    .btn-new-wf:hover { background: #2980b9; color: #fff; }
    .btn-run-all {
        background: #27ae60; color: #fff; border: none; border-radius: 8px;
        padding: 9px 20px; font-size: 14px; font-weight: 600; cursor: pointer;
    }
    .btn-run-all:hover { background: #219a52; color: #fff; }
    .btn-run-all:disabled { opacity: 0.7; cursor: wait; }
    .wf-btn-run { color: #27ae60 !important; }
    .wf-btn-run:hover { background: #eafaf1 !important; }

    .wf-group-header {
        font-size: 14px; font-weight: 700; color: #777; text-transform: uppercase;
        letter-spacing: 0.5px; margin: 20px 0 10px; padding-bottom: 6px;
        border-bottom: 2px solid #e9ecef;
    }

    .wf-card {
        background: #fff; border-radius: 10px; padding: 16px 20px; margin-bottom: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 15px;
    }
    .wf-card.inactive { opacity: 0.55; }
    .wf-card-body { flex: 1; }
    .wf-card-name { font-size: 15px; font-weight: 600; color: #2c3e50; margin-bottom: 4px; }
    .wf-card-detail { font-size: 12px; color: #888; margin-bottom: 2px; }
    .wf-badge {
        display: inline-block; padding: 2px 8px; border-radius: 4px;
        font-size: 11px; font-weight: 600; color: #fff; margin-right: 4px;
    }
    .wf-badge-scheduled { background: #3498db; }
    .wf-badge-condition { background: #e67e22; }
    .wf-badge-event { background: #9b59b6; }
    .wf-badge-notification { background: #27ae60; }
    .wf-badge-email { background: #e84393; }
    .wf-badge-paused { background: #95a5a6; }
    .wf-badge-success { background: #27ae60; }
    .wf-badge-failed { background: #e74c3c; }

    .wf-card-actions { display: flex; gap: 6px; }
    .wf-btn-icon {
        width: 32px; height: 32px; border-radius: 6px; border: 1px solid #ddd;
        background: #fff; cursor: pointer; display: flex; align-items: center;
        justify-content: center; font-size: 14px; color: #555;
    }
    .wf-btn-icon:hover { background: #f0f0f0; }
    .wf-btn-icon.danger:hover { background: #fdeaea; color: #e74c3c; }

    .wf-empty {
        text-align: center; padding: 50px 20px; color: #999; font-size: 15px;
        background: #fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .wf-exec-section { margin-top: 30px; }
    .wf-exec-title { font-size: 16px; font-weight: 600; color: #2c3e50; margin-bottom: 12px; }
    .wf-exec-table {
        width: 100%; background: #fff; border-radius: 10px; overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .wf-exec-table th {
        background: #f8f9fa; padding: 10px 14px; font-size: 12px;
        font-weight: 600; color: #555; text-align: left;
    }
    .wf-exec-table td { padding: 10px 14px; font-size: 13px; color: #333; border-top: 1px solid #f0f0f0; }

    /* Modal overrides */
    #workflowModal .modal-header { border-bottom: 1px solid #e9ecef; }
    #workflowModal .modal-title { font-weight: 600; }
    #workflowModal .form-group { margin-bottom: 14px; }
    #workflowModal label { font-size: 13px; font-weight: 600; color: #555; margin-bottom: 4px; }
    #workflowModal .form-control { font-size: 13px; }
    .trigger-fields, .action-fields { display: none; }
    .trigger-fields.visible, .action-fields.visible { display: block; }
    .config-section { background: #f8f9fa; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
    .config-section-title { font-size: 13px; font-weight: 700; color: #555; margin-bottom: 10px; text-transform: uppercase; }

    .wf-loading { text-align: center; padding: 40px; color: #999; }

    /* Template Gallery */
    .tpl-header {
        display: flex; align-items: center; gap: 15px; cursor: pointer;
        padding: 14px 20px; background: #f0f7ff; border-radius: 10px 10px 0 0;
        border: 1px solid #d0e3f7; margin-bottom: 0;
    }
    .tpl-header-title { font-size: 16px; font-weight: 600; color: #2c3e50; }
    .tpl-header-sub { font-size: 13px; color: #777; flex: 1; }
    .tpl-toggle { font-size: 12px; color: #3498db; font-weight: 600; }
    .tpl-gallery {
        background: #f8fbff; border: 1px solid #d0e3f7; border-top: none;
        border-radius: 0 0 10px 10px; padding: 20px; margin-bottom: 25px;
    }
    .tpl-gallery.collapsed { display: none; }
    .tpl-cat-header {
        font-size: 13px; font-weight: 700; color: #777; text-transform: uppercase;
        letter-spacing: 0.5px; margin: 15px 0 8px; padding-bottom: 4px;
        border-bottom: 1px solid #e0e8f0;
    }
    .tpl-cat-header:first-child { margin-top: 0; }
    .tpl-card {
        display: flex; align-items: center; gap: 14px;
        background: #fff; border-radius: 8px; padding: 14px 18px; margin-bottom: 8px;
        border: 1px solid #e9ecef;
    }
    .tpl-card-icon {
        width: 38px; height: 38px; border-radius: 8px; background: #eef2ff;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; color: #3498db; flex-shrink: 0;
    }
    .tpl-card-body { flex: 1; }
    .tpl-card-name { font-size: 14px; font-weight: 600; color: #2c3e50; margin-bottom: 2px; }
    .tpl-card-desc { font-size: 12px; color: #888; margin-bottom: 4px; }
    .tpl-card-badges { display: flex; gap: 4px; }
    .btn-install {
        background: #3498db; color: #fff; border: none; border-radius: 6px;
        padding: 6px 16px; font-size: 12px; font-weight: 600; cursor: pointer;
        white-space: nowrap;
    }
    .btn-install:hover { background: #2980b9; }
    .btn-installed {
        background: #27ae60; color: #fff; border: none; border-radius: 6px;
        padding: 6px 16px; font-size: 12px; font-weight: 600; cursor: default;
        white-space: nowrap;
    }
</style>

<div class="wf-container">
    <div class="wf-title">Workflow Automation</div>
    <div class="wf-subtitle">Configure automated notifications and alerts</div>

    <div class="wf-stats" id="wf-stats">
        <div class="wf-stat-card wf-stat-active"><div class="wf-stat-val" id="stat-active">0</div><div class="wf-stat-lbl">Active</div></div>
        <div class="wf-stat-card"><div class="wf-stat-val" id="stat-total">0</div><div class="wf-stat-lbl">Total</div></div>
        <div class="wf-stat-card"><div class="wf-stat-val" id="stat-executed">0</div><div class="wf-stat-lbl">Executed Today</div></div>
        <div class="wf-stat-card wf-stat-failed"><div class="wf-stat-val" id="stat-failed">0</div><div class="wf-stat-lbl">Failed Today</div></div>
    </div>

    <div class="wf-toolbar">
        <button class="btn-run-all" id="btn-run-all" onclick="runAllWorkflows()"><i class="fa fa-play"></i> Run All Active</button>
        <button class="btn-new-wf" onclick="openCreateModal()">+ New Workflow</button>
    </div>

    <!-- Template Gallery -->
    <div id="template-gallery-section">
        <div class="tpl-header" onclick="toggleGallery()">
            <span class="tpl-header-title">Template Gallery</span>
            <span class="tpl-header-sub">Browse pre-built workflows — click Install to add</span>
            <span class="tpl-toggle" id="tpl-toggle-btn">&#9650; Hide</span>
        </div>
        <div id="tpl-gallery" class="tpl-gallery">
            <div class="wf-loading">Loading templates...</div>
        </div>
    </div>

    <div id="wf-list"><div class="wf-loading">Loading workflows...</div></div>

    <div class="wf-exec-section">
        <div class="wf-exec-title">Recent Executions</div>
        <div id="wf-exec-list"><div class="wf-loading">Loading...</div></div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="workflowModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalTitle">New Workflow</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="wf-id">
                <div class="form-group">
                    <label>Workflow Name</label>
                    <input type="text" class="form-control" id="wf-name" maxlength="255" placeholder="e.g. Unfilled Shift Alert">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select class="form-control" id="wf-category">
                        <option value="scheduling">Scheduling</option>
                        <option value="compliance">Compliance</option>
                        <option value="clinical">Clinical</option>
                        <option value="training">Training</option>
                        <option value="hr">HR</option>
                        <option value="engagement">Engagement</option>
                        <option value="reporting">Reporting</option>
                    </select>
                </div>

                <div class="config-section">
                    <div class="config-section-title">Trigger</div>
                    <div class="form-group">
                        <label>Trigger Type</label>
                        <select class="form-control" id="wf-trigger-type" onchange="showTriggerFields()">
                            <option value="scheduled">Scheduled (time-based)</option>
                            <option value="condition">Condition (data threshold)</option>
                            <option value="event">Event (state check)</option>
                        </select>
                    </div>

                    <div class="trigger-fields" id="tf-scheduled">
                        <div class="form-group">
                            <label>Frequency</label>
                            <select class="form-control" id="tf-frequency">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Day (0=Sun for weekly, 1-28 for monthly, leave blank for daily)</label>
                            <input type="number" class="form-control" id="tf-day" min="0" max="28" placeholder="Optional">
                        </div>
                        <div class="form-group">
                            <label>Time (HH:MM)</label>
                            <input type="time" class="form-control" id="tf-time" value="08:00">
                        </div>
                    </div>

                    <div class="trigger-fields" id="tf-condition">
                        <div class="form-group">
                            <label>Entity</label>
                            <select class="form-control" id="tf-cond-entity">
                                <option value="incidents">Incidents</option>
                                <option value="training">Training</option>
                                <option value="shifts">Shifts</option>
                                <option value="medication">Medication</option>
                                <option value="feedback">Feedback</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Condition</label>
                            <select class="form-control" id="tf-cond-condition">
                                <option value="count_exceeds">Count Exceeds</option>
                                <option value="days_since">Days Since</option>
                                <option value="status_is">Status Is</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Threshold</label>
                            <input type="number" class="form-control" id="tf-cond-threshold" min="0" value="5">
                        </div>
                        <div class="form-group">
                            <label>Lookback Days</label>
                            <input type="number" class="form-control" id="tf-cond-lookback" min="1" max="365" value="7">
                        </div>
                    </div>

                    <div class="trigger-fields" id="tf-event">
                        <div class="form-group">
                            <label>Entity</label>
                            <select class="form-control" id="tf-evt-entity">
                                <option value="incidents">Incidents</option>
                                <option value="training">Training</option>
                                <option value="shifts">Shifts</option>
                                <option value="medication">Medication</option>
                                <option value="feedback">Feedback</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <input type="text" class="form-control" id="tf-evt-status" placeholder="e.g. unfilled, new, 0" maxlength="30">
                        </div>
                        <div class="form-group">
                            <label>Min Count</label>
                            <input type="number" class="form-control" id="tf-evt-min" min="1" value="1">
                        </div>
                    </div>
                </div>

                <div class="config-section">
                    <div class="config-section-title">Action</div>
                    <div class="form-group">
                        <label>Action Type</label>
                        <select class="form-control" id="wf-action-type" onchange="showActionFields()">
                            <option value="send_notification">Send Notification (in-app)</option>
                            <option value="send_email">Send Email</option>
                        </select>
                    </div>

                    <div class="action-fields" id="af-notification">
                        <div class="form-group">
                            <label>Message</label>
                            <textarea class="form-control" id="af-notif-message" rows="3" maxlength="1000" placeholder="Notification message..."></textarea>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" id="af-notif-sticky"> Sticky notification (stays until dismissed)</label>
                        </div>
                    </div>

                    <div class="action-fields" id="af-email">
                        <div class="form-group">
                            <label>Recipients (comma-separated, max 5)</label>
                            <input type="text" class="form-control" id="af-email-recipients" maxlength="500" placeholder="admin@care.com, manager@care.com">
                        </div>
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" class="form-control" id="af-email-subject" maxlength="255" placeholder="Alert Subject">
                        </div>
                        <div class="form-group">
                            <label>Message</label>
                            <textarea class="form-control" id="af-email-message" rows="3" maxlength="2000" placeholder="Email body..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Cooldown Hours (min hours between trigger fires)</label>
                    <input type="number" class="form-control" id="wf-cooldown" min="1" max="168" value="24">
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" id="wf-active" checked> Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveWorkflow()">Save Workflow</button>
            </div>
        </div>
    </div>
</div>

</main>

<script>var baseUrl = "{{ url('') }}";</script>
<script src="{{ url('public/js/roster/workflows.js') }}"></script>

@endsection
