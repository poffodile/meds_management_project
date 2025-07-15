@extends('backEnd.layouts.master')
@section('title',' Purchase Expenses')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">


<!--main content start-->
<section id="main-content">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">
                    <header class="panel-heading">
                        Purchase Expenses Type
                    </header>
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
                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            <div class="clearfix clearfix_space">
                                <div class="btn-group">
                                    <a href="{{url('admin/sales-finance/purchase/purchase-type-add')}}" id="editable-sample_new" class="btn btn-primary">
                                        Add New <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="space15"></div>
                            <table class="table table-striped table-hover table-bordered" id="myTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Expenses Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase_expenses as $key => $value)
                                    <tr class="">
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ date('d-m-Y', strtotime($value->created_at)) }}</td>
                                        <td>{{ $value->title }}</td>
                                        <td>
                                            <button class="btn btn-xs change-status" data-id="{{ $value->id }}" data-status="{{ $value->status }}">
                                                @if($value->status == 1)
                                                <span class="label label-success">Active</span>
                                                @else
                                                <span class="label label-danger">Inactive</span>
                                                @endif
                                            </button>
                                        </td>
                                        <td>
                                            <a href="{{ url('admin/sales-finance/purchase/purchase-expenses-type-edit/'.$value->id) }}" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i></a>
                                            <a href="{{ url('admin/sales-finance/purchase/purchase-expenses-type-delete/'.$value->id) }}" class="btn btn-danger btn-xs"><i class="fa fa-trash-o "></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
    $('#myTable').DataTable();
});

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.change-status').forEach(function(button) {
            button.addEventListener('click', function() {
                const recordId = this.getAttribute('data-id');
                const currentStatus = this.getAttribute('data-status');
                const newStatus = currentStatus == 1 ? 0 : 1;

                if (confirm('Are you sure you want to change the status?')) {
                    fetch(`{{ url('/admin/sales-finance/purchase/change-status/') }}/${recordId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                status: newStatus
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Status updated successfully!');
                                location.reload();
                            } else {
                                alert('Failed to update status.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred. Please try again.');
                        });
                }
            });
        });
    });
</script>
@endsection