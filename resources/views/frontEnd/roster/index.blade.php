@extends('frontEnd.layouts.master')
@section('title','Roaster Management')
@section('content')


<section id="main-content">
    <div class="wrapper ps-0 pe-0 p-t-80">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <header class="panel-heading"> Rota Management</header>
                        <div class="panel-body rosterBox">
                            @foreach($departments as $department)
                                <div class="col-md-6 col-sm-6 col-xs-6">
                                    <a href="{{ url('roster/dashboard') }}">
                                        <div class="sys-mngmnt-box">                                        
                                            <div> 
                                                <div class="sys-mngmnticon">
                                                    <i class="{{ $department->icon }}"></i> 
                                                </div>
                                            </div>
                                            <div class="rotsBoxRightCont">
                                                <h4>{{ $department->name }} </h4>
                                                <p> {{ $department->description }} </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection