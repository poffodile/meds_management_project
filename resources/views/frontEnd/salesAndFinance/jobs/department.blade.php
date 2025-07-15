@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Departments')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<style>
    .addError {
        border: 1px solid red;
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
                        <h4>Departments</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12">
                            <div class="jobsection justify-content-end">
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#customerPop" class="btn btn-warning"><i class="fa fa-plus"></i> Add</a>
                                <a href="javascript:void(0)" id="deleteSelectedRows" class="btn btn-warning">Delete</a>
                            </div>
                            <div class="alert alert-success text-center" id="msg" style="display:none;height:50px">
                                <p id="status_meesage"></p>
                            </div>
                            <div class="maimTable productDetailTable mb-4 table-responsive">
                                <table class="table border-top border-bottom" id="exampleOne">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAll"> <label for="selectAll"> All Select</label></th>
                                            <th>#</th>
                                            <th>Department</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="result">
                                        <?php foreach ($department as $key => $val) { ?>
                                            <tr>
                                                <td><input type="checkbox" id="" class="delete_checkbox" value="{{$val->id}}"></td>
                                                <td>{{++$key}}</td>
                                                <td>{{$val->title}}</td>
                                                <td>
                                                    <?php if ($val->status == 1) { ?>
                                                        <span class="grencheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa fa-check-circle"></i></span>
                                                    <?php } else { ?>
                                                        <span class="grayCheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa fa-check-circle"></i></span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <div class="d-inline-flex align-items-center ">
                                                        <div class="dropdown">
                                                            <a href="#" class="btn btn-sm btn-primary" data-toggle="dropdown">
                                                                Action <i class="fa fa-caret-down"></i>
                                                            </a>
                                                            <div class="dropdown-menu fade-up m-0">
                                                                <a href="javascript:void(0)" data-toggle="modal" data-target="#customerPop" class="dropdown-item modal_dataFetch" data-id="{{ $val->id }}" data-title="{{ $val->title }}" data-status="{{ $val->status }}">Edit Details</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
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
<!--  Modal start here -->
<div class="modal fade" id="customerPop" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="customerModalLabel">Department - Add</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-success text-center success_message" style="display:none;height:50px">
                            <p id="message"></p>
                        </div>
                        <div class="alert alert-danger text-center error_message" style="display:none;height:50px">
                            <p id="error_message"></p>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="formDtail">
                            <form id="form_data">
                                <input type="hidden" name="id" id="id">
                                <div class="mb-3">
                                    <label class="mb-2 col-form-label">Department <span class="radStar ">*</span></label>
                                    <input type="text" class="form-control editInput" id="title" name="title" value="">
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2 col-form-label">Status</label>
                                    <select class="form-control editInput selectOptions" id="statusModal" name="status">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> <!-- End row -->
            </div>
            <div class="modal-footer customer_Form_Popup">
                <!-- <button type="button" class="profileDrop" id="save_dataClose">Save &  Close</button> -->
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="save_data">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- end here -->
<script src="{{url('public/backEnd/js/multiselect.js')}}"></script>
<script>
    $('#save_dataClose').on('click', function() {
        var title = $("#title").val();
        if (title == '') {
            $("#title").addClass('addError');
            return false;
        } else {
            saveData();
            $("#customerPop").modal('hide');
        }
    });

    $('#save_data').on('click', function() {
        saveData();
    });

    function saveData() {
        var token = '<?php echo csrf_token(); ?>';
        var title = $("#title").val();
        var tax_rate = $("#tax_rate").val();
        var tax_code = $("#tax_code").val();
        var exp_date = $("#exp_date").val();
        var status = $.trim($('#statusModal option:selected').val());
        var id = $("#id").val();
        var message;
        var url;
        if (id == '') {
            message = "Added Successfully Done";
            url = '{{ url("/save_department") }}';
        } else {
            message = "Edited Successfully Done";
            url = '{{ url("/edit_department") }}';
        }
        if (title == '') {
            $("#title").addClass('addError');
            return false;
        } else if (tax_rate == '') {
            $("#title").removeClass('addError');
            $("#tax_rate").addClass('addError');
            return false;
        } else {
            $.ajax({
                type: "POST",
                url: url,
                data: {
                    id: id,
                    title: title,
                    status: status,
                    _token: token
                },
                success: function(data) {
                    console.log(data);
                    if (data.vali_error) {
                        $("#error_message").text(data.vali_error);
                        $(".error_message").show();
                        setTimeout(function() {
                            $(".error_message").hide();
                            // $("#form_data")[0].reset();
                        }, 3000);
                        return false;
                    } else if (data.data && data.data.original && data.data.original.error) {
                        alert(data.data.original.error);
                        return false;
                    } else {
                        $("#message").text(message);
                        $(".success_message").show();
                        setTimeout(function() {
                            $(".alert").hide();
                            location.reload();
                            // $("#form_data")[0].reset();
                        }, 3000);

                    }

                }

            });
        }
    }
    $('.modal_dataFetch').on('click', function() {
        var id = $(this).data('id');
        var title = $(this).data('title');
        var status = $(this).data('status');

        $('#id').val(id);
        $('#title').val(title);
        $('#statusModal').val(status);
    });

    function status_change(id, status) {
        var token = '<?php echo csrf_token(); ?>'
        var model = "Department";
        $.ajax({
            type: "POST",
            url: "{{url('/Department_status_change')}}",
            data: {
                id: id,
                status: status,
                model: model,
                _token: token
            },
            success: function(data) {
                console.log(data);
                if ($.trim(data) == 1) {
                    $("#status_meesage").text("status Changed Successfully Done");
                    $("#msg").show();
                    setTimeout(function() {
                        location.reload();
                    }, 3000);


                }

            }
        });
    }
</script>
<script>
    $("#deleteSelectedRows").on('click', function() {
        let ids = [];

        $('.delete_checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        if (ids.length == 0) {
            alert("Please check the checkbox for delete");
        } else {
            if (confirm("Are you sure to delete?")) {
                // console.log(ids);
                var token = '<?php echo csrf_token(); ?>'
                var model = 'Department';
                $.ajax({
                    type: "POST",
                    url: "{{url('/bulk_delete')}}",
                    data: {
                        ids: ids,
                        model: model,
                        _token: token
                    },
                    success: function(data) {
                        console.log(data);
                        if (data) {
                            location.reload();
                        } else {
                            alert("Something went wrong");
                        }
                        // return false;
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText;
                        alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                    }
                });
            }
        }

    });
    $('.delete_checkbox').on('click', function() {
        if ($('.delete_checkbox:checked').length === $('.delete_checkbox').length) {
            $('#selectAll').prop('checked', true);
        } else {
            $('#selectAll').prop('checked', false);
        }
    });
</script>

@endsection