<style>
    label.dash {
        color: #000000;
        font-size: 20px;
        margin-bottom: 20px;
    }
    .dashData {
        margin-bottom: 10px !important;
        display: inline-block !important;
        color: #4c4c46 !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        white-space: nowrap;
        margin-right: 25px;
    }
    .panel {
        box-shadow: unset !important;
    }
    #map-canvas {
        height: 200px;
        width: 200px;
    }
    .position-center {
        width: 100%;
    }
    .dashFlex {
        display: ruby;
        justify-content: space-between;
    }
    .unsetCheck {
        margin: unset !important;
            width: 48%;
    }
</style>
<div id="manager_access_rights" class="tab-pane">
    <div class="row">
        <div class="col-lg-5">
            <section class="panel">
                <div class="panel-body">
                    <div class="position-center">
                        <form role="form" action="" method="post">
                            <div class="form-group">
                                <div class="form-group">
                                    <div class=" col-sm-12 col-lg-12">
                                        <label class="dash">Dashboard</label>
                                        <div class="dashFlex">
                                            <?php foreach ($dashboard as $value) { ?>
                                                <div class="checkbox unsetCheck">
                                                    <label class="dashData">
                                                        <input type="checkbox" name="access_id[]" value="{{ $value['id'] }}" {{ (in_array($value['id'],$user_rights)) ? 'checked':'' }} disabled="">{{ ucfirst($value['module_name']) }}
                                                    </label>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <?php foreach ($managements as $management) { ?>
                                    <div class="form-group">
                                        <div class=" col-sm-10">
                                            <label>{{ ucfirst($management['name']) }}</label>
                                            <?php foreach ($management['module_list'] as $module) { ?>
                                                <div class="checkbox">
                                                    <?php
                                                    $chekd_checkbx = 0;
                                                    $total_checkbx = 0;
                                                    foreach ($module['sub_modules'] as $sub_modules) {
                                                        if (in_array($sub_modules['id'], $user_rights)) {
                                                            $chekd_checkbx++;
                                                        }
                                                        $total_checkbx++;
                                                    }
                                                    if ($total_checkbx == $chekd_checkbx) {
                                                        $selected = 'y';
                                                    } else {
                                                        $selected = 'n';
                                                    }
                                                    ?>
                                                    <label><input type="checkbox" class="acc_heading_chkbox" {{ ($selected == 'y') ? 'checked':'' }} disabled=""> {{ ucfirst($module['module_name']) }}</label>
                                                    <ul type="none" class="sub-checkbox">
                                                        <?php foreach ($module['sub_modules'] as $sub_modules) { ?>
                                                            <li><label><input type="checkbox" name="access_id[]" value="{{ $sub_modules['id'] }}" {{ (in_array($sub_modules['id'],$user_rights)) ? 'checked':'' }} disabled="">{{ ucfirst($sub_modules['submodule_name']) }}</label></li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-lg-7">
            <!-- <div id="my_profile_info" class="tab-pane">
                <div class="position-center">
                    <div class="prf-contacts sttng stf-details">
                        <h2 class="accordion-header"> Personal Information</h2>
                        <div class="accordion-content full-info persnl-detail" style="display: block;">{!! $manager_profile->personal_info !!}</div>
                        
                        <h2 class="accordion-header"> Banking Information
                        <div class="accordion-content full-info">{!! $manager_profile->banking_info !!}</div>
                    </div>
                </div>
            </div> -->

            <div id="my_profile_info" class="tab-pane">
                <div class="position-center">
                    <div class="prf-contacts sttng stf-details">

                        <h2 class="accordion-header">Personal Information</h2>
                        <div class="accordion-content full-info persnl-detail">
                            {!! $manager_profile->personal_info !!}
                        </div>

                        <h2 class="accordion-header">Banking Information</h2>
                        <div class="accordion-content full-info">
                            {!! $manager_profile->banking_info !!}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){ 
        $('.stf-details .full-info').hide();
        $('.stf-details .persnl-detail').show();
        $('.stf-details .accordion-header').click(function(){
            let content = $(this).next('.full-info');

            $('.stf-details .full-info').hide();
            if (content.length) {
                content.show();
            } else {
                console.log('Next element not found'); // debug
            }
        });
    });
</script>