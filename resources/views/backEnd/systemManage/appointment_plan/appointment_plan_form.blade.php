
@extends('backEnd.layouts.master')

@section('title',': Form Builder Form')

@section('content')
<?php
if (isset($form)) {
    $action = url('admin/appointment/plan/edit/' . $form->id);
    $task = "Edit";
} else {
    $action = url('admin/appointment/plan/add');
    $task = "Add";
}
?>

</pre>


<style>
    .hidden {
        display: none;
    }
    .fontwesome-panel {
        top: 0;
        float: left;
        overflow-y: auto;
        position: fixed;
        right: 0;
        width: 100%;
        background: white;
        z-index: 9999;
        height: 100vh;
    }
    .prient-btn input[type="button"] {
        background-color: #1fb5ad;
        border: none;
        color: #fff;
        padding: 7px 22px;
        border-radius: 5px;
        font-weight: 600;
        position: relative;
        /* top: -10px; */
        text-align: center;
        margin: 31px auto 20px auto;
        display: table;
    }


    .has-feedback.formio-component button {
        border-radius: 0px;
    }


    .component-btn-group {
        margin: 10px 0px 0px 0px;
    }

    .card-header {
        padding: 1px 0px 0px 10px;
        border-bottom: 1px solid rgba(0, 0, 0, .125);
        background: #f0f0f0;
    }

    .card {
        border: 1px solid #cdcdcd;
    }

    .component-settings .nav>li>a {
        padding: 8px 10px;
        font-size: 15px;
        border-radius: 0px;
        margin: 10px 0px 0px 0px;
    }

    .nav-tabs {
        border-bottom: none;
    }

    .form-control {
        height: 40px;
    }


    .card-body {
        padding: 10px 18px 20px 18px;
        background: #fff;
    }


    .has-feedback .form-control {
        border-radius: 0px;
        border: 1px solid #ced4da;
    }


    label {
        margin: 10px 0px 10px 0px;
        font-size: 14px;
        font-weight: 500;
    }

    .form-check-label span {
        line-height: 1.8;
    }


    .card-header.bg-default {
        padding: 13px 0px 13px 10px;
        font-size: 14px;
        background: rgba(0, 0, 0, .03);
        border: none !important;
    }


    .card.mb-2 {
        margin: 0px 0px 10px 0px;
    }


    .p-2 {
        padding: 0.5rem !important;
    }


    .btn-secondary.formio-button-remove-row {
        background: #1ca59e;
        color: #fff;
        padding: 8px 12px;
    }

    .editgrid-actions .btn.btn-primary {
        margin: 10px 10px 0px 0px;
    }

    .editgrid-actions .btn.btn-danger {
        margin: 10px 0px 0px 0px;
    }


    .panel {
        border-radius: 0;
    }

    .btn.btn-secondary {
        background: #1ca59e;
        color: #fff;
        border: none;
    }

    .btn.btn-success {
        background: #1ca59e;
        color: #fff;
        border: none;
    }

    .btn.btn-danger {
        background: #1ca59e;
        color: #fff;
        border: none;
    }


    .btn.btn-primary.btn-md {
        background: #1fb5ad;
        color: #fff;
        margin: 20px 0px 0px 15px;
    }

    .formcomponents.col-md-2 {
        width: 21%;
    }

    .formarea.col-md-10 {
        width: 79%;
    }

    .custom-from {
        padding: 30px 0px 0px 0px;
    }

    .p-t-15 {
        margin: 20px 0px 0px 0px;
    }

    .submit-btn-main-area .save-appointmnt-form {
        margin: 0px 10px 0px 0px;
    }

    .form-builder-group-header {
        padding: 1px 0px 0px 0px;
    }

    .input-group {
        display: flex;
    }

    .input-group-text {
        display: flex;
        -ms-flex-align: center;
        align-items: center;
        padding: 8px 15px;
        margin-bottom: 0;
        font-size: .9375rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        text-align: center;
        white-space: nowrap;
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        font-size: 15px;
    }

    .input-group-text i {
        line-height: 22px;
    }

    .form-text {
        color: #a1a1a1;
        margin: 5px 0px 0px 0px;
        font-size: 14px;
    }
    .showTextarea {
    display: none;
    }

    .showTextarea.active {
    display: block;
    }
