@include('frontEnd.salesAndFinance.jobs.layout.header')
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

    .tag_box {
        background: #f5f5f5;
        display: grid;
        border: 1px solid #dee2e6;
    }
</style>
<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>{{$status['page_heading']}}</h3>
                </div>
            </div>
            <div class="col-md-8 col-lg-8 col-xl-8 px-3">
                <div class="pageTitleBtn">
                    <a href="{{url('purchase-orders-search')}}" class="profileDrop"> Search Purchase Orders</a>
                    <a href="{{url('purchase-order-invoices')}}" class="profileDrop"> Invoice Received</a>
                    <a href="{{url('purchase-order-statements')}}" class="profileDrop"> Statements</a>
                </div>
            </div>
        </div>


            <div class="row">
                <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                    <div class="jobsection">
                        <div class="d-inline-flex align-items-center ">
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                                    New
                                </a>
                                <div class="dropdown-menu fade-up m-0">
                                    <a href="{{url('purchase_order')}}" class="dropdown-item">Purchase Order</a>
                                    <a href="{{url('new_credit_notes')}}" class="dropdown-item">Credit Note</a>
                                    <!-- <a href="#!" class="dropdown-item">Print</a>
                                    <a href="#!" class="dropdown-item">Email</a> -->
                            </div>
                        </div>
                    </div>
                    <a href="{{ url('draft_purchase_order') }}" class="profileDrop" <?php if ($status['status'] == 1) { ?>id="active_inactive" <?php } ?>>Draft <span>({{$draftCount}})</span></a>
                    <a href="{{ url('draft_purchase_order?list_mode=AwaitingApprivalPurchaseOrders') }}" class="profileDrop" <?php if ($status['status'] == 2) { ?>id="active_inactive" <?php } ?>>Awaiting Approval<span>({{$awaitingApprovalCount}})</span></a>
                    <a href="{{ url('draft_purchase_order?list_mode=Approved') }}" class="profileDrop" <?php if ($status['status'] == 3) { ?>id="active_inactive" <?php } ?>>Approved<span>({{$approvedCount}})</span></a>
                    <a href="{{ url('draft_purchase_order?list_mode=Rejected') }}" class="profileDrop" <?php if ($status['status'] == 8) { ?>id="active_inactive" <?php } ?>>Rejected<span>({{$rejectedCount}})</span></a>
                    <a href="{{ url('draft_purchase_order?list_mode=Actioned') }}" class="profileDrop" <?php if ($status['status'] == 4) { ?>id="active_inactive" <?php } ?>>Actioned<span>({{$actionedCount}})</span></a>
                    <a href="{{ url('draft_purchase_order?list_mode=Paid') }}" class="profileDrop" <?php if ($status['status'] == 5) { ?>id="active_inactive" <?php } ?>>Paid<span>({{$paidCount}})</span></a>

                </div>
            </div>

        </div>
        <di class="row">
            <div class="col-lg-12">
                <div class="maimTable">
                    <div class="printExpt">
                        <div class="prntExpbtn">
                            <a href="#!">Print</a>
                            <a href="#!">Export</a>
                        </div>
                        <div class="searchFilter">
                            <a href="#!" onclick="hideShowDiv()" class="hidebtn">Show Search Filter</a>
                        </div>

                    </div>

                    <div class="searchJobForm" id="divTohide" style="display:none">
                        <form id="search_dataForm" class="p-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">PO Ref:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="po_ref">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Department:</label>
                                        <div class="col-md-8 position-relative">
                                            <input type="text" class="form-control editInput" id="department">
                                            <input type="hidden" id="selectedDeptId" name="selectedDeptId">
                                            <div class="parent-container department-container"></div>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Tag:</label>
                                        <div class="col-md-8 position-relative">
                                            <input type="text" class="form-control editInput" id="tag">
                                            <input type="hidden" id="selectedTagtId" name="selectedTagtId">
                                            <div class="parent-container tag-container"></div>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">

                                        <label class="col-md-4 col-form-label text-end ">
                                            <a href="#!" class="tutor-student-tooltip-col">
                                                EDD From:

                                                <span class="tutor-student-tooltiptext3">Expedcted Delivery Date</span>
                                            </a>
                                        </label>

                                        <div class="col-md-4">
                                            <input type="date" class="form-control editInput" id="edd_startDate">
                                        </div>

                                        <div class="col-md-4">
                                            <input type="date" class="form-control editInput" id="edd_endDate">
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Supplier:</label>
                                        <div class="col-md-8 position-relative">
                                            <input type="text" class="form-control editInput" id="supplier">
                                            <input type="hidden" id="selectedsupplierId" name="selectedsupplierId">
                                            <div class="parent-container supplier-container"></div>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">PO Date From:</label>
                                        <div class="col-md-4">
                                            <input type="date" class="form-control editInput" id="po_startDate">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="date" class="form-control editInput" id="po_endDate">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Customer:</label>
                                        <div class="col-md-8 position-relative">
                                            <input type="text" class="form-control editInput" id="customer">
                                            <input type="hidden" id="selectedCustomerId" name="selectedCustomerId">
                                            <div class="parent-container customer-container"></div>
                                        </div>
                                    </div>

                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Created By:</label>
                                        <div class="col-md-8 position-relative">
                                            <input type="text" class="form-control editInput" id="created_by">
                                            <input type="hidden" id="selectedcreatedById" name="selectedcreatedById">
                                            <div class="parent-container createdBy-container"></div>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">PO Posted:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="po_posted">
                                                <option selected disabled>--Any--</option>
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Project:</label>
                                        <div class="col-md-8 position-relative">
                                            <input type="text" class="form-control editInput" id="project">
                                            <input type="hidden" id="selectedProjectId" name="selectedProjectId">
                                            <div class="parent-container project-container"></div>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Keywords:</label>
                                        <div class="col-md-8 position-relative">
                                            <input type="text" class="form-control editInput" id="keywords">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Delivery Status:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="delivery_status">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="pageTitleBtn justify-content-center">
                                        <a href="javascript:void(0)" onclick="searchBtn()" class="profileDrop px-3">Search </a>
                                        <a href="javascript:void(0)" onclick="clearBtn()" class="profileDrop px-3">Clear</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="jobsection">
                                    <a href="javascript:void(0)" id="deleteSelectedRows" class="profileDrop">Delete</a>
                                    <a href="javascript:void(0)" id="preview_purchase_orderBoxes" class="profileDrop">Preview Purchase Order</a>
                                    @if($status['status'] == 3 || $status['status'] == 4 || $status['status'] == 5)
                                    <div class=" d-inline-flex align-items-center">
                                    <div class="nav-item dropdown">
                                            <a href="#!" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                                                Email Purchase Order
                                            </a>
                                            <div class="dropdown-menu fade-up m-0">
                                                <a href="javascript:void(0)" class="dropdown-item email_sendCheck" onclick="email_sendCheck(1)">Send As Single Email</a>
                                                <hr class="dropdown-divider emialSend" style="display:none">
                                                <a href="javascript:void(0)" class="dropdown-item emialSend email_sendCheck" onclick="email_sendCheck(2)" style="display:none">Send As Multiple Emails</a>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="javascript:void(0)" id="bulkInvoiceReceived" class="profileDrop">Invoice Received</a>
                                    @if($status['status'] != 5)
                                    <a href="javascript:void(0)" id="BulkRecordPaymentBTN" class="profileDrop">Record Payment</a>
                                    @endif
                                    @endif
                                    <a href="javascript:void(0)" id="approveBtn" class="profileDrop">Approve</a>
                                </div>
                            </div>
                            <!-- <div class="col-md-5">
                                    <div class="pageTitleBtn p-0">
                                        <a href="#" class="profileDrop"> <i class="material-symbols-outlined"> settings </i></a>        
                                    </div>
                                </div> -->
                        </div>
                    </div>

                    <table id="exampleOne" class="display tablechange" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center" style=" width:30px;"><input type="checkbox" id="selectAllCheckBoxes"></th>
                                <th>#</th>
                                <th>PO Ref</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th>Supplier</th>
                                <th>Customer</th>
                                <th>Delivery</th>
                                <th>Sub Total</th>
                                <th>VAT</th>
                                <th>Total </th>
                                <th>Outstanding </th>
                                <th>Status</th>
                                <th>Delivery</th>
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
                            $customer = App\Models\Customer::find($val->customer_id);
                            $sub_total_amount = 0;
                            $total_amount = 0;
                            $vat_amount = 0;
                            $purchaseProductId = 0;
                            $product_id=0;
                            $vat = 0;
                            foreach ($val->purchaseOrderProducts as $product) {
                                $purchaseProductId = $product->id;
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
                            $outstandingAmountTotal = $outstandingAmountTotal + $val->outstanding_amount;
                            ?>
                            <tr>
                                <td>
                                    <div class="text-center"><input type="checkbox" id="" class="delete_checkbox" value="{{$val->id}}"></div>
                                </td>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$val->purchase_order_ref}}</td>
                                <td>{{ date('d/m/Y', strtotime($val->purchase_date)) }}</td>
                                <td>{{ date('d/m/Y', strtotime($val->payment_due_date)) }}</td>
                                <td>{{$val->suppliers->name}} <input type="hidden" value="{{$val->suppliers->email}}" class="supplierEmail" id="supplierEmail{{$val->id}}"> </td>
                                <td>{{$customer->name ?? ''}}</td>
                                <td>{{$val->city}}</td>
                                <td>£{{$sub_total_amount}}</td>
                                <td>£{{$vat_amount}}</td>
                                <td>£{{$total_amount}}</td>
                                <td>£{{$val->outstanding_amount}}</td>
                                <td>{{$status['list_status']}}</td>
                                <td>
                                @if( $status['status'] != 1)
                                    @if($val->delivery_status == 1)
                                    <span class="grencheck"><i class="fa-solid fa-check"></i></span>
                                    @elseif($val->delivery_status == 2)
                                    <a href="javascript:void(0)" class="tutor-student-tooltip-col" style="color:red"><span class="" style="color:#FFCC66"><i class="fa-solid fa-check"></i></span><span class="tutor-student-tooltiptext3">Part Delivered</span></a>
                                    @else
                                    <a href="javascript:void(0)" class="tutor-student-tooltip-col" style="color:red">X<span class="tutor-student-tooltiptext3">Not Delivered</span></a>
                                    @endif
                                @else
                                -
                                @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        <div class="nav-item dropdown">
                                            <a href="#!" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action
                                            </a>
                                            <div class="dropdown-menu fade-up m-0" style="z-index:9999">
                                                @if( $status['status'] != 1 && $status['status'] != 2)
                                                <a href="javascript:void(0)" onclick="openRecordDeliveryModal({{$val->id}},'{{$val->purchase_order_ref}}')" class="dropdown-item">Record Delivery</a>
                                                <hr class="dropdown-divider">
                                                @endif
                                                <a href="{{url('purchase_order_edit?key=')}}{{base64_encode($val->id)}}" class="dropdown-item">Edit</a>
                                                <hr class="dropdown-divider">
                                                <a href="{{url('preview?key=')}}{{base64_encode($val->id)}}" target="_blank" class="dropdown-item">Preview</a>
                                                <hr class="dropdown-divider">
                                                @if( $status['status'] != 1 && $status['status'] != 2)
                                                <a href="{{url('preview?key=')}}{{base64_encode($val->id)}}" target="_blank" class="dropdown-item">Print</a>
                                                <hr class="dropdown-divider">
                                                <a href="javascript:void(0)" onclick="openEmailModal({{$val->id}},'{{$val->purchase_order_ref}}','{{$val->suppliers->email}}','{{$val->suppliers->name}}')" class="dropdown-item">Email</a>
                                                <hr class="dropdown-divider">
                                                @endif
                                                <a href="{{url('purchase_order?duplicate=')}}{{base64_encode($val->id)}}" target="_blank" class="dropdown-item">Duplicate</a>
                                                <hr class="dropdown-divider">
                                                @if($status['status'] != 8 && $status['status'] != 1 )
                                                <a href="javascript:void(0)" onclick="openRejectModal({{$val->id}},'{{$val->purchase_order_ref}}')" class="dropdown-item">Reject</a>
                                                <hr class="dropdown-divider">
                                                @endif
                                                @if($status['status'] == 1 || $status['status'] == 2)
                                                <a href="javascript:void(0)" onclick="openApproveModal({{$val->id}},'{{$val->purchase_order_ref}}')" class="dropdown-item">Approve</a>
                                                <hr class="dropdown-divider">
                                                @endif
                                                @if($status['status'] != 5 && $status['status'] != 1 && $status['status'] != 2)
                                                <a href="javascript:void(0)" onclick="openRecordPaymentModal({{$val->id}},'{{$val->purchase_order_ref}}','{{$val->suppliers->name}}',{{$total_amount}},'{{ date('d/m/Y', strtotime($val->purchase_date)) }}',{{$product_id}},{{$val->outstanding_amount}},{{$val->supplier_id}})" class="dropdown-item">Record Payment</a>
                                                <hr class="dropdown-divider">
                                                <a href="javascript:void(0)" onclick="openInvoiceRecieveModal({{$val->id}},'{{$val->purchase_order_ref}}','{{$val->suppliers->name}}',{{$val->suppliers->id}},{{$sub_total_amount}},'{{ date('d/m/Y', strtotime($val->purchase_date)) }}',{{$vat}},{{$val->outstanding_amount}})" class="dropdown-item">Invoice Received</a>
                                                <hr class="dropdown-divider">
                                                @endif
                                                @if($status['status'] == 8 || $status['status'] == 4 || $status['status'] == 5 || $status['status'] == 3)
                                                    @if($val->delivery_status !=1)
                                                    <a href="#!" class="dropdown-item">Cancel Purchase Order</a>
                                                    <hr class="dropdown-divider">
                                                    @endif
                                                @endif
                                                <a href="#!" class="dropdown-item">CRM / History</a>
                                                <hr class="dropdown-divider">
                                                <a href="#!" class="dropdown-item">Start Timer</a>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        @if(count($list)>0)
                        <tr class="calcualtionShowHide">
                            <th colspan="2"> <label class="col-form-label p-0">Page Sub Total:</label></th>
                            <th colspan="12"></th>
                        </tr>
                        <tr class="calcualtionShowHide">
                            <td colspan="8"></td>

                            <td id="Tablesub_total_amount">£{{$all_subTotalAmount}}</td>
                            <td id="Tablevat_amount">£{{$all_vatTotalAmount}}</td>
                            <td id="Tabletotal_amount">£{{$all_TotalAmount}}</td>
                            <td id="Tableoutstanding_amount" colspan="8">£{{$outstandingAmountTotal}}</td>
                        </tr>
                        @endif
                    </table>

                </div> <!-- End off main Table -->
            </div>
        </di>
    </div>
