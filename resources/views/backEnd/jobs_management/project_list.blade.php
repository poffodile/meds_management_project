@extends('backEnd.layouts.master')

@section('title','Users')

@section('content')

<?php
    $page_url = url('admin/project_list');
?>


<style type="text/css">
 .position-center label {
    font-size: 20px;
    font-weight: 500;
}   
.custom-fieldset {
    position: relative;
    border: 2px solid #00000026;
    padding: 10px;
    margin-top: 20px;
    margin:10px;
}
.custom-legend {
    position: absolute;
    top: -10px;
    left: 20px;
    background-color: white;
    font-weight: bold;
    padding: 0 10px;
}
.modal-body .row {
    margin-bottom: 1rem;
}
p.floatLeft.redText.marginTop8px.marginBottom10 {
    text-align: left;
    font-size: 12px;
    color: #f00;
}
.workflowText{
    border-top:none;
    border-bottom:1px solid #ccc;
}
thead#flowhead {
    background: #eee;
}
.adv-table.editable-table {
    overflow: auto;
}
</style>

<!--main content start-->
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                         <div class="row"> 
                          <div class="col-lg-6">  
                            <div class="clearfix">
                                <div class="btn-group">
                                    <a href="{{url('admin/project_add')}}">
                                        <button id="editable-sample_new" class="btn btn-primary">
                                            Project - Add <i class="fa fa-plus"></i>
                                        </button>
                                    </a>    
                                </div>
                                @include('backEnd.common.alert_messages')
                            </div>
                           </div>
                           <div class="col-lg-6">
                            <div class="cog-btn-main-area">
                             <a class="btn btn-primary" href="#" data-toggle="dropdown">
                                    <i class="fa fa-cog fa-fw"></i>
                                </a>
                                <ul class="dropdown-menu pull-right">
                                    
                                </ul>   
                            </div>
                           </div>
                          </div>
                            <div class="space15"></div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div id="editable-sample_length" class="dataTables_length">
                                        <form method='post' action="{{ $page_url }}" id="records_per_page_form">
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
                                    <form method='post' action="{{ $page_url }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
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
                                        <th>Project Ref</th>
                                        <th>Project Name</th>
                                        <th>Customer Name</th>
                                        <th>Unassigned Jobs</th>
                                        <th>Active</th>
                                        <th>Completed</th>
                                        <th>Authorised</th>
                                        <th>Other</th>
                                        <th>Total Jobs</th>
                                        <th>Project Value</th>
                                        <th>Product Cost</th>
                                        <th>User Cost</th>
                                        <th>Purchase Orders</th>
                                        <th>Expense</th>
                                        <th>Total Cost</th>
                                        <th>Profit</th>
                                        <th>Margin</th>
                                        <th>Invoiced</th>
                                        <th>Act. Profit</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        if(count($project) == 0){ ?>
                                            <?php
                                                echo '<tr style="text-align:center">
                                                      <td colspan="24">No Account Code found.</td>
                                                      </tr>';
                                            ?>
                                        <?php 
                                        } 

                                        else
                                        {
                                            foreach($project as $key => $val) 
                                            {  
                                                $cust=App\models\Customer::find($val->customer_name);?>

                                        <tr >
                                            <td class="user_name">{{ ++$key }}</td>
                                            <td>{{$val->project_ref}}</td>
                                            <td class="transform-none" style="text-transform: none;">{{ucfirst($val->project_name)}}</td>
                                            <td><?php echo ($cust->name) ?? "";?></td>
                                            <td>0</td>
                                            <td>1</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>{{$val->project_value}}</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>{{$val->start_date}}</td>
                                            <td>{{$val->end_date}}</td>
                                            <td>
                                                @if($val->status == 1)
                                                    <a href="javascript:" onclick="status_change('{{base64_encode($val->id)}}',0)" class="btn btn-success">Active</a>
                                                @else
                                                <a href="javascript:" class="btn btn-danger" onclick="status_change('{{base64_encode($val->id)}}',1)">Inactive</a>
                                                @endif
                                            </td>
                                            <td class="action-icn">
                                                <a href="{{ url('admin/project_add?key=')}}{{base64_encode($val->id)}}" class="edit"><span style= "font-size: 13px; color: #000;"><span style= "color: #000"><i data-toggle="tooltip" title="Edit" class="fa fa-edit fa-lg"></i></span></a> &nbsp &nbsp &nbsp 

                                                <a href="javascript:" onclick="delete_job('{{base64_encode($val->id)}}')" class="text-danger"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o fa-lg"></i></a>
                                             
                                            </td>
                                            
                                        </tr>
                                        <?php } } ?>
                                  
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- <div class="row"><div class="col-lg-6"><div class="dataTables_info" id="editable-sample_info">Showing 1 to 28 of 28 entries</div></div><div class="col-lg-6"><div class="dataTables_paginate paging_bootstrap pagination"><ul><li class="prev disabled"><a href="#">← Prev</a></li><li class="next"><a href="#">Next → </a></li></ul></div></div></div> -->
                           
                        </div>
                    </div>
                </section>
            </div>
        </div>
        
        <!-- page end-->
    </section>
</section>
<!--main content end-->

<script>
    function status_change(id,status){
        var id=id;
        var status=status;
        var token='<?php echo csrf_token();?>'
            $.ajax({  
                type:"POST",
                url:"{{url('admin/project_status_change')}}",
                data:{id:id,status:status,_token:token},
                success:function(data)
                {
                    console.log(data);
                    if($.trim(data)=="done"){
                        window.location.reload();
                    }
                }
            }); 
    }
    function delete_job(id){
       if(confirm("Do you want to delete it ?")){
        var id=id;
        var token='<?php echo csrf_token();?>'
            $.ajax({  
                type:"POST",
                url:"{{url('admin/project_delete')}}",
                data:{id:id,_token:token},
                success:function(data)
                {
                    console.log(data);
                    if($.trim(data)=="done"){
                        window.location.reload();
                    }
                }
            }); 
       }
        
    }
    
    // function get_save_workflow(){
    //     var job_type_id=$('#job_type_id').val();
    //     alert(job_type_id)
    // }
</script>

@endsection