</style>



<section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="cus-head">
                                <header class="panel-heading">
                                    System management-Appointment/Plan Builder
                                    <!-- {{ $task }} Form -->
                                </header>
                                <div class="cus-bg">
                                    @include('backEnd.common.alert_messages')
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-md-12 col-sm-12 col-xs-12 popup_error">
                        <div class="popup_notification-box">
                            <div class="alert alert-danger alert-dismissible m-0" role="alert">
                                <button type="button" class="close close-msg-btn"><span aria-hidden="true">&times;</span></button>
                                <strong>Success!</strong> <span class="popup_error_txt">Some error occured, Please try again after sometime.</span>.
                            </div>
                        </div>
                    </div>
                     <div class="form-group col-md-12 col-sm-12 col-xs-12 popup_success popup_alrt_msg" style="display: none;">
                        <div class="popup_notification-box">
                            <div class="alert alert-success alert m-0" role="alert">
                                <button type="button" class="close close-msg-btn"><span aria-hidden="true">×</span></button>
                                <strong>Success!</strong> <span class="popup_success_txt">Data is made editable</span>.
                            </div>
                        </div>
                    </div>
                     <div class="panel-body">
                        <div class="position-center">
                            <form class="form-horizontal" role="form" id="custom-created-fields-form">
                                @csrf
                                <div class="form-group">
                                    <label class="col-lg-2 col-sm-2 control-label">Add:</label>
                                    <div class="col-lg-8">
                                        <input type="text" class="form-control" id="" name="plan_title_add" placeholder="">
                                        <p class="help-block">Enter a title,choose an icon and click plus to description</p>
                                    </div>
                                    <div class="col-lg-1">
                                        <button id="" class="btn btn-primary icon-box-risk" type="button"> <i class="fa fa-info"></i>
                                        </button>
                                        <input type="hidden" name="plan_icon_add" class="form-control plan_icon">
                                    </div>
                                    <div class="col-lg-1">
                                        <button type="button" class="btn btn-primary addTextareaPlus add_plan_desc"> <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- style="display: none;" showTextarea -->
                                <div class="form-group description_plan">
                                    <label class="col-lg-2 col-sm-2 control-label"></label>
                                    <div class="col-lg-8">
                                        <textarea class="form-control" rows="4" name="plan_detail_add"></textarea>
                                    </div>
                                </div>
                               
                                <div class="form-group">
                                    <label class="col-lg-2 col-sm-2 control-label">Add:</label>
                                    <div class="col-lg-5">
                                        <input type="text" class="form-control" id="" placeholder="" name="field_name">
                                            <p class="help-block"> Enter the field name and choose its type</p>
                                    </div>
                                    <label class="col-lg-1 col-sm-2 control-label">Type:</label>
                                      <div class="col-lg-3">
                                        <select name="field_type" class="form-control">
                                            <option value="" selected disabled> Select </option>
                                            <option value="Textbox"> Textbox </option>
                                            <option value="Selectbox"> Selectbox </option>
                                            <option value="Textarea"> Textarea </option>
                                            <option value="Date"> Date </option>
                                        </select>
                                    </div>
                                    <div class="col-lg-1">
                                        <button type="button" id="" class="btn btn-primary add-field-btn"> <i class="fa fa-plus"></i>
                                        </button>
                                    </button>
                                    </div>
                                    
                                    <div class="cus-select-options">
                                        <div class="form-group col-md-offset-1 col-md-10 col-sm-10 col-xs-12 p-0  m-t-10 m-l-50 add-select-options-div " style="display: none;">
                                        
                                            <p>Select options</p>
                                            <!-- <div class="option-div">
                                                <div class="col-md-4 col-sm-4 col-xs-12 p-l-0">
                                                    <input name="cus_option1" placeholder="option" class="form-control" type="text" value="">
                                                </div>
                                                <div class="col-md-4 col-sm-4 col-xs-12 p-l-0">
                                                    <input name="cus_value1" placeholder="value" class="form-control" type="text" value="">
                                                </div>
                                                <div class="col-md-1 col-sm-1 col-xs-1 p-0">
                                                    <button class="btn group-ico remove-option1-btn" type="button"> <i class="fa fa-minus"></i> </button>
                                                </div>
                                            </div> -->
                                            <!-- <div class="col-md-1 col-sm-1 col-xs-1 p-0 plus-btn">
                                                <button class="btn group-ico add-more-option-btn" type="button"> <i class="fa fa-plus"></i> </button>
                                            </div> -->
                                        </div>
                                        <div class="add-more-select-options-div">
                                            <!-- more select options using jquery will be placed here -->
                                        </div>
                                        <div class="col-md-1 col-sm-1 col-xs-1 p-0 appoint-plus-btn">
                                            <button class="btn group-ico add-more-option-btn" type="button"> <i class="fa fa-plus"></i></button>
                                        </div>
                                    <!-- add-select-options-div closed -->
                                </div>

                                <div class="col-md-12 col-sm-12 col-xs-12 m-t-20">
                                    <div class="below-divider"></div>
                                </div>

                                </div>
                             

                                 <div class="form-group">
                                    <label class="col-lg-2 col-sm-2 control-label">Title:</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" id="" placeholder="" name="title">
                                            <p class="help-block">Above are default fields of your form, New custom fields will be shown below.</p>
                                    </div>
                                    <div class="modal-space created-fields ui-sortable" id="sortable">
                                        <!-- created form fields from jquery will be placed here -->
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <input type="hidden" name="plan_title" value="" />
                                        <input type="hidden" name="plan_icon" value="" />
                                        <input type="hidden" name="id" value="<?php if(isset($id) && $id !=''){echo $id;}?>" />
                                        <textarea name="plan_detail" style="display:none" value=""> </textarea>
                                        <button type="button" class="btn btn-default" name="cancel">Cancel</button>
                                        <button type="button" class="btn btn-danger save-appointmnt-form">Confirm</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>