</section>
<!-- Approve Model Start Here -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Authorise Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="text-center mt-3" id="message_approveModal" style="display:none"></div>
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="formDtail">
                            <form id="approveForm" class="customerForm pt-0">
                                @csrf
                                <input type="hidden" name="id" id="id">
                                <input type="hidden" name="po_id" id="po_id">
                                <div class="row">
                                    <label for="inputName" class="col-md-12 col-lg-12 col-xl-12 col-sm-12 col-form-label">Would you like to notify anyone that this purchase order '<span id="purchaseOrderRef"></span>' has been approved?</label>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-md-3 col-lg-3 col-xl-3 col-sm-3 col-form-label">Notify?</label>
                                    <div class="col-sm-9">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="notify_radio" id="radioNo" value="0" checked="">
                                            <label class="form-check-label checkboxtext" for="inlineRadio2">No</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="notify_radio" value="1" id="radioYes">
                                            <label class="form-check-label checkboxtext" for="inlineRadio1">Yes</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-2 row notificationHideShow" style="display:none">
                                    <label for="inputProject"
                                        class="col-sm-3 col-form-label">Notify Who?</label>
                                    <div class="col-sm-9">
                                        <select class="form-control editInput selectOptions" id="notifywhoUser" name="notify_user_id">
                                            <option value=""></option>
                                            <option value="{{Auth::user()->id}}">Me - {{Auth::user()->email}} / {{Auth::user()->phone_no ?? 'No Mobile'}}</option>
                                            @foreach($users as $value)
                                            @if($value->id != Auth::user()->id)
                                            <option value="{{ $value->id }}">{{ $value->name }} - {{$value->email}} / {{$value->phone_no ?? 'No Mobile'}}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2 row notificationHideShow" style="display:none">
                                    <label class="col-sm-3 col-form-label">Send As <span class="radStar ">*</span> </label>
                                    <div class="col-sm-9">
                                        <label for="purchase_notify_who1" class="editInput">
                                            <input type="checkbox" name="notification" id="purchase_notify_who1" value="1" checked=""> Notification (User Only)
                                        </label>
                                        <label for="purchase_notify_who2" class="editInput">
                                            <input type="checkbox" name="sms" id="purchase_notify_who2" value="1"> SMS
                                        </label>
                                        <label for="purchase_notify_who3" class="editInput">
                                            <input type="checkbox" name="email" id="purchase_notify_who3" value="1" checked=""> Email
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> <!-- End row -->
            </div>
            <div class="modal-footer customer_Form_Popup">

                <button type="button" class="profileDrop" id="saveApproveModal" onclick="saveApproveModal()">Save</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- End here -->
