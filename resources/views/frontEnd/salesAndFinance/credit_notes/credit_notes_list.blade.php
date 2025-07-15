@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Purchase Order')
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
                        <h4>Purchase Orders Credit Notes - {{$status['list_status']}}</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12 mt-4">
                            <div class="jobsection justify-content-end">
                                <a href="#!" class="btn btn-warning"> Search Purchase Orders</a>
                                <a href="#!" class="btn btn-warning"> Invoice Received</a>
                                <a href="#!" class="btn btn-warning"> Statements</a>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="jobsection">
                                <div class="d-inline-flex align-items-center ">
                                    <div class="dropdown">
                                        <a href="{{url('new_credit_notes')}}" class="btn btn-warning"> New</a>
                                        <!-- <div class="dropdown-menu fade-up m-0">
                                            <a href="{{url('purchase_order')}}" class="dropdown-item">Purchase Order</a>
                                            <a href="{{url('new_credit_notes')}}" class="dropdown-item">Credit Note</a>
                                        </div> -->
                                    </div>
                                </div>
                                <a href="{{ url('credit_notes/Approved') }}" class="btn btn-warning" <?php if($status['status'] == 1){?>id="active_inactive"<?php }?>>Approved <span>({{$approvedtCount}})</span></a>
                                <a href="{{ url('credit_notes/Paid') }}" class="btn btn-warning" <?php if($status['status'] == 2){?>id="active_inactive"<?php }?>>Paid<span>({{$paidCount}})</span></a>
                                <a href="{{ url('credit_notes/Cancelled') }}" class="btn btn-warning" <?php if($status['status'] == 0){?>id="active_inactive"<?php }?>>Cancelled<span>({{$cancelledCount}})</span></a>
                                <div class="searchFilter">
                                    <a href="#!" onclick="hideShowDiv()" class="hidebtn btn btn-warning">Show Search Filter</a>
                                </div>
                                <a href="javascript:void(0)" id="deleteSelectedRows" class="btn btn-warning">Delete</a>

                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="searchJobForm" id="divTohide" style="display:none">
                                <form id="search_dataForm" class="p-4">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">Credit Note Ref:</label>
                                                <input type="text" class="form-control editInput" id="credit_ref">
                                            </div>
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">Date From:</label>
                                                <div class="row">
                                                    <div class="col-md-6 pe-0">
                                                        <input type="date" class="form-control editInput dateField" id="startDate">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="date" class="form-control editInput dateField" id="endDate">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
                                                <label class="mb-2 col-form-label">Keywords:</label>
                                                <input type="text" class="form-control editInput" id="keywords">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">Created By:</label>
                                                <input type="text" class="form-control editInput" id="created_by">
                                                <input type="hidden" id="selectedcreatedById" name="selectedcreatedById">
                                                <div class="parent-container createdBy-container"></div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="mb-2 col-form-label">Posted:</label>
                                                <select class="form-control editInput selectOptions" id="credit_posted">
                                                    <option selected disabled>--Any--</option>
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="jobsection justify-content-center">
                                                <a href="javascript:void(0)" onclick="searchBtn()" class="btn btn-warning">Search </a>
                                                <a href="javascript:void(0)" onclick="clearBtn()" class="btn btn-default">Clear</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="maimTable productDetailTable ">
                                <div class="table-responsive">
                                    <table id="myTable" class="table border-top" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style=" width:30px;"><input type="checkbox" id="selectAll"></th>
                                                <th>#</th>
                                                <th>Credit Note Ref</th>
                                                <th>Supplier</th>
                                                <th>Date</th>
                                                <th>Sub Total</th>
                                                <th>VAT</th>
                                                <th>Total </th>
                                                <th>Balance Credit </th>
                                                <th>Status</th>
                                                <th>Telephone</th>
                                                <th>Mobile</th>
                                                <th></th>
                                            </tr>
                                        </thead>

                                        <tbody id="search_data">
                                            <?php
                                            $all_subTotalAmount = 0;
                                            $all_vatTotalAmount = 0;
                                            $all_TotalAmount = 0;
                                            $outstandingAmountTotal = 0;
                                            ?>
                                            @foreach($list as $val)
                                            <?php
                                            $sub_total_amount = 0;
                                            $total_amount = 0;
                                            $vat_amount = 0;
                                            $creditProductId = 0;
                                            $product_id = 0;
                                            foreach ($val->creditNoteProducts as $product) {
                                                $creditProductId = $product->id;
                                                $product_id = $product->product_id;
                                                $qty = $product->qty * $product->price;
                                                $sub_total_amount = $sub_total_amount + $qty;
                                                $vat = $qty * $product->vat / 100;
                                                $vat_amount = $vat_amount + $vat;
                                                $total_amount = $total_amount + $vat + $qty;
                                            }
                                            $all_subTotalAmount = $all_subTotalAmount + $sub_total_amount;
                                            $all_vatTotalAmount = $all_vatTotalAmount + $vat_amount;
                                            $all_TotalAmount = $all_TotalAmount + $total_amount;
                                            $outstandingAmountTotal = $outstandingAmountTotal + $val->balance_credit;
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="text-center"><input type="checkbox" id="" class="delete_checkbox" value="{{$val->id}}"></div>
                                                </td>
                                                <td>{{$loop->iteration}}</td>
                                                <td>{{$val->credit_ref}}</td>
                                                <td>{{$val->suppliers->name}}</td>
                                                <td>{{ date('d/m/Y', strtotime($val->date)) }}</td>
                                                <td>£{{$sub_total_amount}}</td>
                                                <td>£{{$vat_amount}}</td>
                                                <td>£{{$total_amount}}</td>
                                                <td>£{{$val->balance_credit}}</td>
                                                <td>{{$status['list_status']}}</td>
                                                <td>{{$val->telephone}}</td>
                                                <td>{{$val->mobile}}</td>
                                                @if($status['status'] == 1)
                                                <td>
                                                    <div class="d-flex justify-content-end">
                                                        <div class="dropdown">
                                                            <a href="#!" class="btn btn-primary btn-sm" data-toggle="dropdown" aria-expanded="false">
                                                                Action <i class="fa fa-caret-down"></i>
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-right fade-up m-0">
                                                                <a href="{{url('credit_note_edit?key=')}}{{base64_encode($val->id)}}" class="dropdown-item">Edit</a>
                                                                <a href="{{url('credit_preview?key=')}}{{base64_encode($val->id)}}" target="_blank" class="dropdown-item">Preview</a>
                                                                <a href="javascript:void(0)" onclick="openEmailModal({{$val->id}},'{{$val->credit_ref}}','{{$val->suppliers->email}}','{{$val->suppliers->name}}')" class="dropdown-item">Email</a>
                                                                <a href="javascript:void(0)" onclick="openAllocateModal({{$val->id}},'{{$val->credit_ref}}',{{$val->supplier_id}},'{{$val->suppliers->name}}',{{$val->balance_credit}},{{$product_id}},'{{$val->date}}')" class="dropdown-item">Allocate</a>
                                                                <a href="javascript:void(0)" onclick="cancelCreditFunction({{$val->id}},'{{$val->credit_ref}}')" class="dropdown-item">Cancel Credit Note</a>
                                                                <a href="#!" class="dropdown-item">CRM / History</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                @else
                                                <td>
                                                    <div class="d-flex justify-content-end">
                                                        <div class="dropdown">
                                                            <a href="#!" class="btn btn-primary btn-sm" data-toggle="dropdown" aria-expanded="false">
                                                                Action <i class="fa fa-caret-down"></i>
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-right fade-up m-0">
                                                                <a href="{{url('credit_note_edit?key=')}}{{base64_encode($val->id)}}" class="dropdown-item">Edit</a>
                                                                <a href="{{url('credit_preview?key=')}}{{base64_encode($val->id)}}" target="_blank" class="dropdown-item">Preview</a>
                                                                <a href="#!" class="dropdown-item">Print</a>
                                                                <a href="javascript:void(0)" onclick="openEmailModal({{$val->id}},'{{$val->credit_ref}}','{{$val->suppliers->email}}','{{$val->suppliers->name}}')" class="dropdown-item">Email</a>
                                                                <!--  <a href="#!" class="dropdown-item">Cancel Credit Note</a> -->
                                                                <a href="#!" class="dropdown-item">CRM / History</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                @endif

                                            </tr>
                                            @endforeach
                                        </tbody>
                                        @if(count($list)>0)
                                        <tr class="calcualtionShowHide">
                                            <th colspan="2"> <label class="col-form-label p-0">Page Sub Total:</label></th>
                                            <th colspan="12"></th>
                                        </tr>
                                        <tr class="calcualtionShowHide">
                                            <td colspan="5"></td>

                                            <td id="Tablesub_total_amount">£{{$all_subTotalAmount}}</td>
                                            <td id="Tablevat_amount">£{{$all_vatTotalAmount}}</td>
                                            <td id="Tabletotal_amount">£{{$all_TotalAmount}}</td>
                                            <td id="Tableoutstanding_amount" colspan="8">£{{$outstandingAmountTotal}}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div> <!-- End off main Table -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<x-purchase-order-email
    emailModalId="emailModal"
    modalTitle="email_modalTitle"
    emailformId="emailformId"
    foreignId="credit_id"
    emailId="emailId"
    toField="toField"
    ccField="ccField"
    subject="emailsubject"
    selectBoxsubject="selectBoxsubject"
    body="emailbody"
    saveButtonId="emailSave"
    saveUrl="{{url('crediNoteEmailSave')}}" />

