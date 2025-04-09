@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Expand Card')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<section class="wrapper px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-body">
                        <div class="col-md-4 col-lg-4 col-xl-4 ">
                            <div class="pageTitle">
                                <h3>Expend Card</h3>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                            <div class="jobsection justify-content-between">
                                <div class="jobsection">
                                    <a href="{{url('petty-cash/expend-card')}}" class="profileDrop button_green" id="active_inactive">Expend card</a>
                                    <a href="{{url('petty-cash/petty_cash')}}" class="profileDrop button_green">Cash</a>
                                </div>
                                <div class="d-flex justify-content-end gap-4 align-items-center">
                                    <div class="d-flex justify-content-end gap-2 align-items-center">
                                        <label for="fromDate"> From:</label>
                                        <input type="date" id="fromDate" class="form-control">
                                        <label for="ToDate"> To:</label>
                                        <input type="date" id="ToDate" class="form-control">
                                    </div>
                                    <a href="{{url('petty-cash/expend_card_add')}}" class="profileDrop button_green"><i class="fa-solid fa-plus"></i> Add</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                            <div class="mt-2">
                                <div class="balance_show">
                                    <h6>Closing Balance on Card = <span id="balanceOnCard">£0</span></h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                            <div class="cash_table">
                                <div class="table-responsive productDetailTable pt-3">
                                    <table id="" class="table mb-0" cellspacing="0" width="100%">
                                        <thead class="table-light">
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
                                                <!-- <th>Action</th> -->
                                            </tr>
                                        </thead>
                                        <tbody id="expend_result">
                                            <?php
                                            $enterInLoop = 0;
                                            $index = 0;
                                            if (!empty($previous_month_data) && $previous_month_data['previousbalanceOnCard'] != 0) {
                                                $enterInLoop = 1; ?>

                                                <tr>
                                                    <td>{{++$index}}</td>
                                                    <td>{{$previous_month_data['prvious_date']}}</td>
                                                    <td>£{{$previous_month_data['previousbalanceOnCard']}}</td>
                                                    <td>£{{$previous_month_data['previousfundAmount']}}</td>
                                                    <td colspan="6"></td>
                                                </tr>
                                            <?php } ?>
                                            <?php
                                            $sumBalanceFund = 0;
                                            $sumPurchaseCashIn = 0;
                                            $totalBalancebfwd = 0;
                                            $totalBalanceFund = 0;
                                            $date = null;
                                            foreach ($expendCard as $val) {
                                                $sumPurchaseCashIn = $sumPurchaseCashIn + $val->purchase_amount;
                                                $totalBalanceFund = $totalBalanceFund + $val->fund_added;
                                                if ($enterInLoop == 0) {
                                                    $totalBalancebfwd = $val->balance_bfwd;
                                                    $enterInLoop = 1;
                                                }
                                                $db_date = date('m', strtotime($val->expend_date));
                                            ?>
                                                <tr>
                                                    <td>{{++$index}}</td>
                                                    <td>{{date('Y-m-d',strtotime($val->expend_date))}}</td>
                                                    <?php if ($previous_month_data['previousbalanceOnCard'] == 0) {
                                                        if ($date != $db_date || $date == null) {
                                                            $date = $db_date; ?>
                                                            <td>£{{$val->balance_bfwd}}</td>
                                                        <?php } else { ?>
                                                            <td></td>
                                                    <?php }
                                                    } ?>
                                                    <td><?php if (isset($val->fund_added) && $val->fund_added != '') {
                                                            echo '£' . $val->fund_added;
                                                        } ?></td>
                                                    <td>£{{$val->purchase_amount}}</td>
                                                    <td>{{$val->card_details}}</td>
                                                    <td><a href="{{url('public/images/finance_petty_cash/'.$val->receipt)}}" target="_blank"><i class="fa-solid fa-eye"></i></a></td>
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
                                                </tr>
                                            <?php }
                                            if ($totalBalancebfwd == 0) {
                                                $sum = $totalBalanceFund + $previous_month_data['previousbalanceOnCard'];
                                            } else {
                                                $sum = $totalBalanceFund + $totalBalancebfwd;
                                            }
                                            $calculation = $sum - $sumPurchaseCashIn;
                                            $balanceOnCard = $calculation - $cash;
                                            ?>
                                            <input type="hidden" id="totalBalanceOnCard" value="{{$balanceOnCard;}}">
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <th colspan="2">Total</th>
                                                <th id="totalBalancebfwd">£{{($totalBalancebfwd) ? $totalBalancebfwd: $previous_month_data['previousbalanceOnCard']}}</th>
                                                <th id="totalBalanceFund">£{{$totalBalanceFund}}</th>
                                                <th id="sumPurchaseCashIn">£{{number_format($sumPurchaseCashIn, 2, '.', '')}}</th>
                                                <th colspan="5"></th>
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
    </div>
</section>

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

<script type="text/javascript" src="{{ url('public/js/salesFinance/petty_cash/expend_card.js') }}"></script>

@endsection