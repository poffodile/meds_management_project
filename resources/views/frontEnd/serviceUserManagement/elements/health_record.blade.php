<!-- Health Record Modal -->
<div class="modal fade" id="healthrecordModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{ $labels['health_record']['label'] }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form method="post" action="" id="add_health_record_form">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Add: </label>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                <input type="text" name="title" class="form-control health_record_input"
                                    maxlength="255">
                                <p class="help-block"> Enter appointment to be added to the schedule, view full calendar
                                    below </p>
                            </div>
                            <div class="col-md-1 col-sm-1 col-xs-1 p-0">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="service_user_id"
                                    value="{{ isset($service_user_id) ? $service_user_id : '' }}">
                                <button class="btn group-ico add_health_record_submit" type="submit"> <i
                                        class="fa fa-plus"></i> </button>
                            </div>
                        </div>
                    </form>
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="below-divider"></div>
                    </div>

                    <!-- alert messages -->
                    @include('frontEnd.common.popup_alert_messages')

                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <h3 class="m-t-0 m-b-20 clr-blue fnt-20"> Recent Records</h3>
                    </div>
                    <div class="row">
                        <form class="riskfilter">
                            <div class="form-group datepicker-sttng date-sttng">
                                <div class="col-md-6 col-sm-10 col-xs-12">
                                    <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date=""
                                        class="input-group date">
                                        <input id="date_range_inputhr" style="cursor: pointer;" name="daterange"
                                            value="{{ date('d-m-Y') }} - {{ date('d-m-Y') }}" type="text"
                                            value="" readonly="" size="16"
                                            class="form-control log-book-datetime">
                                        <span class="input-group-btn add-on datetime-picker2">
                                            <button onclick="showDate()" class="btn btn-primary" type="button"><span
                                                    class="glyphicon glyphicon-calendar"></span></button>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- <input type="date" id="birthday" class="input-date" name="birthday"> -->
                            <!-- <select name="select_riskcategory" id="select_riskcategory" class="select-control">
                                <option value="all">All </option>
                                <option value="0">No Risk </option>
                                <option value="1">Historic </option>
                                <option value="2">Live Risk </option>
                            </select> -->
                            <input type="text" id="keywordhr" class="input-area" onKeyPress="hrmyFunctionkey()"
                                onKeyUp="hrmyFunctionkey()" placeholder="keyword">
                        </form>
                    </div>
                    <form id="edit_health_record_form">
                        <div class="health_record_list">
                            <!-- list will be shown by ajax -->
                        </div>

                </div>
            </div>
            <div class="modal-footer btns m-t-0">
                <a class="su-botm-calndr" href="{{ url('/service/calendar/' . $service_user_id) }}">
                    <div class="pull-left"><i class="fa fa-calendar"></i></div>
                </a>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="service_user_id" class="hlth_su_id" value="{{ $service_user_id }}">
                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                <button class="btn btn-warning submit_edit_health_record" type="button"> Confirm </button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
    //showing model
    $(document).ready(function() {

        $('.health_record_view_btn').click(function() {

            $('.loader').show();
            $('body').addClass('body-overflow');

            var service_user_id = $('.selected_su_id').val();
            if (service_user_id == undefined) {
                service_user_id = "{{ $service_user_id }}";
            }

            $.ajax({
                type: 'get',
                url: "{{ url('/service/health-records') }}" + '/' + service_user_id,
                //dataType : 'json',
                success: function(resp) {

                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    if (resp == '') {
                        $('.health_record_list').html(
                            '<div class="text-center p-b-20" style="width:100%">No Records found.</div>'
                            );
                    } else {
                        $('.health_record_list').html(resp);
                    }

                    //$('.health_record_list').html(resp);
                    $('.health_record_input').val('');
                    $('#healthrecordModal').modal('show');
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        })
    });

    //saving record while adding
    $(document).ready(function() {

        $('.add_health_record_submit').click(function() {

            var title = $('.health_record_input').val();

            var service_user_id = $('.selected_su_id').val();
            if (service_user_id == undefined) {
                service_user_id = "{{ $service_user_id }}";
            }

            $('input[name=\'service_user_id\']').val(service_user_id);
            //alert(service_user_id); return false;

            title = jQuery.trim(title);
            if (title == '') {
                $('.health_record_input').addClass('red_border');
                return false;
            } else {
                $('.health_record_input').removeClass('red_border');
            }

            var formdata = $('#add_health_record_form').serialize();
            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'post',
                url: "{{ url('/service/health-record/add') }}",
                data: formdata,
                success: function(resp) {

                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    //checkResponseAuth
                    // isAuthenticated
                    if (resp == '0') {

                        $('span.popup_error_txt').text(
                            'Some Error Occured, Please try again later.');
                        $('.popup_error').show();
                    } else {

                        $('.health_record_input').val('');
                        $('.health_record_list').html(resp);

                        //show success message
                        $('span.popup_success_txt').text(
                        'Health Record Added Successfully');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $(".popup_success").fadeOut()
                        }, 5000);

                        $('.loader').hide();
                        $('body').removeClass('body-overflow');
                    }
                }
            });

            return false;
        })
    });
</script>

