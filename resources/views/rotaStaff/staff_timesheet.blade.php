@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Staff Timesheet')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<style>
    table.tablechange tbody td {
        font-size: 12px;
        white-space: nowrap;
    }

    .image_delete {
        cursor: pointer;
    }

    .textbox {
        box-sizing: border-box;
        perspective: 500px;
        position: relative;
        text-align: left;
    }

    .textbox input {
        padding: 10px 14px;
        width: 100%;
    }

    .textbox input::placeholder {
        color: #ccc;
    }

    .textbox .autoCompleteJob {
        left: 0;
        position: absolute;
        top: calc(100% + 5px);
        width: 100%;
    }

    .textbox .autoCompleteJob .item {
        animation: showItem .3s ease forwards;
        background-color: #fff;
        box-shadow: 0 8px 8px -10px rgba(0, 0, 0, .4);
        box-sizing: border-box;
        color: #7C8487;
        cursor: pointer;
        display: block;
        font-size: .8rem;
        opacity: 0;
        outline: none;
        padding: 10px;
        text-decoration: none;
        transform-origin: top;
        /* transform: rotateX(-90deg); */
        transform: translateX(10px);
    }

    .textbox .autoCompleteJob .item:hover,
    .textbox .autoCompleteJob .item:focus {
        background-color: #fafafa;
        color: #D1822B;
    }

    @keyframes showItem {
        0% {
            opacity: 0;
            /* transform: rotateX(-90deg); */
            transform: translateX(10px);
        }

        100% {
            opacity: 1;
            /* transform: rotateX(0); */
            transform: translateX(0);
        }
    }

    .select2-container--default .select2-selection--single {
        height: 38px;
        padding: 5px;
        border-radius: 4px;
        border: 1px solid #ced4da;
        font-size: 14px;
    }

    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 100%;
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        padding: 4px;
    }

    .select2-container .select2-selection--multiple {
        min-height: 32px !important;
    }

    .parent-container {
        position: absolute;
        background: #fff;
        width: 190px;
    }

    #productList li:hover {
        cursor: pointer;
    }

    .dropdown-item {
        padding: 6px 15px;
        font-size: 13px;
        color: #212529;
        text-align: inherit;
        text-decoration: none;
        display: block;
        width: 100%;
        background-color: transparent;
        border: 0;
        border-radius: 0;
        transition: all 0.2s ease-in-out;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #212529;
    }
</style>

