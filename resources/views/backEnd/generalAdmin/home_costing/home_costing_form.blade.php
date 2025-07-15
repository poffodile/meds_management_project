@extends('backEnd.layouts.master')
@section('title',' Home Costing')
@section('content')

<section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
			<div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        Home Costing Add
                    </header>
                    <div class="panel-body">
                        <div class="position-center">
                            <form class="form-horizontal" role="form" method="post" action="" id="" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Name</label>
                                    <div class="col-lg-10">
                                        <input type="text" name="title" class="form-control" placeholder="Name" value="" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Payout (Per Annum)</label>
                                    <div class="col-lg-10">
                                        <input type="text" name="payout" class="form-control" placeholder="Enter Amount Per Annum" value="" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Per Month</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="Per Month" class="form-control" placeholder="" value="" >
                                    </div>
                                     <label class="col-lg-2 control-label">Per Week</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="Per Week" class="form-control" placeholder="" value="" >
                                    </div>
                                </div>       

                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-lg-offset-2 col-lg-10">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <button type="submit" class="btn btn-primary" name="submit1">Save</button>
                                                <a href="{{ url('admin/general-admin/petty/cash') }}">
                                                    <button type="button" class="btn btn-default" name="cancel">Cancel</button>
                                                </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
	</section>
</section>						

@endsection