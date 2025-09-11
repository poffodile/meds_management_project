<style>
.search_ils_date{
    display:none;
}

.search_ils_title{
    display:none;
}
</style>
<!-- Living Skill Modal -->
<div class="modal fade" id="livingSkillModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{ $labels['living_skill']['label'] }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-12 col-sm-12 col-xs-12 serch-btns text-right">
                        <button class="btn label-default add-new-btn active" type="button"> Add New </button>
                        <button class="btn label-default logged-btn active liv-skill-logged-btn" type="button"> Logged Records </button>
                        <button class="btn label-default search-btn active" type="button"> Search </button>
                    </div>
                    <div class="add-new-box risk-tabs custm-tabs">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                            <form method="post" action="">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Add: </label>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <div class="select-bi" style="width:100%;float:left;">
                                        <?php
                                            $earning_scheme_label_id = App\EarningSchemeLabel::where('home_id', Auth::user()->home_id)
                                                ->where('label_type', 'I')
                                                ->where('deleted_at', null)
                                                ->value('id');
                                            $living_skill_options = App\EarningSchemeLabelRecord::where('home_id', Auth::user()->home_id)
                                                ->where('status', '1')
                                                ->where('earning_scheme_label_id', $earning_scheme_label_id)
                                                ->where('deleted_at', null)
                                                ->orderBy('id', 'desc')
                                                ->get()
                                                ->toArray();
                                        ?>
                                        <select class="js-example-placeholder-single-ls form-control" id="skills_list" style="width:100%;" name="living_skill_id">
                                            <option value=""></option>
                                            @foreach($living_skill_options as $value)
                                            <option value="{{ $value['id'] }}">{{ $value['description'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <p class="help-block"> Enter the task to be complete, once enter this task can be given a score.</p>
                                </div>
                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-5 add-rcrd">
                                    <label class="col-md-1 col-sm-2 p-t-7"> Select: </label>
                                    <div class="col-md-9 col-sm-6 col-xs-12 l-p-10">
                                        <div class="select-bi" style="width:100%;float:left;">
                                            <select class="js-example-placeholder-single-ls form-control" id="am_pm" style="width:100%;" name="am_pm">
                                                <option value="A">Am</option>
                                                <option value="P">Pm</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1 p-0">
                                        <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button class="btn group-ico save-liv-skill-btn" type="submit"> <i class="fa fa-plus"></i> </button>
                                    </div>
                                </div>
                                <!-- <div class="col-md-1 col-sm-1 col-xs-1 p-0">
                                    <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button class="btn group-ico  save-liv-skill-btn" type="submit"> <i class="fa fa-plus"></i> </button>
                                </div> -->
                            </form>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="below-divider"></div>
                        </div>
                        <!-- alert messages -->
                        @include('frontEnd.common.popup_alert_messages')
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="recent-task-sec">
                                <h3 class="m-t-0 m-b-20 clr-blue fnt-20"> Recent Tasks
                                    <span class="pull-right key-main"><i class="fa fa-key"></i>
                                        <div class="key-sec score-detail">
                                            <h4> Scores Detail </h4>
                                            <ul type="none">
                                                <?php
                                                foreach ($daily_score as $score) {  ?>
                                                    <li> <b class="p-r-10">{{ $score->score }} - </b> {{ $score->title }}</li>
                                                <?php } ?>
                                                <!-- <li><span class="clr-grn"></span> 2. {{ $score->title }} </li>
                                            <li><span class="clr-skyblu"></span> 3. {{ $score->title }} </li>
                                            <li><span class="clr-ornge"></span> 4. {{ $score->title }} </li>
                                            <li><span class="clr-red"></span> 5. {{ $score->title }} </li> -->
                                            </ul>
                                        </div>
                                    </span>
                                </h3>
                            </div>
                        </div>
                        <form method="post" action="" id="edit_skill_form">
                            <div class="su-skill modal-space ">
                            </div>
                            <div class="form-group modal-footer m-t-0 modal-bttm">
                                <a class="bottm-btns" href="{{ url('/service/calendar/'.$service_user_id) }}">
                                    <div class="pull-left"><i class="fa fa-calendar"></i></div>
                                </a>
                                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button class="btn btn-warning sbmt-edittd-liv-skill" type="submit"> Submit </button>
                        </form>
                    </div>
                </div>
                <!-- logged plans -->
                <div class="logged-box risk-tabs custm-tabs">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <h3 class="m-t-0 m-b-20 clr-blue fnt-20"> Logged Records </h3>
                    </div>
                    <!-- alert messages -->
                    @include('frontEnd.common.popup_alert_messages')
                    <form id="edit-living-logged-form">
                        <div class="modal-space modal-pading logged-plan-shown liv-skill-logged-list">
                            <!-- logged risk list be shown here using ajax -->
                        </div>
                    </form>
                    <div class="modal-footer m-t-0 recent-task-sec">
                        <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                        <button class="btn btn-warning edit-living-logged-form-sbmitbtn" type="button"> Confirm</button>
                    </div>
                </div>
                <div class="search-box risk-tabs custm-tabs">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <h3 class="m-t-0 m-b-20 clr-blue fnt-20">Search</h3>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 p-0 type-field">
                        <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 cus-lbl"> Type: </label>
                        <div class="col-md-9 col-sm-9 col-xs-12 m-b-15">
                            <div class="select-style">
                                <select name="ls_search_type" id="search_ils_type">
                                    <option value="">Select</option>
                                    <option value='title'> Title </option>
                                    <option value='date'> Date </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 p-0 search_ils_title">
                        <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 cus-lbl"> Title: </label>
                        <div class="col-md-10 col-sm-10 col-xs-10">
                            <div class="select-bi" style="width:100%;float:left;">
                                <?php
                                    $earning_scheme_label_id = App\EarningSchemeLabel::where('home_id', Auth::user()->home_id)
                                        ->where('label_type', 'I')
                                        ->where('deleted_at', null)
                                        ->value('id');
                                    $living_skill_options = App\EarningSchemeLabelRecord::where('home_id', Auth::user()->home_id)
                                        ->where('status', '1')
                                        ->where('earning_scheme_label_id', $earning_scheme_label_id)
                                        ->where('deleted_at', null)
                                        ->orderBy('id', 'desc')
                                        ->get()
                                        ->toArray();
                                ?>
                                <select class="js-example-placeholder-single-ls form-control" id="search_liv_skill" style="width:100%;" name="search_liv_skill">
                                    <option value=""></option>
                                    @foreach($living_skill_options as $value)
                                    <option value="{{ $value['id'] }}">{{ $value['description'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- <div class="col-md-9 col-sm-9 col-xs-12 m-b-15 title">
                            <input type="hidden" name="" class="form-control" maxlength="255">
                        </div> -->
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd search_ils_date">
                        <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 cus-lbl"> Date: </label>
                        <div class="col-md-9 col-sm-9 col-xs-12 m-b-15">
                            <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date="" class="input-group date dpYears">
                                <input name="ls_date" type="text" value="" size="45" class="form-control" readonly="" maxlength="10">
                                <span class="input-group-btn add-on">
                                    <button class="btn clndr btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <form id="searched-living-skills-form" method="post">
                        <div class="modal-space modal-pading searched-skills text-center">
                            <!-- <div class="modal-space searched-skills p-t-0" > -->
                            <!--searched Record List using ajax -->
                        </div>
                    </form>
                    <div class="modal-footer m-t-0 recent-task-sec">
                        <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                        <button class="btn btn-warning search_liv_skill_btn" type="button"> Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>
    //open modal when click on icon and load the today's living records
    $(document).ready(function() {
        $(document).on('click', '.living-skill-list', function() {
            $('.loader').show();
            $('body').addClass('body-overflow');
            var service_user_id = "{{ $service_user_id }}";
            $.ajax({
                type: 'get',
                url: "{{ url('/service/living-skills') }}" + '/' + service_user_id,
                success: function(resp2) {
                    if (isAuthenticated(resp2) == false) {
                        return false;
                    }
                    if (resp2 == '') {
                        $('.su-skill').html('<div class="text-center p-b-20" style="width:100%">No Records found.</div>');
                    } else {
                        $('.su-skill').html(resp2);
                    }
                    // $('.su-skill').html(resp2);
                    $('#livingSkillModal').modal('show');
                    // $('.add-new-btn').click();
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
    });
</script>
<script>
    $(document).ready(function() {
        //view logged records
        $('.liv-skill-logged-btn').click(function() {
            $('.loader').show();
            $('body').addClass('body-overflow');
            var service_user_id = "{{ $service_user_id }}";
            $.ajax({
                type: 'get',
                url: "{{ url('/service/living-skills') }}" + '/' + service_user_id + '?logged',
                //dataType : "json",
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    if (resp == '') {
                        $('.liv-skill-logged-list').html('<div class="text-center p-b-20" style="width:100%">No Records found.</div>');
                    } else {
                        $('.liv-skill-logged-list').html(resp);
                    }
                    // $('.liv-skill-logged-list').html(resp);
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
        });
    });
</script>
<script>
    //click search btn
    $('input[name=\'ls_date\']').closest('.srch-field').hide();
    $(document).ready(function() {
        $('input[name=\'search_liv_skill\']').keydown(function(event) {
            var keyCode = (event.keyCode ? event.keyCode : event.which);
            if (keyCode == 13) {
                $('.search_liv_skill_btn').click();
                return false;
                //$('.search_files_btn').click();        
            }
        });
        /*$('select[name=\'ls_search_type\']').change(function(){
            $('.searched-skills').html('');
            var dr_src_title = $('input[name=\'search_liv_skill\']');
            var dr_src_date  = $('input[name=\'ls_date\']');
            var type = $(this).val(); 
            if(type == 'date'){
                dr_src_date.closest('.srch-field').show();
                dr_src_date.removeClass('red_border');
                dr_src_title.closest('.srch-field').hide();
            }
            else{
                dr_src_title.closest('.srch-field').show();
                dr_src_title.removeClass('red_border');
                dr_src_date.closest('.srch-field').hide();
            }            
        });*/
        $('select[name=\'ls_search_type\']').change(function() {
            $('.searched-skills').html('');
            var ls_srch_title = $('input[name=\'search_liv_skill\']');
            var ls_srch_date = $('input[name=\'ls_date\']');
            var type = $(this).val();
            if (type == 'date') {
                ls_srch_date.closest('.srch-field').show();
                ls_srch_date.removeClass('red_border');
                ls_srch_title.closest('.srch-field').hide();
                /*--- used to initalize calendar specially for EarningScheme webpage ----*/
                $('.dpYears').datepicker({
                }).on('changeDate', function(e) {
                    $(this).datepicker('hide');
                });
            } else {
                ls_srch_title.closest('.srch-field').show();
                ls_srch_title.removeClass('red_border');
                ls_srch_date.closest('.srch-field').hide();
            }
        });
        $(document).on('click', '.search_liv_skill_btn', function() {
            var ls_search_type = $('select[name=\'ls_search_type\']');
            var search_input = $('input[name=\'search_liv_skill\']');
            var ls_search_date = $('input[name=\'ls_date\']');
            var search = document.getElementById('search_liv_skill').value;
            // var search = search_input.val();
            console.log(search);
            var ls_date = ls_search_date.val();
            var ls_search_type = ls_search_type.val();
            // alert(ls_search_type); return false;
            search = jQuery.trim(search);
            search = search.replace(/[&\/\\#,+()$~%.'":*?<>^@{}]/g, '');
            if (ls_search_type == 'title') {
                if (search == '') {
                    search_input.addClass('red_border');
                    return false;
                } else {
                    search_input.removeClass('red_border');
                }
            } else {
                if (ls_date == '') {
                    ls_search_date.addClass('red_border');
                    return false;
                } else {
                    ls_search_date.removeClass('red_border');
                }
            }
            //for editing functionality
            //check validations
            var error = 0;
            //var enabled = 0;
            $('.searched-skills .edit_skill').each(function(index) {
                var is_disable = $(this).attr('disabled');
                if (is_disable == undefined) {
                    //if it is not disabled
                    var title = $(this).val();
                    title = jQuery.trim(title);
                    if (title == '' || title == '0') {
                        $(this).addClass('red_border');
                        error = 1;
                    } else {
                        $(this).removeClass('red_border');
                    }
                    //enabled = 1;
                }
            });
            if (error == 1) {
                return false;
            }
            /*if(enabled == 0){
                return false;
            }*/
            var formdata = $('#searched-living-skills-form').serialize();
            var service_user_id = "{{ $service_user_id }}";
            $('.loader').show();
            $('body').addClass('body-overflow');
            $.ajax({
                type: 'post',
                // url  : "{{ url('/service/daily-records') }}"+'/'+service_user_id+'?search='+search+'&ls_date='+ls_date,
                url: "{{ url('/service/living-skills') }}" + '/' + service_user_id + '?search=' + search + '&ls_date=' + ls_date + '&ls_search_type=' + ls_search_type,
                data: formdata,
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    resp = $.trim(resp);
                    if (resp == '') {
                        $('.searched-skills').html('{{ NO_RECORD }}');
                    } else {
                        $('.searched-skills').html(resp);
                    }
                    // search field empty
                    // $('input[name=\'search_liv_skill\']').val('');
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
    });
</script>
<script>
    $(document).ready(function() {
        //add new su daily record
        $(document).on('click', '.save-liv-skill-btn', function() {
            var liv_skill_id = $('select[name=\'living_skill_id\']').val();
            var am_pm = $('#am_pm').val();
            // alert(am_pm); 
            // var am_pm = $('select[name=\'am_pm\']').val();
            var service_user_id = $('input[name=\'service_user_id\']').val();
            // alert(service_user_id); return false;
            var error = 0;
            if (liv_skill_id == '') {
                $('select[name=\'living_skill_id\']').parent().addClass('red_border');
                error = 1;
            } else {
                $('select[name=\'living_skill_id\']').parent().removeClass('red_border');
            }
            if (error == 1) {
                return false;
            }
            $('.loader').show();
            $('body').addClass('body-overflow');
            $.ajax({
                type: 'get',
                url: "{{ url('/service/living-skill/add') }}",
                data: {
                    'liv_skill_id': liv_skill_id,
                    'service_user_id': service_user_id,
                    'am_pm': am_pm
                },
                success: function(resp) {
                    //alert(resp); return false;
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    if (resp == '0') {
                        //alert('Sorry record could not be added');
                        $('span.popup_error_txt').text('{{ COMMON_ERROR }}');
                        $('.popup_error').show();
                    } else {
                        $('.su-skill').html(resp);
                        //$('select[name=\'daily_record_id\']').val('');
                        $('#skills_list').val('');
                        //$('#dailyrecordModal').modal('show');
                        $('.loader').hide();
                        $('body').removeClass('body-overflow');
                        //show success message
                        $('span.popup_success_txt').text('{{ ADD_RCD_MSG }}');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $(".popup_success").fadeOut()
                        }, 5000);
                        $('#select2-skills_list-container').html('<span class="select2-selection__placeholder">Select Task</span>');
                    }
                }
            })
            return false;
        });
    });
</script>
<script>
    $(document).ready(function() {
        //saving Editted Skill by click on submit on add new tab
        $(document).on('click', '.sbmt-edittd-liv-skill', function() {
            //validating the input fields
            var validate_res = validate_edit('.su-skill');
            //if any field is empty or if no field is editable then don't call ajax request
            if ((validate_res['err'] == 1) || (validate_res['enabled'] == 0)) {
                //console.log(validate_res); 
                return false;
            }
            //loader
            var formdata = $('#edit_skill_form').serialize();
            $('.loader').show();
            $('body').addClass('body-overflow');
            $.ajax({
                type: 'post',
                url: "{{ url('/service/living-skill/edit') }}",
                data: formdata,
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    $('.su-skill').html(resp);
                    //loader
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                    $('span.popup_success_txt').text('{{ EDIT_RCD_MSG }}');
                    $('.popup_success').show();
                    setTimeout(function() {
                        $(".popup_success").fadeOut()
                    }, 5000);
                }
            });
            return false;
        });
        $(document).on('click', '.edit-living-logged-form-sbmitbtn', function() {
            //var service_user_id = "{{ $service_user_id }}";
            //validating the input fields
            var validate_res = validate_edit('.liv-skill-logged-list');
            //if any field is empty or if no field is editable then don't call ajax request
            if ((validate_res['err'] == 1) || (validate_res['enabled'] == 0)) {
                //console.log(validate_res); 
                return false;
            }
            //loader
            var formdata = $('#edit-living-logged-form').serialize();
            $('.loader').show();
            $('body').addClass('body-overflow');
            //alert(formdata); return false;
            $.ajax({
                type: 'post',
                url: "{{ url('/service/living-skill/edit?logged') }}",
                data: formdata,
                success: function(resp) {
                    //alert(resp); return false;
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    $('.liv-skill-logged-list').html(resp);
                    //loader
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                    $('span.popup_success_txt').text('{{ EDIT_RCD_MSG }}');
                    $('.popup_success').show();
                    setTimeout(function() {
                        $(".popup_success").fadeOut()
                    }, 5000);
                }
            });
            return false;
        });
        function validate_edit(parent_div_class) {
            var response = [];
            response['err'] = 0;
            response['enabled'] = 0;
            //var a = '.su-skill';
            //$('.su-skill .edit_skill').each(function(index){
            $(parent_div_class + ' .edit_skill').each(function(index) {
                var disabled_attr = $(this).attr('disabled');
                // alert(disabled_attr); return false;
                if (disabled_attr == undefined) {
                    // alert('enterd'); return false;
                    var desc = $(this).val();
                    desc = jQuery.trim(desc);
                    if (desc == '' || desc == '0') {
                        if ($(this).hasClass('sel')) {
                            $(this).parent().addClass('red_border');
                        } else {
                            $(this).addClass('red_border');
                        }
                        response['err'] = 1;
                    } else {
                        // alert('not enterd'); return false;
                        if ($(this).hasClass('sel')) {
                            $(this).parent().removeClass('red_border');
                        } else {
                            $(this).removeClass('red_border');
                        }
                    }
                    response['enabled'] = 1;
                }
            });
            return response;
        }
    });
</script>
<script>
    //delete a row
    $(document).on('click', '.delete-skill', function() {
        var su_living_skill_id = $(this).attr('su_living_skill_id');
        // alert(su_living_skill_id);  return false;
        if (!confirm('{{ DEL_CONFIRM}}')) {
            return false;
        }
        $(this).addClass('active_skill'); /*active_record*/
        var skill_token = $('input[name=\'_token\']').val();
        $('.loader').show();
        $('body').addClass('body-overflow');
        $.ajax({
            type: 'post',
            url: "{{ url('/service/living-skill/delete') }}" + '/' + su_living_skill_id,
            data: {
                'su_living_skill_id': su_living_skill_id,
                '_token': skill_token
            },
            success: function(resp) {
                //alert(resp); return false;
                if (isAuthenticated(resp) == false) {
                    return false;
                }
                if (resp == 1) {
                    $('.active_skill').closest('.skill_row').html('');
                    //show delete message
                    $('span.popup_success_txt').text('{{ DEL_RECORD }}');
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                    $('.popup_success').show();
                    setTimeout(function() {
                        $(".popup_success").fadeOut()
                    }, 5000);
                } else {
                    //show delete message error
                    $('span.popup_error_txt').text('Error Occured');
                    $('.popup_error').show();
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        //edit option => making editable 
        $(document).on('click', '.skill_edit_btn', function() {
            var edit_btn = $(this);
            var skill_detail_btn = edit_btn.closest('.pop-notification').find('.skill-detail');
            var su_living_skill_id = $(this).attr('su_living_skill_id');
            //if edit button is already cliked then don't click detail button
            if (skill_detail_btn.hasClass('active')) {
                //return false;      
            } else {
                $(this).closest('.cog-panel').find('.input-plusbox').toggle();
            }
            $('.edit_skill_score_' + su_living_skill_id).removeAttr('disabled');
            $('.edit_detail_' + su_living_skill_id).removeAttr('disabled');
            $(this).closest('.cog-panel').find('.input-plusbox').toggleClass('edit');
            $('.edit_skill_id_' + su_living_skill_id).removeAttr('disabled');
            $(this).closest('.pop-notifbox').removeClass('active');
            //used for checking if it is shown
            $(this).toggleClass('active');
            autosize($("textarea"));
            return false;
        });
        //detail option
        $(document).on('click', '.skill-detail', function() {
            var detail_btn = $(this);
            var su_living_skill_id = $(this).attr('su_living_skill_id');
            //if edit button is already cliked then don't click detail button
            var skill_edit_btn = detail_btn.closest('.pop-notification').find('.skill_edit_btn');
            if (skill_edit_btn.hasClass('active')) {
                // $('.edit_skill_score_'+su_living_skill_id).attr('disabled','disabled');
                // $('.edit_detail_'+su_living_skill_id).attr('disabled','disabled');
                // $('.edit_skill_id_'+su_living_skill_id).attr('disabled','disabled');
                $(this).closest('.cog-panel').find('.input-plusbox').removeClass('edit');
                $(this).closest('.cog-panel').find('.input-plusbox').addClass('view');
                skill_edit_btn.removeClass('active');
                $(this).toggleClass('active');
                return false;
            }
            $(this).closest('.cog-panel').find('.input-plusbox').toggle();
            $(this).closest('.cog-panel').find('.input-plusbox').toggleClass('view');
            $(this).toggleClass('active');
            $(this).closest('.pop-notifbox').removeClass('active');
            //.skill_edit_btn
            return false;
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $(".js-example-placeholder-single-ls").select2({
            dropdownParent: $('#livingSkillModal'),
            placeholder: "Select Task"
        });
    });
</script>
<script>
    //pagination 
    $(document).ready(function() {
        $(document).on('click', '#livingSkillModal .pagination li', function() {
            var page_no = $(this).children('a').text();
            // alert(page_no);
            if (page_no == '') {
                return false;
            }
            if (isNaN(page_no)) {
                var new_url = $(this).children('a').attr('href');
                page_no = new_url[new_url.length - 1];
            }
            $('.loader').show();
            $('body').addClass('body-overflow');
            $.ajax({
                type: 'get',
                url: "{{ url('/service/living-skills') }}" + '/' + "{{ $service_user_id }}" + "?page=" + page_no + "&logged",
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    $('.liv-skill-logged-list').html(resp);
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
    });

    $('#search_ils_type').on('change', function(){
        var searchType = document.getElementById('search_ils_type').value;
        if(searchType == 'title'){
            $('.search_ils_title').show();
        } else {
            $('.search_ils_title').hide();
        }
        if(searchType == 'date'){
            $('.search_ils_date').show();
        } else {
            $('.search_ils_date').hide();
        }
    });

</script>
<!-- <script>
    //pagination of living skill
    $(document).on('click','.liv_skill_paginate .pagination li',function(){
        var new_url = $(this).children('a').attr('href');
        if(new_url == undefined){
            return false;
        }
        $('.loader').show();
        $('body').addClass('body-overflow');
        $.ajax({
            type : 'get',
            url  : new_url+'&logged',
            success:function(resp){
                if(isAuthenticated(resp) == false){
                    return false;
                }
                $('.liv-skill-logged-list').html(resp);
                $('.loader').hide();
                $('body').removeClass('body-overflow');
            }
        });
        return false;
    });
</script> -->
<!-- <script>
    $(document).ready(function(){
        $(document).on('click','.liv-skill-head', function(){
            $(this).next('.liv-skill-content').slideToggle();
            $(this).find('i').toggleClass('fa-angle-down');
            // $('.input-plusbox').show();
        });
    });
</script> -->