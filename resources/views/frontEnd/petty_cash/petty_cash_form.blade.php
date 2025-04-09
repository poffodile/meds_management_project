@include('frontEnd.petty_cash.layout.header')
<style>
    .disabled-tab {
        pointer-events: none;
        opacity: 0.5;
    }
</style>
<section class="main_section_page_petty px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Petty Cash Form</h3> 
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="jobsection">
                <a href="{{url('petty-cash/expend-card')}}" class="profileDrop button_green">Expend card</a>
                <a href="{{url('petty-cash/petty_cash')}}" class="profileDrop button_green"  id="active_inactive">Cash</a>
                </div>
            </div>
        </div>
        <div calss="row">
            <div class="col-md-6 col-lg-6 col-xl-6 mt-4">
                <div class="mt-1 mb-0 text-center" style="display:none" id="message_save"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12"> 
                <div class="newJobForm green_border card mt-4">
                    <form id="cashForm">
                        @csrf
                        <input type="hidden" id="id" name="id" value="">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Date</label>
                                    <div class="col-md-8">
                                        <input type="date" class="form-control editInput checkInput" id="cash_date" name="cash_date">
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Balance b/fwd</label>
                                    <div class="col-md-8">
                                        <?php if($previous_Cash_month_data['total_balanceInCash'] == 0){?>
                                        <input type="text" class="form-control editInput numberInput checkInput <?php if(isset($cash) && $cash !=''){ echo "disabled-tab"; }?>" id="balance_bfwd" name="balance_bfwd" <?php if(isset($cash) && $cash !=''){?> value="{{$cash->balance_bfwd}}" <?php }?> onkeypress="return event.charCode >= 48 && event.charCode <= 57 && value.length<10">
                                        <?php }else{?>
                                        <input type="text" class="form-control editInput numberInput checkInput disabled-tab" id="balance_bfwd" name="balance_bfwd"  value="{{$previous_Cash_month_data['total_balanceInCash']}}" onkeypress="return event.charCode >= 48 && event.charCode <= 57 && value.length<10">
                                        <?php }?>
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Petty Cash In</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput numberInput checkInput" id="petty_cashIn" name="petty_cashIn" onkeypress="return event.charCode >= 48 && event.charCode <= 57 && value.length<10">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Cash Out </label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput numberInput checkInput" id="cash_out" name="cash_out" onkeypress="return event.charCode >= 48 && event.charCode <= 57 && value.length<10">
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Cash Details</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput checkInput" id="card_details" name="card_details">
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Receipt</label>
                                    <div class="col-md-8">
                                        <input type="file" class="form-control editInput checkInput" id="receipt" name="receipt" onchange="check_file()">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Uploaded to DEXT</label>
                                    <div class="col-md-8">
                                        <div class="col-sm-9 col-form-label nq_input">
                                            <input type="radio" name="dext" id="yes" value="1">
                                            <label for="yes">Yes</label>
                                            <input type="radio" name="dext" id="no" value="0" checked>
                                            <label for="no">NO</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Invoice LA</label>
                                    <div class="col-md-8">
                                        <div class="col-sm-9 col-form-label nq_input">
                                            <input type="radio" name="invoice_la" id="yes2" value="1">
                                            <label for="yes2">Yes</label>
                                            <input type="radio" name="invoice_la" id="no2" value="0" checked>
                                            <label for="no2">NO</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Initials</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput checkInput" id="initial" name="initial">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="pageTitleBtn">
                    <a href="javascript:void(0)" onclick="saveCash()" class="profileDrop button_green"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                    <a href="{{url('petty-cash/petty_cash')}}" class="profileDrop button_green"><i class="fa-solid fa-arrow-left"></i> Back</a>
                    <!-- <a href="#" class="profileDrop button_green"> Action <i class="fa-solid fa-arrow-down"></i></a> -->
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    var saveUrl="{{url('petty-cash/saveCash')}}";
    var redirectUrl="{{url('petty-cash/petty_cash')}}";
</script>
<script type="text/javascript" src="{{ url('public/js/salesFinance/petty_cash/cash.js') }}"></script>
@include('frontEnd.petty_cash.layout.footer')