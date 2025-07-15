@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Purchase Expenses Type')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')


<!--main content start-->
<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        <h4>Purchase Expenses Type</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12">
                            <div class="jobsection justify-content-end">
                                <a href="javascript:void(0)" onclick="openExpensesModal('', 'add')" class="btn btn-warning" data-action="add"><i class="fa fa-plus"></i> Add</a>
                                <!-- <a href="javascript:void(0)" id="deleteSelectedRows" class="profileDrop">Delete</a> -->
                            </div>
                            <div class="maimTable productDetailTable mb-4 table-responsive">
                                <table class="table border-top border-bottom tablechange display" id="myTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Expense Type </th>
                                            <th>Status </th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchase_expenses as $purchase_expense)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $purchase_expense->created_at->format('d-m-Y') }}</td>
                                            <td>{{ $purchase_expense->title }}</td>
                                            <td> {{ $purchase_expense->status == 1 ? 'Active' : 'Inactive' }}</td>
                                            <td>
                                                <a href="javascript:void(0)" onclick="openExpensesModal(this, 'edit')" class="dropdown-item assetCatemodal_dataFetch" data-id="{{ $purchase_expense->id }}" data-name="{{ $purchase_expense->title }}" data-status="{{ $purchase_expense->status }}"> <i class="fa fa-pencil" aria-hidden="true"></i></a> | <a href="#!" class="deleteBtn" data-id="{{ $purchase_expense->id }}"><i class="fa fa-trash radStar" aria-hidden="true"></i></a>
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
</section>

<!-- Purchase Expenses Modal start here -->
<div class="modal fade" id="addPurchaseExpensesModel" tabindex="-1" aria-labelledby="purchaseExpesnsesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="purchaseExpesnsesModalLabel"></h4>
            </div>
            <form id="purchaseExpeneseForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="formDtail">
                                <div class="mt-1 mb-0 text-center" id="messagedepreciation_types"></div>
                                <input type="hidden" name="purchase_expense_id" id="purchase_expense_id">
                                <div class="row">
                                    <div class="form-group col-md-12 col-lg-12 col-xl-12">
                                        <label>Title <span class="radStar ">*</span></label>
                                        <input type="text" class="form-control editInput" name="title" id="purchase_expense_title">
                                    </div>
                                    <div class="form-group col-md-12 col-lg-12 col-xl-12">
                                        <label>Status</label>
                                        <select class="form-control editInput selectOptions" id="purchase_expense_status" name="status">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- End row -->
                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="savePurchaseExpesnsesModal">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- end here -->
<script>
  


    $(".deleteBtn").on("click", function () {
            // alert();
        let purchaseExpense = $(this).data("id"); // Get ID from button
        let row = $("#row-" + purchaseExpense); // Select the row

        if (confirm("Are you sure you want to delete this record?")) {
            $.ajax({
                url: "{{ url('purchase/purchase-expenses/delete') }}" + "/"+ purchaseExpense,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success: function (response) {
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                    if (response.success) {
                        // row.find("td:nth-child(7)").text(response.deleted_at); // Update deleted_at column
                        alert(response.message);
                        window.location.reload();
                    }
                },
                error: function () {
                    alert("Something went wrong!");
                },
            });
        }
    });


</script>

<script>
    // Function to open the modal for adding or editing purchase expenses
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
            document.getElementById("purchaseExpesnsesModalLabel").textContent = "Add Purchase Expenses Type";

        } else if (type === "edit") {
            document.getElementById("purchaseExpesnsesModalLabel").textContent = "Edit Purchase Expenses Type";

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
            var id=$("#purchase_expense_id").val();
            var saveUrl="{{ url('purchase/save-purchase-expenses') }}";
            if(id !=''){
                saveUrl="{{ url('purchase/edit-purchase-expenses') }}";
            }
            $.ajax({
                url: saveUrl, // Laravel route
                type: "POST",
                data: $('#purchaseExpeneseForm').serialize(), // Serialize form data
                success: function(response) {
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                    alert(response.message);
                    window.location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // Validation error
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = '';

                        // Clear old errors first
                        $('.text-danger').remove();

                        $.each(errors, function(key, value) {
                            // Display message under each input field
                            let inputField = $(`[name="${key}"]`);
                            if (inputField.length) {
                                inputField.after(`<span class="text-danger">${value[0]}</span>`);
                            }

                            // Collect all messages for optional alert box
                            errorMessages += value[0] + "\n";
                        });

                        // Optional: show all errors in a single alert box
                        // alert("Please fix the following errors:\n" + errorMessages);
                    } else {
                        alert("Something went wrong. Please try again.");
                    }
                }
            });
        });
    });
</script>
@endsection