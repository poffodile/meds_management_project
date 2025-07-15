@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Purchase order Invoice List')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<style>
    .currency {
        padding: 2px 3px 2px 5px;
        line-height: 17px;
        text-shadow: 0 1px 0 #ffffff;
        border: 1px solid #ccc;
        background-color: #efefef;
        margin-right: 5px;
    }

    .image_style {
        cursor: pointer;
    }

    #active_inactive {
        background-color: #57c8f1;
    }

    .tutor-student-tooltip-col {
        position: relative;
        color: #000;
        text-decoration: none;
        font-size: 12px;
    }

    .tutor-student-tooltip-col:hover .tutor-student-tooltiptext3 {
        visibility: visible;
    }

    .tutor-student-tooltiptext3 {
        visibility: hidden;
        width: 155px;
        background-color: #0877bd;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px 10px;
        box-sizing: border-box;
        position: absolute;
        z-index: 1;
        top: 25px;
        left: -30px;
        font-size: 12px;
        font-weight: 500;
        text-transform: capitalize;
    }

    .parent-container {
        position: absolute;
        background: #fff;
        width: 190px;
    }

    #deptList li:hover {
        cursor: pointer;
    }

    #tagList li:hover {
        cursor: pointer;
    }

    #supplierList li:hover {
        cursor: pointer;
    }

    #customerList li:hover {
        cursor: pointer;
    }

    #cretaedByList li:hover {
        cursor: pointer;
    }

    #projectList li:hover {
        cursor: pointer;
    }

    ul#deptList {
        padding: 0 5px;
        height: 156px;
        overflow: auto;
    }

    ul#tagList {
        padding: 0 5px;
        height: 156px;
        overflow: auto;
    }

    ul#supplierList {
        padding: 0 5px;
        height: 156px;
        overflow: auto;
    }

    ul#customerList {
        padding: 0 5px;
        height: 156px;
        overflow: auto;
    }

    ul#cretaedByList {
        padding: 0 5px;
        height: 156px;
        overflow: auto;
    }

    ul#projectList {
        padding: 0 5px;
        height: 156px;
        overflow: auto;
    }

    .multiselect-dropdown {
        height: auto;
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

<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        <h4>Invoice Received</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12 mt-4">
                            <div class="jobsection justify-content-end">
                                <a href="#!" class="btn btn-warning"> Search Purchase Orders</a>
                                <a href="#!" class="btn btn-default"> Invoice Received</a>
                                <a href="#!" class="btn btn-warning"> Statements</a>
                            </div>
                        </div>
                        <div class="col-sm-12 col-lg-12 col-xl-12">
                            <div class="jobsection">
                                <a href="{{url('purchase_order')}}" class="btn btn-warning">New Purchase Order</a>
                                <a href="{{ url('draft_purchase_order') }}" class="btn btn-warning">Draft <span>({{$draftCount}})</span></a>
                                <a href="{{ url('draft_purchase_order?list_mode=AwaitingApprivalPurchaseOrders') }}" class="btn btn-warning">Awaiting Approval <span>({{$awaitingApprovalCount}})</span></a>
                                <a href="{{ url('draft_purchase_order?list_mode=Approved') }}" class="btn btn-warning">Approved <span>({{$approvedCount}})</span></a>
                                <a href="{{ url('draft_purchase_order?list_mode=Rejected') }}" class="btn btn-warning">Rejected <span>({{$rejectedCount}})</span></a>
                                <a href="{{ url('draft_purchase_order?list_mode=Actioned') }}" class="btn btn-warning">Actioned <span>({{$actionedCount}})</span></a>
                                <a href="{{ url('draft_purchase_order?list_mode=Paid') }}" class="btn btn-warning">Paid <span>({{$paidCount}})</span></a>
                                <div class="searchFilter">
                                    <a href="#!" onclick="hideShowDiv()" class="hidebtn btn btn-primary">Show Search Filter</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="searchJobForm" id="divTohide" style="display:none">
                                <form id="search_dataForm" class="p-4">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">Supplier:</label>
                                                <div class="position-relative">
                                                    <input type="text" class="form-control editInput" id="supplier">
                                                    <input type="hidden" id="selectedsupplierId" name="selectedsupplierId">
                                                    <div class="parent-container supplier-container"></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">
                                                    <a href="#!" class="tutor-student-tooltip-col"> ID From:<span class="tutor-student-tooltiptext3">Invoice Date From</span>
                                                    </a>
                                                </label>
                                                <div class="row">
                                                    <div class="col-md-6 pe-0">
                                                        <input type="date" class="form-control editInput" id="id_startDate">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="date" class="form-control editInput" id="id_endDate">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">PO Ref:</label>
                                                <input type="text" class="form-control editInput" id="po_ref">
                                            </div>
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">Created From:</label>
                                                <div class="row">
                                                    <div class="col-md-6 pe-0">
                                                        <input type="date" class="form-control editInput" id="created_startDate">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="date" class="form-control editInput" id="created_endDate">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">Invoice Ref:</label>
                                                <input type="text" class="form-control editInput" id="invoice_ref">
                                            </div>
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">Paid:</label>
                                                <select class="form-control editInput selectOptions" id="paid_status">
                                                    <option selected disabled>--All--</option>
                                                    <option value="1">Paid</option>
                                                    <option value="2">Outstanding</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <div class="jobsection justify-content-center">
                                                <a href="javascript:void(0)" onclick="searchBtn()" class="btn btn-warning px-3">Search </a>
                                                <a href="javascript:void(0)" onclick="clearBtn()" class="btn btn-default px-3">Clear</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="maimTable">
                                <div class="table-responsive">
                                    <table id="myTable" class="display tablechange" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th>Due Date</th>
                                                <th>Supplier</th>
                                                <th>PO Ref</th>
                                                <!-- <th>Supplier</th> -->
                                                <th>Invoice Ref</th>
                                                <th>Amount</th>
                                                <th>Paid</th>
                                                <th>Outstanding </th>
                                                <th>Paid</th>
                                                <th>Created On</th>
                                            </tr>
                                        </thead>
                                        <tbody id="search_data">
                                            <?php
                                            $amount = 0;
                                            $paid = 0;
                                            $outstanding = 0;
                                            ?>
                                            @foreach($list as $val)
                                            <!-- record_type -->
                                            <?php
                                            $paid_record = App\Models\PurchaseOrderRecordPayment::where(['po_id' => $val->po_id, 'deleted_at' => null, 'record_type' => 2])->sum('record_amount_paid');
                                            // echo "<pre>";print_r($paid_record);
                                            $amount = $amount + $val->gross_amount;
                                            $paid = $paid + $paid_record;
                                            $outstanding = $outstanding + $val->oustanding_amount;
                                            ?>
                                            <tr>
                                                <td>{{$loop->iteration}}</td>
                                                <td>{{ date('d/m/Y', strtotime($val->created_at)) }}</td>
                                                <td>{{ date('m/d/Y', strtotime($val->due_date)) }}</td>
                                                <td>{{$val->suppliers->name}}</td>
                                                <td>{{$val->purchaseOrders->purchase_order_ref}}</td>
                                                <td>{{$val->inv_ref}}</td>
                                                <td>£{{$val->gross_amount}}</td>
                                                <td>£{{$paid}}.00</td>
                                                <td>£{{$val->oustanding_amount}}</td>
                                                <td>No</td>
                                                <td>{{ date('d/m/Y H:m', strtotime($val->created_at)) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tr class="calcualtionShowHide">
                                            <th colspan="2"> <label class="col-form-label p-0">Page Sub Total:</label></th>
                                            <th colspan="12"></th>
                                        </tr>
                                        <tr class="calcualtionShowHide">
                                            <td colspan="6"></td>
                                            <td id="Tablesub_total_amount">£{{$amount}}</td>
                                            <td id="Tablevat_amount">£{{$paid}}</td>
                                            <td id="Tableoutstanding_amount" colspan="8">£{{$outstanding}}</td>
                                        </tr>
                                    </table>
                                </div> <!-- End off main Table -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

<script>
    function clearBtn() {
        $("#search_dataForm")[0].reset();
    }

    function searchBtn() {
        var supplier = $("#supplier").val();
        var selectedsupplierId = $("#selectedsupplierId").val();
        var id_startDate = $("#id_startDate").val();
        var id_endDate = $("#id_endDate").val();
        var po_ref = $("#po_ref").val();
        var created_startDate = $("#created_startDate").val();
        var created_endDate = $("#created_endDate").val();
        var invoice_ref = $("#invoice_ref").val();
        var paid_status = $("#paid_status").val();
        let isEmpty = true;
        $("#search_dataForm").find("input, select").each(function() {
            if ($(this).val() && $(this).val() !== "") {
                isEmpty = false;
                return false;
            }
        });
        if (isEmpty) {
            alert("Please fill in at least one field before searching.");
            return false;
        }

        if (id_startDate != '' && id_endDate == '') {
            alert("Please choose both date");
            return false;
        }
        if (created_startDate == '' && created_endDate != '') {
            alert("Please choose both date");
            return false;
        }
        $.ajax({
            url: "{{ url('searchPurchaseOrdersInvoice') }}",
            method: 'post',
            data: {
                supplier: supplier,
                selectedsupplierId: selectedsupplierId,
                id_startDate: id_startDate,
                id_endDate: id_endDate,
                po_ref: po_ref,
                created_startDate: created_startDate,
                created_endDate: created_endDate,
                invoice_ref: invoice_ref,
                paid_status: paid_status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                console.log(response);
                return false;
                var table = $('#exampleOne').DataTable();
                table.destroy();
                if (response.data.length > 0) {
                    $("#search_data").html(response.data);
                    $("#Tablesub_total_amount").text("£" + response.all_subTotalAmount);
                    $("#Tablevat_amount").text("£" + response.all_vatTotalAmount);
                    $("#Tabletotal_amount").text("£" + response.all_TotalAmount);
                    $("#Tableoutstanding_amount").text("£" + response.outstandingAmountTotal);
                    $(".calcualtionShowHide").show();
                } else {
                    if (response.success === false) {
                        alert(response.message);
                        // return false;
                    }
                    $("#search_data").html(response.data);
                    $(".calcualtionShowHide").hide();
                }
                // $('#exampleOne').DataTable();
                $('#exampleOne').DataTable({
                    order: [
                        [1, 'asc']
                    ],
                    language: {
                        paginate: {
                            previous: "Previous",
                            next: "Next"
                        },
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "No entries available",
                        emptyTable: '<span style="">Sorry, there are no items available</span>',
                        infoFiltered: "(filtered from _MAX_ total entries)",
                        lengthMenu: "Show _MENU_ entries",
                        search: "Search:",
                        zeroRecords: "No matching records found"
                    },
                    paging: true
                });
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }
    $("#edd_endDate").change(function() {
        var startDate = document.getElementById("edd_startDate").value;
        var endDate = document.getElementById("edd_endDate").value;

        if ((Date.parse(startDate) >= Date.parse(endDate))) {
            alert("End date should be greater than Start date");
            document.getElementById("edd_endDate").value = "";
        }
    });
    $("#edd_startDate").change(function() {
        var startDate = document.getElementById("edd_startDate").value;
        var endDate = document.getElementById("edd_endDate").value;

        if ((Date.parse(endDate) <= Date.parse(startDate))) {
            alert("Start date should be less than End date");
            document.getElementById("edd_startDate").value = "";
        }
    });
    $("#po_endDate").change(function() {
        var startDate = document.getElementById("po_startDate").value;
        var endDate = document.getElementById("po_endDate").value;

        if ((Date.parse(startDate) >= Date.parse(endDate))) {
            alert("End date should be greater than Start date");
            document.getElementById("po_endDate").value = "";
        }
    });
    $("#po_startDate").change(function() {
        var startDate = document.getElementById("po_startDate").value;
        var endDate = document.getElementById("po_endDate").value;

        if ((Date.parse(endDate) <= Date.parse(startDate))) {
            alert("Start date should be less than End date");
            document.getElementById("po_startDate").value = "";
        }
    });
</script>

<script>
    $(document).ready(function() {
        $('#supplier').on('keyup', function() {
            let search_supplierquery = $(this).val();
            const supplierdivList = document.querySelector('.supplier-container');

            if (search_supplierquery === '') {
                supplierdivList.innerHTML = '';
            }
            if (search_supplierquery.length > 2) {
                $.ajax({
                    url: "{{ url('searchSupplier') }}",
                    method: 'post',
                    data: {
                        search_supplierquery: search_supplierquery,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log(response);
                        // return false;
                        supplierdivList.innerHTML = "";
                        const div = document.createElement('div');
                        div.className = 'supplier_container';

                        const ul = document.createElement('ul');
                        ul.id = "supplierList";
                        if (response.data.length > 0) {
                            response.data.forEach(item => {
                                const li = document.createElement('li');
                                li.textContent = item.name;
                                li.id = item.id;
                                li.name = item.name;
                                li.className = "editInput";
                                ul.appendChild(li);
                                const hr = document.createElement('hr');
                                // hr.className='dropdown-divider';
                                ul.appendChild(hr);
                            });

                            div.appendChild(ul);

                            supplierdivList.appendChild(div);

                            ul.addEventListener('click', function(event) {
                                supplierdivList.innerHTML = '';
                                document.getElementById('supplier').value = '';
                                if (event.target.tagName.toLowerCase() === 'li') {
                                    const selectedsupplierId = event.target.id;
                                    const selectedSupplierName = event.target.name;
                                    console.log('Selected Customer ID:', selectedsupplierId);
                                    console.log('Selected Customer Name:', selectedSupplierName);
                                    $("#supplier").val(selectedSupplierName);
                                    $("#selectedsupplierId").val(selectedsupplierId);
                                }
                            });
                        } else {
                            const Errorli = document.createElement('li');
                            Errorli.textContent = 'Sorry Data Not found';
                            Errorli.id = 'searchError';
                            Errorli.className = "editInput";
                            ul.appendChild(Errorli);
                            div.appendChild(ul);
                            supplierdivList.appendChild(div);
                            setTimeout(function() {
                                supplierdivList.innerHTML = '';
                            }, 1000);
                        }

                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                supplierdivList.innerHTML = '';
                $('#results').empty();
            }
        });
    });
</script>

@endsection