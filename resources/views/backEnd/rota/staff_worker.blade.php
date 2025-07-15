@extends('backEnd.layouts.master')
@section('title',' Staff Worker')
@section('content')

<!--main content start-->
<section id="main-content">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">
                    <header class="panel-heading">
                        Add Staff
                    </header>
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif
                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif
                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            <div class="clearfix clearfix_space">
                                <div class="btn-group">
                                    <a href="{{url('admin/rota/staff-worker-add')}}" id="editable-sample_new" class="btn btn-primary"> Add New <i class="fa fa-plus"></i></a>
                                </div>
                            </div>
                            <section class="panel">
                                <!-- <header class="panel-heading">
                        Dynamic Table
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-cog"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                         </span>
                    </header> -->
                                <div class="panel-body">
                                    <div class="adv-table table-responsive">
                                        <table class="display table table-bordered table-striped" id="staffWorker">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Surname</th>
                                                    <th>Forename</th>
                                                    <th>Address</th>
                                                    <th>Post Code</th>
                                                    <th>DOB</th>
                                                    <th>Acct Number	</th>
                                                    <th>Sort Code	</th>
                                                    <th>Rate of Pay (£)</th>
                                                    <th>Level</th>
                                                    <th>Start Date	</th>
                                                    <th>Job Role	</th>
                                                    <th>NIN</th>
                                                    <th>Starter Decl. (HMRC)</th>
                                                    <th>Probation Start Date	</th>
                                                    <th>Probation End Date	</th>
                                                    <th>Probation Extended Date	</th>
                                                    <th>After Probation Enrolled</th>
                                                    <th>Student Loan</th>
                                                    <th>DBS Clear?</th>
                                                    <th>DBS Number	</th>
                                                    <th>On DBS Update Service?	</th>
                                                    <th>Leave Date	</th>
                                                    <th>Email Address	</th>
                                                    <th>Mobile</th>
                                                    <th>Action</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($staffWorkers as $key => $staffData)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $staffData->surname }}</td>
                                            <td>{{ $staffData->forename }}</td>
                                            <td>{{ $staffData->address }}</td>
                                            <td>{{ $staffData->postCode }}</td>
                                            <td class="white_space_nowrap">{{ \Carbon\Carbon::parse($staffData->DOB)->format('d-m-Y')  }}</td>
                                            <td>{{ $staffData->account_num }}</td>
                                            <td>{{ $staffData->sort_code }}</td>
                                            <td>{{ $staffData->rate_of_pay }}</td>
                                            <td>@if($staffData->level == "qualified") Qualified @else Unqualified @endif</td>
                                            <td class="white_space_nowrap">{{ \Carbon\Carbon::parse($staffData->start_date)->format('d-m-Y') }}</td>
                                            <td>{{ $staffData->job_role }}</td>
                                            <td>{{ $staffData->NIN }}</td>
                                            <td>@if($staffData->starter_declaration == 1 ) Yes-A @elseif($staffData->starter_declaration == 2) Yes-B @elseif($staffData->starter_declaration == 3) Yes-C @else No @endif</td>
                                            <td>{{ \Carbon\Carbon::parse($staffData->probation_start_date)->format('d-m-Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($staffData->probation_end_date)->format('d-m-Y')  }}</td>
                                            <td>{{ \Carbon\Carbon::parse($staffData->probation_renew_date)->format('d-m-Y')  }}</td>
                                            <td>@if($staffData->after_probation_enrolled == "1") Yes @else No @endif</td>
                                            <td>@if($staffData->student_loan == "no_student_loan") No Student Loan @elseif($staffData->student_loan == "postgraduate") Postgraduate @elseif($staffData->student_loan == "plan_1") Plan 1 @elseif($staffData->student_loan == "plan_2") Plan 2 @elseif($staffData->student_loan == "plan_4") Plan 4 @endif</td>
                                            <td>@if($staffData->dbs_clear == 1) Yes @else No @endif</td>
                                            <td>{{ $staffData->dbs_number }}</td>
                                            <td>@if($staffData->dbs_update_service == 1) Yes @else No @endif</td>
                                            <td class="white_space_nowrap">{{ $staffData->leave_date ? \Carbon\Carbon::parse($staffData->leave_date)->format('d-m-Y') : '' }}</td>
                                            <td>{{ $staffData->email }}</td>
                                            <td>{{ $staffData->mobile }}</td>
                                            <td>
                                                <a href="{{ url('/admin/rota/edit-staff-worker/'.$staffData->id) }}" class="openModalBtn openAddStaffModel" ><i class="fa fa-pencil" aria-hidden="true"></i></a> |
                                                <a href="#!" onclick="deleteStaff({{ $staffData->id }})" class="deleteBtn"><i class="fa fa-trash radStar" aria-hidden="true"></i></a>
                                            </td>
                                        </tr>
                                        @endforeach                                                
                                            </tbody>
                                            <!-- <tfoot>
                                                <tr>
                                                    <th>Rendering engine</th>
                                                    <th>Browser</th>
                                                    <th>Platform(s)</th>
                                                    <th class="hidden-phone">Engine version</th>
                                                    <th class="hidden-phone">CSS grade</th>
                                                </tr>
                                            </tfoot> -->
                                        </table>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- page end-->
    </div>
</section>
<script>
    deleteStaffWorker = "{{ url('admin/rota/staff-delete') }}";
</script>
<script type="text/javascript" src="{{ url('public/js/rota/add_staff_worker.js') }}"></script>

@endsection