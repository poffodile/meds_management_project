@include('frontEnd.petty_cash.layout.header')

<section class="main_section_page_petty px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4">
                <div class="pageTitle">
                    <h3>Petty Cash</h3>
                </div>
            </div>
            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="jobsection justify-content-between">
                    <div class="jobsection">
                        <a href="{{url('petty-cash/expend-card')}}" class="profileDrop button_green">Expend card</a>
                        <a href="{{url('petty-cash/petty_cash')}}" class="profileDrop button_green" id="active_inactive">Cash</a>
                    </div>
                    <div class="d-flex justify-content-end gap-4 align-items-center">
                        <div class="d-flex justify-content-end gap-2 align-items-center">
                            <label for="fromDate"> From:</label>
                            <input type="date" id="fromDate" class="form-control">
                            <label for="ToDate"> To:</label>
                            <input type="date" id="ToDate" class="form-control">
                        </div>
                        <a href="{{url('petty-cash/petty-cash-add')}}" class="profileDrop button_green"><i class="fa-solid fa-plus"></i> Add</a>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="mt-2">
                    <div class="balance_show">
                        <h6>Closing Petty Cash balance = <span id="PettyCashbalance">£0</span></h6>
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
                            <tbody id="cash_result">
                                <?php 
                                $total_balance=0;
                                $cash_out=0;
                                $count=0;
                                $balance_bfwd=0;
                                $petty_cashIn=0;
                                foreach($cash as $key=>$val){
                                    $total_balance=$total_balance+$val->balance_bfwd+$val->petty_cashIn;
                                    $cash_out=$cash_out+$val->cash_out;
                                    $petty_cashIn=$petty_cashIn+$val->petty_cashIn;
                                    if($count == 0){
                                        $count=1;
                                        $balance_bfwd=$val->balance_bfwd;
                                    }
                                ?>
                                <tr>
                                    <td>{{++$key}}</td>
                                    <td>{{date('Y-m-d',strtotime($val->cash_date))}}</td>
                                    <td>£{{$val->balance_bfwd}}</td>
                                    <td>£{{$val->petty_cashIn}}</td>
                                    <td>£{{$val->cash_out}}</td>
                                    <td>{{$val->card_details}}</td>
                                    <td><a href="{{url('public/images/finance_cash/'.$val->receipt)}}" target="_blank"><i class="fa-solid fa-eye"></i></a></td>
                                    <td><?php if($val->dext == 1){ echo "Yes";}else{ echo "No"; }?></td>
                                    <td><?php if($val->invoice_la == 1){ echo "Yes"; }else{ echo "No" ;}?></td>
                                    <td>{{$val->initial}}</td>
                                </tr>
                                <?php } $total_balanceInCash=$total_balance-$cash_out;?>
                            </tbody>
                            <input type="hidden" id="total_balanceInCash" value="{{$total_balanceInCash}}">
                            <tfoot>
                            <tr class="table-light">
                                    <th colspan="2">Total</th>
                                    <th id="total_balance">£{{$balance_bfwd}}</th>
                                    <th id="petty_cashIn">£{{$petty_cashIn}}</th>
                                    <th id="cash_out">£{{$cash_out}}</th>
                                    <th colspan="5"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    // 
    $(document).ready(function() {
    var total_balanceInCash=$("#total_balanceInCash").val();
    $("#PettyCashbalance").text("£"+Number(total_balanceInCash).toFixed(2));
    // alert(typeof(totalBalanceOnCard));
});
</script>
<script>
    var filterUrl="{{url('petty-cash/cash_filter')}}";
    var token="<?php echo csrf_token();?>";
</script>
<script type="text/javascript" src="{{ url('public/js/salesFinance/petty_cash/cash.js') }}"></script>
@include('frontEnd.petty_cash.layout.footer')