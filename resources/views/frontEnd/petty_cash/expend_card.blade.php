@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Expand Card')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')
<style>
    .disabled-tab {
        pointer-events: none;
        opacity: 0.5;
    }
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
</style>

<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        <h4>Expand Card</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12">
                            <div class="jobsection justify-content-between align-items-center">
                                <div class="d-flex justify-content-end gap-2 align-items-center">
                                    <label for="fromDate" class="mb-0"> Month:</label>
                                    <!-- <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                                        <input name="date_of_birth" id="fromDate" type="text" value="" autocomplete="off" class="form-control no_input">

                                        <span class="input-group-btn datetime-picker2 btn_height">
                                            <button class="btn btn-primary" type="button" id="openCalendarBtn">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </button>
                                        </span>
                                    </div> -->
                                    <select name="month" id="month" class="form-control">
                                        <option selected disabled>Select Month</option>
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">August</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                    <label for="ToDate" class="mb-0"> Year:</label>
                                    <!-- <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                                        <input name="date_of_birth" id="ToDate" type="text" value="" autocomplete="off" class="form-control no_input">

                                        <span class="input-group-btn datetime-picker2 btn_height">
                                            <button class="btn btn-primary" type="button" id="openCalendarBtn1">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </button>
                                        </span>
                                    </div> -->
                                    <select name="year" id="year" class="form-control">
                                        <option selected disabled>Select Year</option>
                                        @foreach($years as $year)
                                            <option value="{{$year}}">{{$year}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                <!-- <a href="{{url('petty-cash/expend_card_add')}}" class="profileDrop"><i class="fa fa-plus"></i> Add</a> -->
                                    <div class="jobsection mb-0">
                                        <a href="javascript:void(0)" class="btn btn-warning openModalBtn" data-action="add" data-toggle="modal" data-target="#expend_card"><i class="fa fa-plus"></i> Add</a>
                                        <a href="{{url('petty-cash/expend-card')}}" class="btn btn-warning" id="active_inactive">Expend card</a>
                                        <a href="{{url('petty-cash/petty_cash')}}" class="btn btn-warning">Cash</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="mt-2">
                                <div class="balance_show">
                                    <h6>Closing Balance on Card = <span id="balanceOnCard">£0</span></h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="maimTable productDetailTable mb-4 table-responsive">
                                <table class="table border-top border-bottom tablechange" id="expend_cash_table" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Balance b/fwd</th>
                                            <th>Funds added to card</th>
                                            <th>Purchases</th>
                                            <th>Card Details</th>
                                            <th>Receipt</th>
                                            <th>Uploaded to DEXT</th>
                                            <th>Invoice LA</th>
                                            <th>Initials</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="expend_result">
                                        
                                        <input type="hidden" id="totalBalanceOnCard" value="">
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <th colspan="2">Total</th>
                                            <th id="totalBalancebfwd">£0</th>
                                            <th id="totalBalanceFund">£0</th>
                                            <th id="sumPurchaseCashIn">£0</th>
                                            <th colspan="6"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Expend Card Modal start here -->
<div class="modal fade" id="expend_card" tabindex="-1" aria-labelledby="expend_cardLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="expend_cardLabel">Add Expend Card</h4>
            </div>
            <div class="row">
                <div class="col-md-12 col-lg-12 col-xl-12 mt-4">
                    <div class="mt-1 mb-0 text-center" style="display:none" id="message_save"></div>
                </div>
            </div>
            <form id="expend_cardForm">
                <input type="hidden" id="id" name="id" value="">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label> Date <span class="radStar">*</span></label>
                                    <div>
                                        <input type="date" class="form-control editInput checkInput" name="expend_date" id="expend_date" value="" max="">
                                    </div>
                                </div>
                                <!-- <div class="form-group col-md-12">
                                    <label> Balance b/fwd <span class="radStar">*</span></label>
                                    <div> -->
                                        
                                    <!-- <input type="hidden" class="form-control editInput numberInput" id="balance_bfwd" name="balance_bfwd" <?php if (isset($expendCardLastData) && $expendCardLastData != '') { ?> value="{{$expendCardLastData->balance_bfwd}}" <?php } else{?>value="{{$previous_month_data['previousbalanceOnCard']}}"<?php }?>  onkeypress="return event.charCode >= 48 && event.charCode <= 57 && value.length<10"> -->
                                        
                                    <!-- </div>
                                </div> -->
                                <div class="form-group col-md-12">
                                    <label> Fund added to card</label>
                                    <div>
                                        <input type="text" class="form-control editInput numberInput" id="fund_added" name="fund_added" onkeypress="return event.charCode >= 48 && event.charCode <= 57 && value.length<10">
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Purchases</label>
                                    <div>
                                        <input type="text" class="form-control editInput numberInput" id="purchase_amount" name="purchase_amount" onkeypress="return event.charCode >= 48 && event.charCode <= 57 && value.length<10">
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    <label> Card Details <span class="radStar">*</span></label>
                                    <div>
                                        <input type="text" class="form-control editInput checkInput" id="card_details" name="card_details">
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Receipt</label>
                                    <!-- <div>
                                        <input type="file" class="form-control editInput" id="receipt" name="receipt" onchange="check_file()">
                                    </div> -->
                                    <div class="col-md-12 p-0">
                                        <div class="fileupload fileupload-new" data-provides="fileupload">
                                            <div class="fileupload-new thumbnail" id="exist_image" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
                                                <img src="{{url('public/images/noimage.jpg')}}" alt="No Image" />
                                            </div>
                                            <div class="fileupload-preview fileupload-exists thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 20px;"></div>
                                            <div>
                                                <span class="btn btn-white btn-file">
                                                    <span class="fileupload-new"><i class="fa fa-paper-clip"></i> Select image</span>
                                                    <span class="fileupload-exists"><i class="fa fa-undo"></i> Change</span>
                                                    <input name="receipt" type="file" class="default" id="receipt" onchange="check_file()" />
                                                </span>
                                                <!-- <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload"><i class="fa fa-trash"></i>Remove</a> -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Uploaded to DEXT</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="form-check-label m-0" for="yes">Yes</label>
                                        <input class="form-check-input mt-0" type="radio" name="dext" value="1" id="yes">
                                        <label class="form-check-label m-0" for="no">No</label>
                                        <input class="form-check-input mt-0" type="radio" name="dext" value="0" id="no" checked>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Invoice LA</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="form-check-label m-0" for="yes2">Yes</label>
                                        <input class="form-check-input mt-0" type="radio" name="invoice_la" value="1" id="yes2">
                                        <label class="form-check-label m-0" for="no2">No</label>
                                        <input class="form-check-input mt-0" type="radio" name="invoice_la" value="0" id="no2" checked>
                                    </div>
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Initials</label>
                                    <div>
                                        <input type="text" class="form-control editInput" id="initial" name="initial">
                                    </div>
                                </div>
                            </div>
                        </div> <!-- End row -->
                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="" onclick="save_expend_card()">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- end here -->

<script>
    $(document).ready(function() {
        var totalBalanceOnCard = $("#totalBalanceOnCard").val();
        $("#balanceOnCard").text("£" + Number(totalBalanceOnCard).toFixed(2));
        // alert(typeof(totalBalanceOnCard));
    });
</script>

<script>
    var filterUrl = "{{url('petty-cash/expand_card_filter')}}";
    var token = "<?php echo csrf_token(); ?>";
</script>
<script>
    var saveUrl = "{{url('petty-cash/saveExpend')}}";
    var editUrl = "{{url('petty-cash/editExpend')}}";
    var redirectUrl = "{{url('petty-cash/expend-card')}}";
    var getAllExpendCash="{{url('petty-cash/getAllExpendCash')}}";
    var receipt_imag_src='{{url("public/images/finance_petty_cash/")}}';
    var deleteUrl="{{url('petty-cash/expend_delete')}}";
    var existImage="{{url('public/images/noimage.jpg')}}";
    var check_CardclosingAmount="{{url('petty-cash/check_CardclosingAmount')}}";
</script>
<script>
    $(document).ready(function() {
        $('#fromDate').datetimepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true,
            minView: 2
        });

        $('#openCalendarBtn').click(function() {
            $('#fromDate').focus();
        });
        $('#ToDate').datetimepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true,
            minView: 2
        });

        $('#openCalendarBtn1').click(function() {
            $('#ToDate').focus();
        });
    });
</script>

<script type="text/javascript" src="{{ url('public/js/salesFinance/petty_cash/expend_card.js') }}"></script>

@endsection