@extends('backEnd.layouts.master')
@section('title',' Asset Categories')
@section('content')

<!--main content start-->
<section id="main-content">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">
                    <header class="panel-heading">Asset Categories</header>
                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            <div class="clearfix clearfix_space">
                                <div class="btn-group">
                                    <a href="javascript:void(0)" id="asset_category" class="btn btn-primary"  onclick="openAssetCategoryModal()"> Add New <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="space15"></div>
                            <table class="display table table-bordered table-striped" id="assetCatTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list as $key => $val) { ?>
                                        <tr>
                                            <td>{{++$key}}</td>
                                            <td>{{$val->name}}</td>
                                            <td>
                                                @if($val->status == 1)
                                                    <a href="javascript:" onclick="status_change('{{base64_encode($val->id)}}',0)" class="btn btn-success">Active</a>
                                                @else
                                                    <a href="javascript:" class="btn btn-danger" onclick="status_change('{{base64_encode($val->id)}}',1)">Inactive</a>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="javascript:void(0)" data-toggle="modal" data-target="#assetCategoryModal" class="dropdown-item assetCatemodal_dataFetch" data-id="{{ $val->id }}" data-name="{{ $val->name }}" data-status="{{ $val->status }}" class="openModalBtn"><i class="fa fa-pencil" aria-hidden="true"></i></a> | <a href="javascript:void(0)" class="dropdown-item delete_assetCat" data-id="{{ $val->id }}"><i class="fa fa-trash-o text-danger" aria-hidden="true"></i></a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- page end-->
    </div>
<!-- Modal start here -->
 <div class="modal fade popupcloseBtn in" id="assetCategoryModal" tabindex="-1" role="dialog" aria-labelledby="assetCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header terques-bg">
                <h5 class="modal-title pupTitle" id="assetCategoryModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body pdbotm">
                <form id="assetCategoryForm" class="customerForm">
                    <input type="hidden" id="id" name="id" value="">
                    
                    @csrf
                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 popup_error popup_alrt_msg" style="display: none;">
                                <div class="popup_notification-box">
                                    <div class="alert alert-danger alert-dismissible m-0" role="alert">
                                        <button type="button" class="close close-msg-btn"><span aria-hidden="true">&times;</span></button>
                                        <strong>Success!</strong> <span class="popup_error_txt">Some error occured, Please try again after sometime.</span>.
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 popup_success popup_alrt_msg" style="display: none;">
                                <div class="popup_notification-box">
                                    <div class="alert alert-success alert m-0" role="alert">
                                        <button type="button" class="close close-msg-btn"><span aria-hidden="true">×</span></button>
                                        <strong>Success!</strong> <span class="popup_success_txt">Data is made editable</span>.
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-4 control-label">Name <span class="radStar">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control checkInput" id="name" name="name" value="">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-4 control-label">Status</label>
                                <div class="col-sm-8">
                                    <select class="form-control editInput selectOptions" id="statusAssetModal" name="status">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Close</button>
                <button type="button" id="save_data" class="btn btn-primary" onclick="saveassetCategoryModal()">Add</button>
            </div>
        </div>
    </div>
</div>
<!-- End here -->
</section>

<script>
    var assetCatSaveUrl = "{{url('admin/sales-finance/assets/asset-category-save')}}";
    var assetCatEditUrl = "{{url('admin/sales-finance/assets/asset-category-edit')}}";
    var assetCatDeleteUrl = "{{url('admin/sales-finance/assets/asset-category-delete')}}";
    
    function status_change(id, status) {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('admin/sales-finance/assets/asset-category-status-change')}}",
            data: {
                id: id,
                status: status,
                _token: token
            },
            success: function (response) {
                console.log(response);
                if (response.success === true) {
                    location.reload();
                } else {
                    alert("Something went wrong");
                    return false;
                }
            },
            error: function (xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + xhr.message);
            }
        });
    }
</script>
<script src="{{ url('public/js/salesFinance/asset/asset_category.js')}}" defer></script>
@endsection