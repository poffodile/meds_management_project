@extends('backEnd.layouts.master')
@section('title', ' Shift Category')
@section('content')

    <?php
    if (isset($category)) {
        $action = url('admin/user/shift-category/update/' . $category->id);
        $task = 'Edit';
        $form_id = 'EditShiftCategory';
    } else {
        $action = url('admin/user/shift-category/save');
        $task = 'Add';
        $form_id = 'AddShiftCategory';
    }
    ?>

    <style type="text/css">
        .form-actions {
            margin: 20px 0px 0px 0px;
        }

        .col-lg-offset-2 .btn.btn-primary {
            margin: 0px 10px 0px 0px;
        }
    </style>


    <section id="main-content" class="">
        <section class="wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <section class="panel">
                        <header class="panel-heading">
                            {{ $task }} Shift Category
                        </header>
                        <div class="panel-body">
                            <div class="position-center">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @include('backEnd.common.alert_messages')
                                <form class="form-horizontal" role="form" method="post" action="{{ $action }}"
                                    id="{{ $form_id }}">

                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Category Name</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="name" class="form-control"
                                                placeholder="e.g. Day Shift, Night Shift"
                                                value="{{ $category->name ?? old('name') }}"
                                                maxlength="255">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Category Color</label>
                                        <div class="col-lg-10">
                                            <input type="color" name="color" class="form-control"
                                                value="{{ $category->color ?? old('color') ?? '#33cccc' }}"
                                                style="height: 40px; width: 100px; padding: 0;">
                                            <p class="help-block">Select a color to represent this shift category.</p>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Status</label>
                                        <div class="col-lg-10">
                                           <select name="status" class="form-control">
                                            <option value="1" {{ isset($category) && $category->status == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ isset($category) && $category->status == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <div class="row">
                                            <div class="col-lg-offset-2 col-lg-10">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <button type="submit" class="btn btn-primary">Save</button>
                                                <a href="{{ url('admin/user/shift-category') }}" class="btn btn-default">Cancel</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </section>

@endsection
