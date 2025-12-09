@extends('frontEnd.layouts.master')
@section('title','Schedule Shift')
@section('content')




<section id="main-content">
    <div class="wrapper ps-0 pe-0 ">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    @include('frontEnd.roster.common.roster_header')
                  
                </div>
            </div>
            <div class="row">
                <div class="col-md-9">
                    <div class="m-t-30">
                        <div class="panel">
                            <header class="panel-heading"> Shift Schedule </header>
                            <div class="panel-body rosterBox">

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

                            <div class="advancedFiltersBox">

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

                                    <div class="filter-row m-t-15">
                                        <select class="filter-item">
                                            <option>Shift Type</option>
                                        </select>

                                        <select class="filter-item small">
                                            <option>is</option>
                                            <option>is not</option>
                                            <option>contains</option>
                                        </select>

                                        <input type="text" class="filter-input" placeholder="draft" />

                                        <button class="close-btn">×</button>
                                    </div>

                                    <div class="filter-row m-t-15">
                                        <select class="filter-item">
                                            <option>Duration</option>
                                        </select>

                                        <select class="filter-item small">
                                            <option>Date</option>
                                        </select>

                                        <input type="text" class="filter-input" placeholder="draft" />

                                        <button class="close-btn">×</button>
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
                                        <h3>Roster View</h3>
                                        <p>Roster-related content will show here.</p>
                                    </div>

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
                        </div>
                    </div>
                   
                </div>
                <div class="col-md-3">
                    <div class="rotawhitebgColor m-t-30">
                        <div class="panel">
                            <header class="panel-heading">Alerts</header>
                            <div class="panel-body">
                                <div class="alert alert-placement clearfix">
                                    <span class="alert-icon"><i class="fa fa-calendar-o"></i></span>
                                    <div class="notification-info">
                                        <ul class="clearfix notification-meta">
                                            <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Missed Shift</b> - 09:00</a></span></li>
                                            <li class="pull-right notification-time">High</li>
                                        </ul>
                                        <p>A new Placement Plan 'task' is added</p>
                                    </div>
                                </div>
                                <div class="alert alert-placement clearfix">
                                    <span class="alert-icon"><i class="fa fa-calendar-o"></i></span>
                                    <div class="notification-info">
                                        <ul class="clearfix notification-meta">
                                            <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Missed Shift</b> - 09:00</a></span></li>
                                            <li class="pull-right notification-time">3 weeks ago</li>
                                        </ul>
                                        <p>A new Placement Plan 'task' is added</p>
                                    </div>
                                </div>
                                <div class="alert alert-placement clearfix">
                                    <span class="alert-icon"><i class="fa fa-calendar-o"></i></span>
                                    <div class="notification-info">
                                        <ul class="clearfix notification-meta">
                                            <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Missed Shift</b> - 09:00</a></span></li>
                                            <li class="pull-right notification-time">3 weeks ago</li>
                                        </ul>
                                        <p>A new Placement Plan 'task' is added</p>
                                    </div>
                                </div>
                                

                            </div>
                        </div>
                        <div class="panel">
                            <header class="panel-heading">Notifications</header>
                            <div class="panel-body">
                                <div class="alert alert-placement clearfix">
                                    <span class="alert-icon"><i class="fa fa-map-marker"></i></span>
                                    <div class="notification-info">
                                        <ul class="clearfix notification-meta">
                                            <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Mick</b></a></span></li>
                                            <li class="pull-right notification-time">3 weeks ago</li>
                                        </ul>
                                        <p>A new Placement Plan 'task' is added</p>
                                    </div>
                                </div>
                                <div class="alert alert-placement clearfix">
                                    <span class="alert-icon"><i class="fa fa-map-marker"></i></span>
                                    <div class="notification-info">
                                        <ul class="clearfix notification-meta">
                                            <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Mick</b></a></span></li>
                                            <li class="pull-right notification-time">3 weeks ago</li>
                                        </ul>
                                        <p>A new Placement Plan 'task' is added</p>
                                    </div>
                                </div>
                                <div class="alert alert-placement clearfix">
                                    <span class="alert-icon"><i class="fa fa-map-marker"></i></span>
                                    <div class="notification-info">
                                        <ul class="clearfix notification-meta">
                                            <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Mick</b></a></span></li>
                                            <li class="pull-right notification-time">3 weeks ago</li>
                                        </ul>
                                        <p>A new Placement Plan 'task' is added</p>
                                    </div>
                                </div>
                                <div class="alert alert-placement clearfix">
                                    <span class="alert-icon"><i class="fa fa-map-marker"></i></span>
                                    <div class="notification-info">
                                        <ul class="clearfix notification-meta">
                                            <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Mick</b></a></span></li>
                                            <li class="pull-right notification-time">3 weeks ago</li>
                                        </ul>
                                        <p>A new Placement Plan 'task' is added</p>
                                    </div>
                                </div>
                                

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


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
</script>

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
@endsection