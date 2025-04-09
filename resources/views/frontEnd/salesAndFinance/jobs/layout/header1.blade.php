<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ PROJECT_NAME }} @yield('title','') </title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('public/images/favicon.ico') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,300,0,0" />
    <link rel="stylesheet" href="https://www.ville-pont-eveque.fr/tools/library/DataTables/media/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://www.ville-pont-eveque.fr/tools/library/DataTables/extensions/Select/css/select.dataTables.css" />
    @if(Auth::user()->design_layout == 0)
    <link href="{{ url('public/frontEnd/css/style.css') }}" rel="stylesheet">
    <link href="{{ url('public/frontEnd/css/style-responsive.css') }}" rel="stylesheet">
    <link href="{{ url('public/frontEnd/css/developer.css') }}" rel="stylesheet">
    @else
    <link href="{{ url('public/frontEnd/css/dyslexia/dyslexia_style.css') }}" rel="stylesheet">
    <link href="{{ url('public/frontEnd/css/dyslexia/dyslexia_style-responsive.css') }}" rel="stylesheet">
    <link href="{{ url('public/frontEnd/css/dyslexia/dyslexia_developer.css') }}" rel="stylesheet">
    @endif
    <link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/dashboard.css')}}" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .select-dyslexia {
            float: right;
        }

        .select-dyslexia select {
            background-color: #1f88b5;
            padding: 6px 12px;
            display: block;
            height: 34px;
            border-radius: 4px;
            font-size: 14px;
        }

        .select-dyslexia select>option {
            background-color: #1f88b5;
        }

        section.main_section_page {
            padding: 90px 20px 20px !important;
            background-color: #fff;
        }

        .new_header {
            height: 80px;
        }

        .new_header .brand a span {
            color: #fff;
        }

        .horizontal-menu {
            padding-left: 20px;
        }

        .top-nav img {
            border: 2px solid #d9d9d9;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <header class="header fixed-top">
        <div class="new_header">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <div class="brand">
                        <a href="{{ url('/') }}" class="logo">
                            <span>SCITS </span>
                        </a>
                    </div>
                    <div class="horizontal-menu ">
                        <div class="wlcome-header"> Welcome Back, </div>
                    </div>
                </div>
                <div class="col-md-7 d-flex justify-content-end top-nav mt-0 align-items-center cus-nav">
                    <div class="theme_btn">
                        <?php
                        if (Auth::check()) {
                            $design_layout_id = Auth::user()->design_layout;
                        } else {
                            $design_layout_id = '0';
                        }
                        ?>
                        <div class="select-dyslexia">
                            <select class="profileDrop border-0 me-4 sel_design_layout" name="design_layout_id">
                                <option value="0" <?php if (isset($design_layout_id)) {
                                                        if ($design_layout_id == '0') {
                                                            echo "selected";
                                                        }
                                                    } ?>>Default</option>
                                <option value="1" <?php if (isset($design_layout_id)) {
                                                        if ($design_layout_id == '1') {
                                                            echo "selected";
                                                        }
                                                    } ?>>Dyslexia</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4 col-xs-12 col-lg-4">
                        <ul class="nav pull-left top-menu">
                            <!-- user login dropdown start-->
                            <li class="dropdown">
                                <a data-bs-toggle="dropdown" aria-expanded="false"  type="button" href="#">
                                    <?php
                                    $user_image = Auth::user()->image;
                                    if (empty($user_image)) {
                                        $user_image = 'default_user.jpg';
                                    }
                                    $current_path = Request::path();
                                    $user_id = Auth::user()->id;
                                    ?>
                                    <!-- <img alt="" src="{{ userProfileImagePath.'/'.$user_image }}"> -->
                                    <!-- Komal -->
                                    <img alt="" src="{{ env('APP_URL') }}/{{ userProfileImagePath.'/'.$user_image }}">
                                    <span class="username">{{ ucfirst(Auth::user()->name) }}</span>
                                    <b class="caret"></b>
                                </a>
                                <ul class="dropdown-menu extended logout">
                                    <li><a class="dropdown-item" href="{{ url('/my-profile/'.$user_id) }}"> <i class="fa fa-user-circle"></i> My Profile </a></li>
                                    <!-- <li><a class="dropdown-item" href="#" class="add_user"> <i class=" fa fa-user"></i> Add user </a></li>
                                    <li><a class="dropdown-item" href="#dynmicFormModal" data-toggle="modal"> <i class="fa fa-bolt"></i> Forms </a></li>
                                    <li><a class="dropdown-item" href="{{ url('/general-admin') }}"><i class="fa fa-cogs"></i> General Admin </a></li>
                                    <li><a class="dropdown-item" href="{{ url('/lock?path='.$current_path) }}"><i class="fa fa-lock"> </i> Lock</a></li>
                                    <li><a class="dropdown-item" href="#" class="hndovr_logbk"><i class="fa-solid fa-address-book"></i> Hand Over </a></li> -->
                                    <li><a class="dropdown-item" href="{{ url('/logout') }}"><i class="fa fa-key"></i> Log Out</a></li>
                                    <!-- Code given By Ethan start -->
                                    @if(Auth::user()->user_type == "A")
                                    <li id="switch_menu_itm"><a class="dropdown-item" href="{{ url('/switch_home') }}"><i class="fa fa-home"></i> Switch Home</a></li>
                                    @endif
                                    <!-- Code given By Ethan End -->
                                </ul>
                            </li>
                            <!-- user login dropdown end -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script>
        $(document).ready(function() {
            $(document).on('change', '.sel_design_layout', function() {

                var design_layout_id = $('select[name=design_layout_id]').val();
                var normal_layout_id = "{{ url('/change-design-layout/0') }}";
                var dyslexia_layout_id = "{{ url('/change-design-layout/1') }}";
                var no_layout_id = "{{ url('/') }}";
                if (design_layout_id == '0') {
                    location.href = normal_layout_id;
                } else if (design_layout_id == '1') {
                    location.href = dyslexia_layout_id;
                } else {
                    location.href = no_layout_id;
                }
            });
        });
    </script>
    <script type="text/javascript">
        $(".hndovr_logbk").click(function() {
            $('.submt-srvc-user').click();
            // $(document).on('click','.hndovr_logbk',function(){
            // $('#ServiceUserlistModal').modal('show');
            $('#HandoverlogBookModal').modal('show');
        });
    </script>