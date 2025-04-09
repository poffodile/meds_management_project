@extends('backEnd.layouts.master')
@section('title',' Earning Scheme ')
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
                                    <a href="earning-scheme/add">
                                        <button id="editable-sample_new" class="btn btn-primary">
                                            Add Earning Category  <i class="fa fa-plus"></i>
                                        </button>
                                    </a>    
                                </div>
                               @include('backEnd.common.alert_messages')
                            </div>
                            <div class="space15"></div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div id="editable-sample_length" class="dataTables_length">
                                        <form method='post' action="{{ url('admin/earning-scheme') }}" id="records_per_page_form">
                                            <label>
                                                <select name="limit"  size="1" aria-controls="editable-sample" class="form-control xsmall select_limit">
                                                    <option value="10" {{ ($limit == '10') ? 'selected': '' }}>10</option>
                                                    <option value="20" {{ ($limit == '20') ? 'selected': '' }}>20</option>
                                                    <option value="30" {{ ($limit == '30') ? 'selected': '' }}>30</option>
                                                </select> records per page
                                            </label>
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        </form>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <form method='get' action="{{ url('admin/earning-scheme') }}">
                                        <div class="dataTables_filter" id="editable-sample_filter">
                                            <label>Search: <input name="search" type="text" value="{{ $search }}" aria-controls="editable-sample" class="form-control medium" maxlength="255"></label>    
                                            <!-- <button class="btn search-btn" type="submit"><i class="fa fa-search"></i></button>   -->
                                        </div>
                                    </form>
                                </div>
                            </div>


                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered" id="editable-sample">

                                    <thead>
                                    <tr>
                                        
                                        <th>Title</th>
                                        <th>Icon</th>
                                        <th>Status</th>
                                        <th width="20%">Actions</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        if($earning_scheme_results->isEmpty())   
                                        {
                                                echo '<tr style="text-align:center">
                                                      <td colspan="4">No Records found.</td>
                                                      </tr>';
                                        } 

                                        else
                                        {
                                            foreach($earning_scheme_results as $key => $value) 
                                            {  ?>

                                        <tr class="">
                                            
                                            <td>{{ $value->title }}</td>
                                            <td class="action-icn">
                                                <a><i class="{{ $value->icon }}" ></i></a>
                                            </td>
                                            <?php 
                                                $status = '';
                                                if($value->status == 1)
                                                {
                                                    $status = 'Active';
                                                } 
                                                else if($value->status == 0)
                                                {
                                                    $status = 'Inactive';                                         
                                                } 
                                                
                                                else{} 
                                            ?>
                                            <td>{{ $status }}</td>
                                            <td class="action-icn">
                                                <a href="{{ url('admin/earning-scheme/edit/'.$value->id) }}" class="edit"><span><i data-toggle="tooltip" title="Edit" class="fa fa-edit fa-lg"></i></span></a>

                                                <a href="{{ url('admin/earning-scheme/delete/'.$value->id) }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o fa-lg"></i></a>

                                                <a href="{{ url('admin/earning-scheme/incentive/'.$value->id) }}"><span><i class="fa fa-italic fa-lg" title="Incentives" aria-hidden="true"></i></span></a>
                                            </td>

                                            <!-- <td class="action-icn">
                                                <a href="{{ url('admin/earning-scheme/edit/'.$value->id) }}" class="edit"><i data-toggle="tooltip" title="Edit" class="fa fa-edit"></i></a>
                                                <a href="{{ url('admin/earning-scheme/delete/'.$value->id) }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o"></i></a>
                                                <a href="{{ url('admin/earning-scheme/incentive/'.$value->id) }}"><i class="fa fa-italic" title="Incentives" aria-hidden="true"></i></a>
                                            </td> -->
                                        </tr>
                                        <?php } } ?>
                                  
                                    </tbody>
                                </table>
                            </div>

                            <!-- <div class="row"><div class="col-lg-6"><div class="dataTables_info" id="editable-sample_info">Showing 1 to 28 of 28 entries</div></div><div class="col-lg-6"><div class="dataTables_paginate paging_bootstrap pagination"><ul><li class="prev disabled"><a href="#">← Prev</a></li><li class="next"><a href="#">Next → </a></li></ul></div></div></div> -->


                            @if($earning_scheme_results->links() !== null) 
                            {{ $earning_scheme_results->links() }}
                            @endif
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->
        </section>
    </section>
    <!--main content end-->

    <!--script for this page only-->
<!-- <script src="{{ url('public/backEnd/js/table-editable.js') }}"></script> -->

<!-- END JAVASCRIPTS -->
@endsection