<x-credit-note-allocate
    allocateModalId="allocateModal"
    modalTitle="allocate_modalTitle"
    allocateformId="allocateformId"
    foreignId="credit_id"
    allocateId="id"
    modalSubTitle="allocate_sub_title"
    fieldsetTitle="allocate_fieldset_title"
    saveButtonId="allocateSave"
    saveUrl="{{url('crediNoteAllocateSave')}}" />
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
                var model = 'CreditNote';
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
<script>
    function clearBtn() {
        $("#search_dataForm")[0].reset();
        location.reload();
    }

    function searchBtn() {
        var credit_ref = $("#credit_ref").val();
        var department = $("#department").val();
        var tag = $("#tag").val();
        var startDate = $("#startDate").val();
        var endDate = $("#endDate").val();
        var supplier = $("#supplier").val();
        var po_startDate = $("#po_startDate").val();
        var po_endDate = $("#po_endDate").val();
        var customer = $("#customer").val();
        var created_by = $("#created_by").val();
        var credit_posted = $("#credit_posted").val();
        var project = $("#project").val();
        var keywords = $("#keywords").val();
        var delivery_status = $("#delivery_status").val();
        var status = '<?php echo $status['status']; ?>'
        var list_status = '<?php echo $status['list_status']; ?>'
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

        if (startDate != '' && endDate == '') {
            alert("Please choose both date");
            return false;
        }
        if (startDate == '' && endDate != '') {
            alert("Please choose both date");
            return false;
        }
        $.ajax({
            url: "{{ url('searchCreditNotes') }}",
            method: 'post',
            data: {
                credit_ref: credit_ref,
                department: department,
                selectedDeptId: selectedDeptId,
                tag: tag,
                selectedTagtId: selectedTagtId,
                supplier: supplier,
                selectedsupplierId: selectedsupplierId,
                startDate: startDate,
                endDate: endDate,
                po_startDate: po_startDate,
                po_endDate: po_endDate,
                customer: customer,
                selectedCustomerId: selectedCustomerId,
                created_by: created_by,
                selectedcreatedById: selectedcreatedById,
                credit_posted: credit_posted,
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
    $("#endDate").change(function() {
        var startDate = document.getElementById("startDate").value;
        var endDate = document.getElementById("endDate").value;

        if ((Date.parse(startDate) >= Date.parse(endDate))) {
            alert("End date should be greater than Start date");
            document.getElementById("endDate").value = "";
        }
    });
    $("#startDate").change(function() {
        var startDate = document.getElementById("startDate").value;
        var endDate = document.getElementById("endDate").value;

        if ((Date.parse(endDate) <= Date.parse(startDate))) {
            alert("Start date should be less than End date");
            document.getElementById("startDate").value = "";
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
    });
</script>
<script>
    function openAllocateModal(id, credit_ref, supplier_id, supplier_name, outstandingAmount, product_id, date) {
        $("#allocate_modalTitle").text('Credit Note');
        $("#allocate_sub_title").text('Allocate Credit Note - ' + credit_ref);
        $("#allocate_fieldset_title").text(supplier_name);
        $("#allocate_credit_id").val(id);
        $("#allocate_supplier_id").val(supplier_id);
        $("#allocate_product_id").val(product_id);
        $("#allocate_date").val(date);
        getAllSupplierPurchaseOrder(supplier_id, outstandingAmount);
        $("#allocateModal").modal('show');
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

    function openEmailModal(id, credit_ref, email, name) {
        $("#emailformId")[0].reset();
        $("#dropdownButton").append('<span class="optext">' + email + '&emsp;<b class="removeSpan" onclick="removeSpan(this)">X</b></span>');
        $("#selectedToEmail").val(email);
        $("#email_modalTitle").text("Email Credit Note - " + credit_ref);
        $("#emailsubject").val("Your Credit Note from the Contructor - " + credit_ref);
        $("#email_credit_id").val(id);
        $("#defaultOption").text('Default Credit Note');
        const editor = CKEDITOR.instances['emailbody'];
        const message = `
            Hello,<br>
            Please find attached credit note.<br><br><br><br><br>
            Regards,<br>
            The Contructor<br><br>
            Thanks for using SCITS
        `;
        editor.setData(message);
        $("#emailModal").modal('show');
    }

    function getAllEmails(data) {
        // location.reload();
        $("#emailModal").modal('hide');
    }
</script>
<script>

//     flatpickr(".dateField", {
//     dateFormat: "d/m/Y",
// }); 
function cancelCreditFunction(id,credit_ref){
    if(confirm("Are you sure you want to cancel credit note '"+credit_ref +"'?")){
        $.ajax({
            type: "POST",
            url: "{{url('/cancelCreditNote')}}",
            data: {id:id,credit_ref:credit_ref,_token:'{{csrf_token()}}'},
            success: function(response) {
                // console.log(response);return false;
                if (isAuthenticated(response) == false) {
                    return false;
                }
            if(response.vali_error){
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                }else if(response.success === true){
                    // $(window).scrollTop(0);
                    // $('#message_recordPaymentModal').addClass('success-message').text(response.message).show();
                    // setTimeout(function() {
                    //     $('#message_recordPaymentModal').removeClass('success-message').text('').hide();
                        
                    // }, 3000);
                    location.reload();
                }else if(response.success === false){
                    // $('#message_recordPaymentModal').addClass('error-message').text(response.message).show();
                    // setTimeout(function() {
                    //     $('#error-message').text('').fadeOut();
                    // }, 3000);
                    alert("Something went wrong pleae try later!");
                    return false;

                }
            });
        }
    }

    function getAllAllocates(data) {
        location.reload();
    }
</script>
@endsection