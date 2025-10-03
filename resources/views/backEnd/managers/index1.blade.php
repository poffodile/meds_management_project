@extends('backEnd.layouts.master')
@section('title',' Managers')
@section('content')
<script type="text/javascript" src="{{ url('public/backEnd/js/sweetalert.min.js')}}"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<style type="text/css">
    .switch {
        position: relative;
        display: inline-block;
        width: 35px;
        height: 20px;
    }
    /* Hide default HTML checkbox */
    .switch input {display:none;}

    /* The slider */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 12px;
        width: 12px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked + .slider {
      background-color: #1fb5ad;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #1fb5ad;
    }

    input:checked + .slider:before {
        -webkit-transform: translateX(16px);
        -ms-transform: translateX(16px);
        transform: translateX(16px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
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
                            <div class="clearfix">
                                <div class="btn-group">
                                    <a href="{{ url('admin/managers/add') }}">
                                        <button id="editable-sample_new" class="btn btn-primary">
                                            Add Manager <i class="fa fa-plus"></i>
                                        </button>
                                    </a>
                                </div>
                                @include('backEnd.common.alert_messages')
                            </div>
                            <div class="space15"></div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div id="editable-sample_length" class="dataTables_length">
                                        <form method='post' action="{{ url('admin/managers') }}" id="records_per_page_form">
                                            <label>
                                                <select name="limit"  size="1" aria-controls="editable-sample" class="form-control xsmall select_limit">
                                                    <option value="10" {{ ($limit == '10') ? 'selected': '' }}>10</option>
                                                    <option value="20" {{ ($limit == '20') ? 'selected': '' }}>20</option>
                                                    <option value="30" {{ ($limit == '30') ? 'selected': '' }}>30</option>
                                                    <!-- <option value="all" {{ ($limit == 'all') ? 'selected': '' }}>All</option> -->
                                                </select>records per page
                                            </label>
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        </form>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                    <thead>
                                        <tr>
                                            <th width="20%">Name</th>
                                            <th width="25%">Email</th>
                                            <th>Contact Number</th>
                                            <th>Active</th>
                                            <th width="20%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            if($managers->isEmpty()){
                                                echo '<tr style="text-align:center">
                                                        <td colspan="5">No record found</td>
                                                      </tr>';
                                            }else{
                                                foreach($managers as $manager){
                                        ?>
                                        <tr>
                                            <td>{{ $manager['name']}}</td>
                                            <td>{{ $manager['email']}}</td>
                                            <td>{{ $manager['contact_no'] }}</td>
                                            <td>
                                                <!-- <label class="switch">
                                                    <input type="checkbox" name="status" id="status" manager_id="{{ $manager['id'] }}" value="{{ $manager['status'] }}" <?php if($manager['status'] == '1'){ echo "checked";}?>>
                                                    <span class="slider round"></span>
                                                </label> -->
                                                <div class="action toggle-td">
                                                    <?php if($manager['status'] == '1'){ ?>
                                                        <a class="toggle status" manager_id="{{ $manager['id'] }}" status="{{ $manager['status'] }}"><i class="fa fa-toggle-on"></i></a>
                                                    <?php } else{ ?>
                                                        <a class="toggle status" manager_id="{{ $manager['id'] }}" status="{{ $manager['status'] }}"><i class="fa fa-toggle-off"></i></a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                            <td class="action-icn">
                                                <a href="{{ url('admin/managers/edit').'/'.$manager['id'] }}" class="edit"><span style = "color: #000;"><i data-toggle="tooltip" title="Edit" class="fa fa-edit fa-lg"></i></a>
                                                <a href="{{ url('admin/managers/delete').'/'.$manager['id'] }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o fa-lg"></i></a>
                                            </td>

                                            <!-- <td class="action-icn">
                                                <a href="{{ url('admin/managers/edit').'/'.$manager['id'] }}" class="edit"><i data-toggle="tooltip" title="Edit" class="fa fa-edit"></i></a>
                                                <a href="{{ url('admin/managers/delete').'/'.$manager['id'] }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o"></i></a>
                                            </td> -->
                                        </tr>
                                        <?php } } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->
        </section>
    </section>
    <!--main content end-->

    <!--script for this page only-->
<!-- <script src="{{ url('public/backEnd/js/table-editable.js') }}"></script> -->

<!-- END JAVASCRIPTS -->

<script>
/*    jQuery(document).ready(function() {
        EditableTable.init();
    });*/
</script>

<script type="text/javascript">
    $(document).on('click','.status',function(){
        var toggle_btn      = $(this);
        var manager_id      = $(this).attr('manager_id');
        var status_value    = $(this).attr('status'); // current status
        $('.loader').show();
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        $.ajax({
            type: 'POST',
            url: "{{ url('admin/manager/change-status') }}",
            data:'manager_id= '+ manager_id,
            success:function(data){
                $('.loader').hide();
                if(data == '0'){
                    toggle_btn.closest('.toggle-td').html('<a class="toggle status" manager_id="'+manager_id+'" status="0"><i class="fa fa-toggle-off"></i></a>');

                    swal("Success!", "Changes saved successfully!", "success");
                }else if(data == '1'){

                    toggle_btn.closest('.toggle-td').html('<a class="toggle status" manager_id="'+manager_id+'" status="1"><i class="fa fa-toggle-on"></i></a>');

                    swal("Success!", "Changes saved successfully!", "success");
                    
                }else if(data == 'false'){
                    swal({
                      text: "Manager is already selected",
                      icon: "warning",
                      dangerMode: true,
                    })
                    .then((willDelete) => { 
                        if (willDelete) {
                            return true;
                        } 
                        else {
                            return false;
                        }
                    });
                }
            }
        });
    });
</script>

@endsection

