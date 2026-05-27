@extends('frontEnd.layouts.lockscreen')
@section('title','Send Bug Report')
@section('content')

  <body class="body-500">

    <div class="error-head"> </div>

    <div class="container pg-nt-fond">

      <section class="error-wrapper text-center">
          <h1><img src="{{ asset('/public/images/500.png') }}" alt=""></h1>
          <div class="error-desk pg-nt-fond">
              <h2>OOOPS!!!</h2>
              <p class="nrml-txt-alt pg-nt-fond">Something went wrong.</p>
              <p><!-- Why not try refreshing you page? Or  -->You can <a href="#reportBugModal" data-toggle="modal" class="sp-link">contact our support</a> if the problem persists.</p>
          </div>
          <a href="{{ url('/roster') }}" class="back-btn"><i class="fa fa-home"></i> Back To Home</a>
      </section>

    </div>

    @include('frontEnd.serviceUserManagement.elements.report_bug')

  </body>
@endsection