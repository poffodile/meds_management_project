@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Purchase Expenses')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')


<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-body">
                        <div class="col-md-4 col-lg-4 col-xl-4 ">
                            <div class="pageTitle">
                                <h3>Purchase Expenses</h3>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="maimTable">
                                <div class="printExpt">
                                    <div class="prntExpbtn">
                                        <a href="#!">Print</a>
                                        <a href="#!">Export</a>
                                    </div>
                                </div>
                                <div class="markendDelete delete_btn_end">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="jobsection">
                                                <a href="javascript:void(0)" onclick="openExpensesModal('', 'add')" class="profileDrop">New</a>
                                            </div>
                                        </div>
                                        <div class="col-md-6 deleteSelectedRows">
                                            <div class="jobsection">
                                                <a href="javascript:void(0)" id="deleteSelectedRows" class="profileDrop">Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="productDetailTable pt-3">
                                    <table class="table tablechange mb-0" id="exampleOne">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="col-1"><input type="checkbox" id="selectAllCheckBoxes"></th>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th>Expense </th>
                                                <th>Status </th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($purchase_expenses as $purchase_expense)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" id="" class="delete_checkbox" value="{{$purchase_expense->id}}">
                                                </td>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $purchase_expense->created_at }}</td>
                                                <td>{{ $purchase_expense->title }}</td>
                                                <td>
                                                    <?php if ($purchase_expense->status == 1) { ?>
                                                        <span class="grencheck" onclick="status_change({{$purchase_expense->id}},{{$purchase_expense->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                                    <?php } else { ?>
                                                        <span class="grayCheck" onclick="status_change({{$purchase_expense->id}},{{$purchase_expense->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <div class="dropdown action_dropdown">
                                                        <a href="#" class="dropdown-toggle profileDrop" data-toggle="dropdown" aria-expanded="false">
                                                            <div class="action_drop"><span>Action &nbsp;</span> <i class="fa fa-sort-desc" aria-hidden="true"></i></div>
                                                        </a>
                                                        <div class="dropdown-menu">
                                                            <a href="javascript:void(0)" onclick="openExpensesModal(this, 'edit')" class="dropdown-item assetCatemodal_dataFetch" data-id="{{ $purchase_expense->id }}" data-name="{{ $purchase_expense->title }}" data-status="{{ $purchase_expense->status }}">Edit Details</a>
                                                        </div>
                                                    </div>
                                                    <!-- <div class="d-inline-flex align-items-center ">
                                                        <div class="nav-item dropdown">
                                                            <a href="#" class="nav-link dropdown-toggle profileDrop" data-toggle="dropdown">Action</a>
                                                            <div class="dropdown-menu fade-up m-0">
                                                                <a href="javascript:void(0)" onclick="openExpensesModal(this, 'edit')" class="dropdown-item assetCatemodal_dataFetch" data-id="{{ $purchase_expense->id }}" data-name="{{ $purchase_expense->title }}" data-status="{{ $purchase_expense->status }}">Edit Details</a>
                                                            </div>
                                                        </div>
                                                    </div> -->
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- End off main Table -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Purchase Expenses Modal start here -->
<div class="modal fade" id="addPurchaseExpensesModel" tabindex="-1" aria-labelledby="purchaseExpesnsesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="purchaseExpesnsesModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times" aria-hidden="true"></i></button>
            </div>
            <div class="modal-body">
                <form id="purchaseExpeneseForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="formDtail">
                                <div class="col-md-12 col-lg-12 col-xl-12 text-center">
                                    <div class="mt-1 mb-0 text-center" id="messagedepreciation_types"></div>
                                    <input type="hidden" name="purchase_expense_id" id="purchase_expense_id">
                                    <div class="row">
                                        <div class="col-md-12 col-lg-12 col-xl-12">
                                            <div class="formDtail">
                                                <div class="form-group row">
                                                    <label for="inputName" class="col-sm-2 col-form-label text-left">Title <span class="radStar ">*</span></label>
                                                    <div class="col-md-9">
                                                        <input type="text" class="form-control editInput" name="title" id="purchase_expense_title">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="inputProject" class="col-sm-2 col-form-label text-left">Status</label>
                                                    <div class="col-md-9">
                                                        <select class="form-control editInput selectOptions" id="purchase_expense_status" name="status">
                                                            <option value="1">Active</option>
                                                            <option value="0">Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- End row -->
                    </div>
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="profileDrop" id="savePurchaseExpesnsesModal">Save</button>
                <button type="button" class="profileDrop gray" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- end here -->

@endsection
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
                var model = 'PurchaseExpenses';
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

    function openExpensesModal(element, type) {
        console.log(type);
        $("#addPurchaseExpensesModel").modal('show');
        // document.getElementById('purchaseExpesnsesModalLabel').textContent = type === "add" ? "Add Purchase Order" : "Edit Purchase Order";

        if (type === "add") {
            // Clear form fields for a new entry
            document.getElementById("purchase_expense_id").value = "";
            document.getElementById("purchase_expense_title").value = "";
            document.getElementById("purchase_expense_status").value = "";

            // Set modal title for "Add"
            document.getElementById("purchaseExpesnsesModalLabel").textContent = "Add Purchase Expenses";

        } else if (type === "edit") {
            document.getElementById("purchaseExpesnsesModalLabel").textContent = "Edit Purchase Expenses";

            let id = element.dataset.id;
            let name = element.dataset.name;
            let status = element.dataset.status;

            // Set form values
            document.getElementById("purchase_expense_id").value = id;
            document.getElementById("purchase_expense_title").value = name;
            document.getElementById("purchase_expense_status").value = status;

        }

    }

    $(document).ready(function() {
        $("#savePurchaseExpesnsesModal").on("click", function(e) {
            e.preventDefault(); // Prevent page reload

            $.ajax({
                url: "{{ url('purchase/save-purchase-expenses') }}", // Laravel route
                type: "POST",
                data: $('#purchaseExpeneseForm').serialize(), // Serialize form data
                success: function(response) {
                    alert(response.message);
                    window.location.reload();
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessage = "<p style='color:red;'>";
                    $.each(errors, function(key, value) {
                        errorMessage += value[0] + "<br>";
                    });
                    errorMessage += "</p>";
                    alert(errorMessage);
                }
            });
        });
    });
</script>

<script type="text/javascript" src="{{ url('public/js/salesFinance/dayBook/purchaseDayBook.js') }}"></script>