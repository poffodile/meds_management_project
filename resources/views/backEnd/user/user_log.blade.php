@extends('backEnd.layouts.master')

@section('title',' User Logs')

@section('content')

<!--main content start-->
    <section id="main-content">
        <section class="wrapper">
        <!-- page start-->

        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <!-- <header class="panel-heading">
                        Editable Table
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-cog"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                         </span>
                    </header> -->
                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            @include('backEnd.common.alert_messages')
                            <div class="col-lg-3 col-sm-3" style="margin-bottom:15px">
                                <label for="inputName" class="control-label">Month:</label>
                                <select id="select_month" class="form-control">
                                        <option selected disabled>Select Month</option>
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
                            </div>
                            <div class="col-lg-3 col-sm-3" style="margin-bottom:15px">
                                <label for="inputName" class="control-label">Year:</label>
                                <select id="select_year" class="form-control">
                                    <option selected disabled>Select Year</option>
                                    @foreach($years as $year)
                                        <option value="{{$year}}">{{$year}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="clearfix">
                                <!-- <div class="btn-group" style="margin:25px">
                                    <a href="{{ url('admin/user/annual-leave/add/'.$user_id) }}">
                                        <button id="editable-sample_new" class="btn btn-primary">
                                            Add Annual Leave <i class="fa fa-plus"></i>
                                        </button>
                                    </a>
                                </div> -->
                            </div>
                            <div class="space15"></div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div id="editable-sample_length" class="dataTables_length">
                                        <form method='post' action="{{ url('admin/user/logs/'.$user_id) }}" id="records_per_page_form">
                                            <label>
                                                <select name="limit"  size="1" aria-controls="editable-sample" class="form-control xsmall select_limit">
                                                    <option value="10" {{ ($limit == '10') ? 'selected': '' }}>10</option>
                                                    <option value="20" {{ ($limit == '20') ? 'selected': '' }}>20</option>
                                                    <option value="30" {{ ($limit == '30') ? 'selected': '' }}>30</option>
                                                    <!-- <option value="all" {{ ($limit == 'all') ? 'selected': '' }}>All</option> -->
                                                </select> records per page
                                            </label>
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="month" id="month" value="{{$selected_month}}">
                                            <input type="hidden" name="year" id="year" value="{{$selected_year}}">
                                        </form>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <form method='get' action="{{ url('admin/user/logs/'.$user_id) }}">
                                        <div class="dataTables_filter" id="editable-sample_filter">
                                            <label>Search: <input name="search" type="text" value="{{ $search }}" aria-controls="editable-sample" class="form-control medium" ></label>
                                            <!-- <button class="btn search-btn" type="submit"><i class="fa fa-search"></i></button>   -->
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Clock In Time</th>
                                        <th>Clock Out Time</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th width="20%">Actions</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        if($u_annual_leave->isEmpty()){ ?>
                                            <?php
                                                echo '<tr style="text-align:center">
                                                      <td colspan="7">No records found.</td>
                                                      </tr>';
                                            ?>
                                        <?php 
                                        } 
    									else
                                        {
                                            foreach($u_annual_leave as $key => $value) {

                                            $login_date = date('d M Y', strtotime($value->login_date));

                                        ?>

                                        <tr>
                                            <td>{{ ++$key }}</td>
                                            <td>{{ $login_date }}</td>
                                            <td>{{ date('h:i:s A', strtotime($value->check_in_time)) }}</td>
                                            <td>
                                                @if(!empty($value->check_out_time))
                                                        {{ date('h:i:s A', strtotime($value->check_out_time)) }}
                                                    @endif
                                            </td>
                                            <td>{{ ucfirst($value->reason) }}</td>
                                            <td>
                                                <?php if(empty($value->check_out_time)){?>
                                                    <button class="btn btn-info">Pending...</button>
                                                <?php } else if($value->is_valid == 1){?>
                                                    <button class="btn btn-primary" onclick="is_valid({{$value->id}},0)">Valid</button>
                                                <?php }else{?>
                                                    <button class="btn btn-danger" onclick="is_valid({{$value->id}},1)">In-valid</button>
                                                <?php }?>
                                            </td>
                                            
                                            <td>
                                                <!-- <a href="{{ url('admin/user/annual-leave/edit/'.$value->id) }}" class="edit"><i data-toggle="tooltip" title="Edit" class="fa fa-edit"></i></a> -->
                                                <a href="{{ url('admin/user/logs/delete/'.$value->id) }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o"></i></a> 
                                            </td>
                                            
                                        </tr>
                                        <?php } } ?>
                                  
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- <div class="row"><div class="col-lg-6"><div class="dataTables_info" id="editable-sample_info">Showing 1 to 28 of 28 entries</div></div><div class="col-lg-6"><div class="dataTables_paginate paging_bootstrap pagination"><ul><li class="prev disabled"><a href="#">← Prev</a></li><li class="next"><a href="#">Next → </a></li></ul></div></div></div> -->
                            @if($u_annual_leave->links() !== null) 
                            {{ $u_annual_leave->links() }}
                            @endif

                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->
        </section>
        <script>
            function is_valid(id,update_value){
                if((id === '') || (id === undefined) || (update_value === '') || (update_value === undefined)){
                    alert("Something went wrong! Please try again later");
                    return false;
                }else{
                    var message=(update_value == 0) ? 'In-valid':'Valid';
                    if(confirm("Do you really want to "+message)){
                        $.ajax({
                            type: "POST",
                            url: "{{url('admin/user/is_valid')}}",
                            data: {id:id,update_value:update_value,_token:"{{ csrf_token() }}"},
                            success: function(response) {
                                console.log(response);
                                // return false;
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
            $("#select_month").on('change',function(){
                $("#select_year").val('');
                $("#month").val($(this).val());
            });
            $("#select_year").on('change',function(){
                var month=$("#month").val();
                var year=$("#year").val($(this).val());
                if(month == '' || month == undefined){
                    alert("Please select month first");
                    return false;
                }else{
                    $('#records_per_page_form').submit();
                }
            });
        </script>
    </section>
<!--main content end-->

@endsection