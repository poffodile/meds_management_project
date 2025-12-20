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
                    <button class="btn">⚙️ Customize</button>
                    <!-- <button class="btn btn-primary">📊 Publish</button> -->
                </div>
            </div>

             <!-- Alerts -->
            <div class="rota_alerts">
                <div class="rota_alert">
                    <div class="rota_alert-icon"><i class="fa fa fa-calendar-o"></i></div>
                    <div class="rota_alert-content">
                        <div class="rota_alert-title">Mixed Shift - 09:30</div>
                        <div class="rota_alert-description">Ron contacted for PDA one can I have contact No one assigned</div>
                        <div class="rota_alert-bottmDescription"> <i class="fa fa-bolt"></i> Contact care immediately and verify shift status</div>
                    </div>
                    <div class="rota_alert-badge">New</div>
                </div>

                <div class="rota_alert">
                    <div class="rota_alert-icon"><i class="fa fa fa-calendar-o"></i></div>
                    <div class="rota_alert-content">
                        <div class="rota_alert-title">Mixed Shift</div>
                        <div class="rota_alert-description">Ron contacted for PDA one can I have contact No one assigned</div>
                        <div class="rota_alert-bottmDescription"> <i class="fa fa-bolt"></i> Contact care immediately and verify shift status</div>
                    </div>
                    <div class="rota_alert-badge">New</div>
                </div>

                <div class="rota_alert">
                    <div class="rota_alert-icon"><i class="fa fa fa-calendar-o"></i></div>
                    <div class="rota_alert-content">
                        <div class="rota_alert-title">Unfilled Shift in Next 24 Hours</div>
                        <div class="rota_alert-description">May 12, 2025: 16:30 at All or Care Home assigned care! Check Margaret Smith</div>
                        <div class="rota_alert-bottmDescription"> <i class="fa fa-bolt"></i> Assign a qualified carer to this shift urgently</div>
                    </div>
                    <div class="rota_alert-badge">New</div>
                </div>

                <div class="rota_view-all">View All (4 More) →</div>
            </div>

            
            <!-- Smart Automation -->
            <div class="smart-automation">
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
            </div>

            <div class="rota_dashboard-cards">

                <div class="rota_dash-card blue">
                    <div class="rota_dash-left">
                        <p class="rota_title">Active Carers</p>
                        <h2 class="rota_count">{{ $userCount }}</h2>
                    </div>
                    <div class="rota_dash-icon">
                        <i class="fa fa-users"></i>
                    </div>
                </div>

                <div class="rota_dash-card green">
                    <div class="rota_dash-left">
                        <p class="rota_title">Active Clients</p>
                        <h2 class="rota_count">{{ $serviceUserCount }}</h2>
                    </div>
                    <div class="rota_dash-icon">
                        <i class="fa fa-user"></i>
                    </div>
                </div>

                <div class="rota_dash-card purple">
                    <div class="rota_dash-left">
                        <p class="rota_title">Today's Shifts</p>
                        <h2 class="rota_count">3</h2>
                    </div>
                    <div class="rota_dash-icon">
                        <i class="fa fa-calendar"></i>
                    </div>
                </div>

                <div class="rota_dash-card orangeClr">
                    <div class="rota_dash-left">
                        <p class="rota_title">Unfilled Shifts</p>
                        <h2 class="rota_count">307</h2>
                    </div>
                    <div class="rota_dash-icon">
                        <i class="fa fa-exclamation-circle"></i>
                    </div>
                </div>

            </div>

                   <!-- Smart Suggestions -->
            <div class="suggestions sectionWhiteBgAllUse">
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
            </div>

               <!-- Quick Actions -->
            <div class="quick-actions sectionWhiteBgAllUse">
                <div class="section-header">
                     <h2 class="section-title">Quick Actions</h2>
                </div>
                
                <div class="quick_alert-notice">
                    ⚠️ <strong>Attention Required</strong> - No contact shifts today poor/poor
                </div>

                <div class="quick_actions-grid">
                    <div class="quick_action-card">
                        <div class="quick_action-icon" style="background-color: #4299e1; color: white;">➕</div>
                        <div class="quick_action-label">Create Shift</div>
                    </div>

                    <div class="quick_action-card">
                        <div class="quick_action-icon" style="background-color: #48bb78; color: white;">👤</div>
                        <div class="quick_action-label">Add Carer</div>
                    </div>

                    <div class="quick_action-card">
                        <div class="quick_action-icon" style="background-color: #9f7aea; color: white;">🏠</div>
                        <div class="quick_action-label">Add Client</div>
                    </div>

                    <div class="quick_action-card">
                        <div class="quick_action-icon" style="background-color: #fc8181; color: white;">📄</div>
                        <div class="quick_action-label">Leave Requests</div>
                    </div>
                </div>
            </div>
             
              <!-- Recent Activity -->
            <div class="recent-activity sectionWhiteBgAllUse">
                <div class="section-header">
                    <h2 class="section-title">Recent Activity</h2>
                </div>

                <div class="activity-item">
                    <div class="activity-icon">S</div>
                    <div class="activity-content">
                        <div class="activity-title">Shift unfilled</div>
                        <div class="activity-description">Unknown → Unknown</div>
                        <div class="activity-time">Dec 27, 2024 at 11:13 AM</div>
                    </div>
                    <div class="activity-status">unfilled</div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon">S</div>
                    <div class="activity-content">
                        <div class="activity-title">Shift unfilled</div>
                        <div class="activity-description">Unknown → Unknown</div>
                        <div class="activity-time">Dec 27, 2024 at 11:13 AM</div>
                    </div>
                    <div class="activity-status">unfilled</div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon">S</div>
                    <div class="activity-content">
                        <div class="activity-title">Shift unfilled</div>
                        <div class="activity-description">Unknown → Unknown</div>
                        <div class="activity-time">Dec 27, 2024 at 11:13 AM</div>
                    </div>
                    <div class="activity-status">unfilled</div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon">S</div>
                    <div class="activity-content">
                        <div class="activity-title">Shift unfilled</div>
                        <div class="activity-description">Unknown → Unknown</div>
                        <div class="activity-time">Dec 27, 2024 at 11:13 AM</div>
                    </div>
                    <div class="activity-status">unfilled</div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon">S</div>
                    <div class="activity-content">
                        <div class="activity-title">Shift unfilled</div>
                        <div class="activity-description">Unknown → Unknown</div>
                        <div class="activity-time">Dec 27, 2024 at 11:13 AM</div>
                    </div>
                    <div class="activity-status">unfilled</div>
                </div>
            </div>

            <div class="sectionWhiteBgAllUse">
                <div class="section-header">
                    <h2 class="section-title">Today's Shifts</h2>
                </div>
                <div class="">
                    <div class="todayShiftsList">
                        <div class="siftTime">
                            <div class="siftTimeCont">
                                <i class="fa fa-clock-o"></i>
                                <span><strong>09:00 - 17:00</strong></span>
                            </div>
                            <div class="unfilledbtn">scheduled</div>
                        </div>
                        <div class="siftTime">
                            <div class="siftTimeCont">
                                <i class="fa fa-user-o"></i>
                                <span>Carer: <strong> Unassigned</strong></span>
                            </div>
                        </div>
                        <div class="siftTime">
                            <div class="siftTimeCont">
                                <i class="fa  fa-map-marker"></i>
                                <span>Client: <strong> Unknown Client</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="todayShiftsList m-t-15">
                        <div class="siftTime">
                            <div class="siftTimeCont">
                                <i class="fa fa-clock-o"></i>
                                <span><strong>09:00 - 17:00</strong></span>
                            </div>
                            <div class="unfilledbtn">scheduled</div>
                        </div>
                        <div class="siftTime">
                            <div class="siftTimeCont">
                                <i class="fa fa-user-o"></i>
                                <span>Carer: <strong> Unassigned</strong></span>
                            </div>
                        </div>
                        <div class="siftTime">
                            <div class="siftTimeCont">
                                <i class="fa  fa-map-marker"></i>
                                <span>Client: <strong> Unknown Client</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="todayShiftsList m-t-15">
                        <div class="siftTime">
                            <div class="siftTimeCont">
                                <i class="fa fa-clock-o"></i>
                                <span><strong>09:00 - 17:00</strong></span>
                            </div>
                            <div class="unfilledbtn">scheduled</div>
                        </div>
                        <div class="siftTime">
                            <div class="siftTimeCont">
                                <i class="fa fa-user-o"></i>
                                <span>Carer: <strong> Unassigned</strong></span>
                            </div>
                        </div>
                        <div class="siftTime">
                            <div class="siftTimeCont">
                                <i class="fa  fa-map-marker"></i>
                                <span>Client: <strong> Unknown Client</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="todayShiftsList m-t-15">
                        <div class="siftTime">
                            <div class="siftTimeCont">
                                <i class="fa fa-clock-o"></i>
                                <span><strong>09:00 - 17:00</strong></span>
                            </div>
                            <div class="unfilledbtn">scheduled</div>
                        </div>
                        <div class="siftTime">
                            <div class="siftTimeCont">
                                <i class="fa fa-user-o"></i>
                                <span>Carer: <strong> Unassigned</strong></span>
                            </div>
                        </div>
                        <div class="siftTime">
                            <div class="siftTimeCont">
                                <i class="fa  fa-map-marker"></i>
                                <span>Client: <strong> Unknown Client</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            

      
    </div>
@endsection
  </main>
  <!-- page-content" -->
</div> <!-- page-wrapper No remove this div-->



