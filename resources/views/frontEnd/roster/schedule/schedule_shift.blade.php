@extends('frontEnd.layouts.master')
@section('title', 'Schedule Shift')
@section('content')

    @include('frontEnd.roster.common.roster_header')
    <style>

    </style>
    <script type="text/javascript" src="{{ url('/public/frontEnd/js/external-dragging-calendar.js') }}"></script>
    <main class="page-content">
        <div class="container-fluid">

            <div class="topHeaderCont">
                <div>
                    <h1>Shift Schedule</h1>
                    <p class="header-subtitle">Manage and assign shifts to carers</p>
                </div>
                <div class="topFilters">

                    <button class="filterBtn activeDot">
                        <span class="dot"></span> All Active
                    </button>
                    <button class="filterBtn">
                        📅 Today
                    </button>
                    <button class="filterBtn">
                        📆 This Week
                    </button>
                    <button class="filterBtn">
                        ⭐ Saved Views
                    </button>
                    <button class="filterBtn">
                        ⬇ Export
                    </button>
                    <button class="filterBtn highlight">
                        ✨ AI Generate
                    </button>
                    <button class="filterBtn">
                        🛠 Smart Allocate
                    </button>
                    <button class="filterBtn lightGreen">
                        🔁 Recurring
                    </button>
                    <button class="profileDrop">+ Add Shift</button>

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

            <!-- Smart Suggestions -->
            <div class="suggestions">
                <div class="suggestion-card" style="border: 1px solid #fdbb76; border-left:4px solid #fdbb76">
                    <div class="suggestion-icon" style="background-color: #ea580c;">⚠️</div>
                    <div class="suggestionRightCont">
                        <div class="suggestion-title">Unfilled Shifts Detected</div>
                        <div class="suggestion-description">
                            You have 292 shifts without assigned carers. Would you like AI to help assign them?
                        </div>
                        <div class="suggestion-actions">
                            <button class="suggestion_btn-small suggestion_btn-orange">Auto-Assign →</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- advancedFiltersBox -->

            <div class="advancedFiltersBox m-b-15">
                <button class="advBtn">
                    🔍 Advanced Filters ▼
                </button>
                <div class="filterPanel" style="display:none;">
                    <h4>QUICK PRESETS</h4>
                    <div class="quickPresets">
                        <button class="preset activeDot">
                            <span class="dot"></span> Active Only
                        </button>
                        <button class="preset">
                            📅 This Week
                        </button>
                        <button class="preset yellow">
                            ⚠ High Priority
                        </button>
                    </div>


                    <div class="filter-row m-t-15">
                        <select class="filter-item">
                            <option>Status</option>
                            <option>Active</option>
                            <option>Draft</option>
                            <option>Closed</option>
                        </select>

                        <select class="filter-item small">
                            <option>is</option>
                            <option>is not</option>
                            <option>contains</option>
                        </select>

                        <input type="text" class="filter-input" placeholder="draft" />

                        <button class="close-btn">×</button>
                    </div>

                    <hr>
                    <h4>ADD FILTER FIELD</h4>
                    <div class="searchBox">
                        <span>🔍</span>
                        <input type="text" placeholder="Search all fields...">
                    </div>

                    <div class="filterFields">
                        <button class="fieldBtn">+ Status</button>
                        <button class="fieldBtn">+ Shift Type</button>
                        <button class="fieldBtn">+ Date</button>
                        <button class="fieldBtn">+ Duration</button>
                    </div>

                </div>
            </div>
            <!-- End of advancedFiltersBox -->

            <div class="calendarTabs">
                <div class="tabs">
                    <button class="tab active" data-tab="roster">
                        <span>📋</span> Roster
                    </button>

                    <button class="tab" data-tab="day">
                        <span>📅</span> Day
                    </button>

                    <button class="tab" data-tab="week">
                        <span>🗓️</span> Week
                    </button>

                    <button class="tab" data-tab="month">
                        <span>📆</span> Month
                    </button>

                    <button class="tab" data-tab="days90">
                        <span>🗃️</span> 90 Days
                    </button>

                    <button class="tab" data-tab="list">
                        <span>📋</span> List
                    </button>

                    <button class="tab" data-tab="group">
                        <span>🔄</span> By Group
                    </button>

                    <button class="tab" data-tab="split">
                        <span>🔳</span> Split
                    </button>
                </div>

                <!-- TAB CONTENT -->
                <div class="tab-content">

                    <div class="content active" id="roster">

                        <div class="shift-roster">

                            <!-- Header -->
                            <div class="sr-header">
                                <div class="sr-title">
                                    <h2>Care Home</h2>
                                    <span>Shift Roster</span>
                                </div>

                                <div class="sr-stats">
                                    <div>
                                        <strong>19</strong>
                                        <span>Total Shifts</span>
                                    </div>
                                    <div class="greenClr">
                                        <strong>19</strong>
                                        <span>Filled</span>
                                    </div>
                                    <div class="orangeClr">
                                        <strong>0</strong>
                                        <span>Open</span>
                                    </div>
                                    <div>
                                        <strong>124h</strong>
                                        <span>Hours</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Filters -->
                            <div class="sr-filters">
                                <div>
                                    <select>
                                        <option>Runs</option>
                                        <option>Resource</option>
                                    </select>
                                    <select>
                                        <option>By Staff</option>
                                        <option>By Client</option>
                                        <option>By Cisit</option>
                                    </select>
                                    <select>
                                        <option>View: Planned</option>
                                        <option>View: Actual</option>
                                        <option>View: Both</option>
                                    </select>
                                    <select>
                                        <option>Duration: 1 Day</option>
                                        <option>Duration: 1 Week</option>
                                        <option>Duration: 2 Weeks</option>
                                    </select>
                                    <button class="lock-btn">✔ Locked Visits</button>
                                </div>

                                <input type="text" placeholder="Search..." />
                            </div>

                            <!-- Calendar -->
                            <div class="sr-calendar">

                                <!-- Header Row -->
                                <!-- <div class="sr-row sr-head">
                                                                            </div> -->

                                <!-- Staff Row -->
                                <!-- <div class="sr-row">
                                                                            </div> -->

                            </div>




                            <!--main content start-->
                            <section id="main-content">
                                <section class="wrapper">
                                    <!-- page start-->
                                    <section class="panel">
                                        <header class="panel-heading">
                                            Draggable Calendar
                                            <span class="tools pull-right">
                                                <a href="javascript:;" class="fa fa-chevron-down"></a>
                                                <a href="javascript:;" class="fa fa-cog"></a>
                                                <a href="javascript:;" class="fa fa-times"></a>
                                            </span>
                                        </header>
                                        <div class="panel-body">
                                            <!-- page start-->
                                            <div class="row">
                                                <aside class="col-lg-9">
                                                    <div id="calendar" class="has-toolbar"></div>
                                                </aside>
                                                <aside class="col-lg-3">
                                                    <h4 class="drg-event-title"> Draggable Events</h4>
                                                    <div id='external-events'>
                                                        <div class='external-event label label-primary fc-event'>My Event 1</div>
                                                        <div class='external-event label label-success fc-event'>My Event 2</div>
                                                        <div class='external-event label label-info fc-event'>My Event 3</div>
                                                        <div class='external-event label label-inverse fc-event'>My Event 4</div>
                                                        <div class='external-event label label-warning fc-event'>My Event 5</div>
                                                        <div class='external-event label label-danger fc-event'>My Event 6</div>
                                                        <div class='external-event label label-default fc-event'>My Event 7</div>
                                                        <div class='external-event label label-primary fc-event'>My Event 8</div>
                                                        <div class='external-event label label-info fc-event'>My Event 9</div>
                                                        <div class='external-event label label-success fc-event'>My Event 10</div>
                                                        <p class="border-top drp-rmv">
                                                            <input type='checkbox' id='drop-remove' />
                                                            remove after drop
                                                        </p>
                                                    </div>
                                                </aside>
                                            </div>
                                            <!-- page end-->
                                        </div>
                                    </section>
                                    <!-- page end-->
                                </section>
                            </section>
                            <!--main content end-->
                            <!--right sidebar start-->
                            <div class="right-sidebar">
                                <div class="search-row">
                                    <input type="text" placeholder="Search" class="form-control">
                                </div>
                                <div class="right-stat-bar">
                                    <ul class="right-side-accordion">
                                        <li class="widget-collapsible">
                                            <a href="#" class="head widget-head red-bg active clearfix">
                                                <span class="pull-left">work progress (5)</span>
                                                <span class="pull-right widget-collapse"><i class="ico-minus"></i></span>
                                            </a>
                                            <ul class="widget-container">
                                                <li>
                                                    <div class="prog-row side-mini-stat clearfix">
                                                        <div class="side-graph-info">
                                                            <h4>Target sell</h4>
                                                            <p>
                                                                25%, Deadline 12 june 13
                                                            </p>
                                                        </div>
                                                        <div class="side-mini-graph">
                                                            <div class="target-sell">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="prog-row side-mini-stat">
                                                        <div class="side-graph-info">
                                                            <h4>product delivery</h4>
                                                            <p>
                                                                55%, Deadline 12 june 13
                                                            </p>
                                                        </div>
                                                        <div class="side-mini-graph">
                                                            <div class="p-delivery">
                                                                <div class="sparkline" data-type="bar" data-resize="true" data-height="30"
                                                                    data-width="90%" data-bar-color="#39b7ab" data-bar-width="5"
                                                                    data-data="[200,135,667,333,526,996,564,123,890,564,455]">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="prog-row side-mini-stat">
                                                        <div class="side-graph-info payment-info">
                                                            <h4>payment collection</h4>
                                                            <p>
                                                                25%, Deadline 12 june 13
                                                            </p>
                                                        </div>
                                                        <div class="side-mini-graph">
                                                            <div class="p-collection">
                                                                <span class="pc-epie-chart" data-percent="45">
                                                                    <span class="percent"></span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="prog-row side-mini-stat">
                                                        <div class="side-graph-info">
                                                            <h4>delivery pending</h4>
                                                            <p>
                                                                44%, Deadline 12 june 13
                                                            </p>
                                                        </div>
                                                        <div class="side-mini-graph">
                                                            <div class="d-pending">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="prog-row side-mini-stat">
                                                        <div class="col-md-12">
                                                            <h4>total progress</h4>
                                                            <p>
                                                                50%, Deadline 12 june 13
                                                            </p>
                                                            <div class="progress progress-xs mtop10">
                                                                <div style="width: 50%" aria-valuemax="100" aria-valuemin="0"
                                                                    aria-valuenow="20" role="progressbar"
                                                                    class="progress-bar progress-bar-info">
                                                                    <span class="sr-only">50% Complete</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="widget-collapsible">
                                            <a href="#" class="head widget-head terques-bg active clearfix">
                                                <span class="pull-left">contact online (5)</span>
                                                <span class="pull-right widget-collapse"><i class="ico-minus"></i></span>
                                            </a>
                                            <ul class="widget-container">
                                                <li>
                                                    <div class="prog-row">
                                                        <div class="user-thumb">
                                                            <a href="#"><img src="images/avatar1_small.jpg" alt=""></a>
                                                        </div>
                                                        <div class="user-details">
                                                            <h4><a href="#">Jonathan Smith</a></h4>
                                                            <p>
                                                                Work for fun
                                                            </p>
                                                        </div>
                                                        <div class="user-status text-danger">
                                                            <i class="fa fa-comments-o"></i>
                                                        </div>
                                                    </div>
                                                    <div class="prog-row">
                                                        <div class="user-thumb">
                                                            <a href="#"><img src="images/avatar1.jpg" alt=""></a>
                                                        </div>
                                                        <div class="user-details">
                                                            <h4><a href="#">Anjelina Joe</a></h4>
                                                            <p>
                                                                Available
                                                            </p>
                                                        </div>
                                                        <div class="user-status text-success">
                                                            <i class="fa fa-comments-o"></i>
                                                        </div>
                                                    </div>
                                                    <div class="prog-row">
                                                        <div class="user-thumb">
                                                            <a href="#"><img src="images/chat-avatar2.jpg" alt=""></a>
                                                        </div>
                                                        <div class="user-details">
                                                            <h4><a href="#">John Doe</a></h4>
                                                            <p>
                                                                Away from Desk
                                                            </p>
                                                        </div>
                                                        <div class="user-status text-warning">
                                                            <i class="fa fa-comments-o"></i>
                                                        </div>
                                                    </div>
                                                    <div class="prog-row">
                                                        <div class="user-thumb">
                                                            <a href="#"><img src="images/avatar1_small.jpg" alt=""></a>
                                                        </div>
                                                        <div class="user-details">
                                                            <h4><a href="#">Mark Henry</a></h4>
                                                            <p>
                                                                working
                                                            </p>
                                                        </div>
                                                        <div class="user-status text-info">
                                                            <i class="fa fa-comments-o"></i>
                                                        </div>
                                                    </div>
                                                    <div class="prog-row">
                                                        <div class="user-thumb">
                                                            <a href="#"><img src="images/avatar1.jpg" alt=""></a>
                                                        </div>
                                                        <div class="user-details">
                                                            <h4><a href="#">Shila Jones</a></h4>
                                                            <p>
                                                                Work for fun
                                                            </p>
                                                        </div>
                                                        <div class="user-status text-danger">
                                                            <i class="fa fa-comments-o"></i>
                                                        </div>
                                                    </div>
                                                    <p class="text-center">
                                                        <a href="#" class="view-btn">View all Contacts</a>
                                                    </p>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="widget-collapsible">
                                            <a href="#" class="head widget-head purple-bg active">
                                                <span class="pull-left"> recent activity (3)</span>
                                                <span class="pull-right widget-collapse"><i class="ico-minus"></i></span>
                                            </a>
                                            <ul class="widget-container">
                                                <li>
                                                    <div class="prog-row">
                                                        <div class="user-thumb rsn-activity">
                                                            <i class="fa fa-clock-o"></i>
                                                        </div>
                                                        <div class="rsn-details ">
                                                            <p class="text-muted">
                                                                just now
                                                            </p>
                                                            <p>
                                                                <a href="#">Jim Doe </a>Purchased new equipments for zonal office setup
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="prog-row">
                                                        <div class="user-thumb rsn-activity">
                                                            <i class="fa fa-clock-o"></i>
                                                        </div>
                                                        <div class="rsn-details ">
                                                            <p class="text-muted">
                                                                2 min ago
                                                            </p>
                                                            <p>
                                                                <a href="#">Jane Doe </a>Purchased new equipments for zonal office setup
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="prog-row">
                                                        <div class="user-thumb rsn-activity">
                                                            <i class="fa fa-clock-o"></i>
                                                        </div>
                                                        <div class="rsn-details ">
                                                            <p class="text-muted">
                                                                1 day ago
                                                            </p>
                                                            <p>
                                                                <a href="#">Jim Doe </a>Purchased new equipments for zonal office setup
                                                            </p>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="widget-collapsible">
                                            <a href="#" class="head widget-head yellow-bg active">
                                                <span class="pull-left"> shipment status</span>
                                                <span class="pull-right widget-collapse"><i class="ico-minus"></i></span>
                                            </a>
                                            <ul class="widget-container">
                                                <li>
                                                    <div class="col-md-12">
                                                        <div class="prog-row">
                                                            <p>
                                                                Full sleeve baby wear (SL: 17665)
                                                            </p>
                                                            <div class="progress progress-xs mtop10">
                                                                <div class="progress-bar progress-bar-success" role="progressbar"
                                                                    aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"
                                                                    style="width: 40%">
                                                                    <span class="sr-only">40% Complete</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="prog-row">
                                                            <p>
                                                                Full sleeve baby wear (SL: 17665)
                                                            </p>
                                                            <div class="progress progress-xs mtop10">
                                                                <div class="progress-bar progress-bar-info" role="progressbar"
                                                                    aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"
                                                                    style="width: 70%">
                                                                    <span class="sr-only">70% Completed</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!--right sidebar end-->

                        </div>



                    </div><!-- ********************************************************* -->

                    <div class="content" id="day">
                        <h3>Day View</h3>
                        <p>Day schedule appears here.</p>
                    </div>

                    <div class="content" id="week">
                        <h3>Week View</h3>
                        <p>Weekly details appear here.</p>
                    </div>

                    <div class="content" id="month">
                        <h3>Month View</h3>
                        <p>Monthly overview shown here.</p>
                    </div>

                    <div class="content" id="days90">
                        <h3>90 Days View</h3>
                        <p>3-month schedule will be shown here.</p>
                    </div>

                    <div class="content" id="list">
                        <h3>List View</h3>
                        <p>List format content appears here.</p>
                    </div>

                    <div class="content" id="group">
                        <h3>By Group View</h3>
                        <p>Grouped data will appear here.</p>
                    </div>

                    <div class="content" id="split">
                        <h3>Split View</h3>
                        <p>Split layout appears here.</p>
                    </div>
                </div>
            </div>





        </div>

        <!-- Advanced Filters  -->
        <script>
            const btn = document.querySelector('.advBtn');
            const panel = document.querySelector('.filterPanel');

            btn.addEventListener('click', () => {
                if (panel.style.display === "none") {
                    panel.style.display = "block";
                } else {
                    panel.style.display = "none";
                }
            });
        </script>


        <!-- calendarTabs -->
        <script>
            const tabs = document.querySelectorAll(".tab");
            const contents = document.querySelectorAll(".content");

            tabs.forEach(tab => {
                tab.addEventListener("click", () => {
                    document.querySelector(".tab.active")?.classList.remove("active");
                    tab.classList.add("active");

                    let tabName = tab.getAttribute("data-tab");

                    contents.forEach(content => {
                        content.classList.remove("active");
                    });

                    document.getElementById(tabName).classList.add("active");
                });
            });

            // Make external events draggable
            // $('#external-events .fc-event').each(function() {

            //     $(this).data('event', {
            //         title: $.trim($(this).text()),
            //         stick: true
            //     });

            //     $(this).draggable({
            //         zIndex: 999,
            //         revert: true,
            //         revertDuration: 0
            //     });
            // });
            // $(document).ready(function() {

            //     $('#calendar').fullCalendar({
            //         header: {
            //             left: 'prev,next today',
            //             center: 'title',
            //             right: 'month,agendaWeek,agendaDay'
            //         },

            //         editable: true,
            //         droppable: true,

            //         drop: function(date) {
            //             console.log('Dropped on ' + date.format());
            //         }
            //     });

            // });

            $(document).ready(function() {

                // Make external events draggable
                $('#external-events .fc-event').each(function() {

                    var eventObject = {
                        title: $.trim($(this).text())
                    };

                    // store event data
                    $(this).data('eventObject', eventObject);

                    // make draggable
                    $(this).draggable({
                        zIndex: 999,
                        revert: true,
                        revertDuration: 0
                    });
                });

                // Initialize calendar
                $('#calendar').fullCalendar({

                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                    },

                    editable: true,
                    droppable: true,

                    drop: function(date, jsEvent, ui) {

                        var originalEventObject = $(this).data('eventObject');

                        var copiedEventObject = $.extend({}, originalEventObject);

                        copiedEventObject.start = date;

                        // 🔥 THIS LINE APPENDS EVENT TO CALENDAR
                        $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);
                    }
                });

            });
        </script>

    @endsection
</main>
