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
        <div class="rota_alerts" id="alertsContainer">
            @forelse ($scheduled_shifts as $shift)
            <div class="rota_alert {{ $loop->index >= 3 ? 'extra-alert' : '' }}" @if($loop->index >= 3) style="display: none;" @endif>
                <div class="rota_alert-icon"><i class="fa fa fa-calendar-o"></i></div>
                <div class="rota_alert-content">
                    <div class="rota_alert-title">{{ $shift->client_name ?? 'Unknown Client' }} - {{ date('H:i', strtotime($shift->start_time)) }}</div>
                    <div class="rota_alert-description">
                        Assigned to: {{ $shift->staff_name ?? 'Unassigned' }} |
                        Shift Type: {{ ucfirst($shift->shift_type) }} |
                        Date: {{ date('M d, Y', strtotime($shift->start_date)) }}
                    </div>
                    <div class="rota_alert-bottmDescription">
                        <i class="fa fa-clock-o"></i> {{ date('h:i A', strtotime($shift->start_time)) }} - {{ date('h:i A', strtotime($shift->end_time)) }}
                        @if($shift->notes)
                        <br><i class="fa fa-info-circle"></i> {{ $shift->notes }}
                        @endif
                    </div>
                </div>
                <div class="rota_alert-badge">New</div>
            </div>
            @empty
            <div class="rota_alert">
                <div class="rota_alert-content">
                    <div class="rota_alert-title">No shifts found</div>
                </div>
            </div>
            @endforelse

            @if(count($scheduled_shifts) > 3)
            <div class="rota_view-all" id="viewAllAlerts" style="cursor: pointer;" onclick="toggleAlerts()">View All ({{ count($scheduled_shifts) }}) →</div>
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

        <!-- Quick Actions -->
        <div class="quick-actions sectionWhiteBgAllUse">
            <div class="section-header">
                <h2 class="section-title">Quick Actions</h2>
            </div>

            <div class="quick_alert-notice">
                ⚠️ <strong>Attention Required</strong> - No contact shifts today poor/poor
            </div>

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
                        <div class="quick_action-label"> Leave Requests</div>
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
    @endsection
</main>
<!-- page-content" -->
</div> <!-- page-wrapper No remove this div-->