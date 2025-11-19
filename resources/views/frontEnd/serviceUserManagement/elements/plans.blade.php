<!-- Plan Modal -->
<div class="modal fade" id="planModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"> Plans </h4>
            </div>
            <div class="modal-body">
                <div class="foor-box-wrap foor-plan">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="profile-nav alt profile-plan-div">
                                <a href="{{ url('/service/placement-plans/' . $service_user_id) }}">
                                    <section class="panel text-center profile-square" style="height: 191px">
                                        <div class="plan-user-heading alt wdgt-row red-bg">
                                            <i class="{{ $labels['placement_plan']['icon'] }}"></i>
                                        </div>
                                        <div class="panel-body">
                                            <div class="wdgt-text">
                                                {{ $labels['placement_plan']['label'] }}
                                            </div>
                                        </div>
                                    </section>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12 rmp_plan_modal">
                            <a href="{{ url('/service/rmp/' . $service_user_id) }}">
                                <div class="profile-nav alt profile-plan-div">
                                    <section class="panel text-center profile-square" style="height: 191px">
                                        <div class="plan-user-heading alt wdgt-row label-success">
                                            <i class="{{ $labels['rmp']['icon'] }}"></i>
                                        </div>
                                        <div class="panel-body">
                                            <div class="wdgt-text">
                                                {{ $labels['rmp']['label'] }}
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12 m-t-10">
                            <a href="{{ url('/service/bmp/' . $service_user_id) }}">
                                <div class="profile-nav alt profile-plan-div">
                                    <section class="panel text-center profile-square" style="height: 191px">
                                        <div class="plan-user-heading alt wdgt-row label-danger">
                                            <i class="{{ $labels['rmp']['icon'] }}"></i>
                                        </div>
                                        <div class="panel-body">
                                            <div class="wdgt-text">
                                                {{ $labels['bmp']['label'] }}
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12 m-t-10">
                            <a href="http://66.116.198.68:8099/Learner/Learner-Login/" target="_blank">
                                <div class="profile-nav alt profile-plan-div">
                                    <section class="panel text-center profile-square" style="height: 191px">
                                        <div class="plan-user-heading alt wdgt-row label-inverse">
                                            <i class="{{ $labels['education_record']['icon'] }}"></i>
                                        </div>
                                        <div class="panel-body">
                                            <div class="wdgt-text">
                                                {{ $labels['education_record']['label'] }}
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
