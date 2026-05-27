@extends('frontEnd.layouts.master')
@section('title','CRM Dashboard Details')
@section('content')

@include('frontEnd.roster.common.roster_header')

<main class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="staffHeaderp">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3>CRM Details</h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ url('/roster/crm-dashboard') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4>CRM Record Details</h4>
                        <p>Detailed information will appear here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