<!-- Font awesome(icons) -->
       @include('backEnd.common.icon_list')
<!-- Font awesome(icons) end -->
<script>
    
    $(document).on('click','.select_all',function(){

        if($(this).is(':checked')){
            $('.plan-row input[type="checkbox"]').prop('checked', true);
        }else{
            $('.plan-row input[type="checkbox"]').prop('checked', false);
        }

    })

</script>
<script type="text/javascript">
    $(document).on('click','.plan_del_btn',function(){
        var plan_id = [];
        $("input[type=checkbox]:checked").each(function() {

            plan_id.push($(this).val());
        });
        // console.log(plan_id);
        if(plan_id != ''){
            $('.loader').show();
            var plan_token = "{{ csrf_token() }}";
            $.ajax({
                type    : 'post',
                url     : "{{ url('/system/del/plans') }}",
                dataType: "json",
                data    : {'plan_id':plan_id,'_token':plan_token},
                cache   : false,

                success:function(resp){
                    // console.log(resp);                  
                    if(resp == 1){
                        // $('.active_risk').closest('.risk-row').html('');
                        $("input[type=checkbox]:checked").each(function() {

                            $(this).closest('.plan-row').html('');
                        });
                        $('.loader').hide();
                        $('body').removeClass('body-overflow');

                        $('span.popup_success_txt').text('Risk Deleted Successfully');
                        $('.popup_success').show();
                        setTimeout(function(){$(".popup_success").fadeOut()}, 5000);
                    }
                    else{
                        $('span.popup_error_txt').text('Error Occured');
                        $('.popup_error').show();

                        $('.loader').hide();
                        $('body').removeClass('body-overflow');

                    }
                }
            });
        }
        

        
    });
</script>
<!-- end view appointment modal -->
<script>

    $(".appointments").click(function(){
        $('#appointmentplanmodal').modal('show');

        /*$('.loader').show();
        $('body').addClass('body-overflow');
        $.ajax({
            type : 'get',
            url  : "{{ url('/system/plans') }}",
            success:function(resp){
                $('.appointments-list').html(resp);
                reset_appointment_modal();
                $('#appointmentplanmodal').modal('show');
                $('.loader').hide();
                $('body').removeClass('body-overflow');
            }
        });*/
    });
    
