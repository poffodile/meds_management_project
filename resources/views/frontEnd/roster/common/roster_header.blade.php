<link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
<style>
  .page-wrapper .sidebar-wrapper,
  .sidebar-wrapper .sidebar-brand>a,
  .sidebar-wrapper .sidebar-dropdown>a:after,
  .sidebar-wrapper .sidebar-menu .sidebar-dropdown .sidebar-submenu li a:before,
  .sidebar-wrapper ul li a i,
  .page-wrapper .page-content,
  .sidebar-wrapper .sidebar-search input.search-menu,
  .sidebar-wrapper .sidebar-search .input-group-text,
  .sidebar-wrapper .sidebar-menu ul li a,
  #show-sidebar,
  #close-sidebar {
    -webkit-transition: all 0.3s ease;
    -moz-transition: all 0.3s ease;
    -ms-transition: all 0.3s ease;
    -o-transition: all 0.3s ease;
    transition: all 0.3s ease;
  }

  /*----------------page-wrapper----------------*/
  /* 
.page-wrapper {
  height: 100vh;
} */

  .page-wrapper .theme {
    width: 40px;
    height: 40px;
    display: inline-block;
    border-radius: 4px;
    margin: 2px;
  }

  .page-wrapper .theme.chiller-theme {
    background: #1e2229;
  }

  /*----------------toggeled sidebar----------------*/

  .page-wrapper.toggled .sidebar-wrapper {
    left: 0px;
  }

  @media screen and (min-width: 768px) {
    .page-wrapper.toggled .page-content {
      padding-left: 240px;
      margin-top: 60px;
    }
  }

  /*----------------show sidebar button----------------*/
  #show-sidebar {
    position: fixed;
    left: 10px;
    top: 17px;
    border-radius: 0 4px 4px 0px;
    width: 40px;
    transition-delay: 0.3s;
    z-index: 99999;
    color: #fff;
    font-size: 20px;
  }

  .page-wrapper.toggled #show-sidebar {
    left: -40px;
  }

  /*----------------sidebar-wrapper----------------*/

  .sidebar-wrapper {
    width: 240px;
    height: 100%;
    max-height: 100%;
    position: fixed;
    top: 0;
    left: -300px;
    z-index: 999;
    padding-top: 85px;
  }

  .sidebar-wrapper ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
  }

  .sidebar-wrapper a {
    text-decoration: none;
  }

  /*----------------sidebar-content----------------*/

  .sidebar-content {
    max-height: calc(100% - 30px);
    height: calc(100% - 30px);
    overflow-y: auto;
    position: relative;
  }

  .sidebar-content.desktop {
    overflow-y: hidden;
  }

  /*--------------------sidebar-brand----------------------*/

  .sidebar-wrapper .sidebar-brand {
    padding: 10px 20px;
    display: flex;
    align-items: center;
  }

  .sidebar-wrapper .sidebar-brand>a {
    text-transform: uppercase;
    font-weight: bold;
    flex-grow: 1;
  }

  .sidebar-wrapper .sidebar-brand #close-sidebar {
    cursor: pointer;
    font-size: 20px;
  }

  /*--------------------sidebar-header----------------------*/

  .sidebar-wrapper .sidebar-header {
    padding: 20px;
    overflow: hidden;
  }

  .sidebar-wrapper .sidebar-header .user-pic {
    float: left;
    width: 60px;
    padding: 2px;
    border-radius: 12px;
    margin-right: 15px;
    overflow: hidden;
  }

  .sidebar-wrapper .sidebar-header .user-pic img {
    object-fit: cover;
    height: 100%;
    width: 100%;
  }

  .sidebar-wrapper .sidebar-header .user-info {
    float: left;
  }

  .sidebar-wrapper .sidebar-header .user-info>span {
    display: block;
  }

  .sidebar-wrapper .sidebar-header .user-info .user-role {
    font-size: 12px;
  }

  .sidebar-wrapper .sidebar-header .user-info .user-status {
    font-size: 11px;
    margin-top: 4px;
  }

  .sidebar-wrapper .sidebar-header .user-info .user-status i {
    font-size: 8px;
    margin-right: 4px;
    color: #5cb85c;
  }

  /*-----------------------sidebar-search------------------------*/

  .sidebar-wrapper .sidebar-search>div {
    padding: 10px 20px;
  }

  /*----------------------sidebar-menu-------------------------*/

  .sidebar-wrapper .sidebar-menu {
    padding-bottom: 10px;
  }

  .sidebar-wrapper .sidebar-menu .header-menu span {
    font-weight: 600;
    font-size: 13px;
    padding: 15px 20px 5px 20px;
    display: inline-block;
    text-transform: uppercase;
  }

  .sidebar-wrapper .sidebar-menu ul li a {
    display: flex;
    width: 100%;
    text-decoration: none;
    position: relative;
    padding: 8px 30px 8px 20px;
  }

  .sidebar-wrapper .sidebar-menu ul li a i {
    margin-right: 10px;
    font-size: 20px;
    width: 30px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    border-radius: 4px;
  }

  .sidebar-wrapper .sidebar-menu ul li a:hover>i::before {
    display: inline-block;
    animation: swing ease-in-out 0.5s 1 alternate;
  }

  .sidebar-wrapper .sidebar-menu .sidebar-dropdown>a:after {
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    content: "\f105";
    font-style: normal;
    display: inline-block;
    font-style: normal;
    font-variant: normal;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    background: 0 0;
    position: absolute;
    right: 15px;
    top: 14px;
  }

  .sidebar-wrapper .sidebar-menu .sidebar-dropdown .sidebar-submenu ul {
    padding: 5px 0;
  }

  .sidebar-wrapper .sidebar-menu .sidebar-dropdown .sidebar-submenu li {
    padding-left: 25px;
    font-size: 13px;
  }

  .sidebar-wrapper .sidebar-menu .sidebar-dropdown .sidebar-submenu li a:before {
    content: "\f111";
    font-family: "Font Awesome 5 Free";
    font-weight: 400;
    font-style: normal;
    display: inline-block;
    text-align: center;
    text-decoration: none;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    margin-right: 10px;
    font-size: 8px;
  }

  .sidebar-wrapper .sidebar-menu ul li a span.label,
  .sidebar-wrapper .sidebar-menu ul li a span.badge {
    float: right;
    margin-top: 8px;
    margin-left: 5px;
  }

  .sidebar-wrapper .sidebar-menu .sidebar-dropdown .sidebar-submenu li a .badge,
  .sidebar-wrapper .sidebar-menu .sidebar-dropdown .sidebar-submenu li a .label {
    float: right;
    margin-top: 0px;
  }

  .sidebar-wrapper .sidebar-menu .sidebar-submenu {
    display: none;
  }

  .sidebar-wrapper .sidebar-menu .sidebar-dropdown.active>a:after {
    transform: rotate(90deg);
    right: 17px;
  }

  /*--------------------------side-footer------------------------------*/



  /*--------------------------page-content-----------------------------*/

  .page-wrapper .page-content {
    display: inline-block;
    width: 100%;
    padding-left: 0px;
    padding-top: 20px;
    margin-top: 60px;
  }

  .page-wrapper .page-content>div {
    padding: 20px 6rem;
  }

  .page-wrapper .page-content {
    overflow-x: hidden;
    background: #f6f9fd;
  }

  /*------scroll bar---------------------*/

  ::-webkit-scrollbar {
    width: 6px;
    height: 7px;
  }

  ::-webkit-scrollbar-button {
    width: 0px;
    height: 0px;
  }

  ::-webkit-scrollbar-thumb {
    background: #bbbbbbff;
    border: 0px none #ffffff;
    border-radius: 0px;
  }

  ::-webkit-scrollbar-track {
    background: #f1f1f1;
  }

  /*-----------------------------chiller-theme-------------------------------------------------*/

  .chiller-theme .sidebar-wrapper {
    background: #ffffffff;
  }

  .chiller-theme .sidebar-wrapper .sidebar-header,
  .chiller-theme .sidebar-wrapper .sidebar-search,
  .chiller-theme .sidebar-wrapper .sidebar-menu {
    border-top: 1px solid #f1f2f7;
  }

  .chiller-theme .sidebar-wrapper .sidebar-search input.search-menu,
  .chiller-theme .sidebar-wrapper .sidebar-search .input-group-text {
    border-color: transparent;
    box-shadow: none;
  }

  .chiller-theme .sidebar-wrapper .sidebar-header .user-info .user-role,
  .chiller-theme .sidebar-wrapper .sidebar-header .user-info .user-status,
  .chiller-theme .sidebar-wrapper .sidebar-search input.search-menu,
  .chiller-theme .sidebar-wrapper .sidebar-brand>a,
  .chiller-theme .sidebar-wrapper .sidebar-menu ul li a,
  .chiller-theme .sidebar-footer>a {
    color: #818896;
  }

  .chiller-theme .sidebar-wrapper .sidebar-menu ul li:hover>a,
  .chiller-theme .sidebar-wrapper .sidebar-menu .sidebar-dropdown.active>a,
  .chiller-theme .sidebar-wrapper .sidebar-header .user-info,
  .chiller-theme .sidebar-wrapper .sidebar-brand>a:hover,
  .chiller-theme .sidebar-footer>a:hover i {
    color: #b8bfce;
  }

  .page-wrapper.chiller-theme.toggled #close-sidebar {
    color: #bdbdbd;
  }

  .page-wrapper.chiller-theme.toggled #close-sidebar:hover {
    color: #ffffff;
  }

  .chiller-theme .sidebar-wrapper ul li:hover a i,
  .chiller-theme .sidebar-wrapper .sidebar-dropdown .sidebar-submenu li a:hover:before,
  .chiller-theme .sidebar-wrapper .sidebar-search input.search-menu:focus+span,
  .chiller-theme .sidebar-wrapper .sidebar-menu .sidebar-dropdown.active a i {
    color: #16c7ff;
    text-shadow: 0px 0px 10px rgba(22, 199, 255, 0.5);
  }

  .chiller-theme .sidebar-wrapper .sidebar-menu ul li a i,
  .chiller-theme .sidebar-wrapper .sidebar-menu .sidebar-dropdown div,

  .chiller-theme .sidebar-wrapper .sidebar-search .input-group-text {
    background: #1f88b50f;
    color: #5a5a5a;
  }

  .chiller-theme .sidebar-wrapper .sidebar-search .input-group-text {
    background: #f1f2f7;
    font-size: 16px;
    padding: 6px;
    line-height: 35px;
    border-bottom-right-radius: 4px;
    border-top-right-radius: 4px;
  }

  .chiller-theme .sidebar-wrapper .sidebar-search input.search-menu {
    background: #f1f2f7;
  }

  .chiller-theme .sidebar-wrapper .sidebar-menu .header-menu span {
    color: #6c7b88;
  }

  .chiller-theme .sidebar-footer {
    background: #3a3f48;
    box-shadow: 0px -1px 5px #282c33;
    border-top: 1px solid #464a52;
  }

  .chiller-theme .sidebar-footer>a:first-child {
    border-left: none;
  }

  .chiller-theme .sidebar-footer>a:last-child {
    border-right: none;
  }

  .sidebar-wrapper .sidebar-menu ul li span {
    font-size: 13px;
    color: #686868;
    font-weight: 600;
    line-height: 28px;
  }

  .sidebar-wrapper .sidebar-menu ul li a:hover {
    background: #f1f2f7;
  }

  .chiller-theme .sidebar-wrapper .sidebar-menu ul li a.active {
    background-color: #f1f2f7;
  }

  .chiller-theme .sidebar-wrapper .sidebar-menu ul li a.active i,
  .chiller-theme .sidebar-wrapper .sidebar-menu ul li a.active span {
    color: #525252ff;
  }
