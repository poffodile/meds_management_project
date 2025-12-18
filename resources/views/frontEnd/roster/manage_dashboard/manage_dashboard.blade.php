@extends('frontEnd.layouts.master')
@section('title','Manage Dashboard')
@section('content')


@include('frontEnd.roster.common.roster_header')


 <main class="page-content">
    <div class="container-fluid">
      
            <div class="topHeaderCont">
                <div>
                    <h1>Manager Dashboard</h1>
                    <p class="header-subtitle">Operational overview and key metrics</p>
                </div>
                <div class="header-actions">
                     <button class="btn btn-primary">📊 Export</button>
                    <button class="btn">⚙️ Customize</button>                   
                </div>
            </div>

            <div class="rota_dashboard-cards">

                <div class="rota_dash-card blue">
                    <div class="rota_dash-left">
                        <p class="rota_title">Active Carers</p>
                        <h2 class="rota_count">11</h2>
                    </div>
                    <div class="rota_dash-icon">
                        <i class="fa fa-users"></i>
                    </div>
                </div>

                <div class="rota_dash-card green">
                    <div class="rota_dash-left">
                        <p class="rota_title">Active Clients</p>
                        <h2 class="rota_count">9</h2>
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

             <!-- Alerts -->

            <div class="sectionWhiteBgAllUse">
                <div class="section-header">
                    <h2 class="section-title">System Alerts</h2>
                </div>
                 
                <div class="rota_alerts m-b-0">
                    <div class="rota_alert">
                        <div class="rota_alert-icon"><i class="fa fa-bell-o"></i></div>
                        <div class="rota_alert-content">
                            <div class="rota_alert-title">Mixed Shift - 09:30</div>
                            <div class="rota_alert-description">Ron contacted for PDA one can I have contact No one assigned</div>
                            <div class="rota_alert-bottmDescription"> <i class="fa fa-bolt"></i> Contact care immediately and verify shift status</div>
                        </div>
                        <div class="rota_alert-badge">High</div>
                    </div>

                    <div class="rota_alert">
                        <div class="rota_alert-icon"><i class="fa fa-bell-o"></i></div>
                        <div class="rota_alert-content">
                            <div class="rota_alert-title">Mixed Shift</div>
                            <div class="rota_alert-description">Ron contacted for PDA one can I have contact No one assigned</div>
                            <div class="rota_alert-bottmDescription"> <i class="fa fa-bolt"></i> Contact care immediately and verify shift status</div>
                        </div>
                        <div class="rota_alert-badge">High</div>
                    </div>

                    <div class="rota_alert">
                        <div class="rota_alert-icon"><i class="fa fa-bell-o"></i></div>
                        <div class="rota_alert-content">
                            <div class="rota_alert-title">Unfilled Shift in Next 24 Hours</div>
                            <div class="rota_alert-description">May 12, 2025: 16:30 at All or Care Home assigned care! Check Margaret Smith</div>
                            <div class="rota_alert-bottmDescription"> <i class="fa fa-bolt"></i> Assign a qualified carer to this shift urgently</div>
                        </div>
                        <div class="rota_alert-badge">High</div>
                    </div>

                    <div class="rota_alert">
                        <div class="rota_alert-icon"><i class="fa fa-bell-o"></i></div>
                        <div class="rota_alert-content">
                            <div class="rota_alert-title">Mixed Shift</div>
                            <div class="rota_alert-description">Ron contacted for PDA one can I have contact No one assigned</div>
                            <div class="rota_alert-bottmDescription"> <i class="fa fa-bolt"></i> Contact care immediately and verify shift status</div>
                        </div>
                        <div class="rota_alert-badge">High</div>
                    </div>

                    <div class="rota_alert">
                        <div class="rota_alert-icon"><i class="fa fa-bell-o"></i></div>
                        <div class="rota_alert-content">
                            <div class="rota_alert-title">Unfilled Shift in Next 24 Hours</div>
                            <div class="rota_alert-description">May 12, 2025: 16:30 at All or Care Home assigned care! Check Margaret Smith</div>
                            <div class="rota_alert-bottmDescription"> <i class="fa fa-bolt"></i> Assign a qualified carer to this shift urgently</div>
                        </div>
                        <div class="rota_alert-badge">High</div>
                    </div>

                    <div class="rota_view-all m-b-0">+ 15 More Alert →</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">                        
                    <div class="panel">
                        <header class="panel-heading headingCapitilize" style="background-color: #f0fdfa;"> <i class="fa fa-user"></i> Staff &amp; Shifts</header>
                        <div class="panel-body">
                            <div class="staffShifts">
                                <div class="todayNumber">
                                <p>Today's Shifts</p>
                                <h3>3</h3>
                                </div>
                                <div class="fillPersent">
                                <p>Fill Rate</p>
                                <h3 class="text-green">33.3%</h3>
                                </div>
                            </div>
                            <div class="staffShifts_alert-box">
                                <div class="staffShifts_alert-icon">⚠</div>
                                <div class="staffShifts_alert-content">
                                    <p class="staffShifts_alert-title">300 Unfilled Shifts</p>
                                    <p class="staffShifts_alert-subtitle">Needs attention</p>
                                </div>
                            </div>
                            <div class="text-center">
                                <a href="#!" class="profileDrop profileDropNobgcolor d-block"><i class="fa fa-calendar-o"></i> Full view Schedule</a>
                            </div>
                        </div>
                    </div>
                    <div class="panel">
                        <header class="panel-heading headingCapitilize" style="background-color: #eef2ff;"> <i class="fa fa-warning (alias)"></i> Incidents &amp; Safety</header>
                        <div class="panel-body">
                            <div class="staffShifts">
                                <div class="todayNumber">
                                <p>This Month</p>
                                <h3>3</h3>
                                </div>
                                <div class="fillPersent">
                                <p>Unresolved</p>
                                <h3 class="text-orange">1</h3>
                                </div>
                            </div>
                            <div class="text-center">
                                <a href="#!" class="profileDrop profileDropNobgcolor d-block"><i class="fa fa-info-circle"></i> View All Incidents</a>
                            </div>
                        </div>
                    </div>
                    <div class="panel">
                        <header class="panel-heading headingCapitilize" style="background-color: #fdf2f8;"> <i class="fa fa-home"></i> Occupancy &amp; Capacity</header>
                        <div class="panel-body">

                            <div class="occupancyBox">
                                <div class="topRow">
                                    <span>Current Occupancy</span>
                                    <span class="value">8/50</span>
                                </div>

                                <div class="progressBar">
                                    <div class="progressFill" style="width:16%;"></div>
                                </div>
                            </div>
                            <div class="staffShifts">
                                <div class="todayNumber">
                                <p>Occupancy Rate</p>
                                <h3>66.0%</h3>
                                </div>
                                <div class="fillPersent">
                                <p>Planned Admissions</p>
                                <h3>3</h3>
                                </div>
                            </div>
                            <div class="text-center">
                                <a href="#!" class="profileDrop profileDropNobgcolor d-block"><i class="fa fa-info-circle"></i> Manage client information</a>
                            </div>
                        </div>
                    </div>
                    <div class="panel">
                        <header class="panel-heading headingCapitilize" style="background-color: #fefce8;"> <i class="fa fa-book"></i> Training Compliance</header>
                        <div class="panel-body">
                            <div class="occupancyBox">
                                <div class="topRow">
                                    <span>Completion Rate</span>
                                    <span class="value">0.0%</span>
                                </div>

                                <div class="progressBar">
                                    <div class="progressFill" style="width:0%;"></div>
                                </div>
                            </div>
                            <div class="staffShifts">
                                <div class="todayNumber">
                                <p>Expiring Soon</p>
                                <h3 class="text-orange">3</h3>
                                </div>
                                <div class="fillPersent">
                                <p>Overdue</p>
                                <h3 class="text-orange">33.3%</h3>
                                </div>
                            </div>
                            <div class="text-center">
                                <a href="#!" class="profileDrop profileDropNobgcolor d-block"><i class="fa fa-info-circle"></i> View Training</a>
                            </div>
                        </div>
                    </div>                     
                </div>
                <div class="col-md-6">
                    <div class="panel">
                        <header class="panel-heading headingCapitilize" style="background-color: #f0fdfa;"> <i class="fa fa-user"></i> Staff &amp; Shifts</header>
                        <div class="panel-body">
                            <div class="staffShifts">
                                <div class="todayNumber">
                                <p>Today's Shifts</p>
                                <h3>3</h3>
                                </div>
                                <div class="fillPersent">
                                <p>Fill Rate</p>
                                <h3 class="text-green">33.3%</h3>
                                </div>
                            </div>
                            <div class="staffShifts_alert-box">
                                <div class="staffShifts_alert-icon">⚠</div>
                                <div class="staffShifts_alert-content">
                                    <p class="staffShifts_alert-title">300 Unfilled Shifts</p>
                                    <p class="staffShifts_alert-subtitle">Needs attention</p>
                                </div>
                            </div>
                            <div class="text-center">
                                <a href="#!" class="profileDrop profileDropNobgcolor d-block"><i class="fa fa-calendar-o"></i> Full view Schedule</a>
                            </div>
                        </div>
                    </div>
                    <div class="panel">
                        <header class="panel-heading headingCapitilize" style="background-color: #eef2ff;"> <i class="fa fa-warning (alias)"></i> Incidents &amp; Safety</header>
                        <div class="panel-body">
                            <div class="staffShifts">
                                <div class="todayNumber">
                                <p>This Month</p>
                                <h3>3</h3>
                                </div>
                                <div class="fillPersent">
                                <p>Unresolved</p>
                                <h3 class="text-orange">1</h3>
                                </div>
                            </div>
                            <div class="text-center">
                                <a href="#!" class="profileDrop profileDropNobgcolor d-block"><i class="fa fa-info-circle"></i> View All Incidents</a>
                            </div>
                        </div>
                    </div>
                    <div class="panel">
                        <header class="panel-heading headingCapitilize" style="background-color: #fdf2f8;"> <i class="fa fa-home"></i> Occupancy &amp; Capacity</header>
                        <div class="panel-body">

                            <div class="occupancyBox">
                                <div class="topRow">
                                    <span>Current Occupancy</span>
                                    <span class="value">8/50</span>
                                </div>

                                <div class="progressBar">
                                    <div class="progressFill" style="width:16%;"></div>
                                </div>
                            </div>
                            <div class="staffShifts">
                                <div class="todayNumber">
                                <p>Occupancy Rate</p>
                                <h3>66.0%</h3>
                                </div>
                                <div class="fillPersent">
                                <p>Planned Admissions</p>
                                <h3>3</h3>
                                </div>
                            </div>
                            <div class="text-center">
                                <a href="#!" class="profileDrop profileDropNobgcolor d-block"><i class="fa fa-info-circle"></i> Manage client information</a>
                            </div>
                        </div>
                    </div>
                    <div class="panel">
                        <header class="panel-heading headingCapitilize" style="background-color: #fefce8;"> <i class="fa fa-book"></i> Training Compliance</header>
                        <div class="panel-body">
                            <div class="occupancyBox">
                                <div class="topRow">
                                    <span>Completion Rate</span>
                                    <span class="value">0.0%</span>
                                </div>

                                <div class="progressBar">
                                    <div class="progressFill" style="width:0%;"></div>
                                </div>
                            </div>
                            <div class="staffShifts">
                                <div class="todayNumber">
                                <p>Expiring Soon</p>
                                <h3 class="text-orange">3</h3>
                                </div>
                                <div class="fillPersent">
                                <p>Overdue</p>
                                <h3 class="text-orange">33.3%</h3>
                                </div>
                            </div>
                            <div class="text-center">
                                <a href="#!" class="profileDrop profileDropNobgcolor d-block"><i class="fa fa-info-circle"></i> View Training</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



    </div>
    @endsection
 </main>

</div><!-- page-wrapper No remove this div-->


