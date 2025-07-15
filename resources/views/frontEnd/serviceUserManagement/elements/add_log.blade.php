<!-- Log Book Modal -->
<div class="modal fade" id="addLogModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Daily Log</h4>
            </div>
            <div class="modal-body">
                @include('frontEnd.common.popup_alert_messages')
                <div class="row">

                    <form id="su-log-book-form">
                        @csrf
                        <div class="add-new-box risk-tabs custm-tabs">

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0"><!-- add-rcrd -->
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> User: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-style">
                                        <select name="service_user_id" class='su_name' <?php if (isset($_GET['key'])) {
                                                                                            echo "disabled";
                                                                                        } ?> />
                                        <!-- <option value="{{$service_user_id}}">{{ $service_user_name }}</option> -->
                                        @foreach($service_users as $val)
                                        <option <?php if (isset($_GET['key'])) {
                                                    if ($_GET['key'] == $val->id) {
                                                        echo "Selected";
                                                    }
                                                } ?> value="{{$val->id}}">{{$val->name}}</option>
                                        @endforeach

                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Title: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-bi" style="width:100%;float:left;">
                                        <input type="text" class="form-control" placeholder="" name="log_title" />
                                    </div>
                                    <p class="help-block"> Enter the Title of Log and add details below.</p>
                                </div>
                            </div>
                         
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0"><!-- add-rcrd -->
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Category: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-style">
                                        <select name="category" class='su_name' required>
                                            <option disabled selected value> -- select an option -- </option>
                                            @foreach ($categorys as $key )
                                            <option value="{{$key['id']}}">{{ $key['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 datepicker-sttng date-sttng">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Date: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date"> <!--  dpYears  -->
                                        <input name="log_date" id="daily_log_date" value="{{ date('d-m-Y H:i') }}" type="text" readonly="" size="16" class="form-control daily-log-book-datetime">
                                        <span class="input-group-btn add-on datetime-picker2">
                                            <input type="text" value="" name="" id="log-book-datetimepicker" autocomplete="off" class="form-control date-btn2">
                                            <button class="btn btn-primary" type="button"><span class="glyphicon glyphicon-calendar"></span></button>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Details: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-bi">
                                        <textarea name="log_detail" class="form-control detail-info-txt log-detail" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- new image -->
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Image: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-bi">
                                        <input type="file" name="log_image" class="form-control detail-info-txt log-image">
                                    </div>
                                </div>
                            </div>
                            <!-- new image -->
                        

                            <div class="form-group modal-footer m-t-0 modal-bttm">
                                <button class="btn btn-default cancel-log" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                                <input type="hidden" name="id" value="">
                                <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button class="btn btn-warning submit-log hide-field" type="button"> Submit </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        $('#addLogModal').on('shown.bs.modal', function(e) {
            let currentDateTime = moment().format('DD-MM-YYYY HH:mm');
            $('#daily_log_date').val(currentDateTime);
        });

        var today = new Date;
        $('#log-book-datetimepicker').datetimepicker({
            format: 'dd-mm-yyyy',
            // endDate: today,
            // minView : 2

        }).on("change.dp", function(e) {
            var currentdate = $(this).data("datetimepicker").getDate();
            var newFormat = ("0" + currentdate.getDate()).slice(-2) + "-" + ("0" + (currentdate.getMonth() + 1)).slice(-2) + "-" +
                currentdate.getFullYear() + " " + ("0" + currentdate.getHours()).slice(-2) + ":" + ("0" + currentdate.getMinutes()).slice(-2);

            $('.daily-log-book-datetime').val(newFormat);
        });

        $('#log-book-datetimepicker').on('click', function() {
            $('#log-book-datetimepicker').datetimepicker('show');
        });

        $("#logBookModal").scroll(function() {
            $('#log-book-datetimepicker').datetimepicker('place')
        });

        $('#log-book-datetimepicker').on('change', function() {
            $('#log-book-datetimepicker').datetimepicker('hide');
        });
    });
</script>

<!-- Add Log to ServiceUser LogBook -->
<script>
    $('.cancel-log').click(function() {
        $('select[name=\'category\']').val('');
        $('input[name=\'log_title\']').val('');
        $('#daily_log_date').val('');
        $('textarea[name=\'log_detail\']').val('');
    });

    $('.submit-log').click(function() {

        var category = $('select[name=\'category\']').val();
        var log_title = $('input[name=\'log_title\']').val();
        var log_date = $('input[name=\'log_date\']').val();
        var log_image = $('input[name=\'log_image\']').val();
        var log_detail = $('.log-detail').val();
        var token = $('input[name=\'_token\']').val();

        //var formdata = $('#su-log-book-form').serialize();

        var error = 0;

        if (category == null) {
            $('select[name=\'category\']').addClass('red_border');
            error = 1;
        } else {
            $('select[name=\'category\']').removeClass('red_border');
        }

        // if(log_date == ''){ 
        //     $('input[name=\'log_date\']').addClass('red_border');
        //     error = 1;
        // }else{ 
        //     $('input[name=\'log_date\']').removeClass('red_border');
        // }

        // if(log_title == ''){ 

        //     $('input[name=\'log_title\']').addClass('red_border');
        //     error = 1;
        // }else{

        //     $('input[name=\'log_title\']').removeClass('red_border');
        // }

        // if(log_detail == ''){ 
        //     $('textarea[name=\'log_detail\']').addClass('red_border');
        //     error = 1;
        // }else{ 
        //     $('textarea[name=\'log_detail\']').removeClass('red_border');
        // }

        // if(log_image == ''){ 
        //     $('input[name=\'log_image\']').addClass('red_border');
        //     error = 1;
        // }else{ 
        //     $('input[name=\'log_image\']').removeClass('red_border');
        // }

        if (error == 1) {
            return false;
        }

        //alert(error);
        $('.loader').show();
        $('body').addClass('body-overflow');
        $.ajax({
            type: 'post',
            url: "{{ url('/service/logbook/add') }}",
            data: new FormData($("#su-log-book-form")[0]),
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            //dataType : 'json',

            success: function(resp) {
                alert(resp);
                console.log("resp", resp)
                if (isAuthenticated(resp) == false) {
                    return false;
                }

                if (resp == false) {
                    $('span.popup_error_txt').text('Error Occured', 'Try after sometime');
                    $('.popup_error').show();
                    setTimeout(function() {
                        $(".popup_error").fadeOut()
                    }, 5000);
                } else {

                    $('select[name=\'category\']').val('');
                    $('input[name=\'log_title\']').val('');
                    $('input[name=\'log_date\']').val('');
                    $('textarea[name=\'log_detail\']').val('');

                    //show success message
                    $('span.popup_success_txt').text('Daily log Added Successsfully');
                    $('.popup_success').show();
                    setTimeout(function() {
                        $(".popup_success").fadeOut();
                        $('.loader').hide();
                        $('body').removeClass('body-overflow');
                        location.reload();
                    }, 2000);
                }

                return false;
            }
        });
        return false;
    });
</script>

@include('frontEnd.serviceUserManagement.elements.handover_to_staff')