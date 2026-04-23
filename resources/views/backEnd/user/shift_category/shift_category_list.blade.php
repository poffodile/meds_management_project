@extends('backEnd.layouts.master')
@section('title',' Shift Category')
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
                                    <h3>Shift Category</h3>
                                      <a href="{{ url('admin/user/shift-category/add') }}">
                                        <button id="editable-sample_new" class="btn btn-primary">
                                            Add Shift Category <i class="fa fa-plus"></i>
                                        </button>
                                    </a>
                                </div>
                                @include('backEnd.common.alert_messages')
                            </div>
                            <div class="space15"></div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                    <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Name</th>
                                        <th>Color</th>
                                        <th>Time Range</th>
                                        <th>Rate (£)</th>
                                        <th>Status</th>
                                        <th width="20%">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                      @foreach($categories as $category)
                                        <tr class="">
                                            <td>{{ date('d-m-Y', strtotime($category->created_at)) }}</td>
                                            <td>{{ $category->name }}</td>
                                            <td>
                                                @if($category->color)
                                                <span style="display:inline-block; width:20px; height:20px; background-color:{{ $category->color }}; border-radius:3px;"></span>
                                                <span style="vertical-align: top; margin-left: 5px;">{{ $category->color }}</span>
                                                @else
                                                -
                                                @endif
                                            </td>
                                            <td>
                                                @if($category->start_time && $category->end_time)
                                                    {{ date('H:i', strtotime($category->start_time)) }} - {{ date('H:i', strtotime($category->end_time)) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $category->rate ? '£' . number_format($category->rate, 2) : '-' }}</td>
                                            <td>
                                                @if($category->status == 1)
                                                    Active
                                                @else
                                                    Inactive
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ url('admin/user/shift-category/edit/'.$category->id) }}" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i></a>
                                                <a href="{{ url('admin/user/shift-category/delete/'.$category->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this Shift Category?');"><i class="fa fa-trash-o "></i></a>
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
