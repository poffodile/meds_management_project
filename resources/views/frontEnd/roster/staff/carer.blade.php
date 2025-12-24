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
                    <button class="btn add_staff" data-toggle="modal" data-target="#addStaffModal"><i class="fa fa-plus"></i> Add Carer</button>
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
                                                    <span>Dementia Care</span> <span>Medication Administration</span>
                                                </div>
                                            </div>
                                            <div class="rate">
                                                <div class="label">Hourly Rate</div>
                                                <div class="amount">£15.00</div> {{ $carer->pay_rates }}
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
                                                    <div class="name">{{ $carer->name }}</div>
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
                                                <span>Dementia Care</span> <span>Medication Administration</span>
                                            </div>
                                        </div>
                                        <div class="rate">
                                            <div class="label">Hourly Rate</div>
                                            <div class="amount">£15.00</div> {{ $carer->pay_rates }}
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
                                                <div class="name">{{ $carer->name }}</div>
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
                                            <span>Dementia Care</span> <span>Medication Administration</span>
                                        </div>
                                    </div>
                                    <div class="rate">
                                        <div class="label">Hourly Rate</div>
                                        <div class="amount">£15.00</div>
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
                                                <div class="name">{{ $carer->name }}</div>
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
                                            <span>Dementia Care</span> <span>Medication Administration</span>
                                        </div>
                                    </div>
                                    <div class="rate">
                                        <div class="label">Hourly Rate</div>
                                        <div class="amount">£15.00</div>
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


        <!-- add Carer Modal -->
        {{-- <div class="modal fade leaveCommunStyle" id="addCarerModal" tabindex="1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Carer</h4>
                    </div>
                    <div class="modal-body approveLeaveModal">
                        <div class="carer-form">
                            <form>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Full Name *</label>
                                        <input type="text" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Email *</label>
                                        <input type="email" class="form-control">
                                    </div>

                                    <div class="col-md-6  m-t-10">
                                        <label>Phone *</label>
                                        <input type="text" class="form-control">
                                    </div>
                                    <div class="col-md-6  m-t-10">
                                        <label>Status</label>
                                        <select class="form-control">
                                            <option>Active</option>
                                            <option>Inactive</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6  m-t-10">
                                        <label>Employment Type</label>
                                        <select class="form-control">
                                            <option>Full Time</option>
                                            <option>Part Time</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6  m-t-10">
                                        <label>Hourly Rate (£)</label>
                                        <input type="number" value="15" class="form-control">
                                    </div>
                                </div>

                                <div class="overtime">
                                    <label>
                                        <input type="checkbox"> Available for Overtime
                                    </label>
                                </div>

                                <div class="qualifications">
                                    <h4>Qualifications</h4>
                                    <div class="checkbox-grid">
                                        <label><input type="checkbox"> NVQ Level 2 Health & Social Care</label>
                                        <label><input type="checkbox"> NVQ Level 3 Health & Social Care</label>
                                        <label><input type="checkbox"> First Aid Certificate</label>
                                        <label><input type="checkbox"> Dementia Care Specialist</label>
                                        <label><input type="checkbox"> Medication Administration</label>
                                        <label><input type="checkbox"> Care Certificate</label>
                                        <label><input type="checkbox"> Dementia Care</label>
                                        <label><input type="checkbox"> First Aid</label>
                                    </div>
                                </div>

                                <div class="address">
                                    <label>Address</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" placeholder="Street">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" placeholder="City">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" placeholder="Postcode">
                                        </div>
                                    </div>
                                </div>

                                <div class="emergency m-t-10">
                                    <label>Emergency Contact</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" placeholder="Name">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" placeholder="Phone">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" placeholder="Relationship">
                                        </div>
                                    </div>
                                </div>

                                <div class="row m-t-10">
                                    <div class="col-md-4">
                                        <label>DBS Certificate Number</label>
                                        <input type="text" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label>DBS Expiry Date</label>
                                        <input type="date" class="form-control">
                                    </div>
                                </div>

                                <div class="actions">
                                    <button type="button" class="cancel">Cancel</button>
                                    <button type="submit" class="submit">Create Carer</button>
                                </div>

                            </form>
                        </div>

                        <!-- <div class="leave-body">
                                
                                <div class="leave-info">
                          
                                </div>

                                <div class="form-group">
                                    <label>Address</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            
                                        </div>
                                        <div class="col-md-6">
                                            
                                        </div>
                                    </div>
                                </div>

                                

                                <div class="footer-actions">
                                    <button class="btn btn-cancel">Cancel</button>
                                    <button class="btn btn-approve leave-approve-btn">Create Carer</button>
                                </div>
                            </div> -->
                    </div>
                </div>
            </div>
        </div> --}}

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
