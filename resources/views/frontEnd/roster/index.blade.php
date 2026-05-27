@extends('frontEnd.layouts.master')
@section('title','Roster Management')
@section('content')


@include('frontEnd.roster.common.roster_header')
<!-- sidebar-wrapper  -->
<main class="page-content">
    <div class="container-fluid">

        <div class="topHeaderCont">
            <div>
                <h1>Dashboard</h1>
                <p class="header-subtitle">Your care management system</p>
            </div>
            <div class="header-actions">
                <!-- <button class="btn">⚙️ Customize</button> -->
                <!-- <button class="btn btn-primary">📊 Publish</button> -->
            </div>
        </div>


        <!-- Alerts -->
        @php
        $totalCriticalAlertsCount = count($missed_shifts) + count($unfilled_shifts_alerts) + count($critical_alerts);
        $alertIndex = 0;
        @endphp

        <div class="rota_alerts" id="alertsContainer">
            @if($totalCriticalAlertsCount > 0)
            {{-- Missed Shifts --}}
            @foreach ($missed_shifts as $shift)
            <div class="rota_alert {{ $alertIndex >= 3 ? 'extra-alert' : '' }}" @if($alertIndex>= 3) style="display: none;" @endif>
                <div class="rota_alert-icon"><i class="fa fa-exclamation-circle"></i></div>
                <div class="rota_alert-content">
                    <div class="rota_alert-title">{{ $shift->client->name ?? 'Unknown Client' }} - MISSED SHIFT</div>
                    <div class="rota_alert-description">
                        The shift scheduled for {{ date('d M, Y', strtotime($shift->start_date)) }} at {{ date('H:i', strtotime($shift->start_time)) }} was missed.
                    </div>
                    <div class="rota_alert-bottmDescription">
                        <i class="fa fa-clock-o"></i> Missed at: {{ date('h:i A', strtotime($shift->start_time)) }}
                    </div>
                </div>
                <div class="rota_alert-badge">Critical</div>
            </div>
            @php $alertIndex++; @endphp
            @endforeach

            {{-- Unassigned Shifts --}}
            @foreach ($unfilled_shifts_alerts as $shift)
            <div class="rota_alert {{ $alertIndex >= 3 ? 'extra-alert' : '' }}" @if($alertIndex>= 3) style="display: none;" @endif>
                <div class="rota_alert-icon"><i class="fa fa-user-times"></i></div>
                <div class="rota_alert-content">
                    <div class="rota_alert-title">{{ $shift->client->name ?? 'Unknown Client' }} - UNASSIGNED SHIFT</div>
                    <div class="rota_alert-description">
                        No carer assigned for shift starting in less than 24 hours: {{ date('d M, Y', strtotime($shift->start_date)) }}
                    </div>
                    <div class="rota_alert-bottmDescription">
                        <i class="fa fa-clock-o"></i> Starts at: {{ date('h:i A', strtotime($shift->start_time)) }}
                    </div>
                </div>
                <div class="rota_alert-badge">Unassigned</div>
            </div>
            @php $alertIndex++; @endphp
            @endforeach

            {{-- Custom Critical Alerts --}}
            @foreach ($critical_alerts as $alert)
            <div class="rota_alert {{ $alertIndex >= 3 ? 'extra-alert' : '' }}" @if($alertIndex>= 3) style="display: none;" @endif>
                <div class="rota_alert-icon"><i class="fa fa-warning"></i></div>
                <div class="rota_alert-content">
                    <div class="rota_alert-title">{{ $alert->client->name ?? 'Unknown Client' }} - {{ $alert->alert_title }}</div>
                    <div class="rota_alert-description">
                        {{ $alert->description }}
                    </div>
                    <div class="rota_alert-bottmDescription">
                        <i class="fa fa-info-circle"></i> Critical Alert recorded on {{ date('d M, Y', strtotime($alert->created_at)) }}
                    </div>
                </div>
                <div class="rota_alert-badge">Critical</div>
            </div>
            @php $alertIndex++; @endphp
            @endforeach
            @else
            <div class="rota_alert">
                <div class="rota_alert-content">
                    <div class="rota_alert-title">No critical alerts found</div>
                </div>
            </div>
            @endif

            @if($totalCriticalAlertsCount > 3)
            <div class="rota_view-all" id="viewAllAlerts" style="cursor: pointer;" onclick="toggleAlerts()">View All ({{ $totalCriticalAlertsCount }}) →</div>
            @endif
        </div>

        <script>
            function toggleAlerts() {
                const extras = document.querySelectorAll('#alertsContainer .extra-alert');
                const btn = document.getElementById('viewAllAlerts');
                let isHidden = true;

                if (extras.length > 0) {
                    isHidden = extras[0].style.display === 'none';
                }

                extras.forEach(item => {
                    item.style.display = isHidden ? 'flex' : 'none';
                });

                if (isHidden) {
                    btn.innerText = 'Show Less ↑';
                } else {
                    btn.innerText = 'View All ({{ count($scheduled_shifts) }}) →';
                }
            }
        </script>


        <!-- Smart Automation -->
        <!-- <div class="smart-automation">
            <div class="smart-automation-content">
                <div class="smart-automation-title">
                    ✨ Smart Automation Available
                    <span class="smart-automation-badge">BETA</span>
                </div>
                <div class="smart-automation-description">
                    AI capability speed availability setup<br>
                    Get an instance matching forever group scheduling
                </div>
            </div>
            <button class="btn-white">🔌 Auto-Fill</button>
        </div> -->
        <!-- Smart Automation -->

        <div class="rota_dashboard-cards">
            <a href="{{ url('roster/carer') }}" class="rota_dash-card blue" style="text-decoration: none; color: inherit;">
                <div class="rota_dash-left">
                    <p class="rota_title">Active Carers</p>
                    <h2 class="rota_count">{{ $userCount }}</h2>
                </div>
                <div class="rota_dash-icon">
                    <i class="fa fa-users"></i>
                </div>
            </a>

            <a href="{{ url('roster/client') }}" class="rota_dash-card green" style="text-decoration: none; color: inherit;">
                <div class="rota_dash-left">
                    <p class="rota_title">Active Clients</p>
                    <h2 class="rota_count">{{ $serviceUserCount }}</h2>
                </div>
                <div class="rota_dash-icon">
                    <i class="fa fa-user"></i>
                </div>
            </a>

            <a href="{{ url('roster/schedule-shift') }}" class="rota_dash-card purple" style="text-decoration: none; color: inherit;">
                <div class="rota_dash-left">
                    <p class="rota_title">Today's Shifts</p>
                    <h2 class="rota_count">{{ $today_shifts->count() }}</h2>
                </div>
                <div class="rota_dash-icon">
                    <i class="fa fa-calendar"></i>
                </div>
            </a>

            <a href="{{ url('roster/schedule-shift') }}" class="rota_dash-card orangeClr" style="text-decoration: none; color: inherit;">
                <div class="rota_dash-left">
                    <p class="rota_title">Unfilled Shifts</p>
                    <h2 class="rota_count">{{ $unfilled_shifts_count }}</h2>
                </div>
                <div class="rota_dash-icon">
                    <i class="fa fa-exclamation-circle"></i>
                </div>
            </a>
        </div>

        <!-- Smart Suggestions -->
        <!-- <div class="suggestions sectionWhiteBgAllUse">
            <div class="suggestions-header">
                💡 Smart Suggestions <span style="background-color: #2d3748; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px;">5</span>
            </div>

            <div class="suggestion-card">
                <div class="suggestion-icon" style="background-color: #ea580c;">📊</div>
                <div class="suggestionRightCont">
                    <div class="suggestion-title"> Unfilled Shifts This Week</div>
                    <div class="suggestion-description">
                        There's 419 unfilled carer assignments from 14-20 for suggestions to auto-assign.<br>
                        <strong>+2,500 more carers</strong>
                    </div>
                    <div class="suggestion-actions">
                        <button class="suggestion_btn-small suggestion_btn-orange">Auto-Assign →</button>
                        <button class="suggestion_btn-small suggestion_btn-outline">Use AI</button>
                    </div>
                </div>
            </div>

            <div class="suggestion-card">
                <div class="suggestion-icon" style="background-color: #ea580c;">⚠️</div>
                <div class="suggestionRightCont">
                    <div class="suggestion-title"> Carer Workload Alert</div>
                    <div class="suggestion-description">
                        Sarah-Brown is now working 50 consecutive days. Consider redistributing their shifts to avoid burnout.
                    </div>
                    <div class="suggestion-actions">
                        <button class="suggestion_btn-small suggestion_btn-orange">View Schedule →</button>
                    </div>
                </div>
            </div>

            <div class="suggestion-card blue">
                <div class="suggestion-icon" style="background-color: #4299e1;">💬</div>
                <div class="suggestionRightCont">
                    <div class="suggestion-title"> Pending Leave Request</div>
                    <div class="suggestion-description">
                        3 care workers are waiting for time approvals. Please and approve to the their plan.
                    </div>
                    <div class="suggestion-actions">
                        <button class="suggestion_btn-small suggestion_btn-blue">Review Requests →</button>
                    </div>
                </div>
            </div>

            <div class="suggestion-card purple">
                <div class="suggestion-icon" style="background-color: #9f7aea;">📊</div>
                <div class="suggestionRightCont">
                    <div class="suggestion-title"> Imbalanced Shift Distribution</div>
                    <div class="suggestion-description">
                        Patterns are enabling shifts are significantly underfilled. Consider onboarding by better with this balance.
                    </div>
                    <div class="suggestion-actions">
                        <button class="suggestion_btn-small suggestion_btn-purple">View Analytics →</button>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- Smart Suggestions -->

        {{-- SOS Alert Trigger --}}
        <div class="sectionWhiteBgAllUse" style="background: #d9534f !important; border: none; text-align: center; padding: 15px;">
            <button type="button" id="sos-trigger-btn" class="btn btn-lg" style="background: #fff; color: #d9534f; font-weight: bold; font-size: 18px; padding: 10px 40px; border-radius: 4px;">
                <i class="fa fa-exclamation-triangle"></i> SOS ALERT
            </button>
            <p style="color: #fff; margin: 8px 0 0; font-size: 13px;">Press to alert all managers immediately</p>
        </div>

        {{-- SOS Alert History --}}
        <div class="sectionWhiteBgAllUse">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fa fa-exclamation-triangle" style="color: #d9534f;"></i> SOS Alert History
                    <span id="sos-active-count" class="badge" style="background: #d9534f; color: #fff; margin-left: 5px;"></span>
                </h2>
            </div>
            <div id="sos-alerts-container">
                <p class="text-muted text-center">Loading alerts...</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions sectionWhiteBgAllUse">
            <div class="section-header">
                <h2 class="section-title">Quick Actions</h2>
            </div>

            @if($unfilled_shifts_count > 0)
            <div class="quick_alert-notice">
                ⚠️ <strong>Attention Required</strong> - {{ $unfilled_shifts_count }} unfilled shifts need assignment
            </div>
            @endif

            <div class="quick_actions-grid">
                <a href="{{ url('roster/schedule-shift') }}">
                    <div class="quick_action-card">
                        <div class="quick_action-icon" style="background-color: #4299e1; color: white;">➕</div>
                        <div class="quick_action-label"> Create Shift</div>
                    </div>
                </a>

                <a href="{{ url('roster/carer') }}">
                    <div class="quick_action-card">
                        <div class="quick_action-icon" style="background-color: #48bb78; color: white;">👤</div>
                        <div class="quick_action-label"> Add Carer</div>
                    </div>
                </a>

                <a href="{{ url('roster/client') }}">
                    <div class="quick_action-card">
                        <div class="quick_action-icon" style="background-color: #9f7aea; color: white;">🏠</div>
                        <div class="quick_action-label"> Add Client</div>
                    </div>
                </a>

                <a href="{{ url('roster/leave-request') }}">
                    <div class="quick_action-card">
                        <div class="quick_action-icon" style="background-color: #fc8181; color: white;">📄</div>
                        <div class="quick_action-label"> Leave Requests ({{ $pendingLeaveCount }})</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity sectionWhiteBgAllUse">
            <div class="section-header">
                <h2 class="section-title">Recent Activity</h2>
            </div>

            @forelse ($scheduled_shifts as $shift)
            <div class="activity-item">
                <div class="activity-icon">S</div>
                <div class="activity-content">
                    <div class="activity-title">New Shift Created</div>
                    <div class="activity-description">{{ $shift->client_name ?? 'Unknown' }} → {{ $shift->staff_name ?? 'Unassigned' }}</div>
                    <div class="activity-time">{{ date('M d, Y at h:i A', strtotime($shift->created_at)) }}</div>
                </div>
                <div class="activity-status"><a href="{{ url('roster/schedule-shift') }}"> {{ $shift->staff_id ? 'scheduled' : 'unfilled' }}</a></div>
            </div>
            @empty
            <div class="activity-item">
                <div class="activity-content">
                    <div class="activity-title">No recent activity</div>
                </div>
            </div>
            @endforelse
        </div>

        <div class="sectionWhiteBgAllUse">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="section-title">Today's Shifts</h2>
                <a href="{{ url('roster/schedule-shift') }}" class="view-full-schedule" style="color: #4299e1; font-weight: 600; font-size: 14px; text-decoration: none;">View Full Schedule →</a>
            </div>
            <div class="">
                @forelse ($scheduled_shifts as $shift)
                <div class="todayShiftsList {{ !$loop->first ? 'm-t-15' : '' }}">
                    <div class="siftTime">
                        <div class="siftTimeCont">
                            <i class="fa fa-clock-o"></i>
                            <span><strong>{{ date('H:i', strtotime($shift->start_time)) }} - {{ date('H:i', strtotime($shift->end_time)) }}</strong></span>
                        </div>
                        <div class="unfilledbtn">{{ $shift->staff_id ? 'scheduled' : 'unfilled' }}</div>
                    </div>
                    <div class="siftTime">
                        <div class="siftTimeCont">
                            <i class="fa fa-user-o"></i>
                            <span>Carer: <strong> {{ $shift->staff_name ?? 'Unassigned' }}</strong></span>
                        </div>
                    </div>
                    <div class="siftTime">
                        <div class="siftTimeCont">
                            <i class="fa  fa-map-marker"></i>
                            <span>Client: <strong> {{ $shift->client_name ?? 'Unknown Client' }}</strong></span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="todayShiftsList">
                    <p>No shifts scheduled for today.</p>
                </div>
                @endforelse
            </div>
        </div>


    </div>

    {{-- SOS Trigger Modal --}}
    <div class="modal fade" id="sosModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: #d9534f; color: #fff;">
                    <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Send SOS Alert</h4>
                    <button type="button" class="close" data-dismiss="modal" style="color: #fff;"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p><strong>This will alert all managers immediately.</strong></p>
                    <div class="form-group">
                        <label for="sos-message">What's the emergency? (optional)</label>
                        <textarea id="sos-message" class="form-control" rows="3" maxlength="2000" placeholder="Describe the emergency..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" id="sos-confirm-btn" class="btn btn-danger"><i class="fa fa-exclamation-triangle"></i> SEND SOS</button>
                </div>
            </div>
        </div>
    </div>

    {{-- SOS Resolve Modal --}}
    <div class="modal fade" id="sosResolveModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: #5cb85c; color: #fff;">
                    <h4 class="modal-title"><i class="fa fa-check"></i> Resolve SOS Alert</h4>
                    <button type="button" class="close" data-dismiss="modal" style="color: #fff;"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="resolve-notes">Resolution notes (optional)</label>
                        <textarea id="resolve-notes" class="form-control" rows="3" maxlength="2000" placeholder="How was this resolved?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="resolve-alert-id" value="">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" id="resolve-confirm-btn" class="btn btn-success"><i class="fa fa-check"></i> Resolve</button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="sos-user-type" value="{{ Auth::user()->user_type }}">
    <script>
        var baseUrl = "{{ url('') }}";
    </script>
    <script src="{{ url('public/js/roster/sos_alerts.js') }}"></script>

    @endsection
</main>
<!-- page-content" -->
</div> <!-- page-wrapper No remove this div-->