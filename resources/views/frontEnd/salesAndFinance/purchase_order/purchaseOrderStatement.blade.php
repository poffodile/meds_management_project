@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Purchase order Statements')
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
        background-color: #474747;
    }

    .parent-container {
        position: absolute;
        background: #fff;
        width: 190px;
    }

    #supplierList li:hover {
        cursor: pointer;
    }

    ul#supplierList {
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
                        <h4 id="bladeheading">Purchase Order Statements</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12 mt-4">
                            <div class="jobsection justify-content-end">
                                <a href="#!" class="btn btn-warning"> Search Purchase Orders</a>
                                <a href="#!" class="btn btn-warning"> Invoice Received</a>
                                <a href="#!" class="btn btn-default dropdown-toggle"> Statements</a>
                            </div>
                        </div>
                        <div class="col-sm-12 col-lg-12 col-xl-12">
                            <div class="jobsection">
                                <div class="dropdown">
                                    <a href="#" class="nav-link btn btn-warning" data-toggle="dropdown" aria-expanded="false"> New 
                                        <i class="fa fa-caret-down"></i></a>
                                    <div class="dropdown-menu fade-up m-0">
                                        <a href="{{url('purchase_order')}}" class="dropdown-item">Purchase Order</a>
                                        <a href="{{url('new_credit_notes')}}" class="dropdown-item">Credit Note</a>
                                        <!-- <a href="#!" class="dropdown-item">Print</a>
                                        <a href="#!" class="dropdown-item">Email</a> -->
                                    </div>
                                </div>
                                <a href="{{ url('draft_purchase_order') }}" class="btn btn-warning">Draft <span>({{$draftCount}})</span></a>
                                <a href="{{ url('draft_purchase_order?list_mode=AwaitingApprivalPurchaseOrders') }}" class="btn btn-warning">Awaiting Approval <span>({{$awaitingApprovalCount}})</span></a>
                                <a href="{{ url('draft_purchase_order?list_mode=Approved') }}" class="btn btn-warning">Approved <span>({{$approvedCount}})</span></a>
                                <a href="{{ url('draft_purchase_order?list_mode=Rejected') }}" class="btn btn-warning">Rejected <span>({{$rejectedCount}})</span></a>
                                <a href="{{ url('draft_purchase_order?list_mode=Actioned') }}" class="btn btn-warning">Actioned <span>({{$actionedCount}})</span></a>
                                <a href="{{ url('draft_purchase_order?list_mode=Paid') }}" class="btn btn-warning">Paid <span>({{$paidCount}})</span></a>
                                <div class="searchFilter">
                                    <a href="#!" onclick="hideShowDiv()" class="hidebtn btn btn-primary">Hide Search Filter</a>
                                </div>
                                <a href="javascript:void(0)" id="deleteSelectedRows" class="btn btn-danger">Delete</a>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="searchJobForm" id="divTohide">
                                <form id="search_dataForm" class="p-4">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="mb-3">
                                                <label class="col-form-label mb-2">Supplier:</label>
                                                <div class="position-relative">
                                                    <input type="text" class="form-control editInput" placeholder="Supplier" id="supplier">
                                                    <input type="hidden" id="selectedsupplierId" name="selectedsupplierId">
                                                    <div class="parent-container supplier-container"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="mb-3">
                                                <label class="col-form-label mb-2">PO Date From:</label>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <input type="date" class="form-control editInput current_date" placeholder="PO Date Start From" id="po_startDate">
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <input type="date" class="form-control editInput current_date" placeholder="PO Date End From" id="po_endDate">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="jobsection justify-content-center">
                                                <a href="javascript:void(0)" onclick="searchBtn(1)" class="btn btn-warning px-3">Full Statment </a>
                                                <a href="javascript:void(0)" onclick="searchBtn(2)" class="btn btn-warning px-3">Outstanding Statement</a>
                                                <a href="javascript:void(0)" onclick="clearBtn()" class="btn btn-default px-3">Clear</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="table-responsive productDetailTable">
                                <table id="exampleOne" class="table border-top border-bottom tablechange" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Reference</th>
                                            <th>Supplier Ref.</th>
                                            <th>Delivery Address</th>
                                            <th>Net Amount</th>
                                            <th>Vat</th>
                                            <th>Total</th>
                                            <th>Paid Amount</th>
                                            <th>Gross</th>
                                        </tr>
                                    </thead>
                                    <tbody id="search_data">

                                    </tbody>
                                    <tr class="calcualtionShowHide" style="display:none">
                                        <th colspan="2"> <label class="col-form-label p-0">Page Sub Total:</label></th>
                                        <th colspan="12"></th>
                                    </tr>
                                    <tr class="calcualtionShowHide" style="display:none">
                                        <td colspan="4"></td>
                                        <td id="Tablesub_total_amount">£0</td>
                                        <td id="Tablevat_amount">£0</td>
                                        <td id="Tabletotal_amount">£0</td>
                                        <td id="Tableoutstanding_amount">£0</td>
                                        <td id="gross_amount">£0</td>
                                    </tr>
                                </table>
                            </div> 
                            <!-- End off main Table -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="{{url('public/frontEnd/js/multiselect.js')}}"></script>