$(document).ready(function(){
    //form builder scripts

    $('.add-more-option-btn').hide();

    var option_count = 1;
    var field_count  = 1;

    $('.add-field-btn').on('click',function(){
        // alert(option_count);return false;
        var field = add_field_in_form(option_count,field_count,'ADD');
        if(field == false){
            return false;
        } 

        // showing new created fields
        $('.created-fields').append(field);
        field_count++;

        // select box options set to default
        $('.add-more-option-btn').hide();
        option_count = 1; 

        reset_appointment_modal();
    });

   $(document).on('click','.field-remove-btn', function(){
        $(this).closest('.cus-field').remove();
   });

    /*select box jquery*/
    $("select[name=\'field_type\'").change(function(){
    
        var value = $(this).val();
        if(value == 'Selectbox'){ 
            $('.add-select-options-div').show();
            $('.add-more-option-btn').show();
            $('.add-more-select-options-div').show();
            $('.add-more-select-options-div').html('');

            option_count = 1;
            $('.add-more-select-options-div').append('<div class="form-group col-md-offset 1 col-md-10 col-sm-10 col-xs-12 p-0 m-l-50 option-div"> <div class="col-md-8 col-sm-8 col-xs-12 p-l-0"> <input name="cus_option'+option_count+'" placeholder="option" class="form-control " type="text" value=""> </div>  <div class="col-md-1 col-sm-1 col-xs-1 p-0"> <button class="btn group-ico remove-option-btn" type="button"> <i class="fa fa-minus"></i> </button> </div> </div>');

            /* both option and value
            $('.add-more-select-options-div').append('<div class="form-group col-md-offset 1 col-md-10 col-sm-10 col-xs-12 p-0 m-l-50 option-div"> <div class="col-md-4 col-sm-4 col-xs-12 p-l-0"> <input name="cus_option'+option_count+'" placeholder="option" class="form-control " type="text" value=""> </div> <div class="col-md-4 col-sm-4 col-xs-12 p-l-0"> <input name="cus_value'+option_count+'" placeholder="value" class=" form-control" type="text" value=""> </div> <div class="col-md-1 col-sm-1 col-xs-1 p-0"> <button class="btn group-ico remove-option-btn" type="button"> <i class="fa fa-minus"></i> </button> </div> </div>');*/

        } else{
            $('.add-select-options-div').hide();
            $('.add-more-option-btn').hide();   
            $('.add-more-select-options-div').hide();
        }
    });

   $(document).on('click','.add-more-option-btn', function(){

        option_count++;
        $('.add-more-select-options-div').append('<div class="form-group col-md-offset 1 col-md-10 col-sm-10 col-xs-12 p-0 m-l-50 option-div"> <div class="col-md-8 col-sm-8 col-xs-12 p-l-0"> <input name="cus_option'+option_count+'" placeholder="option" class="form-control " type="text" value=""> </div>  <div class="col-md-1 col-sm-1 col-xs-1 p-0"> <button class="btn group-ico remove-option-btn" type="button"> <i class="fa fa-minus"></i> </button> </div> </div>');

        /* both option text and value  
        $('.add-more-select-options-div').append('<div class="form-group col-md-offset 1 col-md-10 col-sm-10 col-xs-12 p-0 m-l-50 option-div"> <div class="col-md-4 col-sm-4 col-xs-12 p-l-0"> <input name="cus_option'+option_count+'" placeholder="option" class="form-control " type="text" value=""> </div> <div class="col-md-4 col-sm-4 col-xs-12 p-l-0"> <input name="cus_value'+option_count+'" placeholder="value" class=" form-control" type="text" value=""> </div> <div class="col-md-1 col-sm-1 col-xs-1 p-0"> <button class="btn group-ico remove-option-btn" type="button"> <i class="fa fa-minus"></i> </button> </div> </div>');*/
   });

    $(document).on('click','.remove-option-btn', function(){
        $(this).closest('.option-div').remove();
    });

    $(document).on('click','.remove-option1-btn', function(){
        //$(this).closest('.option-div').find('input').val('');
        $(this).closest('.option-div').remove();
        
        //$(this).closest('.add-select-options-div').removeClass('m-t-10');
        //$(this).closest('.option-div').toggle();
    });
    /*select box jquery end*/

    $(document).on('click','.save-appointmnt-form', function() { 
        $('input[name=\'plan_title\']').val($('input[name=\'plan_title_add\']').val());
       var icon= $('input[name=\'plan_icon\']').val($('input[name=\'plan_icon_add\']').val());
        $('textarea[name=\'plan_detail\']').val($('textarea[name=\'plan_detail_add\']').val());
        var error = 0;
        if( $('input[name=\'plan_title_add\']').val() == '' ) {
            $('input[name=\'plan_title_add\']').addClass('red_border');
            //$('input[name=\'plan_icon_add\']').siblings('span').addClass('red_border');
                error = 1;
        } else {
           $('input[name=\'plan_title_add\']').removeClass('red_border');
           //$('input[name=\'plan_icon_add\']').siblings('span').removeClass('red_border');
        } 
        if( $('input[name=\'plan_icon_add\']').val() == '') {
            $('input[name=\'plan_icon_add\']').siblings('button').addClass('red_border');
                error = 1;
        } else {
            $('input[name=\'plan_icon_add\']').siblings('button').removeClass('red_border');
        }

        if(error == 1){
            return false;   
        }                                           

        var formData = $('#custom-created-fields-form').serialize();
        var id=$('input[name=\'id\']').val();
        //alert(formData);
        // $('.loader').show();
        // $('body').addClass('body-overflow');

        $.ajax({
            url : "{{ url('admin/appointment/plans/store') }}",
            type : "post",
            data : formData,
            success:function(resp){
                console.log(resp);
                if(resp == 'true'){

                    field_count == 1;
                    // remove new created fields from div
                    $('.created-fields').html('');
                    
                    // select box options set to default
                    $('.add-more-option-btn').hide();
                    option_count = 1; 

                    reset_appointment_modal();
                    $('input[name=\'plan_title_add\']').val('');
                    $('textarea[name=\'plan_detail_add\']').val('');
                    $('input[name=\'plan_icon_add\']').val('');
                    $('.icon-box-risk i').attr('class','fa fa-info');
                    $('textarea[name=\'plan_detail_add\']').closest('.description_plan').hide();

                    //show success message
                    $('.popup_alrt_msg').show();
                    $('.alert-success').show();
                    if(id == ''){
                        $('span.popup_success_txt').text('Plan Added successfully');
                    }else{
                        $('span.popup_success_txt').text('Plan Editted successfully');
                    }
                    setTimeout(function(){$(".popup_alrt_msg").fadeOut()}, 5000);
                    window.location.href='<?php echo url('admin/appointment/plans');?>'

                } else if(resp == 'false'){
                    $('span.popup_error_txt').text('Error Occured');
                    $('.popup_error').show();
                    setTimeout(function(){$(".popup_error").fadeOut()}, 5000);

                } else{
                    $('.popup_error').show();
                    $('.alert-danger').show();
                    $('span.popup_error_txt').text(' No input field added in the form');
                    setTimeout(function(){$(".popup_error").fadeOut()}, 5000);
                }
                $('.loader').hide();
                $('body').removeClass('body-overflow');
            }
        });
        
        return false;
    });

    function add_field_in_form(option_count,field_count,operation){

        if(operation == 'ADD'){ 
            var field_namei = 'field_name';
            var field_typei = 'field_type';            
        } else{ 
            var field_namei = 'e_field_name';
            var field_typei = 'e_field_type';
        }

        //if(operation == 'ADD'){ 
            var field_name = $("input[name=\'"+field_namei+"\']").val();
            var field_type = $("select[name=\'"+field_typei+"\']").val();            
        /*} else{ 
            var field_name = $("input[name=\'e_field_name\']").val().trim();
            var field_type = $("select[name=\'e_field_type\']").val().trim();
        }*/
        var column_name = field_name.replace(' ','_');
        column_name = column_name.toLowerCase();
        
        var error_add_field = 0;
        if(field_name == ''){
            $("input[name=\'"+field_namei+"\']").addClass('red_border');
            error_add_field = 1;
        } else{
            $("input[name=\'"+field_namei+"\']").removeClass('red_border');                
        }

        if(field_type == '' || field_type == null){
            $("select[name=\'"+field_typei+"\']").addClass('red_border');
            error_add_field = 1;
        } else{
            $("select[name=\'"+field_typei+"\']").removeClass('red_border');                
        }

        if(error_add_field == 1){
            return false;
        }
       
        if(field_type == 'Textbox'){

            var new_field = '<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 cus-field"> <label class="col-md-2 col-sm-2 col-xs-12 p-t-7" type="'+field_type+'"> '+field_name+': </label> <div class="col-md-8 col-sm-8 col-xs-12 p-l-0"> <input type="text" name="'+column_name+'" class="form-control trans">  <input type="hidden" name="formdata['+field_count+'][label]" value="'+field_name+'"> <input type="hidden" name="formdata['+field_count+'][column_name]" value="'+column_name+'"> <input type="hidden" name="formdata['+field_count+'][column_type]" value="'+field_type+'"> </div> <div class="col-md-1 col-sm-1 col-xs-1 p-0"> <button class="btn group-ico field-remove-btn" type="button"> <i class="fa fa-minus"></i> </button> </div> <div class="col-md-1 col-sm-1 col-xs-1 m-t-5 p-l-10"><span class="sort-sp"><i class="fa fa-sort"></i></span></div> </div>';
            
        } else if(field_type == 'Selectbox'){
            var options = '';
            var option_name = '';
            var option_value = '';
            var opt = '';
            var j = 0;
            ///alert(option_count);
            for(var i=1; i <= option_count; i++){

                if(operation == 'ADD'){ 
                    option_name = $("input[name=\'cus_option"+i+"\']").val();
                    //option_value= $("input[name=\'cus_value"+i+"\']").val();
                } else{
                    option_name = $("input[name=\'e_cus_option"+i+"\']").val();
                    //option_value= $("input[name=\'e_cus_value"+i+"\']").val();                    
                }

                if(option_name !== undefined){
                    option_name = option_name.trim();
                    //option_value = option_value.trim();
                    
                    //if( (option_name !== '') && (option_value !== '') ) {            
                        options += '<option value="'+option_name+'">'+option_name+'</option>';

                        opt += '<input type="hidden" name="formdata['+field_count+'][select_options]['+j+'][option_name]" value="'+option_name+'">';
                        j++;
                    //}
                }

                /*if(option_value !== undefined){
                    option_name = option_name.trim();
                    option_value = option_value.trim();
                    
                    if( (option_name !== '') && (option_value !== '') ) {            
                        options += '<option value="'+option_value+'">'+option_name+'</option>';

                        opt += '<input type="hidden" name="formdata['+field_count+'][select_options]['+j+'][option_name]" value="'+option_name+'"> <input type="hidden" name="formdata['+field_count+'][select_options]['+j+'][option_value]" value="'+option_value+'">';
                        j++;
                    }
                }*/
            }
            /*if(opt == ''){
                alert('Please')
            }*/
          
            var new_field = '<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 cus-field "> <label class="col-md-2 col-sm-2 col-xs-12 p-t-7" type="'+field_type+'"> '+field_name+' </label> <div class="col-md-8 col-sm-8 col-xs-12 p-l-0"> <div class="select-style"> <select name="'+column_name+'" readonly class="trans form-control"> '+options+' </select>  <input type="hidden" name="formdata['+field_count+'][label]" value="'+field_name+'"> <input type="hidden" name="formdata['+field_count+'][column_name]" value="'+column_name+'"> <input type="hidden" name="formdata['+field_count+'][column_type]" value="'+field_type+'"> '+opt+' </div> </div> <div class="col-md-1 col-sm-1 col-xs-1 p-0"> <button class="btn group-ico field-remove-btn" type="button"> <i class="fa fa-minus"></i> </button> </div> <div class="col-md-1 col-sm-1 col-xs-1 m-t-5 p-l-10"><span class="sort-sp"><i class="fa fa-sort"></i></span></div> </div>';    

        } else if(field_type == 'Textarea'){
            var new_field = '<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 cus-field "> <label class="col-md-2 col-sm-2 col-xs-12 p-t-7" type="'+field_type+'"> '+field_name+' </label> <div class="col-md-8 col-sm-8 col-xs-12 p-l-0">  <textarea name="'+column_name+'" class="form-control trans" readonly></textarea> </div> <input type="hidden" name="formdata['+field_count+'][label]" value="'+field_name+'"> <input type="hidden" name="formdata['+field_count+'][column_name]" value="'+column_name+'">  <input type="hidden" name="formdata['+field_count+'][column_type]" value="'+field_type+'">  <div class="col-md-1 col-sm-1 col-xs-1 p-0"> <button class="btn group-ico field-remove-btn" type="button"> <i class="fa fa-minus"></i> </button> </div> <div class="col-md-1 col-sm-1 col-xs-1 m-t-5 p-l-10"><span class="sort-sp"><i class="fa fa-sort"></i></span></div> </div>'; 

        } else if(field_type == 'Date'){
            var new_field = '<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 cus-field "> <label class="col-md-2 col-sm-2 col-xs-12 p-t-7" type="'+field_type+'"> '+field_name+' </label>  <div class="col-md-8 col-sm-8 col-xs-12 p-l-0">  <div data-date-format="dd-mm-yyyy" data-date="12-02-2012" class="input-group date dpYears">  <input name="date" readonly="" value="" size="16" class="form-control trans" type="text" style="background:transparent; "> <input type="hidden" name="formdata['+field_count+'][label]" value="'+field_name+'"> <input type="hidden" name="formdata['+field_count+'][column_name]" value="'+column_name+'"> <input type="hidden" name="formdata['+field_count+'][column_type]" value="'+field_type+'"> <span class="input-group-btn "> <button class="btn btn-primary calndr-btn" type="button"><i class="fa fa-calendar"></i></button> </span>  </div> </div> <div class="col-md-1 col-sm-1 col-xs-1 p-0"> <button class="btn group-ico field-remove-btn" type="button"> <i class="fa fa-minus"></i> </button>  </div> <div class="col-md-1 col-sm-1 col-xs-1 m-t-5 p-l-10"><span class="sort-sp"><i class="fa fa-sort"></i></span></div> </div>';

        } else{
            var new_field = '';
        }

        
        return new_field;
    }

    function reset_appointment_modal(){
        
        //reset plan info
        // $('input[name=\'plan_title_add\']').val()
        // $('input[name=\'plan_icon_add\']').val()

        //reset add select options
        $("input[name=\'field_name\']").val('');
        $("select[name=\'field_type\']").val('');
        
        //reset select box
        $('.add-select-options-div').hide(); 
        // $("input[name=\'cus_option1\']").val('');
        // $("input[name=\'cus_value1\']").val('');

        //$('#custom-created-fields-form').find('input').val('');
        $('.add-more-select-options-div').html('');
    }
    
    

    /*Editing plan */
    var e_option_count = 1;
    var e_field_count  = 1;
<?php if(isset($id) && $id !=''){?>
    $(document).ready(function(){

        // e_reset_appointment_modal();
        var plan_id = <?php echo $id; ?>
        
        // $('.loader').show();
        // $('body').addClass('body-overflow');
       
        $.ajax({
            type : 'get',
            url  : "{{ url('admin/appointment/plans/view') }}"+'/'+plan_id,
            dataType : 'json',
            success:function(resp){
                console.log(resp)
                var response = resp['response'];
                if(response == true){
                    var plan_id = resp['plan_id']; 
                    //var alrdy_savd_flds = resp['total_fields'];
                    var title   = resp['title'];
                    var icon    = resp['icon'];
                    var detail  = resp['detail'];
                    var formdata = resp['formdata'];
                    // alert(icon);return
                    e_field_count = resp['total_fields'];
                    e_field_count++;
                    $('input[name=\'plan_title_add\']').val(title);
                    $('input[name=\'plan_icon_add\']').val(icon);
                    $('.icon-box-risk').find('i').attr('class', icon);
                    $('textarea[name=\'plan_detail_add\']').val(detail);
                    if(detail){
                        $('.description_plan').show();
                    }
                    $('#custom-created-fields-form').find('input[name=\'plan_id\']').val(plan_id);
                    //$('#e-custom-created-fields-form').find('input[name=\'alrdy_savd_flds\']').val(alrdy_savd_flds);

                    $('.created-fields').html(formdata);
                } else{
                    alert("{{ COMMON_ERROR }}");
                }

                $('.loader').hide();
                $('body').removeClass('body-overflow');
            }
        });
        return false;
    });
<?php }?>
    $(document).on('click','.remove-option-btn', function(){
        $(this).closest('.option-div').remove();
    });

    $(document).on('click','.remove-option1-btn', function(){
        //$(this).closest('.option-div').find('input').val('');
        $(this).closest('.option-div').remove();
        
        //$(this).closest('.add-select-options-div').removeClass('m-t-10');
        //$(this).closest('.option-div').toggle();
    });
    /*select box jquery edit case end*/


});
</script>
<script>
    /*------Font awesome icons script ---------*/ 
    $(document).ready(function(){ 
        $('.fontwesome-panel').hide();
        $('.icon-box-risk').on('click',function(){ 
            $('body').addClass('modal-open');
            $('.fontwesome-panel').show();
            $('.fontwesome-panel').find('#icons-fonts').attr('id','risk-fonts');
        });

        $('.fontawesome-cross').on('click',function(){
           $('body').removeClass('modal-open');
           $('.fontwesome-panel').hide(); 
           
        });

        $(document).on('click','#risk-fonts .fa-hover a', function () {             
            var trim_txt = $(this).find('i');
            var new_class = trim_txt.attr('class');
            $('.icon-box-risk i').attr('class',new_class);
            $('body').toggleClass('modal-open');
            $('.fontwesome-panel').hide(); 
            $('.plan_icon').val(new_class);
            //alert(new_class);
        });
    });
