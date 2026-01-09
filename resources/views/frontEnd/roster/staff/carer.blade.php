<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
@extends('frontEnd.layouts.master')
@section('title', 'Carer')
@section('content')


    @include('frontEnd.roster.common.roster_header')

    <main class="page-content">
        <div class="container-fluid">

            <div class="topHeaderCont">
                <div>
                    <h1>Carers</h1>
                    <p class="header-subtitle">Manage your care team</p>
                </div>
                <div class="header-actions">
                    <button class="btn add_staff openStaffModal" data-mode="add"><i class="fa fa-plus"></i> Add Carer</button>
                </div>
            </div>

            <div class="rota_dashboard-cards simpleCard">
                <div class="rota_dash-card blue">
                    <div class="rota_dash-left">
                        <p class="rota_title">Total Carers</p>
                        <h2 class="rota_count">{{ $counts['all'] }}</h2>
                    </div>
                </div>

                <div class="rota_dash-card orangeClr">
                    <div class="rota_dash-left">
                        <p class="rota_title">Active</p>
                        <h2 class="rota_count greenText">{{ $counts['active'] }}</h2>
                    </div>
                </div>

                <div class="rota_dash-card green">
                    <div class="rota_dash-left">
                        <p class="rota_title">On Leave</p>
                        <h2 class="rota_count orangeText">{{ $counts['on_leave'] }}</h2>
                    </div>
                </div>

                <div class="rota_dash-card redClr">
                    <div class="rota_dash-left">
                        <p class="rota_title">Inactive</p>
                        <h2 class="rota_count">{{ $counts['inactive'] }}</h2>
                    </div>
                </div>

            </div>

            <div class="calendarTabs leaveRequesttabs m-t-20">
                <div class="tabs">
                    <div class="input-group searchWithtabs">
                        <span class="input-group-addon btn-white"><i class="fa fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Username">
                    </div>
                    <button class="tab active" data-tab="allCarerActibity">
                        All
                    </button>

                    <button class="tab" data-tab="activeCarer">
                        Active
                    </button>

                    <button class="tab" data-tab="onLeaveCarer">
                        On Leave
                    </button>

                    <button class="tab" data-tab="inactiveCarer">
                        Inactive
                    </button>
                </div>

                <!-- TAB CONTENT -->
                <div class="tab-content carertabcontent">
                    <div class="content active" id="allCarerActibity">
                        @if (count($allStaff) > 0)
                            <div class="row">
                                @foreach ($activeStaff as $carer)
                                    <div class="col-md-4">
                                        <div class="profile-card">
                                            <div class="card-header">
                                                <div class="user">
                                                    <div class="avatar">{{ strtoupper(substr(trim($carer->name), 0, 1)) }}</div>
                                                    <div class="info">
                                                        <div class="name"><a href="{{ url('roster/carer-details/' . $carer->id) }}">{{ $carer->name }}</a></div>
                                                        <div class="role">part time</div>
                                                    </div>
                                                </div>
                                                <span class="status {{ $carer->status == 1 ? 'greenShowbtn' : 'inactive' }}">
                                                    {{ $carer->status == 1 ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                            <div class="details">
                                                <div class="item">
                                                    <i class="fa-solid fa-phone"></i> <span>{{ $carer->phone_no }}</span>
                                                </div>
                                                <div class="item">
                                                    <i class="fa-regular fa-envelope"></i> <span>{{ $carer->email }}</span>
                                                </div>
                                                <div class="item">
                                                    <i class="fa-solid fa-location-dot"></i> <span>{{ $carer->current_location }}</span>
                                                </div>
                                            </div>
                                            <div class="sectionCarer">
                                                <div class="label">Qualifications:</div>
                                                <div class="tags">
                                                    @if (!empty($carer->qualifications) && count($carer->qualifications))
                                                        @foreach ($carer->qualifications as $qualification)
                                                            <span>
                                                                {{ $qualification->name ?? $qualification }}
                                                            </span>
                                                        @endforeach
                                                    @else
                                                        <span>No qualifications</span>
                                                    @endif
                                                </div>
                                            </div>
                                          
                                            <div class="rate">
                                                <div class="label">Hourly Rate</div>
                                                <div class="amount">£{{ $carer->pay_rate ?? '0.00' }}</div>
                                            </div>
                                            <div class="supervision">
                                                <i class="fa-regular fa-clipboard"></i> <span>Supervision: No supervision</span>
                                            </div>
                                            <div class="actions">
                                                <button 
                                                    class="edit openStaffModal"  
                                                    data-mode="edit" 
                                                    data-id="{{ $carer->id }}"
                                                    data-name="{{ $carer->name }}" 
                                                    data-username="{{ $carer->user_name }}" 
                                                    data-phone="{{ $carer->phone_no }}"
                                                    data-email="{{ $carer->email }}"
                                                    data-job-title="{{ $carer->job_title }}"
                                                    data-department="{{ $carer->department }}"
                                                    data-description="{{ $carer->description }}"
                                                    data-status="{{ $carer->status }}"
                                                    data-employment-type="{{ $carer->employment_type }}"
                                                    data-pay-rate="{{ $carer->pay_rate }}"
                                                    data-image="{{ $carer->image }}"
                                                    data-emergency_contact_name="{{ $carer->emergency_contact_name }}"
                                                    data-emergency_contact_phone="{{ $carer->emergency_contact_phone }}"
                                                    data-emergency_contact_relationship="{{ $carer->emergency_contact_relationship }}"
                                                    data-payroll="{{ $carer->payroll }}"
                                                    data-dbs_expiry_date = "{{ $carer->dbs_expiry_date }}"
                                                    data-dbs_certificate_number = "{{ $carer->dbs_certificate_number }}"
                                                    data-date-of-joining = "{{ $carer->date_of_joining }}"
                                                    data-date-of-leaving = "{{ $carer->date_of_leaving }}"
                                                    data-holiday-entitlement="{{ $carer->holiday_entitlement}}"
                                                    data-overtime-availability="{{ $carer->available_for_overtime }}"
                                                    data-max-extra-hours="{{ $carer->max_extra_hours }}"
                                                    data-current-location="{{ $carer->current_location }}"
                                                    data-qualifications='@json($carer->qualifications)'> 
                                                    <i class="fa-regular fa-pen-to-square"></i> Edit 
                                                </button>
                                                <button class="delete" data-id="{{ $carer->id }}"> <i class="fa-regular fa-trash-can"></i> </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="leave-card">
                                    <div class="leavebanktabCont">
                                        <i class="fa fa-calendar-o"></i>
                                        <h4>No carers found</h4>
                                        <p>Add your first carer to get started</p>
                                    </div>
                        @endif
                    </div>
                </div> <!--End off All Leaves -->

                <div class="content" id="activeCarer">
                    @if (count($activeStaff) > 0)
                        <div class="row">
                            @foreach ($activeStaff as $carer)
                                <div class="col-md-4">
                                    <div class="profile-card">
                                        <div class="card-header">
                                            <div class="user">
                                                <div class="avatar">{{ strtoupper(substr(trim($carer->name), 0, 1)) }}</div>
                                                <div class="info">
                                                    <div class="name"><a href="{{ url('roster/carer-details/' . $carer->id) }}">{{ $carer->name }}</a></div>
                                                    <div class="role">part time</div>
                                                </div>
                                            </div>
                                            <span class="status {{ $carer->status == 1 ? 'greenShowbtn' : 'inactive' }}">
                                                {{ $carer->status == 1 ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <i class="fa-solid fa-phone"></i> <span>{{ $carer->phone_no }}</span>
                                            </div>
                                            <div class="item">
                                                <i class="fa-regular fa-envelope"></i> <span>{{ $carer->email }}</span>
                                            </div>
                                            <div class="item">
                                                <i class="fa-solid fa-location-dot"></i> <span>{{ $carer->current_location }}</span>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="label">Qualifications:</div>
                                            <div class="tags">
                                                @if (!empty($carer->qualifications) && count($carer->qualifications))
                                                        @foreach ($carer->qualifications as $qualification)
                                                            <span>
                                                                {{ $qualification->name ?? $qualification }}
                                                            </span>
                                                        @endforeach
                                                    @else
                                                        <span>No qualifications</span>
                                                    @endif

                                            </div>
                                        </div>
                                        <div class="rate">
                                            <div class="label">Hourly Rate</div>
                                            <div class="amount">£{{ $carer->pay_rate ?? '0.00' }}</div>
                                        </div>
                                        <div class="supervision">
                                            <i class="fa-regular fa-clipboard"></i> <span>Supervision: No supervision</span>
                                        </div>
                                        <div class="actions">
                                             <button 
                                                    class="edit openStaffModal"  
                                                    data-mode="edit" 
                                                    data-id="{{ $carer->id }}"
                                                    data-name="{{ $carer->name }}" 
                                                    data-username="{{ $carer->user_name }}" 
                                                    data-phone="{{ $carer->phone_no }}"
                                                    data-email="{{ $carer->email }}"
                                                    data-job-title="{{ $carer->job_title }}"
                                                    data-department="{{ $carer->department }}"
                                                    data-description="{{ $carer->description }}"
                                                    data-status="{{ $carer->status }}"
                                                    data-employment-type="{{ $carer->employment_type }}"
                                                    data-pay-rate="{{ $carer->pay_rate }}"
                                                    data-image="{{ $carer->image }}"
                                                    data-emergency_contact_name="{{ $carer->emergency_contact_name }}"
                                                    data-emergency_contact_phone="{{ $carer->emergency_contact_phone }}"
                                                    data-emergency_contact_relationship="{{ $carer->emergency_contact_relationship }}"
                                                    data-payroll="{{ $carer->payroll }}"
                                                    data-dbs_expiry_date = "{{ $carer->dbs_expiry_date }}"
                                                    data-dbs_certificate_number = "{{ $carer->dbs_certificate_number }}"
                                                    data-date-of-joining = "{{ $carer->date_of_joining }}"
                                                    data-date-of-leaving = "{{ $carer->date_of_leaving }}"
                                                    data-holiday-entitlement="{{ $carer->holiday_entitlement}}"
                                                    data-overtime-availability="{{ $carer->available_for_overtime }}"
                                                    data-max-extra-hours="{{ $carer->max_extra_hours }}"
                                                    data-current-location="{{ $carer->current_location }}"
                                                    data-qualifications='@json($carer->qualifications)'> 
                                                    <i class="fa-regular fa-pen-to-square"></i> Edit 
                                                </button>
                                                <button class="delete" data-id="{{ $carer->id }}"> <i class="fa-regular fa-trash-can"></i> </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="leave-card">
                                <div class="leavebanktabCont">
                                    <i class="fa fa-calendar-o"></i>
                                    <h4>No carers found</h4>
                                    <p>Add your first carer to get started</p>
                                </div>
                            </div>
                    @endif

                </div>
            </div>

            <div class="content" id="onLeaveCarer">
                @if (count($onLeaveStaff) > 0)
                    <div class="row">
                        @foreach ($onLeaveStaff as $carer)
                            <div class="col-md-4">
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">{{ strtoupper(substr(trim($carer->name), 0, 1)) }}</div>
                                            <div class="info">
                                                <div class="name"><a href="{{ url('roster/carer-details/' . $carer->id) }}">{{ $carer->name }}</a></div>
                                                <div class="role">part time</div>
                                            </div>
                                        </div>
                                        <span class="status {{ $carer->status == 1 ? 'greenShowbtn' : 'inactive' }}">
                                            {{ $carer->status == 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i> <span>{{ $carer->phone_no }}</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-regular fa-envelope"></i> <span>{{ $carer->email }}</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="sectionCarer">
                                        <div class="label">Qualifications:</div>
                                        <div class="tags">
                                             @if (!empty($carer->qualifications) && count($carer->qualifications))
                                                        @foreach ($carer->qualifications as $qualification)
                                                            <span>
                                                                {{ $qualification->name ?? $qualification }}
                                                            </span>
                                                        @endforeach
                                                    @else
                                                        <span>No qualifications</span>
                                                    @endif
                                        </div>
                                    </div>
                                    <div class="rate">
                                        <div class="label">Hourly Rate</div>
                                        <div class="amount">£{{ $carer->pay_rate ?? '0.00' }}</div>
                                    </div>
                                    <div class="supervision">
                                        <i class="fa-regular fa-clipboard"></i> <span>Supervision: No supervision</span>
                                    </div>
                                    <div class="actions">
                                        <button class="edit" data-id="{{ $carer->id }}"> <i class="fa-regular fa-pen-to-square"></i> Edit </button>
                                        <button class="delete" data-id="{{ $carer->id }}"> <i class="fa-regular fa-trash-can"></i> </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="row">
                        <div class="leave-card">
                            <div class="leavebanktabCont">
                                <i class="fa fa-calendar-o"></i>
                                <h4>No carers on leave</h4>
                                <p>Carers on leave will be displayed here</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="content" id="inactiveCarer">
                @if (count($inactiveStaff) > 0)
                    <div class="row">
                        @foreach ($inactiveStaff as $carer)
                            <div class="col-md-4">
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">{{ strtoupper(substr(trim($carer->name), 0, 1)) }}</div>
                                            <div class="info">
                                                <div class="name"><a href="{{ url('roster/carer-details/' . $carer->id) }}">{{ $carer->name }}</a></div>
                                                <div class="role">part time</div>
                                            </div>
                                        </div>
                                        <span class="status {{ $carer->status == 1 ? 'greenShowbtn' : 'inactive' }}">
                                            {{ $carer->status == 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i> <span>{{ $carer->phone_no }}</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-regular fa-envelope"></i> <span>{{ $carer->email }}</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>{{ $carer->current_location }}</span>
                                        </div>
                                    </div>
                                    <div class="sectionCarer">
                                        <div class="label">Qualifications:</div>
                                        <div class="tags">
                                            @if (!empty($carer->qualifications) && count($carer->qualifications))
                                                        @foreach ($carer->qualifications as $qualification)
                                                            <span>
                                                                {{ $qualification->name ?? $qualification }}
                                                            </span>
                                                        @endforeach
                                                    @else
                                                        <span>No qualifications</span>
                                                    @endif
                                        </div>
                                    </div>
                                    <div class="rate">
                                        <div class="label">Hourly Rate</div>
                                        <div class="amount">£{{ $carer->pay_rate ?? '0.00' }}</div>
                                    </div>
                                    <div class="supervision">
                                        <i class="fa-regular fa-clipboard"></i> <span>Supervision: No supervision</span>
                                    </div>
                                    <div class="actions">
                                         <button 
                                                    class="edit openStaffModal"  
                                                    data-mode="edit" 
                                                    data-id="{{ $carer->id }}"
                                                    data-name="{{ $carer->name }}" 
                                                    data-username="{{ $carer->user_name }}" 
                                                    data-phone="{{ $carer->phone_no }}"
                                                    data-email="{{ $carer->email }}"
                                                    data-job-title="{{ $carer->job_title }}"
                                                    data-department="{{ $carer->department }}"
                                                    data-description="{{ $carer->description }}"
                                                    data-status="{{ $carer->status }}"
                                                    data-employment-type="{{ $carer->employment_type }}"
                                                    data-pay-rate="{{ $carer->pay_rate }}"
                                                    data-image="{{ $carer->image }}"
                                                    data-emergency_contact_name="{{ $carer->emergency_contact_name }}"
                                                    data-emergency_contact_phone="{{ $carer->emergency_contact_phone }}"
                                                    data-emergency_contact_relationship="{{ $carer->emergency_contact_relationship }}"
                                                    data-payroll="{{ $carer->payroll }}"
                                                    data-dbs_expiry_date = "{{ $carer->dbs_expiry_date }}"
                                                    data-dbs_certificate_number = "{{ $carer->dbs_certificate_number }}"
                                                    data-date-of-joining = "{{ $carer->date_of_joining }}"
                                                    data-date-of-leaving = "{{ $carer->date_of_leaving }}"
                                                    data-holiday-entitlement="{{ $carer->holiday_entitlement}}"
                                                    data-overtime-availability="{{ $carer->available_for_overtime }}"
                                                    data-max-extra-hours="{{ $carer->max_extra_hours }}"
                                                    data-current-location="{{ $carer->current_location }}"
                                                    data-qualifications='@json($carer->qualifications)'> 
                                                    <i class="fa-regular fa-pen-to-square"></i> Edit 
                                                </button>
                                                <button class="delete" data-id="{{ $carer->id }}"> <i class="fa-regular fa-trash-can"></i> </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="row">
                        <div class="leave-card">
                            <div class="leavebanktabCont">
                                <i class="fa fa-calendar-o"></i>
                                <h4>No inactive carers found</h4>
                                <p>Add carers to see them listed here</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <!-- END TAB CONTENT -->
        </div>
        </div>
        </div>
        </div>

        @include('frontEnd.systemManagement.elements.add_staff')

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

    @endsection
</main>
