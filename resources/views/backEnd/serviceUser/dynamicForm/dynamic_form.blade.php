@extends('backEnd.layouts.master')
@section('title',':Dynamic Form')
@section('content')

<section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        View Form
                    </header>
                    <div class="panel-body">
                        <form method="post" action="{{ url('admin/service-user/dynamic-form/edit') }}">
                            <div class="position-center dynamic-form-width">
                                {!! $result !!}

                                <div class="form-group">
                                    <div class="row pad-0-left">
                                        <div class="col-lg-offset-3 col-lg-10">
                                            <div class="add-admin-btn-area">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="hidden" name="d_form_id" value="{{ $d_form_id }}">
                                                <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                                                <button type="submit" class="btn btn-primary save-btn" name="submit1" id="sbt-d-form">Save</button>
                                                <a href="{{ url('admin/service-user/dynamic-forms/'.$service_user_id) }}">
                                                    <button type="button" class="btn btn-default" name="cancel">Cancel</button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>

<script>
    $(document).ready(function() {
        $(document).on('click', '#sbt-d-form', function() {
            var form_title = $('input[name=\'title\']').val().trim();
            if (form_title == '') {
                $('input[name=\'title\']').addClass('red_border');
                return false;
            }
            // alert(form_title); return false;
        });
    });
</script>


@endsection