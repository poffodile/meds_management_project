<!-- send Modification request Modal Start -->

<div class="modal fade" id="ModifyRequestModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

                <h4 class="modal-title" id="myModalLabel"> Modification Request </h4>

            </div>

            <form method="post" action="{{ url('/service/earning-scheme/incentive/suspend/save') }}">

                <div class="modal-body">

                    <div class="row">

                        

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">

                            <label class="col-md-1 m-t-5 text-right">Detail</label>

                            <div class="col-md-10">

                                <textarea name="suspended_detail" value="" rows="5" class="form-control"></textarea>

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

<!-- send Modification request Modal end -->