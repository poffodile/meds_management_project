@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Staff Log View')
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
                        <h4>{{$staff->name ?? ""}} Log View</h4>
                    </header>
                    <div class="col-lg-12 mt-3">
                            <div class="jobsection justify-content-between align-items-center">
                                <div class="d-flex justify-content-end gap-2 align-items-center">
                                    <label for="fromDate" class="mb-0"> Month:</label>
                                    <select name="month" id="month" class="form-control">
                                        <option selected="" disabled="">Select Month</option>
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">August</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                    <label for="ToDate" class="mb-0"> Year:</label>
                                    <select name="year" id="year" class="form-control">
                                        <option selected="" disabled="">Select Year</option>
                                        @foreach($years as $year)
                                            <option value="{{$year}}">{{$year}}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-warning" onclick="location.reload()">Reset</button>
                                </div>
                            </div>
                        </div>
                    <div class="panel-body">
                        
                            <div class="maimtable productDetailTable mb-4  table-responsive">
                                
                                <table class="table border-top border-bottom tablechange" id="staffLogViewTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Login Date</th>
                                            <th>Clock In Time</th>
                                            <th>Clock Out Time</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="staff_log_view_data">
                                        @foreach($logs as $val)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $val->login_date }}</td>
                                                <td>{{ date('h:i:s A', strtotime($val->check_in_time)) }}</td>
                                                <td>
                                                    @if(!empty($val->check_out_time))
                                                        {{ date('h:i:s A', strtotime($val->check_out_time)) }}
                                                    @endif
                                                </td>
                                                <td>{{ $val->reason }}</td>
                                                <td><?php if(empty($val->check_out_time)){?>
                                                    <button class="btn btn-info">Pending...</button>
                                               <?php } else if($val->is_valid == 1){?>
                                                        <button class="btn btn-warning" onclick="is_valid({{$val->id}},0)">Valid</button>
                                                    <?php }else{?>
                                                        <button class="btn btn-warning" onclick="is_valid({{$val->id}},1)">In-valid</button>
                                                    <?php }?>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> <!-- End off main Table -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
<script>
    $(document).ready(function(){
        setTimeout(function() {
            $(".alert").fadeOut();
        }, 3000);
    });
    new DataTable('#staffLogViewTable');
</script>
<script>
$("#year").on('change',function(){
    log_filter_function();
});
$("#month").on('change',function(){
    $("#year").val('');
});
function log_filter_function(){
    var year=$("#year").val();
    var month=$("#month").val();
    var log='<?php echo $staff->id;?>'
    if(year == '' || year == null){
        alert("Please Select The Year");
        return false;
    }else if(month == '' || month == null){
        alert("Please Select The Month");
        return false;
    }else{
        $.ajax({
            type: "POST",
            url: "{{url('/satff/log/view/filter')}}",
            data: {year:year,month:month,log:log,_token:"{{ csrf_token() }}"},
            success: function(response) {
                console.log(response);
                // return false;
                if (typeof isAuthenticated === "function") {
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                }
                if (response.success === true) {
                    var table = $('#staffLogViewTable').DataTable();
                    table.destroy();
                    var data = response.data;
                    let html_data = '';
                    let index = 1;
                    function formatTime(datetimeString) {
                        if (!datetimeString) return '';

                        let date = new Date(datetimeString);
                        let hours = date.getHours();
                        let minutes = date.getMinutes();
                        let seconds = date.getSeconds();

                        let ampm = hours >= 12 ? 'PM' : 'AM';
                        hours = hours % 12;
                        hours = hours ? hours : 12;

                        return (
                            String(hours).padStart(2, '0') + ":" +
                            String(minutes).padStart(2, '0') + ":" +
                            String(seconds).padStart(2, '0') + " " + ampm
                        );
                    }
                    var status;
                    data.forEach((value) => {
                        if(value.check_out_time == '' || value.check_out_time == null){
                            status="<button class='btn btn-info'>Pending...</button>";
                        }else if(value.is_valid == 1){
                            status = `<button class='btn btn-warning' onclick="is_valid('${value.id}', 0)">Valid</button>`;
                        }else{
                            status = `<button class='btn btn-warning' onclick="is_valid('${value.id}', 1)">In-valid</button>`;
                        }
                        html_data += `
                            <tr>
                                <td>${index}</td>
                                <td>${value.login_date}</td>
                                <td>${formatTime(value.check_in_time)}</td>
                                <td>${formatTime(value.check_out_time)}</td>
                                <td>${value.reason ?? ''}</td>
                                <td>${status}</td>
                            </tr>`;
                        index++;
                    });

                    $("#staff_log_view_data").html(html_data); 
                    $('#staffLogViewTable').DataTable();
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + error);
            }
        });
    }
}
function is_valid(id,update_value){
    if((id === '') || (id === undefined) || (update_value === '') || (update_value === undefined)){
        alert("Something went wrong! Please try again later");
        return false;
    }else{
        var message=(update_value == 0) ? 'In-valid':'Valid';
        if(confirm("Do you really want to "+message)){
            $.ajax({
                type: "POST",
                url: "{{url('/satff/log/view/is_valid')}}",
                data: {id:id,update_value:update_value,_token:"{{ csrf_token() }}"},
                success: function(response) {
                    console.log(response);
                    // return false;
                    if (typeof isAuthenticated === "function") {
                        if (isAuthenticated(response) == false) {
                            return false;
                        }
                    }
                    if (response.success === true) {
                        location.reload();
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
    }
}
</script>
@endsection