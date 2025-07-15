@extends('backEnd.layouts.master')
@section('title',' Expend Card')
@section('content')
<style>
    .success-message {
        position: relative;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -16%);
        padding: 10px 20px;
        background-color: #e2ffea;
        color: #078111;
        border: 1px solid #078111;
        border-radius: 2px;
        font-size: 13px;
        font-weight: bold;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 9999;
    }
    .error-message {
        position: relative;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -16%);
        padding: 10px 20px;
        background-color: #f8d7da;
        color: #b50111;
        border: 1px solid #f5c6cb;
        border-radius: 2px;
        font-size: 13px;
        font-weight: bold;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 9999;
    }
</style>
<!--main content start-->
<section id="main-content">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">
                    <header class="panel-heading">
                        Expend Card
                        <!-- <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-cog"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span> -->
                    </header>
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            <h4>Closing Balance on Card = <span id="balanceOnCard">£0.00</span></h4>
                             <div class="col-lg-3 col-sm-3" style="margin-bottom:15px">
                                <label for="inputName" class="control-label">Month:</label>
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
                            </div>
                            <div class="col-lg-3 col-sm-3" style="margin-bottom:15px">
                                <label for="inputName" class="control-label">Year:</label>
                                <select name="year" id="year" class="form-control">
                                    <option selected disabled>Select Year</option>
                                    @foreach($years as $year)
                                        <option value="{{$year}}">{{$year}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="clearfix clearfix_space">
                                

                                <div class="btn-group">
                                    <a href="javascript:void(0)" id="editable-sample_new" data-toggle="modal" data-target="#expend_card" data-action="add" class="btn btn-primary openModalBtn"> Add New <i class="fa fa-plus"></i>
                                    </a>
                                    
                                </div>
                                <div class="btn-group">
                                    <a href="{{ url('admin/sales-finance/expend-card') }}" class="btn btn-primary "> Expend</a>
                                </div> 
                                <div class="btn-group">
                                    <a href="{{ url('admin/sales-finance/petty-cash') }}" class="btn btn-primary "> Cash</a>
                                </div>  
                                <!-- <div class="btn-group">
                                    <button class="btn btn-default"> Export </button>
                                </div> -->
                            </div>
                            <div class="space15"></div>
                            <table class="display table table-bordered table-striped" id="expend_cash_table">
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
        <!-- page end-->
    </div>
<!-- Modal start here -->
    <div class="modal fade popupcloseBtn in" id="expend_card" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header terques-bg">
                <h5 class="modal-title pupTitle" id="expend_cardLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body pdbotm">
	            <div class="alert text-center" id="message_save" style="display:none;height:50px">         	
	            </div>
                <form id="expend_cardForm" class="customerForm">
                    <input type="hidden" id="id" name="id" value="">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-6">
                                <div class="form-group row">
                                    <label for="inputName" class="col-sm-4 control-label">Date <span class="radStar">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="date" class="form-control" id="expend_date" name="expend_date" value="" max="">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputName" class="col-sm-4 control-label">Fund added to card</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control numberInput" id="fund_added" name="fund_added" value="">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputProject"
                                        class="col-sm-4 control-label">Purchases </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control numberInput" id="purchase_amount" name="purchase_amount">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputName" class="col-sm-4 control-label">Card Details <span class="radStar">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control " id="card_details" name="card_details" value="">
                                    </div>
                                </div>
                                
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <!-- <div class="form-group row">
                                <label for="inputName" class="col-sm-4 control-label">User <span class="radStar">*</span></label>
                                <div class="col-sm-8">
                                    <select name="loginUserId" id="loginuserid" class="form-control ">
                                        <option selected disabled>Select User</option>
                                        @foreach($users as $user)
                                            <option value="{{$user->id}}">{{$user->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> -->
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 control-label">Receipt</label>
                                <div class="col-sm-10">
                                    <input type="file" class="" onchange="check_file()" id="receipt" name="receipt" value="">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-4 control-label">Uploaded to DEXT</label>
                                <div class="col-sm-8">

                                    <label class="radio-inline">
                                        <input type="radio"  name="dext" id="authorised1" value="1">Yes
                                    </label>
                                    <label class="radio-inline">
                                            <input type="radio" name="dext" id="authorised0" value="0" checked="">No
                                    </label>

                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-4 control-label">Invoice LA</label>
                                <div class="col-sm-8">

                                    <label class="radio-inline">
                                        <input type="radio"  name="invoice_la" id="authorised1" value="1">Yes
                                    </label>
                                    <label class="radio-inline">
                                            <input type="radio" name="invoice_la" id="authorised0" value="0" checked="">No
                                    </label>

                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-4 control-label">Initials</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control " id="initial" name="initial" value="">
                                </div>
                            </div>
                            
                        </div>
                        
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Close</button>
                <button type="button" id="save_data" class="btn btn-primary" onclick="save_expend_card()">Add</button>
            </div>
        </div>
    </div>
</div>
<!-- end here -->
</section>

<script>
    $(document).ready(function() {
        var totalBalanceOnCard = $("#totalBalanceOnCard").val();
        $("#balanceOnCard").text("£" + Number(totalBalanceOnCard).toFixed(2));
        // alert(typeof(totalBalanceOnCard));
    });
</script>

<script>
    var filterUrl = "{{url('admin/sales-finance/expand_card_filter')}}";
    var token = "<?php echo csrf_token(); ?>";
</script>
<script>
    var saveUrl = "{{url('admin/sales-finance/expend-card/saveExpend')}}";
    var editUrl = "{{url('admin/sales-finance/expend-card/editExpend')}}";
    var redirectUrl = "{{url('admin/sales-finance/expend-card')}}";
    var getAllExpendCash="{{url('admin/sales-finance/getAllExpendCard')}}";
    var receipt_imag_src='{{url("public/images/finance_petty_cash/")}}';
    var deleteUrl="{{url('admin/sales-finance/expend-card/expend_delete')}}";
    var existImage="{{url('public/images/noimage.jpg')}}";
    // var check_CardclosingAmount="{{url('petty-cash/check_CardclosingAmount')}}";
</script>
<script type="text/javascript" src="{{ url('public/js/salesFinance/petty_cash/expend_card.js') }}"></script>
@endsection