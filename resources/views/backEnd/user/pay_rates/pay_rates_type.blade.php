@extends('backEnd.layouts.master')
@section('title',' Rate Type')
@section('content')

<!--main content start-->
    <section id="main-content">
        <section class="wrapper">
        <!-- page start-->

        <div class="row">
            <div class="col-sm-12">
                <section class="panel">                   
                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            <div class="clearfix">
                                <div class="btn-group">
                                    <h3>Pay Rate Type</h3>
                                      <a href="{{ url('admin/user/pay-rate-type/add') }}">
                                        <button id="editable-sample_new" class="btn btn-primary">
                                            Add Pay Rate Type <i class="fa fa-plus"></i>
                                        </button>
                                    </a>
                                </div>
                                @include('backEnd.common.alert_messages')
                            </div>
                            <div class="space15"></div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div id="editable-sample_length" class="dataTables_length">
                                        <form method='post' action="" id="records_per_page_form">
                                            <label>
                                                <select name="limit"  size="1" aria-controls="editable-sample" class="form-control xsmall select_limit">
                                                    <option value="10" >10</option>
                                                    <option value="20" >20</option>
                                                    <option value="30" >30</option>
                                                </select> records per page
                                            </label>
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        </form>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <form method='get' action="">
                                        <div class="dataTables_filter" id="editable-sample_filter">
                                            <label>Search: <input name="search" type="text" value="" aria-controls="editable-sample" class="form-control medium" ></label>
                                            <!-- <button class="btn search-btn" type="submit"><i class="fa fa-search"></i></button>   -->
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                    <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th width="20%">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                      @foreach($rateTypes as $type)
                                        <tr class="">
                                            <td>{{ date('d-m-Y', strtotime($type->created_at)) }}</td>
                                            <td>{{ $type->type_name }}</td>
                                            <td>
                                                @if($type->status == 1)
                                                    Active
                                                @else
                                                    Inactive
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ url('admin/user/pay-rate-type/edit/'.$type->id) }}" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i></a>
                                                <a href="{{ url('admin/user/pay-rate-type/delete/'.$type->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this item?');"><i class="fa fa-trash-o "></i></a>
                                            </td>
                                        </tr>
                                        @endforeach
                                  
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->
        </section>
    </section>
<!--main content end-->

@endsection