<style>
    ul.sidebar-menu li ul.sub.pdlft {
        /* padding-left: 36px; */
        list-style: none;
        /* background-color: #202025; */
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
                    <li>
                        <a href="{{ url('admin/welcome') }}" class="{{ $page == 'welcome' ? 'active' : '' }}">
                            <i class="fa fa-street-view "></i>
                            <span>Welcome</span>
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ url('admin/dashboard') }}" class="{{ $page == 'dashboard' ? 'active' : '' }}">
                        <i class="fa fa-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                @if ($super_admin == 'S')
                    <li class="sub-menu">
                        <a href="{{ url('admin/migrations') }}" class="<?php if ($page == 'company_manager' || $page == 'system-admins' || $page == 'supr_migrations' || $page == 'super_admin_user' || $page == 'file_category_name' || $page == 'social-app' || $page == 'ethnicity' || $page == 'company_charges') {
                            echo 'active';
                        } ?>">
                            <i class="fa fa-university"></i>
                            <span>Super Admin</span>
                        </a>
                        <ul class="sub">
                            <li class="{{ $page == 'company_manager' ? 'active' : '' }}">
                                <a href="{{ url('admin/company-managers') }}">
                                    <!-- <i class="fa fa-university"></i> -->
                                    <span>Company Manager</span>
                                </a>
                            </li>
                            <li class="{{ $page == 'system-admins' ? 'active' : '' }}">
                                <a href="{{ url('admin/system-admins') }}">
                                    <!-- <i class="fa fa-university"></i> -->
                                    <span>Companies</span>
                                </a>
                            </li>
                            <li class="{{ $page == 'company_charges' ? 'active' : '' }}">
                                <a href="{{ url('admin/company-charges') }}">
                                    <!-- <i class="fa fa-university"></i> -->
                                    <span>Company Charges</span>
                                </a>
                            </li>
                            <li class="{{ $page == 'supr_migrations' ? 'active' : '' }}">
                                <a href="{{ url('super-admin/migrations') }}">
                                    <!-- <i class="fa fa-upload"></i> -->
                                    <span>Migrations</span>
                                </a>
                            </li>

                            <li class="{{ $page == 'super_admin_user' ? 'active' : '' }}">
                                <a href="{{ url('super-admin/users') }}">
                                    <!-- <i class="fa fa-upload"></i> -->
                                    <span>Super Admin Users</span>
                                </a>
                            </li>

                            <li class="{{ $page == 'file_category_name' ? 'active' : '' }}">
                                <a href="{{ url('super-admin/filemanager-categories') }}">
                                    <!-- <i class="fa fa-upload"></i> -->
                                    <span>File Manager Categories</span>
                                </a>
                            </li>
                            <!-- <li class="{{ $page == 'file_category_name' ? 'active' : '' }}">
                            <a href="{{ url('super-admin/filemanager-categories') }}"> -->
                            <!-- <i class="fa fa-upload"></i> -->
                            <!-- <span>Mandatory leave</span>
                            </a>
                        </li> -->

                            <li class="{{ $page == 'social-app' ? 'active' : '' }}">
                                <a href="{{ url('super-admin/social-apps') }}">
                                    <!-- <i class="fa fa-upload"></i> -->
                                    <span>Social Apps</span>
                                </a>
                            </li>
                            <li class="{{ $page == 'ethnicity' ? 'active' : '' }}">
                                <a href="{{ url('super-admin/ethnicities') }}">
                                    <!-- <i class="fa fa-upload"></i> -->
                                    <span>Ethnicity</span>
                                </a>
                            </li>

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
                        $page == 'daily_log_sub_category'
                    ) {
                        echo 'active';
                    } ?>">
                        <i class="fa fa-laptop"></i>
                        <span>System Management</span>
                    </a>
                    {{-- {{ $page }} --}}
                    <ul class="sub">
                        <li class="<?php if ($page == 'users' || $page == 'user-task' || $page == 'user-sick-leave' || $page == 'user-annual-leave' || $page == 'user-late-leave') {
                            echo 'active';
                        } ?>"><a href="{{ url('admin/users') }}">Users</a></li>
                        <li class="<?php if ($page == 'service-users' || $page == 'service-users-care-history' || $page == 'care_team' || $page == 'moods' || $page == 'su_migration_form' || $page == 'incident-report' || $page == 'external-service' || $page == 'su-dynamic-form' || $page == 'file_manager' || $page == 'service-user-my-money-history' || $page == 'service-user-my-money-request' || $page == 'service-users-log-book' || $page == 'service-users-living-skill' || $page == 'service-users-rmp' || $page == 'service-users-risk' || $page == 'service-users-earn-schm' || $page == 'service-users-bmp') {
                            echo 'active';
                        } ?>"><a href="{{ url('admin/service-users') }}">Childs</a></li>

                        <li class="{{ $page == 'child_section' ? 'active' : '' }}"><a
                                href="{{ url('admin/child-sections') }}">Child Section</a></li>
                        <li class="{{ $page == 'agents' ? 'active' : '' }}"><a
                                href="{{ url('admin/agents') }}">Agent</a></li>
                        <!-- <li class="{{ $page == 'daily_records' ? 'active' : '' }}"><a href="{{ url('admin/daily-record') }}">Daily Log</a></li> -->
                        <!-- <li class="{{ $page == 'daily-record-scores' ? 'active' : '' }}"><a href="{{ url('admin/daily-record-scores') }}">Daily Log Scores</a></li> -->
                        <li class="{{ $page == 'education-training' ? 'active' : '' }}"><a
                                href="{{ url('admin/education-trainings') }}">Education Records</a></li>
                        <li class="{{ $page == 'living_skill' ? 'active' : '' }}"><a
                                href="{{ url('admin/living-skill') }}"> Independent Living Skills </a></li>
                        <!-- <li class="{{ $page == 'mfc' ? 'active' : '' }}"><a href="{{ url('admin/mfc-records') }}">MFC</a></li> -->
                        <li class="{{ $page == 'risks' ? 'active' : '' }}"><a
                                href="{{ url('admin/risk') }}">Risks</a></li>
                        <li
                            class="{{ $page == 'earning_scheme' || $page == 'incentive_earning_scheme' ? 'active' : '' }}">
                            <a href="{{ url('admin/earning-scheme') }}">Earning Scheme </a></li>
                        <li class="{{ $page == 'earning_scheme_label' ? 'active' : '' }}"><a
                                href="{{ url('admin/earning-scheme-labels') }}">Earning Scheme Labels</a></li>
                        <li class="{{ $page == 'form-builder' || $page == 'form-builder' ? 'active' : '' }}"><a
                                href="{{ url('admin/form-builder') }}">Form Builder </a></li>
                        <li class="{{ $page == 'label' ? 'active' : '' }}"><a
                                href="{{ url('admin/labels') }}">Labels</a></li>
                        <li class="{{ $page == 'categories' ? 'active' : '' }}"><a
                                href="{{ url('admin/categories') }}">Log Category Labels</a></li>
                        <li class="{{ $page == 'support_ticket' ? 'active' : '' }}"><a
                                href="{{ url('admin/support-ticket') }}">Support Ticket</a></li>
                        <li class="{{ $page == 'modification-request' ? 'active' : '' }}"><a
                                href="{{ url('admin/modification-requests') }}">Modification Requests</a></li>
                        <!-- <li class="{{ $page == 'contact-us' ? 'active' : '' }}"><a href="{{ url('admin/contact-us') }}"> Contact-us </a></li> -->
                        <li class="{{ $page == 'system-guide' ? 'active' : '' }}"><a
                                href="{{ url('admin/system-guide-category') }}"> System Guide </a></li>
                        <li class="{{ $page == 'managers' ? 'active' : '' }}"><a href="{{ url('admin/managers') }}">
                                Managers </a></li>
                        <!-- <li class="{{ $page == 'placement_plan' ? 'active' : '' }}"><a href="{{ url('admin/placement-plan') }}">Placement Plan</a></li> -->
                        <li class="{{ $page == 'appointmen_plans' ? 'active' : '' }}"><a
                                href="{{ url('admin/appointment/plans') }}"> Appointments / Plans </a></li>
                        <li class="{{ $page == 'daily_log_category' ? 'active' : '' }}"><a
                                href="{{ url('admin/daily-log-category') }}"> Daily Log Category </a></li>
                        <li class="{{ $page == 'daily_log_sub_category' ? 'active' : '' }}"><a
                                href="{{ url('admin/daily-log-sub-category') }}"> Daily Log Sub Category </a></li>
                    </ul>
                </li>

                <li class="sub-menu">
                    <a href="javascript:;" class="<?php if ($page == 'staff_worker') {
                        echo 'active';
                    } ?>"> <i class="fa fa-users"></i> <span>Rota
                            management</span> </a>
                    <ul class="sub">
                        <li class="{{ $page == 'staff_worker' ? 'active' : '' }}"><a
                                href="{{ url('/admin/rota/staff-worker') }}">Staff</a></li>
                    </ul>
                </li>

                <li class="sub-menu">
                    <a href="javascript:;" class="<?php if (($page == 'care_team_job_title') || ($page == 'mood_title') || ($page == 'access_levels') || ($page == 'rota_shift') || ($page == 'pay_rates_type') || ($page == 'pay_rates') || ($page == 'homelist') || ($page == 'policies') || ($page == 'leaves') || ($page == 'incidenttype') || ($page == 'client_taskTyep') || ($page == 'client_taskCategory') || ($page == 'alert_type')) {
                        echo 'active';
                    } ?>">
                        <i class="fa fa-home"></i>
                        <span>Home Management</span>
                    </a>
                    <ul class="sub">
                        <!--if($super_admin != 'S')-->
                        @if ($super_admin == 'O')
                            <li class="{{ $page == 'homelist' ? 'active' : '' }}"><a
                                    href="{{ url('admin/homelist') }}">Homes</a></li>
                        @endif

                        <li class="{{ $page == 'care_team_job_title' ? 'active' : '' }}">
                            <a href="{{ url('admin/care-team-job-titles') }}">Care Team Job Titles</a>
                        </li>
                        <li class="{{ $page == 'mood_title' ? 'active' : '' }}">
                            <a href="{{ url('admin/moods') }}">Moods</a>
                        </li>
                        <li class="{{ $page == 'pay_rates_type' ? 'active' : '' }}">
                            <a href="{{ url('admin/user/pay-rates-type') }}">Rates Type</a>
                        </li>
                        <li class="{{ $page == 'pay_rates' ? 'active' : '' }}">
                            <a href="{{ url('admin/user/pay-rates') }}">Pay Rates</a>
                        </li>
                        <li class="{{ $page == 'access_levels' ? 'active' : '' }}">
                            <a href="{{ url('admin/home/access-levels') }}">Access Levels</a>
                        </li>
                        <!-- <li class="{{ $page == 'rota_shift' ? 'active' : '' }}">
                            <a href="{{ url('admin/home/rota-shift') }}">Rota Shift</a>
                        </li> -->
                        <li class="{{ $page == 'policies' ? 'active' : '' }}">
                            <a href="{{ url('admin/home/policies') }}">Policies & Procedure</a>
                        </li>

                        <li class="{{ $page == 'leaves' ? 'active' : '' }}">
                            <a href="{{ url('admin/leave-type') }}">Leaves </a>
                        </li>
                        <li class="{{ $page == 'incidenttype' ? 'active' : '' }}">
                            <a href="{{ url('admin/incident-type') }}">Incident Type </a>
                        </li>
                        <li class="{{ $page == 'safeguardingtype' ? 'active' : '' }}">
                            <a href="{{ url('admin/safeguarding-type') }}">Safeguarding Type </a>
                        </li>
                        <li class="sub-menu">
                            <a href="javascript:void(0)"
                                <?php if (($page == 'client_taskTyep') || ($page == 'client_taskCategory') || ($page == 'alert_type')) {
                                    echo 'class="active" style="color:#1fb5ad"';
                                } ?>>
                                <i class="fa fa-list-ul"></i><span>Client</span>
                            </a>
                            <ul class="sub pdlft">
                                <li class="{{ ($page == 'client_taskTyep') ? 'active' : '' }}">
                                    <a href="{{ url('admin/client-task-type') }}">  <!--<i class="fa fa-briefcase"></i> -->  Task Type  </a>
                                </li>
                                <li class="{{ ($page == 'client_taskCategory') ? 'active' : '' }}">
                                    <a href="{{ url('admin/task/category') }}"> <!--<i class="fa fa-paperclip"></i> --> Category </a>
                                </li>
                                <li class="{{ ($page == 'alert_type') ? 'active' : '' }}">
                                    <a href="{{ url('admin/alert-type') }}"> <!--<i class="fa fa-paperclip"></i> --> Alert Type </a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="sub-menu">
                    <a href="javascript:;" class="<?php if ($page == 'agenda_meeting' || $page == 'petty_cash' || $page == 'log_book' || $page == 'weekly_allowance' || $page == 'staff_training' || $page == 'home_costing' || $page == 'department') {
                        echo 'active';
                    } ?>">
                        <i class="fa fa-cogs"></i>
                        <span>General Admin</span>
                    </a>
                    <ul class="sub">

                        <li class="{{ $page == 'agenda_meeting' ? 'active' : '' }}">
                            <a href="{{ url('admin/general-admin/agenda/meetings') }}">Agenda Meetings </a>
                        </li>
                        <li class="{{ $page == 'petty_cash' ? 'active' : '' }}">
                            <a href="{{ url('admin/general-admin/petty/cash') }}">Petty Cash </a>
                        </li>
                        <li class="{{ $page == 'log_book' ? 'active' : '' }}">
                            <a href="{{ url('admin/general-admin/log/book') }}">Log Book </a>
                        </li>
                        <li class="{{ $page == 'weekly_allowance' ? 'active' : '' }}">
                            <a href="{{ url('admin/general-admin/allowance/weekly') }}">Weekly Allowance </a>
                        </li>
                        <li class="{{ $page == 'staff_training' ? 'active' : '' }}">
                            <a href="{{ url('admin/general-admin/staff/training') }}">Staff Training </a>
                        </li>
                        <li class="{{ $page == 'home_costing' ? 'active' : '' }}">
                            <a href="{{ url('admin/general-admin/home-costing') }}">Home Costing </a>
                        </li>
                        <li class="{{ $page == 'department' ? 'active' : '' }}">
                            <a href="{{ url('admin/general-admin/department') }}">Department </a>
                        </li>
                    </ul>
                </li>
                <!-- Sales and Finanace -->
             
                <!-- Jobs -->
               
                <!-- Setting Section Start -->
               
                <!-- Setting Section End -->

                <!-- Contact Section Start -->
            
                <!-- Contact Section End -->
            </ul>
        </div>
        <!-- sidebar menu end-->
    </div>
</aside>
<!--sidebar end-->
