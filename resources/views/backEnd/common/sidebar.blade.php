<style>
    ul.sidebar-menu li ul.sub.pdlft {
        list-style: none;
    }
</style>

<!-- sidebar start -->
<?php $super_admin = Session::get('scitsAdminSession')->access_type; ?>
<aside>
    <div id="sidebar" class="nav-collapse">
        <!-- sidebar menu start-->
        <div class="leftside-navigation">
            <ul class="sidebar-menu" id="nav-accordion">
                @if ($super_admin == 'S' || $super_admin == 'O')
                <!-- if super admin or owner is login -->
                <li> <a href="{{ url('admin/welcome') }}" class="{{ $page == 'welcome' ? 'active' : '' }}"> <i class="fa fa-street-view "></i> <span>Welcome</span> </a></li>
                @endif
                <li> <a href="{{ url('admin/dashboard') }}" class="{{ $page == 'dashboard' ? 'active' : '' }}"> <i class="fa fa-dashboard"></i> <span>Dashboard</span> </a> </li>
                @if ($super_admin == 'S')
                <li class="sub-menu">
                    <a href="{{ url('admin/migrations') }}" class="{{ in_array($page, ['company_manager', 'system-admins', 'supr_migrations', 'super_admin_user', 'file_category_name', 'social-app', 'ethnicity', 'company_charges']) ? 'active' : '' }}">
                        <i class="fa fa-university"></i> <span>Super Admin</span> </a>
                    <ul class="sub">
                        <li class="{{ $page == 'company_manager' ? 'active' : '' }}"><a href="{{ url('admin/company-managers') }}"> <span>Company Manager</span> </a></li>
                        <li class="{{ $page == 'system-admins' ? 'active' : '' }}"><a href="{{ url('admin/system-admins') }}"> <span>Companies</span> </a></li>
                        <li class="{{ $page == 'company_charges' ? 'active' : '' }}"><a href="{{ url('admin/company-charges') }}"> <span>Company Charges</span> </a></li>
                        <!-- <li class="{{ $page == 'super_admin_user' ? 'active' : '' }}"><a href="{{ url('super-admin/users') }}"> <span>Super Admin Users</span> </a></li>
                        <li class="{{ $page == 'file_category_name' ? 'active' : '' }}"><a href="{{ url('super-admin/filemanager-categories') }}"> <span>File Manager Categories</span> </a></li>
                        <li class="{{ $page == 'social-app' ? 'active' : '' }}"><a href="{{ url('super-admin/social-apps') }}"> <span>Social Apps</span> </a></li>
                        <li class="{{ $page == 'ethnicity' ? 'active' : '' }}"><a href="{{ url('super-admin/ethnicities') }}"> <span>Ethnicity</span> </a></li> -->
                    </ul>
                </li>
                @endif

                <li class="sub-menu">
                    <a href="javascript:;" class="<?php if (
                                                        $page == 'users' ||
                                                        $page == 'service-users' ||
                                                        $page == 'daily_records' ||
                                                        $page == 'risks' ||
                                                        $page == 'earning_scheme' ||
                                                        $page == 'earning_scheme_label' ||
                                                        $page == 'incentive_earning_scheme' ||
                                                        $page == 'service-users-care-history' ||
                                                        $page == 'care_team' ||
                                                        $page == 'moods' ||
                                                        $page == 'support_ticket' ||
                                                        $page == 'placement_plan' ||
                                                        $page == 'form-builder' ||
                                                        $page == 'label' ||
                                                        $page == 'categories' ||
                                                        $page == 'su_migration_form' ||
                                                        $page == 'modification-request' ||
                                                        $page == 'living_skill' ||
                                                        $page == 'education-training' ||
                                                        $page == 'mfc' ||
                                                        $page == 'incident-report' ||
                                                        $page == 'contact-us' ||
                                                        $page == 'daily-record-scores' ||
                                                        $page == 'system-guide' ||
                                                        $page == 'external-service' ||
                                                        $page == 'su-dynamic-form' ||
                                                        $page == 'file_manager' ||
                                                        $page == 'user-task' ||
                                                        $page == 'user-sick-leave' ||
                                                        $page == 'user-annual-leave' ||
                                                        $page == 'service-user-my-money-history' ||
                                                        $page == 'service-user-my-money-request' ||
                                                        $page == 'service-users-log-book' ||
                                                        $page == 'service-users-living-skill' ||
                                                        $page == 'service-users-rmp' ||
                                                        $page == 'service-users-risk' ||
                                                        $page == 'service-users-earn-schm' ||
                                                        $page == 'service-users-bmp' ||
                                                        ($page == 'agents' || $page == 'managers') ||
                                                        $page == 'appointmen_plans' ||
                                                        $page == 'child_section' ||
                                                        $page == 'daily_log_category' ||
                                                        $page == 'daily_log_sub_category' ||
                                                        $page == 'transport' ||
                                                        $page == 'department'
                                                    ) {
                                                        echo 'active';
                                                    } ?>">
                        <i class="fa fa-laptop"></i>
                        <span>System Management</span>
                    </a>
                    {{-- {{ $page }} --}}
                    <ul class="sub">
                        <li class="{{ in_array($page, ['users', 'user-task', 'user-sick-leave', 'user-annual-leave', 'user-late-leave']) ? 'active' : '' }}"><a href="{{ url('admin/users') }}">Users</a></li>
                        <li class="{{ in_array($page, ['service-users', 'service-users-care-history', 'care_team', 'moods', 'su_migration_form', 'incident-report', 'external-service', 'su-dynamic-form', 'file_manager', 'service-user-my-money-history', 'service-user-my-money-request', 'service-users-log-book', 'service-users-living-skill', 'service-users-rmp', 'service-users-risk', 'service-users-earn-schm', 'service-users-bmp']) ? 'active' : '' }}"><a href="{{ url('admin/service-users') }}">Childs</a></li>
                        <li class="{{ in_array($page, ['form-builder']) ? 'active' : '' }}"><a href="{{ url('admin/form-builder') }}">Form Builder </a></li>
                        <li class="{{ in_array($page, ['daily_log_category']) ? 'active' : '' }}"><a href="{{ url('admin/daily-log-category') }}"> Daily Log Category </a></li>
                        <li class="{{ in_array($page, ['daily_log_sub_category']) ? 'active' : '' }}"><a href="{{ url('admin/daily-log-sub-category') }}"> Daily Log Sub Category </a></li>
                        <li class="{{ in_array($page, ['transport']) ? 'active' : '' }}"><a href="{{ url('admin/transport') }}"> Transport </a></li>
                        <li class="{{ in_array($page, ['department']) ? 'active' : '' }}"><a href="{{ url('admin/general-admin/department') }}">Department </a></li>
                        <!--<li class="{{ $page == 'child_section' ? 'active' : '' }}"> <a href="{{ url('admin/child-sections') }}">Child Section</a></li>-->
                        <!--<li class="{{ $page == 'agents' ? 'active' : '' }}"><a href="{{ url('admin/agents') }}">Agent</a></li>-->
                        <!-- <li class="{{ $page == 'daily_records' ? 'active' : '' }}"><a href="{{ url('admin/daily-record') }}">Daily Log</a></li> -->
                        <!-- <li class="{{ $page == 'daily-record-scores' ? 'active' : '' }}"><a href="{{ url('admin/daily-record-scores') }}">Daily Log Scores</a></li> -->
                        <!--<li class="{{ $page == 'education-training' ? 'active' : '' }}"><a href="{{ url('admin/education-trainings') }}">Education Records</a></li>-->
                        <!--<li class="{{ $page == 'living_skill' ? 'active' : '' }}"><a href="{{ url('admin/living-skill') }}"> Independent Living Skills </a></li>-->
                        <!-- <li class="{{ $page == 'mfc' ? 'active' : '' }}"><a href="{{ url('admin/mfc-records') }}">MFC</a></li> -->
                        <!--<li class="{{ $page == 'risks' ? 'active' : '' }}"><a href="{{ url('admin/risk') }}">Risks</a></li>-->
                        <!--<li class="{{ $page == 'earning_scheme' || $page == 'incentive_earning_scheme' ? 'active' : '' }}"> <a href="{{ url('admin/earning-scheme') }}">Earning Scheme </a></li>-->
                        <!--<li class="{{ $page == 'earning_scheme_label' ? 'active' : '' }}"><a href="{{ url('admin/earning-scheme-labels') }}">Earning Scheme Labels</a></li>-->
                        <!--<li class="{{ $page == 'label' ? 'active' : '' }}"><a href="{{ url('admin/labels') }}">Labels</a></li>-->
                        <!--<li class="{{ $page == 'categories' ? 'active' : '' }}"><a href="{{ url('admin/categories') }}">Log Category Labels</a></li>-->
                        <!--<li class="{{ $page == 'support_ticket' ? 'active' : '' }}"><a href="{{ url('admin/support-ticket') }}">Support Ticket</a></li>-->
                        <!--<li class="{{ $page == 'modification-request' ? 'active' : '' }}"><a href="{{ url('admin/modification-requests') }}">Modification Requests</a></li>-->
                        <!-- <li class="{{ $page == 'contact-us' ? 'active' : '' }}"><a href="{{ url('admin/contact-us') }}"> Contact-us </a></li> -->
                        <!--<li class="{{ $page == 'system-guide' ? 'active' : '' }}"><a href="{{ url('admin/system-guide-category') }}"> System Guide </a></li>-->
                        <!--<li class="{{ $page == 'managers' ? 'active' : '' }}"><a href="{{ url('admin/managers') }}">Managers </a></li>-->
                        <!-- <li class="{{ $page == 'placement_plan' ? 'active' : '' }}"><a href="{{ url('admin/placement-plan') }}">Placement Plan</a></li> -->
                        <!--<li class="{{ $page == 'appointmen_plans' ? 'active' : '' }}"><a href="{{ url('admin/appointment/plans') }}"> Appointments / Plans </a></li>-->
                    </ul>
                </li>

                <!--<li class="sub-menu">-->
                <!--    <a href="javascript:;" class="<?php //if ($page == 'staff_worker') { echo 'active'; } 
                                                        ?>"> <i class="fa fa-users"></i> <span>Rota management</span> </a>-->
                <!--    <ul class="sub">-->
                <!--        <li class="{{ $page == 'staff_worker' ? 'active' : '' }}"><a href="{{ url('/admin/rota/staff-worker') }}">Staff</a></li>-->
                <!--    </ul>-->
                <!--</li>-->

                <li class="sub-menu">
                    <a href="javascript:;" class="{{ in_array($page, ['care_team_job_title', 'mood_title', 'access_levels', 'rota_shift', 'pay_rates_type', 'pay_rates', 'homelist', 'policies', 'shift_category', 'leaves', 'incidenttype']) ? 'active' : '' }}">
                        <i class="fa fa-home"></i>
                        <span>Home Management</span>
                    </a>
                    <ul class="sub">
                        <!--if($super_admin != 'S')-->
                        @if ($super_admin == 'O')
                        <li class="{{ $page == 'homelist' ? 'active' : '' }}"><a href="{{ url('admin/homelist') }}">Homes</a></li>
                        @endif

                        <!-- <li class="{{ $page == 'care_team_job_title' ? 'active' : '' }}"><a href="{{ url('admin/care-team-job-titles') }}">Care Team Job Titles</a></li> -->
                        <li class="{{ $page == 'mood_title' ? 'active' : '' }}"><a href="{{ url('admin/moods') }}">Moods</a></li>
                        <li class="{{ $page == 'pay_rates_type' ? 'active' : '' }}"><a href="{{ url('admin/user/pay-rates-type') }}">Rates Type</a></li>
                        <li class="{{ $page == 'pay_rates' ? 'active' : '' }}"><a href="{{ url('admin/user/pay-rates') }}">Pay Rates</a></li>
                        <li class="{{ $page == 'shift_category' ? 'active' : '' }}"><a href="{{ url('admin/user/shift-category') }}">Shift Category</a></li>
                        <li class="{{ $page == 'access_levels' ? 'active' : '' }}"><a href="{{ url('admin/home/access-levels') }}">Access Levels</a></li>
                        <li class="{{ $page == 'incidenttype' ? 'active' : '' }}"><a href="{{ url('admin/incident-type') }}">Incident Type </a></li>
                        <li class="{{ $page == 'safeguardingtype' ? 'active' : '' }}"><a href="{{ url('admin/safeguarding-type') }}">Safeguarding Type </a></li>
                        <li class="{{ $page == 'stafftasktype' ? 'active' : '' }}"><a href="{{ url('admin/stafftask-type') }}">Staff Task Type </a></li>
                        <li class="{{ $page == 'entitytype' ? 'active' : '' }}"><a href="{{ url('admin/entity-type') }}">Entity Type </a></li>
                        <li class="{{ $page == 'policylibrarycategory' ? 'active' : '' }}"><a href="{{ url('admin/policylibrarycategory') }}">Policy Library Category </a></li>
                        <!-- <li class="{{ $page == 'rota_shift' ? 'active' : '' }}"><a href="{{ url('admin/home/rota-shift') }}">Rota Shift</a></li> -->
                        <!--<li class="{{ $page == 'policies' ? 'active' : '' }}"><a href="{{ url('admin/home/policies') }}">Policies & Procedure</a></li>-->
                        <!--<li class="{{ $page == 'leaves' ? 'active' : '' }}"><a href="{{ url('admin/leave-type') }}">Leaves </a></li>-->
                    </ul>
                </li>

                <li class="sub-menu">
                    <a href="javascript:;" class="{{ in_array($page, ['agenda_meeting', 'petty_cash', 'log_book', 'weekly_allowance', 'staff_training', 'home_costing', 'client_taskTyep', 'client_taskCategory', 'alert_type', 'client_care_plan']) ? 'active' : '' }}">
                        <i class="fa fa-cogs"></i> <span>General Setting</span>
                    </a>
                    <ul class="sub">
                        <li class="sub-menu">
                            <a href="javascript:void(0)" @if(in_array($page, ['client_taskTyep', 'client_taskCategory' , 'alert_type', 'client_care_plan' ])) class="active" style="color:#1fb5ad" @endif>
                                <i class="fa fa-list-ul"></i><span>Client</span>
                            </a>
                            <ul class="sub pdlft">
                                <li class="{{ ($page == 'client_taskTyep') ? 'active' : '' }}"><a href="{{ url('admin/client-task-type') }}"> Task Type </a></li>
                                <li class="{{ ($page == 'client_taskCategory') ? 'active' : '' }}"><a href="{{ url('admin/task/category') }}"> Category </a></li>
                                <li class="{{ ($page == 'alert_type') ? 'active' : '' }}"><a href="{{ url('admin/alert-type') }}"> Alert Type </a></li>
                                <li class="{{ ($page == 'client_care_plan') ? 'active' : '' }}"><a href="{{ url('admin/client-care-plan') }}"> Care Plan </a></li>
                            </ul>
                        </li>
                        <!--<li class="{{ $page == 'agenda_meeting' ? 'active' : '' }}">-->
                        <!--    <a href="{{ url('admin/general-admin/agenda/meetings') }}">Agenda Meetings </a>-->
                        <!--</li>-->
                        <!--<li class="{{ $page == 'petty_cash' ? 'active' : '' }}">-->
                        <!--    <a href="{{ url('admin/general-admin/petty/cash') }}">Petty Cash </a>-->
                        <!--</li>-->
                        <!--<li class="{{ $page == 'log_book' ? 'active' : '' }}">-->
                        <!--    <a href="{{ url('admin/general-admin/log/book') }}">Log Book </a>-->
                        <!--</li>-->
                        <!--<li class="{{ $page == 'weekly_allowance' ? 'active' : '' }}">-->
                        <!--    <a href="{{ url('admin/general-admin/allowance/weekly') }}">Weekly Allowance </a>-->
                        <!--</li>-->
                        <!--<li class="{{ $page == 'staff_training' ? 'active' : '' }}">-->
                        <!--    <a href="{{ url('admin/general-admin/staff/training') }}">Staff Training </a>-->
                        <!--</li>-->
                        <!--<li class="{{ $page == 'home_costing' ? 'active' : '' }}">-->
                        <!--    <a href="{{ url('admin/general-admin/home-costing') }}">Home Costing </a>-->
                        <!--</li>-->
                        <!--<li class="{{ $page == 'department' ? 'active' : '' }}">-->
                        <!--    <a href="{{ url('admin/general-admin/department') }}">Department </a>-->
                        <!--</li>-->
                    </ul>
                </li>
            </ul>
        </div>
        <!-- sidebar menu end-->
    </div>
</aside>
<!--sidebar end-->