</style>
<div class="page-wrapper chiller-theme toggled">
  <a id="show-sidebar" class="btn btn-sm btn-dark" href="#">
    <i class="fa fa-bars"></i>
  </a>
  <nav id="" class="sidebar-wrapper">
    <div class="sidebar-content">
      <div class="sidebar-brand">
        <a href="#">Care Roster</a>
        <div id="close-sidebar">
          <i class="fa  fa-angle-double-left"></i>
        </div>
      </div>

      <!-- sidebar-header  -->
      <div class="sidebar-search">
        <div>
          <div class="input-group d-flex">
            <input type="text" class="form-control search-menu" placeholder="Search...">
            <div class="input-group-append">
              <span class="input-group-text">
                <i class="fa fa-search" aria-hidden="true"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
      <!-- sidebar-search  -->
      <div class="sidebar-menu">
        <ul>
          <li class="header-menu"> <span>Residential Care</span> </li>
          <li> <a href="{{ url('/roster')}}"><i class='bx  bx-dashboard'></i> <span>Dashboard</span> </a> </li>
          <li> <a href="{{ url('/roster/manage-dashboard') }}"><i class='bx bx-dashboard'></i> <span>Manager Dashboard</span> </a></li>
          <li> <a href="{{ url('/roster/schedule-shift') }}"><i class='bx bx-calendar'></i> <span>Schedule</span> </a></li>
          <li> <a href="{{ url('/roster/carer-availability') }}"><i class='bx bx-calendar'></i> <span>Carer Availability</span> </a></li>
          <li> <a href="{{ url('/roster/carer') }}"><i class='bx bx-group'></i> <span>Carers</span> </a></li>
          <li> <a href="{{ url('/roster/client') }}"><i class='bx bx-user-circle'></i> <span>Clients</span> </a></li>
          <li> <a href="{{ url('/roster/daily-log') }}"><i class='bx  bx-clipboard-detail'></i> <span>Daily Log</span></a></li>
          <li class="header-menu"> <span>Domiciliary Care</span> </li>
          <li> <a href="#"><i class='bx bx-dashboard'></i> <span>Dom Care Dashboard</span></a></li>
          <li> <a href="#"><i class='bx bx-location'></i> <span>Visit Schedule</span></a></li>
          <li> <a href="#"><i class='bx bx-calendar'></i> <span>Staff Availability</span></a></li>
          <li> <a href="#"><i class='bx bx-group'></i> <span>Staff</span></a></li>
          <li> <a href="#"><i class='bx bx-user-circle'></i> <span>Clients</span></a></li>
          <li> <a href="#"><i class='bx bx-send'></i> <span>Runs</span></a></li>
          <li> <a href="#"><i class='bx  bx-file-report'></i> <span>Reports</span></a></li>
          <li> <a href="#"><i class='bx bx-message'></i> <span>Communications</span></a></li>
          <li> <a href="#"><i class='bx bx-message'></i> <span>Client Feedback</span></a></li>

          <li class="header-menu"> <span>Supported Living</span> </li>
          <li> <a href="#"><i class='bx bx-dashboard'></i> <span>SL Dashboard</span></a></li>
          <li> <a href="#"><i class='bx bx-user-circle'></i> <span>Clients</span></a></li>
          <li> <a href="#"><i class='bx  bx-home'></i> <span>Properties</span></a></li>
          <li> <a href="#"><i class='bx bx-calendar'></i> <span>Schedule</span></a></li>

          <!-- <li class="header-menu"> <span>General</span> </li>
          <li> <a href="{{ url('/roster/messaging-center') }}"><i class="fa fa-globe"></i> <span> Messaging Center</span> </a> </li>
          <li> <a href="{{ url('/roster/staff-task') }}"><i class="fa fa-globe"></i> <span>Staff Tasks</span> </a> </li>
          <li> <a href="{{ url('/roster/care-document') }}"><i class="fa fa-globe"></i> <span> Care Documents</span> </a> </li>
          <li> <a href="{{ url('/roster/leave-request') }}"><i class="fa fa-globe"></i> <span> Leave Requests </span> </a> </li>
          <li> <a href="{{ url('/roster/payroll-finance') }}"><i class="fa fa-globe"></i> <span> Payroll & Finance </span> </a> </li>
          <li> <a href="#"><i class="fa fa-book"></i> <span>Documentation</span></a></li>
          <li> <a href="{{ url('/roster/incident-management') }}"><i class="fa fa-book"></i> <span>Incident Management</span></a></li>
          <li> <a href="#"><i class="fa fa-calendar"></i> <span>Calendar</span> </a></li>
          <li> <a href="#"> <i class="fa fa-folder"></i> <span>Examples</span> </a></li> -->
          <li class="header-menu"> <span>Day Centre</span> </li>
          <li> <a href="#!"><i class='bx bx-dashboard'></i> <span>Day Centre Dashboard</span> </a> </li>
          <li> <a href="#!"><i class='bx bx-user-circle'></i> <span>Clients</span> </a> </li>
          <li> <a href="#!"><i class='bx bx-pulse'></i> <span>Activities</span> </a> </li>
          <li> <a href="#!"><i class='bx bx-calendar'></i> <span>Sessions</span> </a> </li>
          <li> <a href="#!"><i class='bx  bx-clipboard-detail'></i> <span>Attendance</span> </a> </li>
          <li> <a href="#!"><i class='bx  bx-clipboard-detail'></i> <span>Follow-up Tracker</span></a></li>
          <li> <a href="#!"><i class='bx  bx-phone'></i> <span>Call Transcripts</span></a></li>

          <li class="header-menu"> <span>General</span> </li>
          <li> <a href="#!"><i class='bx  bx-mobile'></i> <span>Staff Portal</span> </a></li>
          <li> <a href="{{ url('/roster/messaging-center') }}"><i class='bx  bx-message'></i> <span>Messaging Center</span> </a></li>
          <li> <a href="{{ url('/roster/staff-task') }}"><i class='bx  bx-clipboard-detail'></i> <span>Staff Tasks</span> </a></li>
          <li> <a href="{{ url('/roster/care-document') }}"><i class='bx  bx-folder-open'></i> <span>Care Documents</span> </a></li>
          <li> <a href="{{ url('roster/supervision-management') }}"><i class='bx  bx-clipboard-detail'></i> <span>Staff Supervisions</span> </a></li>
          <li> <a href="{{ url('/roster/incident-management') }}"><i class='bx  bx-shield'></i> <span>Incident Management</span> </a></li>
          <?php $encodedUser = base64_encode(Auth::user()->user_name);
          $url = "http://thunderingslap.com/Learner/Learner-Dashboard/?key=" . $encodedUser;
          ?>
          <li> <a href="{{ $url }}"><i class='bx  bx-education'></i> <span>Training</span> </a></li>
          <li> <a href="{{url('/roster/notifications')}}"><i class='bx  bx-bell'></i> <span>Notifications</span> </a></li>
          <li> <a href="{{ url('/roster/leave-request') }}"><i class='bx  bx-clipboard-detail'></i> <span>Leave Requests</span> </a></li>
          <li> <a href="{{ url('/roster/payroll-finance') }}"><i class='bx bx-file-detail'></i> <span>Payroll & Finance</span> </a></li>
          <li> <a href="{{ url('/roster/reports') }}"><i class='bx  bx-file-report'></i> <span>Reports</span> </a></li>
          <li> <a href="{{ url('/roster/onboarding-configuration') }}"><i class='bx bx-cog'></i><span>Onboarding Configuration</span></a></li>
          <li> <a href="{{ url('/roster/staffonboarding')}}"><i class='bx  bx-file-report'></i> <span>Staff Onboarding</span> </a></li>
          <li> <a href="{{ url('/roster/clientonboarding')}}"><i class='bx  bx-file-report'></i> <span>Client Onboarding</span> </a></li>
          <li> <a href="{{ url('/roster/compliance-hub')}}"><i class='bx  bx-shield'></i> <span>Compliance Hub</span> </a></li>
          <li> <a href="{{ url('/roster/task-center')}}"><i class='bx  bx-clipboard-detail'></i> <span>Task Center</span> </a></li>
          <li> <a href="{{ url('/roster/action-plan-progress')}}"><i class='bx  bx-clipboard-detail'></i> <span>Action Plan Progress</span> </a></li>
          <li> <a href="{{ url('/roster/reporting-engine')}}"><i class='bx bx-file-detail'></i> <span>Reporting Engine</span> </a></li>
          <li> <a href="{{ url('/roster/compliance-dashboard')}}"><i class='bx  bx-message'></i> <span>Compliance Dashboard</span> </a></li>
          <li> <a href="{{ url('/roster/audit-log')}}"><i class='bx bx-file-detail'></i> <span>Audit Log</span> </a></li>
          <li> <a href="{{ url('/roster/form-builder')}}"><i class='bx bx-file-detail'></i> <span>Form Builder</span> </a></li>
          <li> <a href="{{ url('/roster/crm-dashboard')}}"><i class='bx bx-group'></i> <span>CRM Dashboard</span> </a></li>
          <li> <a href="{{ url('/roster/client-comms-hub')}}"><i class='bx  bx-message'></i> <span>Client Comms Hub</span> </a></li>
          <li class="header-menu"> <span>System</span> </li>
          <li> <a href="{{ url('/roster/role-management')}}"><i class='bx  bx-shield'></i> <span>Role Management</span> </a></li>
          <li> <a href="{{ url('/roster/module-settings')}}"><i class='bx  bx-cog'></i> <span>Module Settings</span> </a></li>
          <li> <a href="{{ url('/roster/user-management')}}"><i class='bx bx-group'></i> <span>User Management</span> </a></li>
          <li> <a href="#!"><i class='bx bx-file-detail'></i> <span>Technical Spec</span> </a></li>
        </ul>
      </div>
      <!-- sidebar-menu  -->
    </div>
    <!-- sidebar-content  -->
  </nav>




  <script>
    $(".sidebar-dropdown > a").click(function() {
      $(".sidebar-submenu").slideUp(200);
      if (
        $(this)
        .parent()
        .hasClass("active")
      ) {
        $(".sidebar-dropdown").removeClass("active");
        $(this)
          .parent()
          .removeClass("active");
      } else {
        $(".sidebar-dropdown").removeClass("active");
        $(this)
          .next(".sidebar-submenu")
          .slideDown(200);
        $(this)
          .parent()
          .addClass("active");
      }
    });

    $("#close-sidebar").click(function() {
      $(".page-wrapper").removeClass("toggled");
    });
    $("#show-sidebar").click(function() {
      $(".page-wrapper").addClass("toggled");
    });


    $(document).ready(function() {
      var currentUrl = window.location.href;

      $(".sidebar-menu ul li a").each(function() {
        if (this.href === currentUrl) {
          $(".sidebar-menu ul li a").removeClass("active");
          $(this).addClass("active");
        }
      });
    });
  </script>