@extends('backEnd.layouts.master')

@section('title',' User Timesheet')

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
                            <div class="clearfix">
                                <div class="btn-group">
                                    <!-- <a href="{{ url('admin/user/annual-leave/add/'.$user_id) }}">
                                        <button id="editable-sample_new" class="btn btn-primary">
                                            Add Time Sheet <i class="fa fa-plus"></i>
                                        </button>
                                    </a> -->
                                </div>
                                @include('backEnd.common.alert_messages')
                            </div>
                            <div class="space15"></div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div id="editable-sample_length" class="dataTables_length">
                                        <form method='post' action="{{ url('admin/user/timesheet/'.$user_id) }}" id="records_per_page_form">
                                            <label>
                                                <select name="limit"  size="1" aria-controls="editable-sample" class="form-control xsmall select_limit">
                                                    <option value="10" {{ ($limit == '10') ? 'selected': '' }}>10</option>
                                                    <option value="20" {{ ($limit == '20') ? 'selected': '' }}>20</option>
                                                    <option value="30" {{ ($limit == '30') ? 'selected': '' }}>30</option>
                                                    <!-- <option value="all" {{ ($limit == 'all') ? 'selected': '' }}>All</option> -->
                                                </select> records per page
                                            </label>
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        </form>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <form method='get' action="{{ url('admin/user/timesheet/'.$user_id) }}">
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
                                        <th>{{date('F Y')}}</th>
                                        <th>Total Shitf Hours</th>
                                        <th>Category Type</th>
                                        <th>Extra Hours</th>
                                        <th>Comments</th>
                                        <th width="20%">Actions</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        if($u_timesheet->isEmpty()){ ?>
                                            <?php
                                                echo '<tr style="text-align:center">
                                                      <td colspan="6">No records found.</td>
                                                      </tr>';
                                            ?>
                                        <?php 
                                        } 
    									else
                                        {
                                            function formatHours($decimalHours) {
                                                if ($decimalHours == 0 || $decimalHours == null) {
                                                    return "";
                                                }

                                                $hours = floor($decimalHours);
                                                $minutes = round(($decimalHours - $hours) * 60);

                                                return "{$hours}h {$minutes}min";
                                            }
                                            $category_type='';
                                            foreach($u_timesheet as $key => $value) {
                                            $date = date('d M Y', strtotime($value->date));
                                            if($value->category_id == 1){
                                                $category_type='Sleep';
                                            }else if($value->category_id == 2){
                                                $category_type='Disturbance';
                                            }else if($value->category_id == 3){
                                                $category_type='Wake Night';
                                            }else if($value->category_id == 4){
                                                $category_type='Annual Leave';
                                            }else{
                                                $category_type='On Call';
                                            }
                                            $total_hours=App\RotaAssignEmployee::where('emp_id',$value->user_id)->whereDate('created_at',$value->date)->sum('total_hours');
                                            $ex_time = explode('.', $value->hours);
                                            $hour = isset($ex_time[0]) ? $ex_time[0] . 'h' : '0h';
                                            $min  = isset($ex_time[1]) ? $ex_time[1] . 'min' : '0min';

                                        ?>

                                        <tr>
                                            <td>{{ $date }}</td>
                                            <td>{{ formatHours($total_hours) }}</td>
                                            <td>{{ $category_type }}</td>
                                            <td>{{ $hour }} {{ $min }}</td>
                                            <td>{{ ucfirst($value->comments) }}</td>
                                            <td class="action-icn">
                                                
                                                <a href="{{ url('admin/user/timesheet/edit/'.$value->id) }}" class="edit"><i data-toggle="tooltip" title="Edit" class="fa fa-edit"></i></a>
                                                <a href="{{ url('admin/user/timesheet/delete/'.$value->id) }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o"></i></a>
                                            </td>
                                            
                                        </tr>
                                        <?php } } ?>
                                  
                                    </tbody>
                                </table>
                            </div>
                            @if($u_timesheet->links() !== null) 
                            {{ $u_timesheet->links() }}
                            @endif

                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->
        </section>
    </section>
<!--main content end-->

@endsection