<!--main content start-->
<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        <h4>Staff Timesheet</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12">
                            <div class="jobsection justify-content-between align-items-center">
                                <div class="d-flex justify-content-end gap-4 align-items-center">
                                    <a href="javascript:void(0)" class="btn btn-warning modal_open" data-action="add"><i class="fa fa-plus"></i> Add</a>
                                    <label for="fromDate" class="mb-0">Date:</label>
                                    <!-- <select name="category" id="category" class="form-control">
                                        <option selected="" disabled="">Select Category</option>
                                        <option value="1">Sleep</option>
                                        <option value="2">Disturbance</option>
                                        <option value="3">Wake Night</option>
                                        <option value="4">Annual Leave</option>
                                        <option value="5">On Call</option>
                                    </select> -->
                                    <input type="date" name="" id="date_timesheet">
                                    <button type="button" class="btn btn-warning" onclick="location.reload()">Reset</button>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="col-lg-12">
                            <div class="searchJobForm" id="divTohide" style="display: none;">
                                <form id="search_dataForm" class="p-4">
                                    <div class="row justify-content-center">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">Expense By:</label>
                                                <select class="form-control editInput selectOptions" id="expenseBy">
                                                    <option selected disabled></option>
                                                    <option value="{{Auth::user()->id}}">{{Auth::user()->name}}</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">Expense Date:</label>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <input type="date" class="form-control editInput" id="start_date">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="date" class="form-control editInput" id="end_date">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">Customer:</label>
                                                <input type="text" class="form-control editInput" id="customer_name" placeholder="Type Customer Name">
                                                <input type="hidden" id="selectedId" name="selectedId">
                                                <div class="parent-container"></div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">Keywords:</label>
                                                <input type="text" class="form-control editInput" id="keywords" keywords="" placeholder="Keywords to seacrh">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">Billable:</label>
                                                <select class="form-control editInput selectOptions" id="billable_search">
                                                    <option selected disabled>--Any--</option>
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="pageTitleBtn justify-content-center">
                                                <a href="javascript:void(0)" onclick="searchBtn()" class="btn btn-primary">Search </a>
                                                <a href="javascript:void(0)" onclick="clearBtn()" class="btn btn-default ms-2">Clear</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div> -->
                        <div class="col-lg-12">
                            @if(session('message'))
                            <div class="alert alert-success text-center success_message mt-3 m-auto" style="height:50px; width:50%">
                                <p>{{ session('message') }}</p>
                            </div>
                            @endif
                            @if(session('staff_error'))
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 popup_alrt_msg">
                                <div class="popup_notification-box">
                                    <div class="alert alert-danger alert-dismissible m-0" role="alert">
                                        <button type="button" class="close close-msg-btn"><span aria-hidden="true">&times;</span></button>
                                        <strong>Success!</strong> <span class="popup_error_txt">{{ session('staff_error') }}</span>.
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 popup_alrt_msgblade" style="display:none">
                                <div class="popup_notification-box">
                                    <div class="alert alert-success alert-dismissible m-0" role="alert">
                                        <button type="button" class="close close-msg-btn"><span aria-hidden="true">&times;</span></button>
                                        <strong>Success!</strong> <span class="popup_success_txt"></span>.
                                    </div>
                                </div>
                            </div>

                            <div class="maimtable productDetailTable mb-4  table-responsive">
                                <!-- <div class="delete_table_row">
                                    <a href="javascript:void(0)" id="deleteSelectedRows" class="btn btn-danger">Delete</a>
                                </div> -->
                                <table class="table border-top border-bottom tablechange" id="satffTimesheetTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{date('F Y')}}</th>
                                            <th>Total Shitf Hours</th>
                                            <th>Category Type</th>
                                            <th>Extra Hours</th>
                                            <th>Comments</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="user_data">
                                        <?php
                                            function formatHours($decimalHours) {
                                                if ($decimalHours == 0 || $decimalHours == null) {
                                                    return "";
                                                }

                                                $hours = floor($decimalHours);
                                                $minutes = round(($decimalHours - $hours) * 60);

                                                return "{$hours}h {$minutes}min";
                                            }
                                            $category_type='';
                                            foreach($time_sheet as $key=>$val){
                                                if($val->category_id == 1){
                                                    $category_type='Sleep';
                                                }else if($val->category_id == 2){
                                                    $category_type='Disturbance';
                                                }else if($val->category_id == 3){
                                                    $category_type='Wake Night';
                                                }else if($val->category_id == 4){
                                                    $category_type='Annual Leave';
                                                }else{
                                                    $category_type='On Call';
                                                }

                                            $total_hours=App\RotaAssignEmployee::where('emp_id',$val->user_id)->whereDate('created_at',$val->date)->sum('total_hours');
                                            $ex_time = explode('.', $val->hours);
                                            $hour = isset($ex_time[0]) ? $ex_time[0] . 'h' : '0h';
                                            $min  = isset($ex_time[1]) ? $ex_time[1] . 'min' : '0min';
                                        ?>
                                       <tr>
                                            <td>{{++$key}}</td>
                                            <td>{{$val->date}}</td>
                                            <td>{{ formatHours($total_hours) }}</td>
                                            <td>{{$category_type}}</td>
                                            <td>{{ $hour }} {{ $min }}</td>
                                            <td>{{ $val->comments }}</td>
                                            <td>
                                                <div class="pageTitleBtn p-0">
                                                    <div class="dropdown">
                                                        <a href="#" class="btn btn-primary btn-sm" data-toggle="dropdown" aria-expanded="false">
                                                            Action <i class="fa fa-caret-down"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-right fade-up m-0">
                                                            <a href="javascript:void(0)" class="dropdown-item col-form-label modal_open" data-action="edit" data-id="{{$val->id}}" data-category_id="{{$val->category_id}}" data-hours="{{$val->hours}}" data-comments="{{$val->comments}}">Edit</a>
                                                            <!-- <a href="javascript:void(0)" class="dropdown-item col-form-label">View Details</a> -->
                                                            <a onclick="time_delete({{$val->id}})" href="javascript:void(0)" class="dropdown-item col-form-label">Delete</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                       </tr>
                                       <?php }?>
                                    </tbody>
                                </table>
                            </div> <!-- End off main Table -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal start here -->
<div class="modal fade" id="stafftimesheetModal" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="ModalLabel"></h4>
            </div>
            <div class="modal-body">
                <div class="form-group col-md-12 col-sm-12 col-xs-12 popup_success popup_alrt_msg" style="display: none;">
                    <div class="popup_notification-box">
                        <div class="alert alert-success alert m-0" role="alert">
                            <button type="button" class="close close-msg-btn"><span aria-hidden="true">×</span></button>
                            <strong>Success!</strong> <span class="popup_success_txt"></span>.
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <form id="form_data" class="customerForm">
                            @csrf
                            <input type="hidden" name="time_sheet_id" id="id">
                            <input type="hidden" name="user_id" id="user_id" value="<?php if(isset($staff)){echo $staff->id;}?>">
                            <div class="mb-3">
                                <label class="mb-2 col-form-label">Category <span class="radStar">*</span></label>
                                    <select class="form-control editInput selectOptions checkInput" id="category_id" name="category_id">
                                        <option selected="" disabled="">Select Category</option>
                                        <option value="1">Sleep</option>
                                        <option value="2">Disturbance</option>
                                        <option value="3">Wake Night</option>
                                        <option value="4">Annual Leave</option>
                                        <option value="5">On Call</option>
                                    </select>
                            </div>
                            <div class="mb-3">
                                <label class="col-form-label mb-2">Hours <span class="radStar">*</span></label>
                                <input type="text" class="form-control editInput textareaInput checkInput" name="hours" id="hours" value="" placeholder="Enter Hours">
                                <small class="form-text text-muted">
                                    Please enter hours in decimal format.  
                                    Example: <b>2.15</b> means 2 hours 15 minutes, <b>1.30</b> means 1 hour 30 minutes.
                                </small>
                            </div>
                            <div class="mb-3">
                                <label class="col-form-label mb-2">Comment <span class="radStar">*</span></label>
                                <textarea class="form-control textareaInput checkInput" id="comments" name="comments" rows="10" placeholder="Enter Your Comment"></textarea>
                            </div>
                        </form>
                    </div>
                </div> <!-- End row -->
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="save_data">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- end here -->
</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
<script>
    $(document).ready(function(){
        setTimeout(function() {
            $(".alert").fadeOut();
        }, 3000);
    });
    new DataTable('#satffTimesheetTable');
