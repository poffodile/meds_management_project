@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Invoices')
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

    /* .parent-container {
        position: absolute;
        background: #fff;
        width: 190px;
    } */

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
                        <h4>{{ucfirst($key_mode)}} Invoices</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12 mt-4">
                            <div class="jobsection justify-content-end">
                                <a href="{{url('purchase-orders-search')}}" class="btn btn-warning"> Search Invoices</a>
                                <a href="{{url('purchase-order-statements')}}" class="btn btn-warning">Account Statements</a>
                            </div>
                        </div>
                        <div class="col-lg-12 mb-4">
                            <div class="jobsection">
                                <div class="nav-item dropdown">
                                    <a href="#" class="nav-link btn btn-warning" data-toggle="dropdown" aria-expanded="false"> New <i class="fa fa-caret-down"></i></a>
                                    <div class="dropdown-menu fade-up m-0">
                                        <a href="{{url('invoices/add')}}" class="dropdown-item">Invoice</a>
                                        <a href="{{url('new_credit_notes')}}" class="dropdown-item">Credit Note</a>
                                        <!-- <a href="#!" class="dropdown-item">Print</a>
                                        <a href="#!" class="dropdown-item">Email</a> -->
                                    </div>
                                </div>
                                <a href="{{ url('invoices/invoice/Draft') }}" class="btn btn-warning"
                                    <?php if ($key_mode === 'Draft') {
                                        echo 'id="active_inactive"';
                                    } ?>>Draft <span>({{$draft_invoice}})</span></a>
                                <a href="{{ url('invoices/invoice/Outstanding') }}" class="btn btn-warning"
                                    <?php if ($key_mode === 'Outstanding') {
                                        echo 'id="active_inactive"';
                                    } ?>>Outstanding <span>({{$outstanding_invoice}})</span></a>
                                <a href="{{ url('invoices/invoice/Overdue') }}" class="btn btn-warning"
                                    <?php if ($key_mode === 'Overdue') {
                                        echo 'id="active_inactive"';
                                    } ?>>Overdue <span>({{$overdue_invoice}})</span></a>
                                <a href="{{ url('invoices/invoice/Paid') }}" class="btn btn-warning"
                                    <?php if ($key_mode === 'Paid') {
                                        echo 'id="active_inactive"';
                                    } ?>>Paid <span>({{$paid_invoice}})</span></a>
                                <div class="searchFilter">
                                    <a href="#!" onclick="hideShowDiv()" class="hidebtn btn btn-default2">Show Search Filter</a>
                                </div>
                                <div class="searchJobForm" id="divTohide" style="display:none">
                                    <form id="search_dataForm" class="p-4">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Invoice Ref:</label>
                                                    <input type="text" class="form-control editInput" id="po_ref">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Department:</label>
                                                    <div class="position-relative">
                                                        <input type="text" class="form-control editInput" id="department">
                                                        <input type="hidden" id="selectedDeptId" name="selectedDeptId">
                                                        <div class="parent-container department-container"></div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Tag:</label>
                                                    <div class="position-relative">
                                                        <input type="text" class="form-control editInput" id="tag">
                                                        <input type="hidden" id="selectedTagtId" name="selectedTagtId">
                                                        <div class="parent-container tag-container"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Supplier:</label>
                                                    <div class="position-relative">
                                                        <input type="text" class="form-control editInput" id="supplier">
                                                        <input type="hidden" id="selectedsupplierId" name="selectedsupplierId">
                                                        <div class="parent-container supplier-container"></div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">PO Date From:</label>
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <input type="date" class="form-control editInput" id="po_startDate">
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <input type="date" class="form-control editInput" id="po_endDate">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2"><a href="#!" class="tutor-student-tooltip-col">EDD From:
                                                            <span class="tutor-student-tooltiptext3">Expedcted Delivery Date</span></a>
                                                    </label>
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <input type="date" class="form-control editInput" id="edd_startDate">
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <input type="date" class="form-control editInput" id="edd_endDate">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Customer:</label>
                                                    <div class="position-relative">
                                                        <input type="text" class="form-control editInput" id="customer">
                                                        <input type="hidden" id="selectedCustomerId" name="selectedCustomerId">
                                                        <div class="parent-container customer-container"></div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Created By:</label>
                                                    <div class="position-relative">
                                                        <input type="text" class="form-control editInput" id="created_by">
                                                        <input type="hidden" id="selectedcreatedById" name="selectedcreatedById">
                                                        <div class="parent-container createdBy-container"></div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">PO Posted:</label>
                                                    <select class="form-control editInput selectOptions" id="po_posted">
                                                        <option selected disabled>--Any--</option>
                                                        <option value="1">Yes</option>
                                                        <option value="0">No</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Project:</label>
                                                    <div class="position-relative">
                                                        <input type="text" class="form-control editInput" id="project">
                                                        <input type="hidden" id="selectedProjectId" name="selectedProjectId">
                                                        <div class="parent-container project-container"></div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Keywords:</label>
                                                    <div class="position-relative">
                                                        <input type="text" class="form-control editInput" id="keywords">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Delivery Status:</label>
                                                    <input type="text" class="form-control editInput" id="delivery_status">
                                                </div>
                                            </div>
                                            <div class="col-sm-12 mt-4">
                                                <div class="jobsection justify-content-center">
                                                    <a href="javascript:void(0)" onclick="searchBtn()" class="btn btn-warning px-3">Search </a>
                                                    <a href="javascript:void(0)" onclick="clearBtn()" class="btn btn-warning px-3">Clear</a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="jobsection">
                                <a href="javascript:void(0)" id="deleteSelectedRows" class="btn btn-danger">Delete</a>
                                <a href="javascript:void(0)" id="preview_purchase_orderBoxes" class="btn btn-warning">Preview Purchase Order</a>
                                <a href="javascript:void(0)" id="preview_purchase_orderBoxes" class="btn btn-warning">Preview Invoice</a>
                                <a href="javascript:void(0)" id="bulkInvoiceReceived" class="btn btn-warning">Change To Invoice</a>
                                <div class=" d-inline-flex align-items-center">
                                    <div class="dropdown">
                                        <a href="#!" class="btn btn-warning" data-toggle="dropdown" aria-expanded="false">Email Purchase Order
                                            <i class="fa fa-caret-down"></i>
                                        </a>
                                        <div class="dropdown-menu fade-up m-0">
                                            <a href="javascript:void(0)" class="dropdown-item email_sendCheck" onclick="email_sendCheck(1)">Send As Single Email</a>
                                            <hr class="dropdown-divider emialSend" style="display:none">
                                            <a href="javascript:void(0)" class="dropdown-item emialSend email_sendCheck" onclick="email_sendCheck(2)" style="display:none">Send As Multiple Emails</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="col-sm-5">
                                    <div class="pageTitleBtn p-0">
                                        <a href="#" class="btn btn-warning"> <i class="material-symbols-outlined"> settings </i></a>        
                                    </div>
                                </div> -->
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="maimtable table-responsive productDetailTable">
                                <table id="exampleOne" class="table border-top border-bottom tablechange" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style=" width:30px;"><input type="checkbox" id="selectAllCheckBoxes"></th>
                                            <th>#</th>
                                            <th>Invoice Ref</th>
                                            <th>Invoice Date</th>
                                            <th>Customer</th>
                                            <th>Customer Ref</th>
                                            <th>Site/Delivery</th>
                                            <th>Sub Total</th>
                                            <th>VAT</th>
                                            <th>Total </th>
                                            <th>Outstanding </th>
                                            <th>Status</th>
                                            <th>Printed</th>
                                            <th>Emailed</th>
                                            <th></th>
                                        </tr>
                                    </thead>

                                    <tbody id="search_data">
                                        <?php
                                        $all_subTotalAmount = 0;
                                        $all_vatTotalAmount = 0;
                                        $all_TotalAmount = 0;
                                        $outstandingAmountTotal = 0;
                                        foreach ($invoice as $key => $val) {
                                            $sub_total = 0;
                                            $vat = 0;
                                            $total = 0;
                                            foreach ($val->invoiceProducts as $product) {
                                                $price = $product->price * $product->qty;
                                                $sub_total = $sub_total + $price;
                                                $percentage = $product->price * $product->vat / 100;
                                                $vat = $vat + $percentage;
                                                $total = $total + $sub_total + $vat;
                                            }
                                            $all_subTotalAmount = $all_subTotalAmount + $sub_total;
                                            $all_vatTotalAmount = $all_vatTotalAmount + $vat;
                                            $all_TotalAmount = $all_TotalAmount + $total;
                                            $outstandingAmountTotal = $outstandingAmountTotal + $val->outstanding;
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="text-center"><input type="checkbox" id="" class="delete_checkbox" value="{{$val->id}}"></div>
                                                </td>
                                                <td>{{++$key}}</td>
                                                <td>{{$val->invoice_ref}}</td>
                                                <td>{{$val->invoice_date}}</td>
                                                <td>{{$val->customers->name}}</td>
                                                <td></td>
                                                <td>
                                                    @if(isset($val->sites) && !empty($val->sites))
                                                    {{$val->sites->site_name}}<br>
                                                    {{$val->sites->address}}
                                                    @else
                                                    {{$val->customers->name}}<br>
                                                    {{$val->customers->address}}
                                                    @endif
                                                </td>
                                                <td>£{{$sub_total}}.00</td>
                                                <td>£{{$vat}}.00</td>
                                                <td>£{{$total}}.00</td>
                                                <td>£{{$val->outstanding}}</td>
                                                <td>{{ucfirst($key_mode)}}</td>
                                                <td>
                                                    @if($val->is_printed == 1)
                                                    <span class="grencheck"><i class="fa fa-check-circle"></i></span>
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($val->is_emailed == 1)
                                                    <span class="grencheck"><i class="fa fa-check-circle"></i></span>
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex justify-content-end">
                                                        <div class="nav-item dropdown">
                                                            <a href="#!" class="nav-link dropdown-toggle btn btn-primary btn-sm" data-toggle="dropdown" aria-expanded="false">
                                                                Action <i class="fa fa-caret-down"></i>
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-right fade-up m-0" style="z-index:9999">
                                                                <a href="javascript:void(0)" class="dropdown-item">Send SMS</a>
                                                                <a href="{{url('invoices/edit?key=')}}{{base64_encode($val->id)}}" class="dropdown-item">Edit</a>
                                                                <a href="{{url('invoices/preview?key=')}}{{base64_encode($val->id)}}&url=preview" target="_blank" class="dropdown-item">Preview</a>
                                                                <a href="{{url('invoices/print?key=')}}{{base64_encode($val->id)}}&url=print" target="_blank" class="dropdown-item">Print</a>
                                                                <a href="javascript:void(0)" class="dropdown-item">Email</a>
                                                                <a href="#!" target="_blank" class="dropdown-item">Duplicate</a>
                                                                <a href="javascript:void(0)" class="dropdown-item">Change To Invoice</a>
                                                                <a href="javascript:void(0)" class="dropdown-item">Cancel Invoice</a>
                                                                <a href="#!" class="dropdown-item">CRM / History</a>
                                                                <a href="#!" class="dropdown-item">Start Timer</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>

                                    <tr class="calcualtionShowHide">
                                        <th colspan="2"> <label class="col-form-label p-0">Page Sub Total:</label></th>
                                        <th colspan="13"></th>
                                    </tr>
                                    <tr class="calcualtionShowHide">
                                        <td colspan="7"></td>
                                        <td id="Tablesub_total_amount">£{{$all_subTotalAmount}}.00</td>
                                        <td id="Tablevat_amount">£{{$all_vatTotalAmount}}.00</td>
                                        <td id="Tabletotal_amount">£{{$all_TotalAmount}}.00</td>
                                        <td id="Tableoutstanding_amount" colspan="8">£{{$outstandingAmountTotal}}.00</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> <!-- End off main Table -->
            </div>
        </div>
    </div>
</section>


<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

<script>
    // search leads show search Filter
    function hideShowDiv() {
        let div = document.getElementById("divTohide");

        if (div.style.display === 'none' || div.style.opacity === '0') {
            div.style.display = 'block';
            div.style.height = div.scrollHeight + 'px'; // Ensures the height is set for the transition
            div.style.opacity = '1';
        } else {
            div.style.height = '0px';
            div.style.opacity = '0';
            // Use a timeout to set display to none after the transition
            setTimeout(() => {
                div.style.display = 'none';
            }, 500); // 500ms matches the CSS transition duration
        }
    }
    // end search leads show search Filter js
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
                var model = 'Invoice';
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
        shoeHideEmailSend();
    });
    $('#selectAllCheckBoxes').on('click', function() {
        $('.delete_checkbox').prop('checked', $(this).prop('checked'));
        shoeHideEmailSend();
    });

    function shoeHideEmailSend() {
        if ($('.delete_checkbox:checked').length > 1) {
            $('.emialSend').show();
        } else {
            $('.emialSend').hide();
        }
    }
