<!-- Add Incentive Suspended Modal Start -->
<div class="modal fade" id="IncentiveSuspendModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"> Suspended Incentive - Add </h4>
            </div>
            <form method="post" action="{{ url('/service/earning-scheme/incentive/suspend/save') }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label class="col-md-1 m-t-5 text-right">Upto Date</label>
                           <!-- <div class=""> -->
                               <!-- <input type="date" name="suspended_date" required class="form-control datetime-picker trans" type="text" value="" autocomplete="off" maxlength="10" readonly="">
                               <p>Enter the number of days to suspend reward incentive</p> -->
                            
                                <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="date col-md-10"> <!-- dpYears -->
                                   <input name="suspended_date" type="text" value="" readonly="" size="16" class="form-control date-supend" maxlength="10">
                                    <span class="input-group-btn add-on datetime-picker2">
                                        <input type="text" value="" name="" id="new-date-suspend" class="form-control date-btn3" autocomplete="off">
                                        <button class="btn supend btn-primary" type="button"><span class="glyphicon glyphicon-calendar"></span></button>
                                    </span>
                                </div>


                           <!--  </div> -->
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label class="col-md-1 m-t-5 text-right">Detail</label>
                            <div class="col-md-10">
                                <textarea name="suspended_detail" value="" rows="5" class="form-control" maxlength="1000"></textarea>
                                <p>Enter the details for suspension</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer incen-foot">
                    <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">

                    <input type="hidden" name="su_earning_incentive_id" value="" class="su_earn_inctv_id">
                    {{ csrf_field() }}
                    <button type="button" class="btn btn-default" data-dismiss="modal"> Close </button>
                    <button type="submit" class="btn btn-primary sbt-suspend-btn"> Save </button>
                </div>  
            </form>
            <div class="row"></div>
        </div>
    </div>
</div>
<!-- Add Incentive Suspended Modal End -->


<!-- View/Edit Incentive Suspended Modal Start -->
<div class="modal fade" id="ViewIncentiveSuspendModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"> Suspended Incentive - View </h4>
            </div>
            <form method="post" action="{{ url('/service/earning-scheme/incentive/suspend/edit') }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label class="col-md-1 m-t-5 text-right">Upto Date</label>
                           <!-- <div class="col-md-10">
                               <input type="date" name="edit_suspended_date" required class="form-control datetime-picker trans" type="text" value="" autocomplete="off" maxlength="10" readonly="">
                               <p>Enter the number of days to suspend reward incentive</p>
                            </div> -->
                            <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="date col-md-10"> <!-- dpYears -->
                               <input name="edit_suspended_date" type="text" value="" readonly="" size="16" class="form-control" maxlength="10">
                                <span class="input-group-btn add-on datetime-picker2">
                                    <input type="text" value="" name="" id="new-date-suspend-edit" class="form-control date-btn3" autocomplete="off" >
                                    <button class="btn supend btn-primary" type="button"><span class="glyphicon glyphicon-calendar"></span></button>
                                </span>
                            </div>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label class="col-md-1 m-t-5 text-right">Detail</label>
                            <div class="col-md-10">
                                <textarea name="edit_suspended_detail" value="" rows="5" class="form-control" readonly="" maxlength="1000"></textarea>
                                <p>Enter the details for suspension</p>
                            </div>
                        </div>
                    </div>
                </div>
               
                <div class="modal-footer incen-foot">
                    <input type="hidden" name="su_incentive_suspended_id" value="" class="">
                    <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                    {{ csrf_field() }}
                    <a href="{{ url('/service/earning-scheme/incentive/suspend/delete/') }}" class="btn btn-danger pull-left cancel_suspension"> Cancel Suspension </a>
                    <button type="button" class="btn btn-default" data-dismiss="modal"> Continue </button>
                  <!--   <button type="submit" class="btn btn-primary"> Save </button> -->
                </div>  
            </form>
            <div class="row"></div>
        </div>
    </div>
</div>
<!-- View/Edit Incentive Suspended Modal End -->