<!-- Record Delivery Modal start here -->
<div class="modal fade" id="recordDeliveryModal" tabindex="-1" aria-labelledby="recordDeliveryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="crecordDeliveryModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="text-center mt-3" id="message_recordDeliveryModal" style="display:none"></div>
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="formDtail">
                            <form id="recordDeliveryForm" class="customerForm pt-0">
                                <input type="hidden" name="po_id" id="recordDelivery_po_id">
                                @csrf
                                <div class="col-sm-12">
                                    <div class="productDetailTable input_style">
                                        <table class="table" id="recordDelivery_result">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="col-1">Code</th>
                                                    <th class="col-2">Product</th>
                                                    <th class="col-2">Description</th>
                                                    <th class="col-2">Price</th>
                                                    <th class="col-1">Qty</th>
                                                    <th class="col-2">Allredy Delivered</th>
                                                    <th class="col-2">Receive More</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                        <div id="pagination-controls-recordDelivery"></div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> <!-- End row -->
            </div>
            <div class="modal-footer customer_Form_Popup">

                <button type="button" class="profileDrop" id="saverecordDeliveryModal" onclick="saverecordDeliveryModal()">Save</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- end here -->
<!-- Record Payment Modal start here -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="recordPaymentModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="text-center mt-3" id="message_recordPaymentModal" style="display:none"></div>
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="formDtail">
                            <form id="recordPaymentForm" class="customerForm pt-0">
                                <input type="hidden" name="po_id" id="recordPayment_po_id">
                                <!-- <input type="hidden" name="product_id" id="recordPayment_ppurchaseProduct_id"> -->
                                <input type="hidden" name="supplier_id" id="recordPayment_ppurchaseSupplier_id">
                                <input type="hidden" name="record_type" value="1">
                                <input type="hidden" name="total_amount" id="recordPayment_total_amount" value="">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 col-lg-6 col-xl-6">
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Purchase Order</label>
                                            <div class="col-sm-9">
                                                <p id="purchaseOrderRecordDate"></p>
                                            </div>
                                        </div>
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Supplier</label>
                                            <div class="col-sm-9">
                                                <p id="record_supplierName"></p>
                                            </div>
                                        </div>
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Purchase Order Total</label>
                                            <div class="col-sm-9">
                                                <p id="record_TotalAmount"></p>
                                            </div>
                                        </div>
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Outstanding Amount</label>
                                            <div class="col-sm-9">
                                                <p id="record_OutstandingAmount"></p>
                                            </div>
                                        </div>
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Amount Paid <span class="radStar">*</span></label>
                                            <div class="col-sm-1">
                                                <div class="tag_box text-center">
                                                    <span style="padding:3px">£</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control editInput textareaInput" id="record_AmountPaid" name="record_amount_paid">
                                            </div>
                                        </div>
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Payment Date <span class="radStar ">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="date" id="record_PaymentDate" name="record_payment_date" class="form-control editInput">
                                            </div>
                                        </div>
                                        <div class="mb-2 row">
                                            <label for="inputProject" class="col-sm-3 col-form-label">Payment Type <span class="radStar ">*</span></label>
                                            <div class="col-sm-7">
                                                <select class="form-control editInput selectOptions" id="record_PaymentType" name="record_payment_type">
                                                    @foreach($paymentTypeList as $type)
                                                    <option value="{{$type->id}}">{{$type->title}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-2">
                                                <a href="javascript:void(0)" class="formicon" onclick="openPaymentTypeModal()"><i class="fa-solid fa-square-plus"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-6 col-xl-6">
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Reference</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput textareaInput" placeholder="Reference (if any)" id="record_Reference" name="record_reference">
                                            </div>
                                        </div>
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Description</label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control textareaInput CustomercheckError" id="record_Description" name="record_description" rows="10" maxlength="500"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> <!-- End row -->
            </div>
            <div class="modal-footer customer_Form_Popup">

                <button type="button" class="profileDrop" id="saverecordPaymentModal" onclick="saverecordPaymentModal()">Save</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- end here -->
<x-payment-type-modal
    paymentTypeModalId="paymentTypeModal"
    modalTitle="New Payment Type - Add"
    paymentTypeformId="paymenTypeform"
    paymentTypeId="paymentType_id"
    inputPaymentType="payment_type_name"
    radioYes="paymentTypeYes"
    radioNo="paymentTypeNo"
    selectStatus="paymentTypeStatus"
    saveButtonId="paymentTypeSave" />
<x-add-invoice-modal
    invoiceModalId="invoiceModal"
    modalTitle="Invoice_modal_title"
    invoiceformId="invoiceform"
    invoiceId="invoice_id"
    purchaseOrder="purchaseOrder_ref"
    inputInvoiceSupplierName="invoiceSupplier_name"
    inputInvoiceRef="invoiuce_ref"
    invoiceNetAmount="invoiceNetAmount"
    invoiceVatId="invoiceVatId"
    invoiceVatAmount="invoiceVatAmount"
    invoiceGrossAmount="invoiceGrossAmount"
    invoiceDate="invoiceDate"
    invoiceDueDate="invoiceDueDate"
    invoiceNotes="invoiceNotes"
    invoiceAttachemnt="invoiceAttachemnt"
    saveButtonId="invoiceSave" />

<x-purchase-order-reject
    rejectModalId="rejectModal"
    modalTitle="Reject Purchase Order"
    rejectformId="rejectform"
    rejectId="reject_id"
    inputRejectMessage="reject_message"
    radioYes="reject_radioYes"
    radioNo="reject_redioNo"
    rejectNotifyWho="rejectnotify_who"
    rejectNotification="rejectNotification"
    rejectSms="rejectSms"
    rejectEmail="rejectEmail"
    saveButtonId="rejectSave" />
<x-purchase-order-email
    emailModalId="emailModal"
    modalTitle="email_modalTitle"
    emailformId="emailformId"
    foreignId="po_id"
    emailId="emailId"
    toField="toField"
    ccField="ccField"
    subject="emailsubject"
    selectBoxsubject="selectBoxsubject"
    body="emailbody"
    saveButtonId="emailSave"
    saveUrl="{{url('purchaseOrderEmailSave')}}"
     />
<x-bulk-invoice-received 
    bulInvoiceModalId="bulkInvoiceReceivedModal"
    modalTitle="bulkInvoiceReceivedModalLabel"
    bulInvoiceformId="bulkInvoiceReceivedForm"
    bulInvoiceId="bulkInvoiceReceivedId"
    saveButtonId="bulkInvoiceReceivedsave"
/>

<x-bulk-record-payment 
    bulkRecordPaymentModalId="bulkRecordPaymentModal"
    modalTitle="bulkRecordPaymentModalLabel"
    bulkRecordPaymentformId="bulkRecordPaymentForm"
    bulkRecordPaymentId="bulkRecordPayemntId"
    saveButtonId="bulkRecordPaymentsave"
/>
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
                var model = 'PurchaseOrder';
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
    $('#selectAllCheckBoxes').on('click', function () {
        $('.delete_checkbox').prop('checked', $(this).prop('checked'));
        shoeHideEmailSend(); 
  });
  function shoeHideEmailSend(){
    if($('.delete_checkbox:checked').length>1){
        $('.emialSend').show();
    }else{
        $('.emialSend').hide();
    }
  }
  $("#preview_purchase_orderBoxes").on('click', function(){
    let previewids = [];
    var name='';
    let error=0;
    $('.delete_checkbox:checked').each(function() {
        const row = $(this).closest('tr');
        const supplierName = row.get(0).querySelector('td:nth-child(6)').textContent.trim();
        if(name == ''){
            name=supplierName;
        }
        if(name !== supplierName){
            error=1;
            return false;
        }
        previewids.push($(this).val());
    });
    if(error){
        alert("Sorry, you cannot preview purchase orders for multiple customers.");
        return false;
    }
    if (previewids.length == 0) {
        alert("Please select at least one purchase order for preview.");
        return false;
    }else{
        // location.href="<?php echo url('preview-purchase-orders?key=');?>"+previewids;
        window.open("<?php echo url('preview-purchase-orders?key=');?>" + previewids, "_blank");
    }
  });
  $("#approveBtn").on('click',function(){
    let approveids = [];

    $('.delete_checkbox:checked').each(function() {
        approveids.push($(this).val());
    });
    if (approveids.length == 0) {
        alert("Please select at least one purchase order for approval.");
    }else{
        var token='<?php echo csrf_token();?>';
        // var url = `{{ url('purchase_order_approveMultiple') }}?key=${approveids.join(',')}`;
        var url=`{{ url('purchase_order_approveMultiple') }}`;
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ approveids: approveids })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            // return false;
            if(data.success === true){
                location.reload();
            }else{
                alert("Something went wrong");
                return false;
            }
            
        })
        .catch(error => {
            // $('.catsuccessdanger').text('There was an error submitting the form.');
            console.error("Error: ",error);
        });
    }
  });
  function email_sendCheck(type){
        let emailids = [];
        var name='';
        var ref='';
        let error=0;
        var SupplierEmail='';
        $('.delete_checkbox:checked').each(function() {
            const row = $(this).closest('tr');
            var supplierNameEmail = row.get(0).querySelector('td:nth-child(6)').textContent.trim();
            ref = row.get(0).querySelector('td:nth-child(3)').textContent.trim();
            SupplierEmail = $('#supplierEmail'+$(this).val()).val();
            
            if(name == ''){
                name=supplierNameEmail;
            }
            if(name !== supplierNameEmail){
                error=1;
                return false;
            }
            emailids.push($(this).val());
        });
        if(error){
            alert("Sorry, you cannot email purchase orders for multiple suppliers.");
            return false;
        }
        if (emailids.length == 0) {
            alert("Please select at least one purchase order for Email.");
            return false;
        }else{
            // window.open("<?php echo url('preview-purchase-orders?key=');?>" + emailids, "_blank");
            $("#emailformId")[0].reset();
            $("#dropdownButton").html('');
            $("#dropdownButton").append('<span class="optext">' + SupplierEmail + '&emsp;<b class="removeSpan" onclick="removeSpan(this)">X</b></span>');
            $("#selectedToEmail").val(SupplierEmail);
            $("#email_modalTitle").text("Email Purchase Order");
            $("#emailsubject").val("Purchase Order from The Contructor - {PO_REF}");
            if(type == 1){
                $("#emailsubject").val("Purchase Order from The Contructor - " + ref);
            }
            $("#email_po_id").val(emailids);
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
                        emptyTable: '<span style="color: #e10078; font-weight: bold;">Sorry, there are no items available</span>',
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
                    paginationControlsProductDetail.append('<button type="button" class="profileDrop" onclick="getProductDetail(' + id + ', \'' + paginationContact.prev_page_url + '\')">Previous</button>');
                }
                if (paginationProductDetails.next_page_url) {
                    paginationControlsProductDetail.append('<button type="button" class="profileDrop" onclick="getProductDetail(' + id + ', \'' + paginationContact.next_page_url + '\')">Next</button>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                // location.reload();
            }
        });
    }
    document.addEventListener('input', function (event) {
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

    function openRecordPaymentModal(id, po_ref, supplier_name, total_amount, date, product_id, outstandingAmount,supplier_id) {
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
    function getAllPurchaseInvices(data){
        location.reload();
    }
$('#bulkInvoiceReceived').on('click', function(){
    const tableBody = document.querySelector(`#bulkInvoiceReceived_result tbody`);
    tableBody.innerHTML='';
    tableBody.innerHTML='<tr><td colspan="9" class="text text-danger text-center" id="norecorderror">Sorry, no records to show</td> </tr>';
    $("#bulkInvoiceReceivedModal").modal('show');
});
$('#BulkRecordPaymentBTN').on('click', function(){
    const tableBody = document.querySelector(`#bulkRecordPayment_result tbody`);
    tableBody.innerHTML='';
    tableBody.innerHTML='<tr><td colspan="8" class="text text-danger text-center" id="norecorderrorRecordPayment">Sorry, no records to show</td> </tr>';
    $("#bulkRecordPaymentModal").modal('show');
});

</script>
@include('frontEnd.salesAndFinance.jobs.layout.footer')