</script>
<!--  -->

<script>    
    //delete plan
    $(document).ready(function(){
        $(document).on('click','.delete-log-plan', function(){
            //alert(1); return false;
            if(!confirm("Are sure you to delete this ?")){
                return false;
            }
            var plan_id = $(this).attr('plan_id');
             //alert(plan_id); return false; 
            $(this).addClass('active_plan');  

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type : 'get',
                url  : "{{ url('/system/plans/delete/') }}"+'/'+plan_id,
                data : { 'plan_id' : plan_id },
                success : function(resp){
                    if(isAuthenticated(resp) == false){
                        return false;
                    }
                    if(resp == 1){
                        $('.active_plan').closest('.plan-row').html('');

                        $('.loader').hide();
                        $('body').removeClass('body-overflow');

                        $('span.popup_success_txt').text('Plan Deleted successfully');
                        $('.popup_success').show();
                        setTimeout(function(){$(".popup_success").fadeOut()}, 5000);
                        $(this).closest('.cog-panel').siblings('.cog-panel').find('.pop-notifbox').removeClass('active');
                    }
                    else{
                        $('span.popup_error_txt').text('Error Occured');
                        $('.popup_error').show();

                        $('.loader').hide();
                        $('body').removeClass('body-overflow');
                        $(this).closest('.cog-panel').siblings('.cog-panel').find('.pop-notifbox').removeClass('active');

                    }       
                }
            });
            return false;

        });
    });
</script>
<script>
    $('.description_plan').hide();
    $('.add_plan_desc').on('click',function(){
        autosize($("textarea"));
        $('.description_plan').toggle();
    });
</script>

<script>
    $('.edit_description_plan').hide();
    //$('.edit_description_plan').on('click', function(){
    $(document).on('click','.e_description_plan', function(){
        autosize($("textarea"));
        $('.edit_description_plan').toggle();
    });
</script>
<script type="text/javascript">
    $(document).on('click','.bck_btn',function(){
        $('.sel_icon i').attr('class','');
    });
</script>
<script>
  document.querySelector('.addTextareaPlus').addEventListener('click', function () {
    document.querySelector('.showTextarea').classList.toggle('active');
  });
</script>

@endsection