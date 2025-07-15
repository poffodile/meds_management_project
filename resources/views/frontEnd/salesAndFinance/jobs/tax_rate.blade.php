@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Tax Rate')
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
                        <h4>Tax Rates</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12">
                            <div class="jobsection justify-content-end">
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#customerPop" class="btn btn-warning"> <i class="fa fa-plus"></i> Add</a>
                                <a href="{{url('/tax_rate?mode=Active')}}" class="btn btn-warning">Active</a>
                                <a href="{{url('/tax_rate?mode=Inactive')}}" class="btn btn-warning">Inactive</a>
                                <a href="javascript:void(0)" id="deleteSelectedRows" class="btn btn-danger">Delete</a>
                            </div>
                            <div class="alert alert-success text-center" id="msg" style="display:none;height:50px">
                                <p id="status_meesage"></p>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="maimTable productDetailTable mb-4 table-responsive">
                                <table id="exampleOne" class="table border-top border-bottom" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAll"> <label for="selectAll"> All Select</label></th>
                                            <th>#</th>
                                            <th>Tax Rate Name</th>
                                            <th>Tax Rate</th>
                                            <th>Tax Code</th>
                                            <th>Expiry Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody id="result">
                                        <?php foreach ($tax_rate as $key => $val) { ?>
                                            <tr>
                                                <td><input type="checkbox" id="" class="delete_checkbox" @php if($val->id == 1 || $val->id == 2 ){ echo "disabled"; } @endphp value="{{$val->id}}"></td>
                                                <td>{{++$key}}</td>
                                                <td>{{$val->name}}</td>
                                                <td>{{$val->tax_rate}}</td>
                                                <td>{{$val->tax_code}}</td>
                                                <td>{{$val->exp_date}}</td>
                                                <td>
                                                    <?php if ($val->status == 1) { ?>
                                                        <span class="grencheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa fa-check-circle"></i></span>
                                                    <?php } else { ?>
                                                        <span class="grayCheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa fa-check-circle"></i></span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <div class="d-inline-flex align-items-center">
                                                        <div class="dropdown">
                                                            <a href="#" class="btn-sm btn btn-primary" data-toggle="dropdown"> Action <i class="fa fa-caret-down"></i></a>
                                                            <div class="dropdown-menu dropdown-menu-right fade-up m-0">
                                                                <a href="javascript:void(0)" data-toggle="modal" data-target="#customerPop" class="dropdown-item modal_dataFetch" data-id="{{ $val->id }}" data-name="{{ $val->name }}" data-tax_rate="{{$val->tax_rate}}" data-tax_code="{{$val->tax_code}}" data-exp_date="{{$val->exp_date}}" data-status="{{ $val->status }}">Edit</a>
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
                <h4 class="modal-title" id="customerModalLabel">Tax Rate - Add</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="text-center" id="message_save"></div>
                        <form id="form_data">
                            <input type="hidden" name="id" id="id">
                            <div class="mb-3">
                                <label class="mb-2 col-form-label">Name <span class="radStar ">*</span></label>
                                <input type="text" class="form-control editInput" id="name" name="name" value="">
                            </div>
                            <div class="mb-3">
                                <label class="mb-2 col-form-label">Tax Rate <span class="radStar ">*</span></label>
                                <input type="text" class="form-control editInput" id="tax_rate" name="tax_rate" value="" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 46">
                            </div>
                            <div class="mb-3">
                                <label class="mb-2 col-form-label">Status</label>
                                <select class="form-control editInput selectOptions" id="statusModal" name="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="mb-2 col-form-label">External Tax Code</label>
                                <input type="text" class="form-control editInput" id="tax_code" name="tax_code" value="" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 46">
                            </div>
                            <div class="mb-3">
                                <label class="mb-2 col-form-label">Expiry Date</label>
                                <input type="date" class="form-control editInput" id="exp_date" name="exp_date" value="">
                            </div>
                        </form>
                    </div>
                </div> <!-- End row -->
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="save_data">Save</button>
                <!-- <button type="button" class="btn btn-warning" id="save_dataClose">Save & Close</button> -->
            </div>
        </div>
    </div>
</div>
<!-- end here -->

<script src="{{url('public/backEnd/js/multiselect.js')}}"></script>

<script>
    $('#save_dataClose').on('click', function() {
        var name = $("#name").val();
        if (name == '') {
            $("#name").addClass('addError');
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
        var name = $("#name").val();
        var tax_rate = $("#tax_rate").val();
        var tax_code = $("#tax_code").val();
        var exp_date = $("#exp_date").val();
        var status = $.trim($('#statusModal option:selected').val());
        var home_id = '<?php echo $home_id; ?>';
        var id = $("#id").val();
        var message;
        var url;
        if (id == '') {
            message = "Added Successfully Done";
            url = '{{ url("/save_tax_rate") }}';
        } else {
            message = "Edited Successfully Done";
            url = '{{ url("/edit_tax_rate") }}';
        }
        if (name == '') {
            $("#name").addClass('addError');
            return false;
        } else if (tax_rate == '') {
            $("#name").removeClass('addError');
            $("#tax_rate").addClass('addError');
            return false;
        } else {
            $.ajax({
                type: "POST",
                url: url,
                data: {
                    id: id,
                    home_id: home_id,
                    name: name,
                    tax_rate: tax_rate,
                    tax_code: tax_code,
                    exp_date: exp_date,
                    status: status,
                    _token: token
                },
                success: function(response) {
                    console.log(response);
                    if (response.vali_error) {
                        $('#message_save').addClass('error-message').text(response.vali_error).show();
                        setTimeout(function() {
                            $('#error-message').text('').fadeOut();
                        }, 3000);
                        return false;
                    } else if (response.success === true) {
                        $('#message_save').addClass('success-message').text(response.message).show();
                        setTimeout(function() {
                            $('#message_save').removeClass('success-message').text('').hide();
                            location.reload();
                        }, 3000);
                    }

                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + error);
                }

            });
        }
    }
    $('.modal_dataFetch').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var tax_rate = $(this).data('tax_rate');
        var tax_code = $(this).data('tax_code');
        var exp_date = $(this).data('exp_date');
        var status = $(this).data('status');

        $('#id').val(id);
        $('#name').val(name);
        $("#tax_rate").val(tax_rate);
        $("#tax_code").val(tax_code);
        $("#exp_date").val(exp_date);
        $('#statusModal').val(status);
    });

    function status_change(id, status) {
        var token = '<?php echo csrf_token(); ?>'
        var model = "Construction_tax_rate";
        $.ajax({
            type: "POST",
            url: "{{url('/tax_rate_status_change')}}",
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
                var model = 'Construction_tax_rate';
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