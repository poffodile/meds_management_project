@extends('backEnd.layouts.master')
@section('title',' Petty Cash')
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
                        Cash
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
                            <h4>Closing Petty Cash balance = <span id="PettyCashbalance">£0.00</span></h4>
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
                                    <a href="javascript:void(0)" data-action="add" data-toggle="modal" data-target="#petty_cash" class="btn btn-primary openModalBtn"> Add New <i class="fa fa-plus"></i>
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
                            <table class="display table table-bordered table-striped" id="petty_cash_table">
                                <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Balance b/fwd</th>
                                            <th>Petty Cash In</th>
                                            <th>Cash Out</th>
                                            <th>Cash Details</th>
                                            <th>Receipt</th>
                                            <th>Uploaded to DEXT</th>
                                            <th>Invoice LA</th>
                                            <th>Initials</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cash_result">
                                        <?php
                                        $total_balance = 0;
                                        $cash_out = 0;
                                        $count = 0;
                                        $balance_bfwd = 0;
                                        $petty_cashIn = 0;
                                        $index = 0;
                                        if (!empty($previous_Cash_month_data) && $previous_Cash_month_data['total_balanceInCash'] != 0) {
                                            $count = 1; ?>

                                            <tr>
                                                <td>{{++$index}}</td>
                                                <td>{{$previous_Cash_month_data['prvious_date']}}</td>
                                                <td>£{{$previous_Cash_month_data['total_balanceInCash']}}</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        <?php }
                                        foreach ($cash as $key => $val) {
                                            // $total_balance = $total_balance + $val->balance_bfwd + $val->petty_cashIn;
                                            $cash_out = $cash_out + $val->cash_out;
                                            $petty_cashIn = $petty_cashIn + $val->petty_cashIn;
                                            if ($count == 0) {
                                                $count = 1;
                                                $balance_bfwd = $val->balance_bfwd;
                                            }
                                            ?>
                                            <tr>
                                                <td>{{++$index}}</td>
                                                <td>{{date('Y-m-d',strtotime($val->cash_date))}}</td>
                                                <td></td>
                                                <td>£{{$val->petty_cashIn ?? 0}}</td>
                                                <td>£{{$val->cash_out ?? 0}}</td>
                                                <td>{{$val->card_details}}</td>
                                                @if($val->receipt)
                                                    <td><a href="{{url('public/images/finance_cash/'.$val->receipt)}}" target="_blank"><i class="fa fa-eye"></i></a></td>
                                                @else
                                                    <td></td>
                                                @endif
                                                <td><?php if ($val->dext == 1) {
                                                        echo "Yes";
                                                    } else {
                                                        echo "No";
                                                    } ?></td>
                                                <td><?php if ($val->invoice_la == 1) {
                                                        echo "Yes";
                                                    } else {
                                                        echo "No";
                                                    } ?></td>
                                                <td>{{$val->initial}}</td>
                                                <td><a href="javascript:void(0)" class="openModalBtn" data-toggle="modal" data-target="#petty_cash" data-action="edit" data-id="{{ $val->id }}" data-cash_date="{{ $val->cash_date }}" data-balance_bfwd="{{ $val->balance_bfwd }}" data-petty_cashin="{{ $val->petty_cashIn }}" data-cash_out="{{ $val->cash_out }}" data-card_details="{{ $val->card_details }}" data-receipt="{{ $val->receipt }}" data-dext="{{ $val->dext }}" data-invoice_la="{{ $val->invoice_la }}" data-initial="{{ $val->initial }}" id=""><i class="fa fa-pencil" aria-hidden="true"></i></a> | <a href="javascript:void(0)" class="deleteBtn" data-id="{{ $val->id }}"><i class="fa fa-trash radStar" aria-hidden="true"></i></a></td>
                                            </tr>
                                        <?php }
                                        $sumCash = $petty_cashIn + (($balance_bfwd == 0) ? $previous_Cash_month_data['total_balanceInCash'] : $balance_bfwd);
                                        $total_balanceInCash = $sumCash - $cash_out; ?>
                                    </tbody>
                                        <input type="hidden" id="total_balanceInCash" value="<?php echo $total_balanceInCash;?>">
                                    <tfoot>
                                        <tr class="table-light">
                                            <th colspan="2">Total</th>
                                            <th id="total_balance">£{{$balance_bfwd}}</th>
                                            <th id="petty_cashIn">£{{$petty_cashIn}}</th>
                                            <th id="cash_out">£{{$cash_out}}</th>
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
    <div class="modal fade popupcloseBtn in" id="petty_cash" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header terques-bg">
                <h5 class="modal-title pupTitle" id="petty_cashLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body pdbotm">
	            <div class="alert text-center" id="message_save" style="display:none;height:50px">         	
	            </div>
                <form id="cashForm" class="customerForm">
                    <input type="hidden" id="id" name="id" value="">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-6">
                                <div class="form-group row">
                                    <label for="inputName" class="col-sm-4 control-label">Date <span class="radStar">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="date" class="form-control checkInput" id="cash_date" name="cash_date" value="" max="">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputName" class="col-sm-4 control-label">Petty Cash In</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control numberInput" id="petty_cashInModal" name="petty_cashIn" value="">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputProject"
                                        class="col-sm-4 control-label">Cash Out </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control numberInput" id="cash_outModal" name="cash_out">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputName" class="col-sm-4 control-label ">Cash Details <span class="radStar">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control checkInput" id="card_details" name="card_details" value="">
                                    </div>
                                </div>
                                
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-6">
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
                <button type="button" id="save_data" class="btn btn-primary" onclick="saveCash()">Add</button>
            </div>
        </div>
    </div>
</div>
<!-- end here -->
</section>

<script>
    // 
    $(document).ready(function() {
        getDatatable();
        var total_balanceInCash = $("#total_balanceInCash").val();
        $("#PettyCashbalance").text("£" + Number(total_balanceInCash).toFixed(2));
        // alert(typeof(totalBalanceOnCard));
    });
</script>
<script>
    var filterUrl = "{{url('admin/sales-finance/petty-cash/cash_filter')}}";
    var token = "<?php echo csrf_token(); ?>";
    var saveUrl = "{{url('admin/sales-finance/petty-cash/saveCash')}}";
    var editUrl = "{{url('admin/sales-finance/petty-cash/editCash')}}";
    var redirectUrl = "{{url('admin/sales-finance/petty-cash')}}";
    var imgSrc = "{{url('public/images/finance_cash/')}}";
    var deleteUrl="{{url('admin/sales-finance/petty-cash/cash_delete')}}";
    var existImage="{{url('public/images/noimage.jpg')}}";
</script>
<script type="text/javascript" src="{{ url('public/js/salesFinance/petty_cash/cash.js') }}"></script>
@endsection