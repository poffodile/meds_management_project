@extends('backEnd.layouts.master')
@section('title',' Department Form')
@section('content')

<?php
if (isset($department)) {
    $task = "Edit";
} else {
    $task = "Add";
}
?>

<section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        {{ $task }} Department
                    </header>
                    <div class="panel-body">
                        <div class="position-center">
                            <form class="form-horizontal" role="form" method="post" action="{{ url('admin/general-admin/department/save') }}" id="">
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Department Name</label>
                                    <div class="col-lg-10">
                                        <input type="hidden" id="" name="id" value="{{ isset($department) ? $department->id : '' }}">
                                        <input type="text" name="name" class="form-control" placeholder="Name" value="{{ isset($department->name) ? $department->name : '' }}" maxlength="255">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Status</label>
                                    <div class="col-lg-10">
                                        <div class="admin-name-list">
                                            <select name="status" class="form-control">
                                                <option value="1" {{ isset($department->status) && $department->status == 1 ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ isset($department->status) && $department->status == 0 ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-lg-offset-2 col-lg-10">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <button type="submit" class="btn btn-primary" name="submit1">Save</button>
                                            <a href="{{ url('admin/general-admin/agenda/meetings') }}">
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