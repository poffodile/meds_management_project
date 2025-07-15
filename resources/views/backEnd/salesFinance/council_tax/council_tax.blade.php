@extends('backEnd.layouts.master')
@section('title',' Council Tax')
@section('content')

<!--main content start-->
<section id="main-content">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">

                    @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif
                    @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif
                    <header class="panel-heading">
                        Council Tax
                    </header>
                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            <div class="clearfix clearfix_space">
                                <div class="btn-group">
                                    <a href="{{url('admin/finance/council-tax-add')}}" id="editable-sample_new" class="btn btn-primary">
                                        Add New <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="space15"></div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered" id="council_tax">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Flat number <br> (if applicable)</th>
                                            <th>Address</th>
                                            <th>PostCode</th>
                                            <th>Council</th>
                                            <th>No of Bedrooms</th>
                                            <th>Owned by Omega</th>
                                            <th>Occupancy</th>
                                            <th>Exempt</th>
                                            <th>Account number</th>
                                            <th>Last bill</th>
                                            <th>Bill Start Date</th>
                                            <th>Bill End Date</th>
                                            <th>Amount paid</th>
                                            <th>Additional Notes</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($council_tax as $key => $value)
                                        <tr class="">
                                            <th>{{ $key + 1 }}</th>
                                            <td>{{ $value->flat_number }}</td>
                                            <td>{{ $value->address }}</td>
                                            <td>{{ $value->post_code }}</td>
                                            <td>{{ $value->council }}</td>
                                            <td>{{ $value->no_of_bedrooms }}</td>
                                            <td>{{ $value->owned_by_omega == 1 ? 'Yes' : 'No' }}</td>
                                            <td>{{ $value->occupancy }}</td>
                                            <td>{{ $value->exempt == 1 ? 'Yes' : 'No' }} </td>
                                            <td>{{ $value->account_number }}</td>
                                            <td>{{ date('d-m-Y', strtotime($value->last_bill_date)) }}</td>
                                            <td>{{ date('d-m-Y', strtotime($value->bill_period_start_date)) }}</td>
                                            <td>{{ date('d-m-Y', strtotime($value->bill_period_end_date)) }}</td>
                                            <td>{{ $value->amount_paid }}</td>
                                            <td>{{ $value->additional }}</td>
                                            <td><a class="edit" href="{{ url('admin/finance/council-tax-edit/'.$value->id) }}"><i class="fa fa-edit"></i></a> |
                                                <a class="text-danger deleteBtn" href="{{ url('admin/finance/council-tax-delete/'.$value->id) }}"><i class="fa fa-trash-o"></i></a>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @if($council_tax->isEmpty())
                                        <tr>
                                            <td colspan="14" class="text-center">No records found</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- page end-->
    </div>
</section>

<script>
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => el.style.display = 'none');
    }, 4000); // hides after 4 seconds

    $(document).ready(function() {
        $('#council_tax').DataTable({
            dom: 'Blfrtip',
            buttons: [{
                    extend: 'csv',
                    text: 'Export' // Rename button
                },
                'colvis'
            ]
        });
    });
</script>
@endsection