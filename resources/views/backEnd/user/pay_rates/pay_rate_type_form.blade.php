@extends('backEnd.layouts.master')
@section('title', ' Rates Type')
@section('content')

    <?php
    if (isset($rateType)) {
        $action = url('admin/user/pay-rate-type/update/' . $rateType->id);
        $task = 'Edit';
        $form_id = 'EditUserPayRateType';
    } else {
        $action = url('admin/user/pay-rate-type/save');
        $task = 'Add';
        $form_id = 'AddUserPayRateType';
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
                            {{ $task }} Rates Type
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
                                        <label class="col-lg-2 control-label">Rate Type</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="type_name" class="form-control"
                                                placeholder="Rate Type"
                                                value="{{ $rateType->type_name ?? old('type_name') }}"
                                                maxlength="255">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Status</label>
                                        <div class="col-lg-10">
                                           <select name="status" class="form-control">
                                            <option value="1" {{ isset($rateType) && $rateType->status == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ isset($rateType) && $rateType->status == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>

                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <div class="row">
                                            <div class="col-lg-offset-2 col-lg-10">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="hidden" name="id" value="">
                                                <button type="submit" class="btn btn-primary">Save</button>
                                                <button type="button" class="btn btn-default"
                                                    name="cancel">Cancel</button>
                                                </a>
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