<script>
    $(document).ready(function() {

        today  = new Date; 
        $('#new-date-suspend').datetimepicker({
            format: 'dd-mm-yyyy',
            startDate: today,
            minView : 2
        }).on("change.dp",function (e) {
            var currdate =$(this).data("datetimepicker").getDate();
            var newFormat = currdate.getDate()+"-" +(currdate.getMonth() + 1)+"-"+currdate.getFullYear();
            $('.date-supend').val(newFormat);
        });
        $('#new-date-suspend').on('click', function(){
            $('#new-date-suspend').datetimepicker('show');
        });

        $( "#IncentiveSuspendModal" ).scroll(function() {
            $('#new-date-suspend').datetimepicker('place')
        });

        $('#new-date-suspend').on('change', function(){
            $('#new-date-suspend').datetimepicker('hide');
        });

        // $('#inc-suspend-date').on('click', function(){ console.log('1'); //alert(1); return false;
        //     $('#inc-suspend-date').datetimepicker('show');
        // });

        // $('#inc-suspend-date').on('change', function(){
        //     $('#inc-suspend-date').datetimepicker('hide');
        // });
    });
</script>

<!-- <script>
    $(document).ready(function() {

        today  = new Date; 
        $('.datetime-picker').datetimepicker({
            format: 'dd-mm-yyyy',
            startDate: today,
            minView : 2
        });

    });
</script> -->

<script>
    // suspended add time validations
    $(document).ready(function(){
        $(document).on('click','.sbt-suspend-btn', function(){
            var suspend_date = $('input[name=\'suspended_date\']').val();
            var suspend_detail = $('textarea[name=\'suspended_detail\']').val().trim();
            suspended_detail = jQuery.trim(suspend_detail);
            if(suspend_date == ''){
                $('input[name=\'suspended_date\']').addClass('red_border');
                return false;
            } else{
               $('input[name=\'suspended_date\']').removeClass('red_border');                    
            }
            if(suspended_detail == ''){
                $('textarea[name=\'suspended_detail\']').addClass('red_border');
                return false;
            } else{
               $('textarea[name=\'suspended_detail\']').removeClass('red_border');                    
            }
        });
    });
</script>

<script>
    $(document).ready(function(){
        // view the suspension (values set in modal)
        $('.incentive_suspended').on('click',function(){

            var su_earning_incentive_id = $(this).attr('su_ern_inc_id');
            var suspended_id = $(this).attr('su_incentive_suspended_id');

            $('input[name=\'su_earning_incentive_id\']').val(su_earning_incentive_id);

            if(suspended_id == '') {
                $('#IncentiveSuspendModal').modal('show');
            } else {

                $('.loader').show();
                $('body').addClass('body-overflow');

                $.ajax({
                    type : 'get',
                    url  : "{{ url('/service/earning-scheme/incentive/suspend/view/') }}"+'/'+suspended_id,
                    dataType : 'json',
                    success: function(resp) {
                        if(isAuthenticated(resp) == false){
                        return false;
                        }
                        var response = resp['response'];
                        if(response == true) {
                            var suspended_id = resp['su_spd_id'];
                            var edit_date    = resp['date'];
                            var edit_detail  = resp['detail'];

                            $('input[name=\'su_incentive_suspended_id\']').val(suspended_id);
                            $('input[name=\'edit_suspended_date\']').val(edit_date);
                            $('textarea[name=\'edit_suspended_detail\']').val(edit_detail);
                            $('#ViewIncentiveSuspendModal').modal('show');
                            setTimeout(function () {
                                autosize($("textarea"));
                            },200);

                        
                        } else {
                        $('.ajax-alert-err').find('.msg').text(COMMON_ERROR);
                        $('.ajax-alert-err').show();
                        setTimeout(function(){$(".ajax-alert-err").fadeOut()}, 5000);
                        }
                        $('.loader').hide();
                        $('body').removeClass('body-overflow');

                    }
                });
                return false;    
            }                
        });

        // remove the suspension of incentive
        $(document).on('click','.cancel_suspension', function(){

            var suspended_id = $('input[name=\'su_incentive_suspended_id\']').val();
            
            if(confirm("Are you sure you want to remove the suspension ?")) {
                var link = $(this).attr('href');
                var new_link = link+'?suspended_id='+suspended_id+'&service_user_id={{ $service_user_id }}';
                $(this).attr('href',new_link);
                return true;
            } else {
                return false;
            }
        });

    });
</script>

