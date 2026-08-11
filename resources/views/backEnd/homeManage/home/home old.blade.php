@extends('frontEnd.layouts.login')
@section('title','Home')
@section('content')
   

      <nav class="navbar navbar-default right-menu">
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><img src="{{ asset('/public/images/userProfileImages/default_user.jpg') }}"> Admin <span class="caret"></span></a>
            <ul class="dropdown-menu" role="menu">
              <li><a href="{{ url('admin/logout') }}"><i class="fa fa-key"></i> Log Out</a></li>
            </ul>
          </li>
        </ul>
      </nav>



      <div class="container home-container">
        <div class="col-sm-6 col-sm-offset-3 col-xs-12">
          
        <?php foreach($home_results as $home)
        { ?>

          <div class="wrap-div mb20">
            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
              <div class="wrap-div">
                <a href="javascript:void(0)">
                  
                    <?php
                        //$image = home.'/photo-dmc.jpg';
                        // if(!empty($hresults->image))
                        // {
                            $image = home.'/'.$home->image;
                        // }
                    ?>
                    <img src="{{ $image}}" class="img-responsive">
                </a>              
              </div>
            </div>
            <div class="col-lg-9 col-md-9 col-sm-6 col-xs-12">
              <div class="wrap-div">
                <div class="outer-div">
                  <div class="inner-div">
                   
                    <h1><a href="{{ url('admin/home/'.$home->id) }}">{{ $home->title }}</a></h1>
                  </div>
                </div> 
              </div>
            </div>
          </div>
        <?php } ?>

<!--           <div class="wrap-div mb20">
            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
              <div class="wrap-div">
                <a href="javascript:void(0)">
                  <img src="{{ asset('/public/images/home/apollo-hospital-image.jpg') }}" class="img-responsive">
                </a>              
              </div>
            </div>
            <div class="col-lg-9 col-md-9 col-sm-6 col-xs-12">
              <div class="wrap-div">
                <div class="outer-div">
                  <div class="inner-div">
                    <h1><a href="javascript:void(0)">Detroit Medical Center</a></h1>
                  </div>
                </div> 
              </div>
            </div>
          </div>

          <div class="wrap-div mb20">
            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
              <div class="wrap-div">
                <a href="javascript:void(0)">
                  <img src="{{ asset('/public/images/home/photo-dmc.jpg') }}" class="img-responsive">
                </a>              
              </div>
            </div>
            <div class="col-lg-9 col-md-9 col-sm-6 col-xs-12">
              <div class="wrap-div">
                <div class="outer-div">
                  <div class="inner-div">
                    <h1><a href="javascript:void(0)">Detroit Medical Center</a></h1>
                  </div>
                </div> 
              </div>
            </div>
          </div>  -->   
        
        </div>
      </div>



@endsection