<script>
    //delete a row
    $(document).ready(function() {

        $(document).on('click', '.delete-health-record', function() {

            if (!confirm('{{ DEL_CONFIRM }}')) {
                return false;
            }

            var su_health_record_id = $(this).attr('su_health_record_id');

            //$(this).addClass('active_record');
            var this_record = $(this);
            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: "{{ url('/service/health-record/delete/') }}" + '/' + su_health_record_id,
                success: function(resp) {

                    if (isAuthenticated(resp) == false) {
                        return false;
                    }

                    if (resp == 1) {
                        this_record.closest('.delete-row').remove();

                        $('.loader').hide();
                        $('body').removeClass('body-overflow');

                        //show success delete message
                        $('span.popup_success_txt').text('Record Deleted Successfully');
                        $('.popup_success').show();

                    } else {

                        //show delete message error
                        $('span.popup_error_txt').text('Error Occured');
                        $('.popup_error').show();

                        $('.loader').hide();
                        $('body').removeClass('body-overflow');
                    }
                    setTimeout(function() {
                        $(".popup_success").fadeOut()
                    }, 5000);
                }
            });
            return false;

        });

    });
</script>

<script>
    //make a row editable
    $(document).ready(function() {
        $(document).on('click', '.edit_health_record', function() {

            $(this).closest('.delete-row').find('input').removeAttr('disabled');
            //$(this).closest('.pop-notifbox').hide();
            $(this).closest('.pop-notifbox').toggleClass('active');
            return false;
        });
    });

    //saving editable row
    $(document).ready(function() {
        $(document).on('click', '.submit_edit_health_record', function() {

            //check validations
            var error = 0;
            var enabled = 0;
            $('.edit_hlth_rcrd').each(function(index) {

                var is_disable = $(this).attr('disabled');

                if (is_disable == undefined) { //if it is not disabled
                    var title = $(this).val();
                    title = jQuery.trim(title);

                    if (title == '') {
                        $(this).addClass('red_border');
                        error = 1;
                    } else {
                        $(this).removeClass('red_border');
                    }
                    enabled = 1;
                }
            });

            if (error == 1) {
                return false;
            }
            if (enabled == 0) {
                return false;
            }

            var service_user_id = $('.selected_su_id').val();
            if (service_user_id == undefined) {
                service_user_id = "{{ $service_user_id }}";
            }
            $('.hlth_su_id').val(service_user_id);

            var formdata = $('#edit_health_record_form').serialize();
            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'post',
                url: "{{ url('/service/health-record/edit') }}",
                data: formdata,
                success: function(resp) {

                    if (isAuthenticated(resp) == false) {
                        return false;
                    }

                    $('.health_record_list').html(resp);
                    $('.health_record_input').removeClass('red_border');

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');

                    //show success message
                    $('span.popup_success_txt').text('Record Editted Successfully.');
                    $('.popup_success').show();
                    setTimeout(function() {
                        $(".popup_success").fadeOut()
                    }, 5000);
                }
            });
            return false;
        });
    });
</script>
<script>
    $('#date_range_inputhr').on('apply.daterangepicker', function(ev, picker) {
        let start_date = picker.startDate.format('YYYY-MM-DD');
        let end_date = picker.endDate.format('YYYY-MM-DD');
        let keyword = $('#keywordhr').val();
        var service_user_id = $('.selected_su_id').val();
        if (service_user_id == undefined) {
            service_user_id = "{{ $service_user_id }}";
        }
        $.ajax({
            type: 'get',
            url: "{{ url('/service/health-records') }}" + '/' + service_user_id + '?start_date=' +
                start_date + '&end_date=' + end_date + '&filter=1&keyword=' + keyword,
            //dataType : 'json',
            success: function(resp) {

                if (isAuthenticated(resp) == false) {
                    return false;
                }
                if (resp == '') {
                    $('.health_record_list').html(
                        '<div class="text-center p-b-20" style="width:100%">No Records found.</div>'
                        );
                } else {
                    $('.health_record_list').html(resp);
                }

                //$('.health_record_list').html(resp);
                $('.health_record_input').val('');
                $('#healthrecordModal').modal('show');
                $('.loader').hide();
                $('body').removeClass('body-overflow');
            }
        });
    });
</script>
<script>
    function hrmyFunctionkey() {
        let start_date = $('#date_range_inputhr').data('daterangepicker').startDate;
        let end_date = $('#date_range_inputhr').data('daterangepicker').endDate;
        let keyword = $('#keywordhr').val();
        var service_user_id = $('.selected_su_id').val();
        if (service_user_id == undefined) {
            service_user_id = "{{ $service_user_id }}";
        }

        $.ajax({
            type: 'get',
            url: "{{ url('/service/health-records') }}" + '/' + service_user_id + '?start_date=' + start_date
                .format('YYYY-MM-DD') + '&end_date=' + end_date.format('YYYY-MM-DD') + '&filter=1&keyword=' +
                keyword,
            //dataType : 'json',
            success: function(resp) {

                if (isAuthenticated(resp) == false) {
                    return false;
                }
                if (resp == '') {
                    $('.health_record_list').html(
                        '<div class="text-center p-b-20" style="width:100%">No Records found.</div>');
                } else {
                    $('.health_record_list').html(resp);
                }

                //$('.health_record_list').html(resp);
                $('.health_record_input').val('');
                $('#healthrecordModal').modal('show');
                $('.loader').hide();
                $('body').removeClass('body-overflow');
            }
        });
    }
</script>
