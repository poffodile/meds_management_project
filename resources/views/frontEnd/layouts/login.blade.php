<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="ThemeBucket">
  <link rel="shortcut icon" type="image/x-icon" href="{{ asset('public/images/favicon.ico') }}">

  <title>{{ PROJECT_NAME }}: @yield('title','') </title>

  <link href="{{ url('public/frontEnd/css/bs3/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ url('public/frontEnd/css/bootstrap-reset.css') }}" rel="stylesheet">
  <link href="{{ url('public/frontEnd/css/font-awesome/css/font-awesome.css') }}" rel="stylesheet">

  <link href="{{ url('public/frontEnd/css/style.css') }}" rel="stylesheet">
  <link href="{{ url('public/frontEnd/css/style-responsive.css') }}" rel="stylesheet">
  <link href="{{ url('public/frontEnd/css/developer.css') }}" rel="stylesheet">

  <script src="{{ url('public/frontEnd/js/jquery.js') }}"></script>
  <script src="{{ url('public/frontEnd/js/bs3/bootstrap.min.js') }}"></script>
  <script type="text/javascript" src="{{ url('public/frontEnd/js/validation/formValidation.min.js') }}"></script>
  <script type="text/javascript" src="{{ url('public/frontEnd/js/validation/bootstrap.js') }}"></script>
  <script src="{{ url('public/frontEnd/js/jquery.validate.js') }}"></script>

</head>
@include('frontEnd.common.alert_messages')

<body class="login-body">

  <div class="container">

    <!-- <div align="center" style="padding-top:35px; margin-bottom:-75px;"><img src="{{ asset('public/images/scits1.png') }}" width="150" alt=""/></div> -->

    @yield('content')

  </div>

  <!-- Placed js at the end of the document so the pages load faster -->
  <!--Core js-->
  <!--  <script src="js/jquery.js"></script>
    <script src="bs3/js/bootstrap.min.js"></script>  -->



  <!-- VALIDATION FILES -->

</body>

</html>