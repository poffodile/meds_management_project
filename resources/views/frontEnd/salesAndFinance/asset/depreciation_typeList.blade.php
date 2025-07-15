@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Depreciation Type')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<!--main content start-->
<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        <h4>Depreciation Type</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12">
                            <div class="jobsection justify-content-end">
                                    <a href="javascript:void(0)" onclick="opendepreciation_typesModal()" class="btn btn-warning"><i class="fa fa-plus"></i> Add</a>
                                    <!-- <a href="javascript:void(0)" class="btn btn-warning">Export</a> -->
                                    <a href="javascript:void(0)" id="deleteSelectedRows" class="btn btn-danger">Delete</a>
                            </div>
                            <div class="maimtable productDetailTable mb-4 table-responsive">
                                <div class="alert success-message text-center" id="msg" style="display:none;height:50px">
                                    <p id="status_meesage"></p>
                                </div>
                                <table id="Depreciation_typeTable" class="table border-top border-bottom tablechange" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="col-1"><input type="checkbox" id="selectAllCheckBoxes"></th>
                                            <th class="col-1">#</th>
                                            <th class="col-2">Name</th>
                                            <th class="col-2">Percentage(%)</th>
                                            <th class="col-2">Status</th>
                                            <th class="col-2">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="search_data">
                                        <?php foreach ($list as $key => $val) { ?>
                                            <tr>
                                                <td>
                                                    <div><input type="checkbox" id="" class="delete_checkbox" value="{{$val->id}}"></div>
                                                </td>
                                                <td>{{++$key}}</td>
                                                <td>{{$val->name}}</td>
                                                <td>{{$val->percentage}}</td>
                                                <td>
                                                    <?php if ($val->status == 1) { ?>
                                                        <span class="grencheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa fa-check-circle"></i></span>
                                                    <?php } else { ?>
                                                        <span class="grayCheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa fa-check-circle"></i></span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#depreciation_typesModal" class="dropdown-item depreciation_typeModal_dataFetch" data-id="{{ $val->id }}" data-name="{{ $val->name }}" data-percentage="{{ $val->percentage }}" data-status="{{ $val->status }}"><i class="fa fa-pencil" aria-hidden="true"></i></a>
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

<!-- Record Payment Modal start here -->
<div class="modal fade" id="depreciation_typesModal" tabindex="-1" aria-labelledby="depreciation_typesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="depreciation_typesModalLabel"></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="formDtail">
                            <div class="col-md-12 col-lg-12 col-xl-12 text-center">
                                <div class="mt-1 mb-0 text-center" id="messagedepreciation_types"></div>
                            </div>
                            <form id="depreciation_typesForm">
                                <input type="hidden" name="id" id="id">
                                @csrf
                                <div>
                                    <div class="form-group">
                                        <label>Name <span class="radStar ">*</span></label>
                                        <input type="text" class="form-control editInput" id="name" name="name" value="">
                                    </div>
                                    <div class="form-group percentage_mark">
                                        <label>Percentage <span class="radStar ">*</span></label>
                                        <div class="row">
                                            <div class="col-sm-1 pe-0">
                                                <div class="icon">
                                                    <span>%</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-11">
                                                <input type="text" class="form-control editInput numberInput" id="percentage" name="percentage" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select class="form-control editInput selectOptions"
                                            id="statusdepreciation_typesModal" name="status">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> <!-- End row -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="savedepreciation_typesModal" onclick="savedepreciation_typesModal()">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- end here -->

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

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
                var model = 'DepreciationType';
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
            $('#selectAllCheckBoxes').prop('checked', true);
        } else {
            $('#selectAllCheckBoxes').prop('checked', false);
        }
    });
    $('#selectAllCheckBoxes').on('click', function() {
        $('.delete_checkbox').prop('checked', $(this).prop('checked'));
    });

    function status_change(id, status) {
        var token = '<?php echo csrf_token(); ?>'
        var model = "DepreciationType";
        $.ajax({
            type: "POST",
            url: "{{url('/sales-finance/assets/asset-depreciation/status_change')}}",
            data: {
                id: id,
                status: status,
                model: model,
                _token: token
            },
            success: function(data) {
                console.log(data);
                if (isAuthenticated(data) == false) {
                    return false;
                }
                if ($.trim(data) == 1) {
                    $("#status_meesage").text("Status Changed Successfully Done");
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
    var assetDepreciationTypeSaveUrl = "{{url('sales-finance/assets/depreciation-type-save')}}";
    var assetDepreciationTypeEditUrl = "{{url('sales-finance/assets/depreciation-type-edit')}}";
</script>
<script src="{{ url('public/js/salesFinance/asset/asset_category.js')}}" defer></script>
@endsection