</script>
<script>
    $(document).on('click','.modal_open',function(){
        var data=($(this).data('action'));
        if(data === 'add'){
            $("#ModalLabel").text("Add Staff Timesheet");
        }else{
            var category_id=$(this).data('category_id');
            var hours=$(this).data('hours');
            var comments=$(this).data('comments');
            var id=$(this).data('id');
            $("#category_id").val(category_id);
            $("#hours").val(hours);
            $("#comments").val(comments);
            $("#id").val(id);
            $("#ModalLabel").text("Edit Staff Timesheet");
        }
        $("#stafftimesheetModal").modal('show');
    });
    $(document).on('input','#hours', function(){
        this.value = this.value.replace(/[^0-9.]/g, '');
        if ((this.value.match(/\./g) || []).length > 1) {
            this.value = this.value.slice(0, -1);
        }
        let floatVal = parseFloat(this.value);
        if (!isNaN(floatVal)) {
            if (floatVal <= 0) this.value = '';
            if (floatVal > 24) this.value = '24.00';
        }
        let parts = this.value.split('.');
        let hours = parseInt(parts[1]);
        if(parts[1] && parts[1].length > 2){
            parts[1] = parts[1].slice(0, 2);
            this.value = parts.join('.');
        }
        if(parts[1] && parts[1] > '59'){
            let update=parts[0]=Number(parts[0])+Number(1);
            this.value = update+'.00';
        }
    });
    $("#save_data").on('click',function(){
        var error=0;
        $('.checkInput').each(function(){
            var formfield=$(this).val();
            if(formfield == '' || formfield == undefined){
                error=1;
                $(this).css('border','1px solid red');
                $(this).focus();
                return false;
            }else{
                $(this).css('border','');
                error=0;
            }
        });
        if(error == 1){
            return false;
        }else{
            $.ajax({
                type: "POST",
                url: "{{ url('/my-profile/time-sheet/add') }}",
                data: new FormData($("#form_data")[0]),
                async: false,
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    // return false;
                    if (typeof isAuthenticated === "function") {
                        if (isAuthenticated(response) == false) {
                            return false;
                        }
                    }
                    if (response.success === true) {
                        // location.reload();
                        $(".popup_alrt_msg").show();
                        $(".alert-success").show();
                        $(".popup_success_txt").text(response.message);
                        setTimeout(function(){
                            location.reload();
                        }, 3000);
                    }else{
                        alert("Something went wrong! Please try again later");
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr);
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                }
            });
        }
    });
function time_delete(id){
    if(id === ''){
        alert("Something went wrong! Please try again later");
        return false;
    }else{
        if(confirm("Are you sure to delete it?")){
            $.ajax({
                type: "delete",
                url: "{{ url('/my-profile/time-sheet/delete') }}/"+id,
                // data: {id:id,_token:"{{csrf_token()}}"},
                success: function(response) {
                    console.log(response);
                    if (typeof isAuthenticated === "function") {
                        if (isAuthenticated(response) == false) {
                            return false;
                        }
                    }
                    $(".popup_alrt_msgblade").show();
                    $(".alert-success").show();
                    $(".popup_success_txt").text(response.message);
                    setTimeout(function(){
                        location.reload();
                    }, 3000);
                    
                },
                error: function(xhr, status, error) {
                    console.log(xhr);
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                }
            });
        }
    }
}
$(document).on('change','#date_timesheet',function(){
    var date=$(this).val();
    var staff_id='<?php echo $staff->id;?>';
    $.ajax({
        type: "post",
        url: "{{ url('/staff/timesheet_filter') }}",
        data: {date:date,staff_id:staff_id,_token:"{{csrf_token()}}"},
        success: function(response) {
            console.log(response);
            // return false;
            if (typeof isAuthenticated === "function") {
                if (isAuthenticated(response) == false) {
                    return false;
                }
            }
            var table = $('#satffTimesheetTable').DataTable();
            table.destroy();
            $("#user_data").html(response.data);
            $('#satffTimesheetTable').DataTable();
        },
        error: function(xhr, status, error) {
            console.log(xhr);
            var errorMessage = xhr.status + ': ' + xhr.statusText;
            alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
        }
    });
});
</script>
@endsection