</script>
<script>
    function clearBtn() {
        $("#search_dataForm")[0].reset();
        location.reload();
    }

    function searchBtn() {
        var po_ref = $("#po_ref").val();
        var department = $("#department").val();
        var tag = $("#tag").val();
        var edd_startDate = $("#edd_startDate").val();
        var edd_endDate = $("#edd_endDate").val();
        var supplier = $("#supplier").val();
        var po_startDate = $("#po_startDate").val();
        var po_endDate = $("#po_endDate").val();
        var customer = $("#customer").val();
        var created_by = $("#created_by").val();
        var po_posted = $("#po_posted").val();
        var project = $("#project").val();
        var keywords = $("#keywords").val();
        var delivery_status = $("#delivery_status").val();
        var status = '<?php echo $key_mode; ?>'
        var list_status = '<?php echo $key_mode; ?>'
        var selectedDeptId = $("#selectedDeptId").val();
        var selectedTagtId = $("#selectedTagtId").val();
        var selectedsupplierId = $("#selectedsupplierId").val();
        var selectedCustomerId = $("#selectedCustomerId").val();
        var selectedcreatedById = $("#selectedcreatedById").val();
        var selectedProjectId = $("#selectedProjectId").val();
        const Httpurl = new URL(window.location.href);
        const params = new URLSearchParams(Httpurl.search);
        const key = params.get('list_mode');
        let isEmpty = true;
        $("#search_dataForm").find("input, select").each(function() {
            if ($(this).val() && $(this).val().trim() !== "") {
                isEmpty = false;
                return false;
            }
        });
        if (isEmpty) {
            alert("Please fill in at least one field before searching.");
            return false;
        }

        if (edd_startDate != '' && edd_endDate == '') {
            alert("Please choose both date");
            return false;
        }
        if (edd_startDate == '' && edd_endDate != '') {
            alert("Please choose both date");
            return false;
        }
        $.ajax({
            url: "{{ url('searchPurchaseOrders') }}",
            method: 'post',
            data: {
                po_ref: po_ref,
                department: department,
                selectedDeptId: selectedDeptId,
                tag: tag,
                selectedTagtId: selectedTagtId,
                supplier: supplier,
                selectedsupplierId: selectedsupplierId,
                edd_startDate: edd_startDate,
                edd_endDate: edd_endDate,
                po_startDate: po_startDate,
                po_endDate: po_endDate,
                customer: customer,
                selectedCustomerId: selectedCustomerId,
                created_by: created_by,
                selectedcreatedById: selectedcreatedById,
                po_posted: po_posted,
                project: project,
                selectedProjectId: selectedProjectId,
                keywords: keywords,
                delivery_status: delivery_status,
                status: status,
                list_status: list_status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                console.log(response);
                // return false;
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
        $('#department').on('keyup', function() {
            let search_deptquery = $(this).val();
            const deptdivList = document.querySelector('.department-container');

            if (search_deptquery === '') {
                deptdivList.innerHTML = '';
            }
            if (search_deptquery.length > 2) {
                $.ajax({
                    url: "{{ url('searchDepartment') }}",
                    method: 'post',
                    data: {
                        search_deptquery: search_deptquery,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log(response);
                        // return false;
                        deptdivList.innerHTML = "";
                        const div = document.createElement('div');
                        div.className = 'dept_container';


                        const ul = document.createElement('ul');
                        ul.id = "deptList";
                        if (response.data.length > 0) {
                            response.data.forEach(item => {
                                const li = document.createElement('li');
                                li.textContent = item.title;
                                li.id = item.id;
                                li.name = item.title;
                                li.className = "editInput";
                                ul.appendChild(li);
                                const hr = document.createElement('hr');
                                // hr.className='dropdown-divider';
                                ul.appendChild(hr);
                            });

                            div.appendChild(ul);

                            deptdivList.appendChild(div);

                            ul.addEventListener('click', function(event) {
                                deptdivList.innerHTML = '';
                                document.getElementById('department').value = '';
                                if (event.target.tagName.toLowerCase() === 'li') {
                                    const selectedDeptId = event.target.id;
                                    const selectedDeptName = event.target.name;
                                    console.log('Selected Customer ID:', selectedDeptId);
                                    console.log('Selected Customer Name:', selectedDeptName);
                                    $("#department").val(selectedDeptName);
                                    $("#selectedDeptId").val(selectedDeptId);
                                    // getCustomerData(selectedId,selectedDeptName);
                                }
                            });
                        } else {
                            const Errorli = document.createElement('li');
                            Errorli.textContent = 'Sorry Data Not found';
                            Errorli.id = 'searchError';
                            Errorli.className = "editInput";
                            ul.appendChild(Errorli);
                            div.appendChild(ul);
                            deptdivList.appendChild(div);
                            setTimeout(function() {
                                deptdivList.innerHTML = '';
                            }, 1000);
                        }

                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                deptdivList.innerHTML = '';
                $('#results').empty();
            }
        });

        $('#tag').on('keyup', function() {
            let search_tagquery = $(this).val();
            const tagdivList = document.querySelector('.tag-container');

            if (search_tagquery === '') {
                tagdivList.innerHTML = '';
            }
            if (search_tagquery.length > 2) {
                $.ajax({
                    url: "{{ url('searchTag') }}",
                    method: 'post',
                    data: {
                        search_tagquery: search_tagquery,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log(response);
                        // return false;
                        tagdivList.innerHTML = "";
                        const div = document.createElement('div');
                        div.className = 'tag_container';


                        const ul = document.createElement('ul');
                        ul.id = "tagList";
                        if (response.data.length > 0) {
                            response.data.forEach(item => {
                                const li = document.createElement('li');
                                li.textContent = item.title;
                                li.id = item.id;
                                li.name = item.title;
                                li.className = "editInput";
                                ul.appendChild(li);
                                const hr = document.createElement('hr');
                                // hr.className='dropdown-divider';
                                ul.appendChild(hr);
                            });

                            div.appendChild(ul);

                            tagdivList.appendChild(div);

                            ul.addEventListener('click', function(event) {
                                tagdivList.innerHTML = '';
                                document.getElementById('tag').value = '';
                                if (event.target.tagName.toLowerCase() === 'li') {
                                    const selectedTagtId = event.target.id;
                                    const selectedTagName = event.target.name;
                                    console.log('Selected Customer ID:', selectedTagtId);
                                    console.log('Selected Customer Name:', selectedTagName);
                                    $("#tag").val(selectedTagName);
                                    $("#selectedTagtId").val(selectedTagtId);
                                }
                            });
                        } else {
                            const Errorli = document.createElement('li');
                            Errorli.textContent = 'Sorry Data Not found';
                            Errorli.id = 'searchError';
                            Errorli.className = "editInput";
                            ul.appendChild(Errorli);
                            div.appendChild(ul);
                            tagdivList.appendChild(div);
                            setTimeout(function() {
                                tagdivList.innerHTML = '';
                            }, 1000);
                        }

                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                tagdivList.innerHTML = '';
                $('#results').empty();
            }
        });

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

        $('#customer').on('keyup', function() {
            let search_query = $(this).val();
            const customerdivList = document.querySelector('.customer-container');

            if (search_query === '') {
                customerdivList.innerHTML = '';
            }
            if (search_query.length > 2) {
                $.ajax({
                    url: "{{ url('searchCustomerName') }}",
                    method: 'post',
                    data: {
                        search_query: search_query,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log(response);
                        // return false;
                        customerdivList.innerHTML = "";
                        const div = document.createElement('div');
                        div.className = 'customer_container';


                        const ul = document.createElement('ul');
                        ul.id = "customerList";
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

                            customerdivList.appendChild(div);

                            ul.addEventListener('click', function(event) {
                                customerdivList.innerHTML = '';
                                document.getElementById('customer').value = '';
                                if (event.target.tagName.toLowerCase() === 'li') {
                                    const selectedCustomerId = event.target.id;
                                    const selectedCustomerName = event.target.name;
                                    console.log('Selected Customer ID:', selectedCustomerId);
                                    console.log('Selected Customer Name:', selectedCustomerName);
                                    $("#customer").val(selectedCustomerName);
                                    $("#selectedCustomerId").val(selectedCustomerId);
                                }
                            });
                        } else {
                            const Errorli = document.createElement('li');
                            Errorli.textContent = 'Sorry Data Not found';
                            Errorli.id = 'searchError';
                            Errorli.className = "editInput";
                            ul.appendChild(Errorli);
                            div.appendChild(ul);
                            customerdivList.appendChild(div);
                            setTimeout(function() {
                                customerdivList.innerHTML = '';
                            }, 1000);
                        }

                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                customerdivList.innerHTML = '';
                $('#results').empty();
            }
        });

        $('#created_by').on('keyup', function() {
            let search_createdbyquery = $(this).val();
            const createdbydivList = document.querySelector('.createdBy-container');

            if (search_createdbyquery === '') {
                createdbydivList.innerHTML = '';
            }
            if (search_createdbyquery.length > 2) {
                $.ajax({
                    url: "{{ url('searchCreatedBy') }}",
                    method: 'post',
                    data: {
                        search_createdbyquery: search_createdbyquery,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log(response);
                        // return false;
                        createdbydivList.innerHTML = "";
                        const div = document.createElement('div');
                        div.className = 'cretedby_container';


                        const ul = document.createElement('ul');
                        ul.id = "cretaedByList";
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

                            createdbydivList.appendChild(div);

                            ul.addEventListener('click', function(event) {
                                createdbydivList.innerHTML = '';
                                document.getElementById('created_by').value = '';
                                if (event.target.tagName.toLowerCase() === 'li') {
                                    const selectedcreatedById = event.target.id;
                                    const selectedCreatedByName = event.target.name;
                                    console.log('Selected Customer ID:', selectedcreatedById);
                                    console.log('Selected Customer Name:', selectedCreatedByName);
                                    $("#created_by").val(selectedCreatedByName);
                                    $("#selectedcreatedById").val(selectedcreatedById);
                                }
                            });
                        } else {
                            const Errorli = document.createElement('li');
                            Errorli.textContent = 'Sorry Data Not found';
                            Errorli.id = 'searchError';
                            Errorli.className = "editInput";
                            ul.appendChild(Errorli);
                            div.appendChild(ul);
                            createdbydivList.appendChild(div);
                            setTimeout(function() {
                                createdbydivList.innerHTML = '';
                            }, 1000);
                        }

                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                createdbydivList.innerHTML = '';
                $('#results').empty();
            }
        });

        $('#project').on('keyup', function() {
            let search_projectquery = $(this).val();
            const projectdivList = document.querySelector('.project-container');

            if (search_projectquery === '') {
                projectdivList.innerHTML = '';
            }
            if (search_projectquery.length > 2) {
                $.ajax({
                    url: "{{ url('searchProject') }}",
                    method: 'post',
                    data: {
                        search_projectquery: search_projectquery,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log(response);
                        // return false;
                        projectdivList.innerHTML = "";
                        const div = document.createElement('div');
                        div.className = 'project_container';


                        const ul = document.createElement('ul');
                        ul.id = "projectList";
                        if (response.data.length > 0) {
                            response.data.forEach(item => {
                                const li = document.createElement('li');
                                li.textContent = item.project_name;
                                li.id = item.id;
                                li.name = item.project_name;
                                li.className = "editInput";
                                ul.appendChild(li);
                                const hr = document.createElement('hr');
                                // hr.className='dropdown-divider';
                                ul.appendChild(hr);
                            });

                            div.appendChild(ul);

                            projectdivList.appendChild(div);

                            ul.addEventListener('click', function(event) {
                                projectdivList.innerHTML = '';
                                document.getElementById('project').value = '';
                                if (event.target.tagName.toLowerCase() === 'li') {
                                    const selectedProjectId = event.target.id;
                                    const selectedProjectName = event.target.name;
                                    console.log('Selected Customer ID:', selectedProjectId);
                                    console.log('Selected Customer Name:', selectedProjectName);
                                    $("#project").val(selectedProjectName);
                                    $("#selectedProjectId").val(selectedProjectId);
                                }
                            });
                        } else {
                            const Errorli = document.createElement('li');
                            Errorli.textContent = 'Sorry Data Not found';
                            Errorli.id = 'searchError';
                            Errorli.className = "editInput";
                            ul.appendChild(Errorli);
                            div.appendChild(ul);
                            projectdivList.appendChild(div);
                            setTimeout(function() {
                                projectdivList.innerHTML = '';
                            }, 1000);
                        }

                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                projectdivList.innerHTML = '';
                $('#results').empty();
            }
        });

    });
</script>
<script>
    function openApproveModal(id, po_ref) {
        $("#purchaseOrderRef").text(po_ref);
        $("#po_id").val(id);
        $("#approveModal").modal('show');
    }
    $('input[name="notify_radio"]').on('change', function() {
        if ($(this).val() == 1) {
            $(".notificationHideShow").show();
        } else {
            $(".notificationHideShow").hide();
        }

    });

    function saveApproveModal() {
        $.ajax({
            type: "POST",
            url: "{{url('/purchase_order_approve')}}",
            data: new FormData($("#approveForm")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                console.log(response);
                if (response.vali_error) {
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                } else if (response.success === true) {
                    $(window).scrollTop(0);
                    $('#message_approveModal').addClass('success-message').text(response.message).show();
                    setTimeout(function() {
                        $('#message_approveModal').removeClass('success-message').text('').hide();
                        location.reload();
                    }, 3000);
                } else if (response.success === false) {
                    $('#message_approveModal').addClass('error-message').text(response.message).show();
                    setTimeout(function() {
                        $('#error-message').text('').fadeOut();
                    }, 3000);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + error);
            }
        });
    }

    function openRecordDeliveryModal(id, po_ref) {
        $("#crecordDeliveryModalLabel").text('Record Payment - ' + po_ref);
        $("#recordDelivery_po_id").val(id);
        getProductDetail(id, pageUrl = '{{ url("getPurchaesOrderProductDetail") }}');
        $("#recordDeliveryModal").modal('show');
    }

    function getProductDetail(id, pageUrl = '{{ url("getPurchaesOrderProductDetail") }}') {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {
                id: id,
                _token: token
            },
            success: function(response) {
                console.log(response);
                // return false;
                var data = response.data[0];
                const tableBody = document.querySelector(`#recordDelivery_result tbody`);
                tableBody.innerHTML = '';
                var purchase_order_products = data.product_details.purchase_order_products;
                // console.log(purchase_order_products);return false;
                if (purchase_order_products.length === 0) {
                    const noDataRow = document.createElement('tr');
                    noDataRow.id = 'EmptyError'
                    const noDataCell = document.createElement('td');

                    noDataCell.setAttribute('colspan', 4);
                    noDataCell.textContent = 'No products found';
                    noDataCell.style.textAlign = 'center';

                    noDataRow.appendChild(noDataCell);
                    tableBody.appendChild(noDataRow);
                } else {
                    const emptyErrorRow = document.getElementById('EmptyError');
                    if (emptyErrorRow) {
                        emptyErrorRow.remove();
                    }
                    purchase_order_products.forEach(product => {
                        const row = document.createElement('tr');

                        const codeCell = document.createElement('td');
                        codeCell.textContent = product.code;
                        const inputCode = document.createElement('input');
                        inputCode.type = 'hidden';
                        inputCode.className = 'product_code';
                        inputCode.name = 'product_code[]';
                        inputCode.value = product.code;
                        codeCell.appendChild(inputCode);
                        row.appendChild(codeCell);

                        const nameCell = document.createElement('td');
                        nameCell.innerHTML = data.purchase_order_products_detail.product_name;
                        row.appendChild(nameCell);

                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.className = 'product_id';
                        hiddenInput.name = 'product_id[]';
                        hiddenInput.value = data.purchase_order_products_detail.id;
                        row.appendChild(hiddenInput);

                        // purchase order product hidden id if not duplicate is null
                        const hiddenID = document.createElement('input');
                        hiddenID.type = 'hidden';
                        hiddenID.className = 'purchase_product_id';
                        hiddenID.name = 'purchase_product_id[]';
                        hiddenID.value = product.id;
                        row.appendChild(hiddenID);
                        // end

                        const descriptionCell = document.createElement('td');
                        descriptionCell.textContent = product.description;
                        // const inputDescription = document.createElement('textarea');
                        // inputDescription.className = 'description';
                        // inputDescription.name = 'description[]';
                        // inputDescription.value = product.description;
                        // descriptionCell.appendChild(inputDescription);
                        row.appendChild(descriptionCell);

                        const priceCell = document.createElement('td');
                        priceCell.textContent = product.price;
                        // const inputPrice = document.createElement('input');
                        // inputPrice.type = 'text';
                        // inputPrice.className = 'product_price input50';
                        // inputPrice.name = 'price[]'; 
                        // inputPrice.value = product.price;
                        // priceCell.appendChild(inputPrice);
                        row.appendChild(priceCell);

                        const qtyCell = document.createElement('td');
                        qtyCell.textContent = product.qty;
                        const inputQty = document.createElement('input');
                        inputQty.type = 'hidden';
                        inputQty.className = 'qty input50';
                        inputQty.name = 'qty[]';
                        inputQty.value = product.qty;
                        qtyCell.appendChild(inputQty);
                        row.appendChild(qtyCell);

                        const alreadyDelivered = document.createElement('td');
                        const inputDelivered = document.createElement('input');
                        inputDelivered.type = 'number';
                        inputDelivered.className = 'already_deliver form-control';
                        inputDelivered.name = 'already_deliver[]';
                        inputDelivered.value = product.deliverd_qty || 0;
                        alreadyDelivered.appendChild(inputDelivered);
                        row.appendChild(alreadyDelivered);

                        const receiveMore = document.createElement('td');
                        const inputReceive = document.createElement('input');
                        inputReceive.type = 'number';
                        inputReceive.className = 'receive_more input50 form-control';
                        inputReceive.name = 'receive_more[]';
                        inputReceive.value = product.receive_more || 0;
                        receiveMore.appendChild(inputReceive);
                        row.appendChild(receiveMore);

                        tableBody.appendChild(row);
                    });
                    $("#product_calculation").show();

                }

                var paginationProductDetails = response.pagination;

                var paginationControlsProductDetail = $("#pagination-controls-recordDelivery");
                paginationControlsProductDetail.empty();
                if (paginationProductDetails.prev_page_url) {
                    paginationControlsProductDetail.append('<button type="button" class="btn btn-warning" onclick="getProductDetail(' + id + ', \'' + paginationContact.prev_page_url + '\')">Previous</button>');
                }
                if (paginationProductDetails.next_page_url) {
                    paginationControlsProductDetail.append('<button type="button" class="btn btn-warning" onclick="getProductDetail(' + id + ', \'' + paginationContact.next_page_url + '\')">Next</button>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                // location.reload();
            }
        });
    }
    document.addEventListener('input', function(event) {
        if (event.target.classList.contains('already_deliver') || event.target.classList.contains('receive_more')) {
            const row = event.target.closest('tr');
            const qty = parseInt(row.querySelector('.qty').value, 10);
            const inputDelivered = row.querySelector('.already_deliver');
            const inputReceive = row.querySelector('.receive_more');

            let deliveredQty = parseInt(inputDelivered.value, 10) || 0;
            let receivedQty = parseInt(inputReceive.value, 10) || 0;

            if (deliveredQty < 0) inputDelivered.value = 0;
            if (receivedQty < 0) inputReceive.value = 0;

            if (deliveredQty > qty) inputDelivered.value = qty;
            if (receivedQty > qty) inputReceive.value = qty;
        }
    });

    function saverecordDeliveryModal() {
        if (confirm("Are you sure you want to receive these stock quantities?")) {
            $.ajax({
                type: "POST",
                url: "{{url('/purchase_order_record_delivered')}}",
                data: new FormData($("#recordDeliveryForm")[0]),
                async: false,
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    if (response.vali_error) {
                        alert(response.vali_error);
                        $(window).scrollTop(0);
                        return false;
                    } else if (response.success === true) {
                        $(window).scrollTop(0);
                        $('#message_recordDeliveryModal').addClass('success-message').text(response.message).show();
                        setTimeout(function() {
                            $('#message_recordDeliveryModal').removeClass('success-message').text('').hide();
                            location.reload();
                        }, 3000);
                    } else if (response.success === false) {
                        $('#message_recordDeliveryModal').addClass('error-message').text(response.message).show();
                        setTimeout(function() {
                            $('#error-message').text('').fadeOut();
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

    function openRecordPaymentModal(id, po_ref, supplier_name, total_amount, date, product_id, outstandingAmount, supplier_id) {
        // alert(outstandingAmount)
        $("#purchaseOrderRecordDate").text(po_ref + ' On ' + date);
        $("#recordPayment_po_id").val(id);
        $("#recordPayment_ppurchaseProduct_id").val(product_id);
        $("#recordPayment_ppurchaseSupplier_id").val(supplier_id);
        $("#record_supplierName").text(supplier_name);
        $("#record_TotalAmount").text('£' + total_amount.toFixed(2));
        $("#record_OutstandingAmount").text('£' + outstandingAmount.toFixed(2));
        $("#recordPayment_total_amount").val(total_amount);
        $("#record_AmountPaid").val(outstandingAmount.toFixed(2));
        $("#recordPaymentModalLabel").text("Record Payment - " + po_ref);
        // $.ajax({
        //     type: "POST",
        //     url: "{{url('/record_payment_details')}}",
        //     data: {id: id,_token:'{{ csrf_token() }}'},
        //     success: function(response) {
        //         console.log(response);
        //     if(response.vali_error){
        //             alert(response.vali_error);
        //             $(window).scrollTop(0);
        //             return false;
        //         }else if(response.success === true){
        //             $(window).scrollTop(0);
        //             $('#message_recordDeliveryModal').addClass('success-message').text(response.message).show();
        //             setTimeout(function() {
        //                 $('#message_recordDeliveryModal').removeClass('success-message').text('').hide();
        //                 location.reload();
        //             }, 3000);
        //         }else if(response.success === false){
        //             $('#message_recordDeliveryModal').addClass('error-message').text(response.message).show();
        //             setTimeout(function() {
        //                 $('#error-message').text('').fadeOut();
        //             }, 3000);
        //         }
        //     },
        //     error: function(xhr, status, error) {
        //         var errorMessage = xhr.status + ': ' + xhr.statusText;
        //         alert('Error - ' + errorMessage + "\nMessage: " + error);
        //     }
        // });
        $("#recordPaymentModal").modal('show');
    }

    function openPaymentTypeModal() {
        $("#paymenTypeform")[0].reset();
        $("#paymentTypeModal").modal('show');
    }

    function getAllPaymentType(data) {
        $("#record_PaymentType").append('<option value="' + data.id + '">' + data.title + '</option>');
    }

    function saverecordPaymentModal() {
        $.ajax({
            type: "POST",
            url: "{{url('/savePurchaseOrderRecordPayment')}}",
            data: new FormData($("#recordPaymentForm")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                console.log(response);
                // return false;
                if (response.vali_error) {
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                } else if (response.success === true) {
                    $(window).scrollTop(0);
                    $('#message_recordPaymentModal').addClass('success-message').text(response.message).show();
                    setTimeout(function() {
                        $('#message_recordPaymentModal').removeClass('success-message').text('').hide();
                        location.reload();
                    }, 3000);
                } else if (response.success === false) {
                    $('#message_recordPaymentModal').addClass('error-message').text(response.message).show();
                    setTimeout(function() {
                        $('#error-message').text('').fadeOut();
                    }, 3000);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + error);
            }
        });
    }

    function openInvoiceRecieveModal(id, po_ref, supplier_name, supplier_id, sub_total_amount, date, vat, outstandingAmount) {
        // alert("supplier_id "+supplier_id);
        $("#invoice_po_id").val(id);
        $("#invoice_supplier_id").val(supplier_id);
        $("#Invoice_modal_title").text("Invoice Recieved - " + po_ref);
        $("#invoiuce_ref").text(po_ref + " On " + date);
        $("#invoiceSupplier_name").text(supplier_name);
        $("#invoiceNetAmount").val(sub_total_amount.toFixed(2));
        $("#invoiceVatAmount").val(vat.toFixed(2));
        $("#invoiceGrossAmount").val(sub_total_amount + vat);
        $("#invoiceModal").modal('show');
    }

    function getAllEmails(data) {
        location.reload();
        $("#emailModal").modal('hide');
    }

    function openRejectModal(id, po_ref) {
        // alert("id "+id);
        // alert("po_ref "+po_ref);
        $("#reject_po_id").val(id);
        $("#rejectpurchaseOrderRef").text(po_ref);
        $("#rejectModal").modal('show');
    }

    function openEmailModal(id, po_ref, email, name) {
        $("#emailformId")[0].reset();
        $("#dropdownButton").append('<span class="optext">' + email + '&emsp;<b class="removeSpan" onclick="removeSpan(this)">X</b></span>');
        $("#selectedToEmail").val(email);
        $("#email_modalTitle").text("Email Purchase Order - " + po_ref);
        $("#emailsubject").val("Purchase Order from The Contructor - " + po_ref);
        $("#email_po_id").val(id);
        $("#defaultOption").text('Default Purchase Order');
        const editor = CKEDITOR.instances['emailbody'];
        const message = `
            Hello,<br>
            Please find attached purchase order.<br><br><br><br><br>
            Regards,<br>
            The Contructor<br><br>
            Thanks for using SCITS
        `;
        editor.setData(message);
        $("#emailModal").modal('show');
    }

    function getAllPurchaseInvices(data) {
        location.reload();
    }
    $('#bulkInvoiceReceived').on('click', function() {
        const tableBody = document.querySelector(`#bulkInvoiceReceived_result tbody`);
        tableBody.innerHTML = '';
        tableBody.innerHTML = '<tr><td colspan="9" class="text text-danger text-center" id="norecorderror">Sorry, no records to show</td> </tr>';
        $("#bulkInvoiceReceivedModal").modal('show');
    });
    $('#BulkRecordPaymentBTN').on('click', function() {
        const tableBody = document.querySelector(`#bulkRecordPayment_result tbody`);
        tableBody.innerHTML = '';
        tableBody.innerHTML = '<tr><td colspan="8" class="text text-danger text-center" id="norecorderrorRecordPayment">Sorry, no records to show</td> </tr>';
        $("#bulkRecordPaymentModal").modal('show');
    });
</script>

@endsection