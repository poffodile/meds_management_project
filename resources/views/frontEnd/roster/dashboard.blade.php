@extends('frontEnd.layouts.master')
@section('title','Dashboard')
@section('content')



<section id="main-content">
    <div class="wrapper ps-0 pe-0 p-t-80">
        <div class="container">
            <div class="row">
                <div class="col-md-">
                    <div class="panel">
                        <header class="panel-heading"> Rota Management</header>
                        <div class="panel-body rosterBox">
                            <div>
                                <h1>
                                    <a href="{{ url('roster/dashboard') }}">Dashboard</a>
                                </h1>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



@endsection