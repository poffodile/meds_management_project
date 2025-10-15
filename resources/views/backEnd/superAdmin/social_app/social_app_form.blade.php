@extends('backEnd.layouts.master')
@section('title', ' Socail App Form')
@section('content')
    <style type="text/css">
        .form-actions {
            margin: 20px 0px 0px 0px;
        }
        .form-actions .btn.btn-primary {
            margin: 0px 10px 0px 0px;
        }
    </style>

    <section id="main-content" class="">
        <section class="wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <section class="panel">
                        <header class="panel-heading">

                            <?php if (!isset($social_app)) {
                                echo 'Add';
                                $form_id = 'SuperAdminSocailAppAddForm';
                            } else {
                                echo 'Edit';
                                $form_id = 'SuperAdminSocailAppEditForm';
                            } ?>

                            Social App
                        </header>
                        <div class="panel-body">
                            <div class="position-center">
                                <form class="form-horizontal" role="form" method="post" action=""
                                    id="{{ $form_id }}" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Name</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="name" class="form-control" placeholder="name"
                                                value="{{ isset($social_app->name) ? $social_app->name : '' }}"
                                                maxlength="255">
                                        </div>
                                    </div>
                                    {{-- <div class="form-group">
                                <label class="col-lg-2 control-label">Icon</label>
                                <div class="col-lg-10">
                                    <input type="text" name="icon" class="form-control" placeholder="icon" value="{{ (isset($social_app->icon)) ? $social_app->icon : '' }}" maxlength="255">
                                </div>
                            </div> --}}

                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Icon</label>
                                        <div class="col-lg-10">
                                            <span class="group-ico icon-box" data-toggle="modal" href="#socialIcon"><i
                                                    class="{{ isset($social_app->icon) ? $social_app->icon : 'fa fa-info' }}"></i>
                                            </span>
                                            <input type="hidden" name="icon"
                                                value="{{ isset($social_app->icon) ? $social_app->icon : 'fa fa-info' }}"
                                                class="risk_icon" maxlength="255">
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <div class="row">
                                            <div class="col-lg-offset-2 col-lg-10">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="hidden" name="user_id"
                                                    value="{{ isset($social_app->id) ? $social_app->id : '' }}">
                                                <button type="submit" class="btn btn-primary" name="submit1">Save</button>
                                                <a href="{{ url('super-admin/social-apps') }}">
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

    <!-- Font awesome(icons) -->
    @include('backEnd.common.social_icon_list')
    <!-- Font awesome(icons) end -->

    <script>
        $(document).ready(function() {
            $('#icons-fonts .fa-hover a').on('click', function(e) {
                e.preventDefault();
                var selectedIcon = $(this).find('i').attr('class');
                $('.icon-box i').attr('class', selectedIcon);
                $('.risk_icon').val(selectedIcon);
                $('#socialIcon').modal('hide');
            });
        });
    </script>
@endsection
