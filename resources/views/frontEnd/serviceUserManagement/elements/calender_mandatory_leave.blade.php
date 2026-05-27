<?php 
    if(isset($system_calendar)) {
        $add_notes = url('/system/calendar/note/add');
    } else {
        $add_notes = url('/service/calendar/note/add');
    }
?>

<!-- Calendar Add Entry -->
<div class="modal fade" id="calndrAddMandatoryLeave" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"> Calendar - Add Mandatory Leave </h4>
            </div>
            <form method="post" id="add-cal-entry-form" action="{{ url('/service/calendar/mandatory_leave/add') }}">
                <div class="modal-body">
                    <div class="row">

                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Child: </label>
                            <div class="col-md-11 col-sm-11 col-xs-12">
                                <div class="select-style">
                                    <?php //echo Auth::user()->home_id; ?>
                                    <select name="su_id_ml">
                                        <option value="0">Select Child</option>
                                        <?php
                                        foreach ($service_users as $key=>$value) { ?>
                                        <option value="{{ $value->id }}"                                         

                                            <?php 
                                            if(!isset($system_calendar)){
                                                if($service_user_id == $value->id){
                                                    echo 'selected';
                                                }
                                            } 
                                            ?>
                                         >{{ $value->name }}</option>

                                        <?php   } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Title: </label>
                            <div class="col-md-11 col-sm-11 col-xs-12">
                                <input name="ml_title" class="form-control" maxlength="255"/>
                            </div>
                        </div>
                        <!-- <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Date: </label>
                            <div class="col-md-11 col-sm-11 col-xs-12">
                                <input type="date" name="ml_date" class="form-control"/>
                            </div>
                        </div> -->
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Note: </label>
                            <div class="col-md-11 col-sm-11 col-xs-12">
                                <textarea name="ml_note" rows="5" class="form-control" maxlength="1000"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer m-t-0">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                    <button class="btn btn-warning save-mandatory-leave-btn" type="submit"> Confirm </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('.save-mandatory-leave-btn').click(function(){
            
            var su           = $('select[name=\'su_id_ml\']'); 
            var note_titles  = $('input[name=\'ml_title\']');     
            var ml_date      = $('input[name=\'ml_date\']');     
            var notes        = $('textarea[name=\'ml_note\']');

            var su_id       = su.val().trim();   
            var note        = notes.val().trim(); 
            var note_title  = note_titles.val().trim(); 
            var err = 0;
            if(su_id == 0) { 
                su.parent().addClass('red_border');
                err = 1;
            } else{
                su.parent().removeClass('red_border');
            }

            if(note == '') { 
                notes.addClass('red_border');
                err = 1;
            } else{
                notes.removeClass('red_border');
            }
            if(note_title == '') { 
                note_titles.addClass('red_border');
                err = 1;
            } else{
                note_titles.removeClass('red_border');
            }

            if(err == 1){
                return false;
            } else{ 
                return true;
            }

        });
    });
</script>

