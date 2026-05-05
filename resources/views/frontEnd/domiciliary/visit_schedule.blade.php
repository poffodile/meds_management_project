@extends('frontEnd.layouts.master')
@section('title','Visit Schedule')
@section('content')

@include('frontEnd.roster.common.roster_header')
<section id="main-content">
    <div class="wrapper ps-0 pe-0 ">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="m-t-30">
                        <div class="panel">
                            <header class="panel-heading"> Visit Schedule</header>
                            <div class="panel-body">
                                <h3>Welcome to Visit Schedule</h3>
                                <p>This page is part of the Domiciliary Care section.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
