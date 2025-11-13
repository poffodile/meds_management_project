
<div class="col-md-12 col-sm-12 col-xs-12 metro-main">
    <form class="form-horizontal" method="POST" action="{{ url('/user/record') }}">
        @csrf
        {{-- <form id="filterFormDatas" class="form-horizontal"> --}}
     
        <div class="form-group col-md-6 col-sm-4 col-xs-12 p-0 metro-design">
            <label class="col-md-3 col-sm-3 col-xs-12 control-label"> Report type: </label>
            <div class="col-md-9 col-sm-9 col-xs-12">
                <div class="select-style">
                <?php
                    $selected = ''; 
                    if(isset($report_type)) {
                        $selected = 'selected';
                    }
                ?>
                    <select name="report_type" id="report_type_select">
                        <option value="ALL"> All </option>
                        <option value="INDIVIDUAL" {{ $selected }}> Individual </option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-sm-4 col-xs-12 p-0">
            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 metro-design">
                <label class="col-md-3 col-sm-3 col-xs-12 control-label"> Date Range: </label>
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <?php   
                        if( (isset($user_type)) || (isset($all_record)) ) {
                            
                            if(!empty($from_date)){
                                $from_date = date('d-m-Y', strtotime($from_date));
                            }
                            if(!empty($to_date)){
                                $to_date   = date('d-m-Y', strtotime($to_date));
                            }
                        } 
                    ?>
                    <input name="from_date" required class="form-control trans" type="text" autocomplete="off" maxlength="10" readonly="" id="datetime-picker2" value="{{ $from_date ? $from_date : '' }}" />
                </div>
                <div class="col-md-1 col-sm-1 col-xs-12 p-0">
                    <label class="col-md-12 col-sm-12 col-xs-12 control-label"> To: </label>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <input name="to_date" required class="form-control trans" id="datetime-picker1" type="text" value="{{ $to_date ? $to_date : '' }}" autocomplete="off" maxlength="10" readonly=""/>
                </div>
            </div>
        </div>
        
        <div class="form-group col-md-6 col-sm-6 col-xs-12 p-0 metro-design">
            <label class="col-md-3 col-sm-3 col-xs-12 control-label"> User type: </label>
            <div class="col-md-9 col-sm-9 col-xs-12">
                <div class="select-style">
                <?php   if(!isset($user_type)) {  ?>
                            <select id="user_type" name="user_type">
                                <option value=""> Select </option>
                                <option value="SERVICE_USER"> Child </option>
                                <option value="STAFF"> Staff </option>
                            </select>
                <?php   }  ?>
                <?php
                    if(isset($user_type)) {  ?>
                    <select id="user_type" name="user_type">
                        <option value=""> Select </option>
                        <option value="SERVICE_USER" {{ ($user_type == 'SERVICE_USER') ? 'selected': '' }}> Child </option>
                        <option value="STAFF" {{ ($user_type == 'STAFF') ? 'selected': '' }}> Staff </option>
                    </select>
                <?php } ?>
                   
                </div>
            </div>
        </div>

        <div class="form-group col-md-4 col-sm-4 col-xs-12 p-0 metro-design">
            <label class="col-md-5 col-sm-4 col-xs-12 control-label cc-label"> User: </label>
            <div class="col-md-7 col-sm-4 col-xs-12 p-0">
               <div class="select-style">
               <?php  ?>
                    <select name="select_user_id" id="select_user">
                        <option value=""> Select </option>
                        <?php 
                            $selected = '';
                            foreach($service_user as $su) {
                                if(isset($select_user)) {
                                    if($select_user == $su->id) {
                                        $selected = 'selected'; 
                                    } else {
                                        $selected = '';
                                    }          
                                }
                        ?>
                            <option value="{{ $su->id}}" {{ $selected }}>{{ $su->name }}</option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group col-md-2 col-sm-2 col-xs-12 text-center" id="">
            {{-- {{ csrf_field() }} --}}
            <button type="submit" class="btn btn-warning" id="">Confirm</button>
        </div>
    </form>
</div>




<script>
    $(document).ready(function() {
        today  = new Date; 
        $('#datetime-picker2').datetimepicker({
           format: 'dd-mm-yyyy',
            // startDate: today,
            endDate: today,
            minView : 2
        });
        /*$('.change_date').datetimepicker({
           format: 'dd-mm-yyyy',
            //format: 'yyyy-mm-dd',
            // startDate: today,
            endDate: today,
            minView : 2
        });*/

        $('#datetime-picker1').datetimepicker({
            format: 'dd-mm-yyyy',
            //format: 'yyyy-mm-dd',
            // startDate: today,
            endDate: today,
            minView : 2,
        });

        $('#datetime-picker1').on('click', function(){
            $('#datetime-picker1').datetimepicker('show');
        });

        $('#datetime-picker2').on('click', function(){
            $('#datetime-picker2').datetimepicker('show');
        });

        $('#datetime-picker2').on('change', function(){
            $('#datetime-picker2').datetimepicker('hide');
        });

        $('#datetime-picker1').on('change', function(){
            $('#datetime-picker1').datetimepicker('hide');
        });
    });
</script>