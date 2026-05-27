<div id="profile_detail" class="tab-pane">
    <div class="position-center">
        <div class="prf-contacts sttng">
            <h2 class="accordion-header"> Personal Information <a href="javascript:void(0)" class="info-edit-btn" clmn-name="personal_info"><i class="fa fa-pencil profile"></i></a></h2>
            <div class="accordion-content full-info persnl-detail" style="display: block;">{!! $patient->personal_info !!}</div>
            <h2 class="accordion-header">Education history <a href="javascript:void(0)" class="info-edit-btn" clmn-name="education_history"><i class="fa fa-pencil profile"></i></a></h2>
            <div class="accordion-content full-info">{!! $patient->education_history !!}</div>
            <h2 class="accordion-header">Bereavement issues <a href="javascript:void(0)" class="info-edit-btn" clmn-name="bereavement_issues"><i class="fa fa-pencil profile"></i></a></h2>
            <div class="accordion-content full-info">{!! $patient->bereavement_issues !!}</div>
            <h2 class="accordion-header">Drug &amp; alcohol issues <a href="javascript:void(0)" class="info-edit-btn" clmn-name="drug_n_alcohol_issues"><i class="fa fa-pencil profile"></i></a></h2>
            <div class="accordion-content full-info">{!! $patient->drug_n_alcohol_issues !!}</div>
            <h2 class="accordion-header">Mental Health issues<a href="javascript:void(0)" class="info-edit-btn" clmn-name="mental_health_issues"><i class="fa fa-pencil profile"></i></a></h2>
            <div class="accordion-content full-info">{!! $patient->mental_health_issues !!}</div>
        </div>
    </div>
</div>
<!--detail Information popup-->
<div class="modal fade" id="detail_info_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close cancel-btn" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Update Profile</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form method="post" action="{{ url('/service/user/edit-details') }}" data-pid='personal_details_edit' enctype="multipart/form-data" id='edit_detail_info'>
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group p-0 col-md-12 col-sm-12 col-xs-12 add-rcrd">
                                <label class="col-md-12 col-sm-12 col-xs-12 p-t-7 detail-info-label">Information</label>
                                <div class="col-md-12 col-sm-12 col-xs-12 r-p-0">
                                    <div class="input-group popovr">
                                        <textarea name="" class="form-control detail-info-txt" rows="15" maxlength="2000" id="persnl_inf"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer modal-bttm m-t-0">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                            <button class="btn btn-default cancel-btn" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                            <button class="btn btn-warning" type="submit"> Confirm </button>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $('.prf-contacts .full-info').hide();
    $('.prf-contacts .persnl-detail').show();
    $(document).ready(function() {
        $('.prf-contacts h2').click(function() {
            $('.prf-contacts .full-info').hide();
            $(this).next('.full-info').show();
        });
    });
</script>
<script>
    $(document).ready(function() {
        //showing more info model
        $('.info-edit-btn').click(function() {
            var heading = $(this).parent().text();
            var text = $(this).parent().next('div').text();
            var text_clmm_name = $(this).attr('clmn-name');
            $('.detail-info-label').text(heading);
            $('.detail-info-txt').text(text);
            $('.detail-info-txt').attr('name', text_clmm_name);
            $('#detail_info_model').modal('show');
            setTimeout(function() {
                var elmnt = document.getElementById("persnl_inf");
                var scroll_height = elmnt.scrollHeight;
                console.log(scroll_height);
                $('#persnl_inf').height(scroll_height);
            }, 200);
        });
    });
</script>