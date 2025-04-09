<style>
    ul.sidebar-menu li ul.sub.pdlft {
        padding-left: 36px;
        list-style: none;
        background-color: #202025;
    }
</style>
<!-- sidebar start -->
<?php $super_admin = Session::get('scitsAdminSession')->access_type;  ?>
<aside>
    <div id="sidebar" class="nav-collapse">
        <!-- sidebar menu start-->
        <div class="leftside-navigation">
            <ul class="sidebar-menu" id="nav-accordion">
                @if( ($super_admin == 'S') || ($super_admin == 'O') ) <!-- if super admin or owner is login -->
                <li>
                    <a href="{{ url('admin/welcome') }}" class="{{ ($page == 'welcome') ? 'active' : ''}}">
                        <i class="fa fa-street-view "></i>
                        <span>Welcome</span>
                    </a>
                </li>
                @endif
                <li>
                    <a href="{{ url('admin/dashboard') }}" class="{{ ($page == 'dashboard') ? 'active' : ''}}">
                        <i class="fa fa-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                @if($super_admin == 'S')

                <li class="sub-menu">
                    <a href="{{ url('admin/migrations') }}" class="<?php if (($page == 'company_manager' || $page == 'system-admins') || ($page == 'supr_migrations') || ($page == 'super_admin_user') || ($page == 'file_category_name') || ($page == 'social-app') || ($page == 'ethnicity') || ($page == 'company_charges')) {
                                                                        echo 'active';
                                                                    } ?>">
                        <i class="fa fa-university"></i>
                        <span>Super Admin</span>
                    </a>
                    <ul class="sub">
                        <li class="{{ ($page == 'company_manager') ? 'active' : ''}}">
                            <a href="{{ url('admin/company-managers') }}">
                                <!-- <i class="fa fa-university"></i> -->
                                <span>Company Manager</span>
                            </a>
                        </li>
                        <li class="{{ ($page == 'system-admins') ? 'active' : ''}}">
                            <a href="{{ url('admin/system-admins') }}">
                                <!-- <i class="fa fa-university"></i> -->
                                <span>Companies</span>
                            </a>
                        </li>
                        <li class="{{ ($page == 'company_charges') ? 'active' : ''}}">
                            <a href="{{ url('admin/company-charges') }}">
                                <!-- <i class="fa fa-university"></i> -->
                                <span>Company Charges</span>
                            </a>
                        </li>
                        <li class="{{ ($page == 'supr_migrations') ? 'active' : ''}}">
                            <a href="{{ url('super-admin/migrations') }}">
                                <!-- <i class="fa fa-upload"></i> -->
                                <span>Migrations</span>
                            </a>
                        </li>

                        <li class="{{ ($page == 'super_admin_user') ? 'active' : ''}}">
                            <a href="{{ url('super-admin/users') }}">
                                <!-- <i class="fa fa-upload"></i> -->
                                <span>Super Admin Users</span>
                            </a>
                        </li>

                        <li class="{{ ($page == 'file_category_name') ? 'active' : ''}}">
                            <a href="{{ url('super-admin/filemanager-categories') }}">
                                <!-- <i class="fa fa-upload"></i> -->
                                <span>File Manager Categories</span>
                            </a>
                        </li>
                        <!-- <li class="{{ ($page == 'file_category_name') ? 'active' : ''}}">
                            <a href="{{ url('super-admin/filemanager-categories') }}"> -->
                        <!-- <i class="fa fa-upload"></i> -->
                        <!-- <span>Mandatory leave</span>
                            </a>
                        </li> -->

                        <li class="{{ ($page == 'social-app') ? 'active' : ''}}">
                            <a href="{{ url('super-admin/social-apps') }}">
                                <!-- <i class="fa fa-upload"></i> -->
                                <span>Social Apps</span>
                            </a>
                        </li>
                        <li class="{{ ($page == 'ethnicity') ? 'active' : ''}}">
                            <a href="{{ url('super-admin/ethnicities') }}">
                                <!-- <i class="fa fa-upload"></i> -->
                                <span>Ethnicity</span>
                            </a>
                        </li>

                    </ul>
                </li>
                @endif

                <li class="sub-menu">
                    <a href="javascript:;" class="<?php if (($page == 'users') || ($page == 'service-users') || ($page == 'daily_records') || ($page == 'risks') || ($page == 'earning_scheme') || ($page == 'earning_scheme_label') || ($page == 'incentive_earning_scheme') || ($page == 'service-users-care-history') || ($page == 'care_team') || ($page == 'moods') || ($page == 'support_ticket') || ($page == 'placement_plan') || ($page == 'form-builder') || ($page == 'label') || ($page == 'categories') || ($page == 'su_migration_form') || ($page == 'modification-request') || ($page == 'living_skill') || ($page == 'education-training') || ($page == 'mfc') || ($page == 'incident-report') || ($page == 'contact-us') || ($page == 'daily-record-scores') || ($page == 'system-guide') || ($page == 'external-service') || ($page == 'su-dynamic-form') || ($page == 'file_manager') || ($page == 'user-task') || ($page == 'user-sick-leave') || ($page == 'user-annual-leave') || ($page == 'service-user-my-money-history') || ($page == 'service-user-my-money-request') || ($page == 'service-users-log-book') || ($page == 'service-users-living-skill') || ($page == 'service-users-rmp') || ($page == 'service-users-risk') || ($page == 'service-users-earn-schm') || ($page == 'service-users-bmp') || $page == 'agents' || $page == 'managers') {
                                                        echo 'active';
                                                    } ?>">
                        <i class="fa fa-laptop"></i>
                        <span>System Management</span>
                    </a>
                    <ul class="sub">
                        <li class="<?php if (($page == 'users') || ($page == 'user-task') || ($page == 'user-sick-leave') || ($page == 'user-annual-leave')) {
                                        echo 'active';
                                    } ?>"><a href="{{ url('admin/users') }}">Users</a></li>
                        <li class="<?php if (($page == 'service-users') || ($page == 'service-users-care-history') || ($page == 'care_team') || ($page == 'moods') || ($page == 'su_migration_form') || ($page == 'incident-report') || ($page == 'external-service') || ($page == 'su-dynamic-form') || ($page == 'file_manager')  || ($page == 'service-user-my-money-history') || ($page == 'service-user-my-money-request') || ($page == 'service-users-log-book') || ($page == 'service-users-living-skill') || ($page == 'service-users-rmp') || ($page == 'service-users-risk') || ($page == 'service-users-earn-schm') || ($page == 'service-users-bmp')) {
                                        echo 'active';
                                    } ?>"><a href="{{ url('admin/service-users') }}">Childs</a></li>

                        <li class="{{ ($page == 'agents') ? 'active' : '' }}"><a href="{{ url('admin/agents') }}">Agent</a></li>

                        <li class="{{ ($page == 'daily_records') ? 'active' : '' }}"><a href="{{ url('admin/daily-record') }}">Daily Log</a></li>

                        <li class="{{ ($page == 'daily-record-scores') ? 'active' : '' }}"><a href="{{ url('admin/daily-record-scores') }}">Daily Log Scores</a></li>

                        <li class="{{ ($page == 'education-training') ? 'active' : '' }}"><a href="{{ url('admin/education-trainings') }}">Education Records</a></li>

                        <li class="{{ ($page == 'living_skill') ? 'active' : '' }}"><a href="{{ url('admin/living-skill') }}"> Independent Living Skills </a></li>
                        <!-- <li class="{{ ($page == 'mfc') ? 'active' : '' }}"><a href="{{ url('admin/mfc-records') }}">MFC</a></li> -->

                        <li class="{{ ($page == 'risks') ? 'active' : '' }}"><a href="{{ url('admin/risk') }}">Risks</a></li>
                        <li class="{{ ($page == 'earning_scheme') || ($page == 'incentive_earning_scheme') ? 'active' : '' }}"><a href="{{ url('admin/earning-scheme') }}">Earning Scheme </a></li>
                        <li class="{{ ($page == 'earning_scheme_label') ? 'active' : '' }}"><a href="{{ url('admin/earning-scheme-labels') }}">Earning Scheme Labels</a></li>
                        <li class="{{ ($page == 'form-builder') || ($page == 'form-builder') ? 'active' : '' }}"><a href="{{ url('admin/form-builder') }}">Form Builder </a></li>

                        <li class="{{ ($page == 'label') ? 'active' : '' }}"><a href="{{ url('admin/labels') }}">Labels</a></li>
                        <li class="{{ ($page == 'categories') ? 'active' : '' }}"><a href="{{ url('admin/categories') }}">Log Category Labels</a></li>

                        <li class="{{ ($page == 'support_ticket') ? 'active' : '' }}"><a href="{{ url('admin/support-ticket') }}">Support Ticket</a></li>
                        <li class="{{ ($page == 'modification-request') ? 'active' : '' }}"><a href="{{ url('admin/modification-requests') }}">Modification Requests</a></li>
                        <!-- <li class="{{ ($page == 'contact-us') ? 'active' : '' }}"><a href="{{ url('admin/contact-us') }}"> Contact-us </a></li> -->
                        <li class="{{ ($page == 'system-guide') ? 'active' : '' }}"><a href="{{ url('admin/system-guide-category') }}"> System Guide </a></li>

                        <li class="{{ ($page == 'managers') ? 'active' : '' }}"><a href="{{ url('admin/managers')}}"> Managers </a></li>

                        <!-- <li class="{{ ($page == 'placement_plan') ? 'active' : '' }}"><a href="{{ url('admin/placement-plan') }}">Placement Plan</a></li> -->
                    </ul>
                </li>

                <li class="sub-menu">
                    <a href="javascript:;" class="<?php if (($page == 'care_team_job_title') || ($page == 'mood_title')  || ($page == 'access_levels')  || ($page == 'rota_shift') || ($page == 'homelist') || ($page == 'policies')) {
                                                        echo 'active';
                                                    } ?>">
                        <i class="fa fa-home"></i>
                        <span>Home Management</span>
                    </a>
                    <ul class="sub">
                        <!--if($super_admin != 'S')-->
                        @if($super_admin == 'O')
                        <li class="{{ ($page == 'homelist') ? 'active' : '' }}"><a href="{{ url('admin/homelist') }}">Homes</a></li>
                        @endif

                        <li class="{{ ($page == 'care_team_job_title') ? 'active' : '' }}">
                            <a href="{{ url('admin/care-team-job-titles') }}">Care Team Job Titles</a>
                        </li>
                        <li class="{{ ($page == 'mood_title') ? 'active' : '' }}">
                            <a href="{{ url('admin/moods') }}">Moods</a>
                        </li>
                        <li class="{{ ($page == 'access_levels') ? 'active' : '' }}">
                            <a href="{{ url('admin/home/access-levels') }}">Access Levels</a>
                        </li>
                        <!-- <li class="{{ ($page == 'rota_shift') ? 'active' : '' }}">
                            <a href="{{ url('admin/home/rota-shift') }}">Rota Shift</a>
                        </li> -->
                        <li class="{{ ($page == 'policies') ? 'active' : '' }}">
                            <a href="{{ url('admin/home/policies') }}">Policies & Procedure</a>
                        </li>
                    </ul>
                </li>

                <li class="sub-menu">
                    <a href="javascript:;" class="<?php if (($page == 'agenda_meeting') || ($page == 'petty_cash') || ($page == 'log_book') || ($page == 'weekly_allowance') || ($page == 'staff_training')) {
                                                        echo 'active';
                                                    } ?>">
                        <i class="fa fa-cogs"></i>
                        <span>General Admin</span>
                    </a>
                    <ul class="sub">

                        <li class="{{ ($page == 'agenda_meeting') ? 'active' : '' }}">
                            <a href="{{ url('admin/general-admin/agenda/meetings') }}">Agenda Meetings </a>
                        </li>
                        <li class="{{ ($page == 'petty_cash') ? 'active' : '' }}">
                            <a href="{{ url('admin/general-admin/petty/cash') }}">Petty Cash </a>
                        </li>
                        <li class="{{ ($page == 'log_book') ? 'active' : '' }}">
                            <a href="{{ url('admin/general-admin/log/book') }}">Log Book </a>
                        </li>
                        <li class="{{ ($page == 'weekly_allowance') ? 'active' : '' }}">
                            <a href="{{ url('admin/general-admin/allowance/weekly') }}">Weekly Allowance </a>
                        </li>
                        <li class="{{ ($page == 'staff_training') ? 'active' : '' }}">
                            <a href="{{ url('admin/general-admin/staff/training') }}">Staff Training </a>
                        </li>
                    </ul>
                </li>
                <!-- Sales and Finanace -->
                <li class="sub-menu">
                    <a href="javascript:;" class="<?php if (($page == 'Leads') || ($page == 'quotes') || ($page == 'jobs') || ($page == 'projects') || ($page == 'invoices') || ($page == 'purchase_orders') || ($page == 'Expense')) {
                                                        echo 'active';
                                                    } ?>">
                        <i class="fa fa-cogs"></i>
                        <span>Sales & Finance</span>
                    </a>
                    <ul class="sub">
                        <li class="{{ ($page == 'Leads') ? 'active' : '' }}"><a href="{{ url('admin/sales-finance/leads') }}">Leads </a></li>
                        <li class="{{ ($page == 'quotes') ? 'active' : '' }}"><a href="{{ url('admin/sales-finance/quotes') }}">Quotes </a></li>
                        <li class="{{ ($page == 'invoices') ? 'active' : '' }}"><a href="{{ url('admin/sales-finance/invoices') }}">Invoices </a></li>
                        <li class="{{ ($page == 'purchase_orders') ? 'active' : '' }}"><a href="{{ url('admin/sales-finance/purchase-order/purchase_orders') }}">Purchase Orders </a></li>
                        <li class="{{ ($page == 'Expense') ? 'active' : '' }}"><a href="{{ url('admin/sales-finance/expense') }}">Expenses </a></li>
                    </ul>
                </li>
                <!-- Jobs -->
                <li class="sub-menu">
                    <a href="javascript:;" class="<?php if (($page == 'jobs_list') || ($page == 'work_flow_list') || ($page == 'product_list') || ($page == 'project_list') || ($page == 'product_category') || ($page == 'account_codes') || ($page == 'tax_rate') || ($page == 'customer_list') || ($page == 'supplier_list') || ($page == 'job_recurring_list') || ($page == 'catalogue') || ($page == 'product_group')) {
                                                        echo 'active';
                                                    } ?>">
                        <i class="fa fa-briefcase"></i>
                        <span>Jobs</span>
                    </a>
                    <ul class="sub">
                        <li class="{{ ($page == 'jobs_list') ? 'active' : '' }}"><a href="{{ url('admin/jobs_list') }}">Jobs </a></li>
                        <li class="{{ ($page == 'job_recurring_list') ? 'active' : '' }}"><a href="{{ url('admin/job_recurring_list') }}">Job Recurring </a></li>
                        <li class="{{ ($page == 'product_category') ? 'active' : '' }}"><a href="{{ url('admin/product_category') }}">Product Category </a></li>
                        <li class="{{ ($page == 'catalogue') ? 'active' : '' }}"><a href="{{ url('admin/catalogue') }}">Catalogue</a></li>
                        <li class="{{ ($page == 'product_group') ? 'active' : '' }}"><a href="{{ url('admin/product_group') }}">Product Group</a></li>
                        <li class="{{ ($page == 'product_list') ? 'active' : '' }}"><a href="{{ url('admin/product_list') }}">Product </a></li>
                        <li class="{{ ($page == 'project_list') ? 'active' : '' }}"><a href="{{ url('admin/project_list') }}">Project </a></li>
                        <li class="{{ ($page == 'account_codes') ? 'active' : '' }}"><a href="{{ url('admin/account_codes') }}">Account Codes </a></li>
                        <li class="{{ ($page == 'tax_rate') ? 'active' : '' }}"><a href="{{ url('admin/tax_rate') }}">% Tax Rate </a></li>
                    </ul>
                </li>
                <!-- Setting Section Start -->
                <li class="sub-menu">
                    <a href="javascript:;" class="<?php if (($page == 'tags') || ($page == 'task_type') || ($page == 'regions') || ($page == 'payment_types') || ($page == 'Lead Reject Type') || ($page == 'Lead Status') || ($page == 'Lead Sources') || ($page == 'lead_task_type') || ($page == 'lead_notes_type') || ($page == 'jobs_type_list') || ($page == 'job_appointment_type') || ($page == 'job_rejection_categories') || ($page == 'job_title') || ($page == 'customer_type') || ($page == 'attachment_types')) {
                                                        echo 'active';
                                                    } ?>">
                        <i class="fa fa-cogs"></i>
                        <span>Setting</span>
                    </a>
                    <ul class="sub">
                        <li class="sub-menu">
                            <a href="javascript:void(0)" <?php if (($page == 'Lead Reject Type') || ($page == 'Lead Status') || ($page == 'Lead Sources') || ($page == 'lead_task_type') || ($page == 'lead_notes_type')) {
                                                                echo 'class="active" style="color:#1fb5ad"';
                                                            } ?>>
                                <i class="fa fa-bullhorn"></i> <span>Leads</span>
                            </a>
                            <ul class="sub pdlft">
                                <li class="{{ ($page == 'Lead Sources') ? 'active' : '' }}"><a href="{{ url('admin/sales-finance/leads/lead_sources') }}"> <i class="fa fa-list-ul"></i> Lead Sources </a></li>
                                <li class="{{ ($page == 'Lead Status') ? 'active' : '' }}"><a href="{{ url('admin/sales-finance/leads/lead_status') }}"> <i class="fa fa-list-ul"></i> Lead Status </a></li>
                                <li class="{{ ($page == 'lead_task_type') ? 'active' : '' }}"><a href="{{ url('admin/sales-finance/leads/lead_task_type') }}"> <i class="fa fa-list-ul"></i> Lead Task Types </a></li>
                                <li class="{{ ($page == 'Lead Reject Type') ? 'active' : '' }}"><a href="{{ url('admin/sales-finance/leads/lead_reject_type') }}"> <i class="fa fa-list-ul"></i> Lead Reject Type </a></li>
                                <li class="{{ ($page == 'lead_notes_type') ? 'active' : '' }}"><a href="{{ url('admin/sales-finance/leads/lead_notes_type') }}"> <i class="fa fa-list-ul"></i> Lead Notes Type </a></li>
                            </ul>
                        </li>

                        <li class="sub-menu">
                            <a href="javascript:void(0)" <?php if (($page == 'jobs_type_list') || ($page == 'job_appointment_type') || ($page == 'job_rejection_categories') || ($page == 'job_title')) {
                                                                echo 'class="active" style="color:#1fb5ad"';
                                                            } ?>>
                                <i class="fa fa-road"></i><span>Jobs</span>
                            </a>
                            <ul class="sub pdlft">
                                <li class="{{ ($page == 'jobs_type_list') ? 'active' : '' }}"><a href="{{ url('admin/jobs_type_list') }}"><i class="fa fa-list-ul"></i> Job Type</a></li>
                                <li class="{{ ($page == 'job_title') ? 'active' : '' }}"><a href="{{ url('admin/job_title') }}"> <i class="fa fa-list-ul"></i> Job Title</a></li>
                                <li class="{{ ($page == 'job_appointment_type') ? 'active' : ''}}"><a href="{{url('admin/job_appointment_type')}}"> <i class="fa fa-list-ul"></i> Job Appointment Type</a></li>
                                <li class="{{ ($page == 'job_rejection_categories') ? 'active' : ''}}"><a href="{{url('admin/job_rejection_categories')}}"><i class="fa fa-list-ul"></i> Appointment Rejection Categories</a></li>
                            </ul>
                        </li>

                        <li class="sub-menu">
                            <a href="javascript:void(0)" <?php if (($page == 'customer_type')) {
                                                                echo 'class="active" style="color:#1fb5ad"';
                                                            } ?>>
                                <i class="fa fa-users"></i><span>Customers</span>
                            </a>
                            <ul class="sub pdlft">
                                <li class="{{ ($page == 'customer_type') ? 'active' : '' }}"><a href="{{ url('admin/customer_type') }}"><i class="fa fa-list-ul"></i> Customers Type</a></li>
                            </ul>
                        </li>

                        <li class="sub-menu">
                            <a href="javascript:void(0)" <?php if (($page == 'crm_section_type')) {
                                                                echo 'class="active" style="color:#1fb5ad"';
                                                            } ?>>
                                <i class="fa fa-comments"></i> <span>CRM</span>
                            </a>
                            <ul class="sub pdlft">
                                <li class="{{ ($page == 'crm_section_type') ? 'active' : '' }}"><a href="{{ url('admin/sales-finance/leads/CRM_section_types') }}"><i class="fa fa-list-ul"></i> CRM Section Type</a></li>
                            </ul>
                        </li>

                        <li class="sub-menu">
                            <a href="javascript:void(0)" <?php if (($page == 'tags') || ($page == 'task_type') || ($page == 'regions') || ($page == 'payment_types') || ($page == 'attachment_types')) {
                                                                echo 'class="active" style="color:#1fb5ad"';
                                                            } ?>>
                                <i class="fa fa-list-ul"></i><span>General</span>
                            </a>
                            <ul class="sub pdlft">
                                <li class="{{ ($page == 'attachment_types') ? 'active' : '' }}"><a href="{{ url('admin/general/attachment_types') }}"> <i class="fa fa-paperclip"></i> Attachment Types </a></li>
                                <li class="{{ ($page == 'payment_types') ? 'active' : '' }}"><a href="{{ url('admin/general/payment_types') }}"><i class="fa fa-money"></i> Payment Types </a></li>
                                <li class="{{ ($page == 'regions') ? 'active' : '' }}"><a href="{{ url('admin/general/regins') }}"><i class="fa fa-globe"></i> Regions </a></li>
                                <li class="{{ ($page == 'task_type') ? 'active' : '' }}"><a href="{{ url('admin/general/task_types') }}"><i class="fa fa-list-ul"></i> Task Type </a></li>
                                <li class="{{ ($page == 'tags') ? 'active' : '' }}"><a href="{{ url('admin/general/tags') }}"><i class="fa fa-tags"></i> Tags </a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <!-- Setting Section End -->

                <!-- Contact Section Start -->
                <li class="sub-menu">
                    <a href="javascript:;" class="<?php if (($page == 'customers') || ($page == 'Suppliers')) {
                                                        echo 'active';
                                                    } ?>"><i class="fa fa-cogs"></i><span>Contacts</span></a>
                    <ul class="sub">
                        <li class="{{ ($page == 'customers') ? 'active' : '' }}"><a href="{{ url('admin/customers') }}">Customers </a></li>
                        <li class="{{ ($page == 'Suppliers') ? 'active' : '' }}"><a href="{{ url('admin/sales-finance/leads/lead_reject_type') }}">Suppliers </a></li>
                    </ul>
                </li>
                <!-- Contact Section End -->

            </ul>
        </div>
        <!-- sidebar menu end-->
    </div>
</aside>
<!--sidebar end-->