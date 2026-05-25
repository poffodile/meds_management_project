@extends('frontEnd.layouts.lockscreen')
@section('title','Page Not Found')
@section('content')

  <body class="body-404">

    <div class="error-head"> </div>

    <div class="container pg-nt-fond">

      <section class="error-wrapper text-center">
          <h1><img src="{{ asset('/public/images/404.png') }}" alt=""></h1>
          <div class="error-desk pg-nt-fond">
              <h2>Page not found</h2>
              <p class="nrml-txt pg-nt-fond">We Couldn’t Find This Page</p>
          </div>
          <a href="{{ url('/roster') }}" class="back-btn"><i class="fa fa-home"></i> Back To Home</a>
      </section>

    </div>
    
  </body>
@endsection 