<script>
    var selectedValues = [];
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("purchaseSearchstatus").addEventListener("change", function() {
            selectedValues = Array.from(this.selectedOptions).map(option => option.value);
            console.log(selectedValues); // This will log the selected values as an array
        });
    });

    function clearBtn() {
        selectedValues = '';
        $("#search_dataForm")[0].reset();
    }

    function searchBtn(type) {
        var supplier = $("#supplier").val();
        var po_startDate = $("#po_startDate").val();
        var po_endDate = $("#po_endDate").val();
        var selectedsupplierId = $("#selectedsupplierId").val();

        if (supplier == '') {
            alert("Please Select the supplier.");
            return false;
        }

        if (po_startDate != '' && po_endDate == '') {
            alert("Please choose both date");
            return false;
        }
        if (po_startDate == '' && po_endDate != '') {
            alert("Please choose both date");
            return false;
        }
        var url = "";
        if (type == 1) {
            url = "{{ url('searchPurchaseOrdersStatements') }}";
        } else {
            url = "{{ url('searchPurchaseOrdersStatementsOutstanding') }}"
        }
        $.ajax({
            url: url,
            method: 'post',
            data: {
                type: type,
                supplier: supplier,
                selectedsupplierId: selectedsupplierId,
                po_startDate: po_startDate,
                po_endDate: po_endDate,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                console.log(response);
                // return false;
                if (type == 1) {
                    $("#bladeheading").text('Purchase Order Statements - Full');
                } else {
                    $("#bladeheading").text('Purchase Order Statements - Outstanding');
                }
                var table = $('#exampleOne').DataTable();
                table.destroy();
                if (response.data.length > 0) {
                    $("#search_data").html(response.data);
                    $("#Tablesub_total_amount").text("£" + response.grandNetAmount);
                    $("#Tablevat_amount").text("£" + response.all_vatTotalAmount);
                    $("#Tabletotal_amount").text("£" + response.all_TotalAmount);
                    $("#Tableoutstanding_amount").text("£" + response.outstandingAmountTotal);
                    $("#gross_amount").text("£" + response.grandGrossAmount);
                    $(".calcualtionShowHide").show();
                } else {
                    $("#search_data").html(response.data);
                    $(".calcualtionShowHide").hide();
                }
                // $('#exampleOne').DataTable();
                $('#exampleOne').DataTable({
                    order: [
                        // [1, 'asc']
                    ],
                    language: {
                        paginate: {
                            previous: "Previous",
                            next: "Next"
                        },
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "No entries available",
                        emptyTable: '<span style="color: #dc3545; font-weight: bold;">Sorry, there are no items available</span>',
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
                                // const hr = document.createElement('hr');
                                // hr.className='dropdown-divider';
                                // ul.appendChild(hr);
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
<script>
    flatpickr(".current_date", {
        dateFormat: "d/m/Y", // Specify the format as dd/mm/yyyy
        defaultDate: new Date()
    });
</script>