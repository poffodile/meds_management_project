@extends('backEnd.layouts.master')
@section('title',' Department')
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
                        Department
                    </header>
                    <div class="clearfix clearfix_space">
                        <div class="btn-group">
                            <a href="{{ url('/admin/general-admin/department/add') }}" id="editable-sample_new" class="btn btn-primary"> Add New <i class="fa fa-plus"></i></a>
                        </div>
                    </div>

                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            <div class="clearfix">
                                @include('backEnd.common.alert_messages')
                            </div>
                            <div class="space15"></div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div id="editable-sample_length" class="dataTables_length">

                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Department </th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th width="20%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($departments as $dept)
                                        <tr>
                                            <td>{{ $dept->id }}</td>
                                            <td>{{ $dept->name }}</td>
                                            <td>{{ $dept->created_at->format('d-m-Y') }}</td>
                                            <td>
                                                <button class="btn btn-xs change-status" data-id="{{ $dept->id }}" data-status="{{ $dept->status }}">
                                                    @if($dept->status == 1)
                                                    <span class="label label-success">Active</span>
                                                    @else
                                                    <span class="label label-danger">Inactive</span>
                                                    @endif
                                                </button>
                                            </td>
                                            <td>
                                                <a href="{{ url('admin/general-admin/department/edit/'.$dept->id) }}" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i></a>
                                                <a href="{{ url('admin/general-admin/department/delete/'.$dept->id) }}" class="btn btn-danger btn-xs"><i class="fa fa-trash-o "></i></a>
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
        <!--main content end-->
</section>
<script>
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => el.style.display = 'none');
    }, 4000); // hides after 4 seconds


    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.change-status').forEach(function(button) {
            button.addEventListener('click', function() {
                const recordId = this.getAttribute('data-id');
                const currentStatus = this.getAttribute('data-status');
                const newStatus = currentStatus == 1 ? 0 : 1;

                if (confirm('Are you sure you want to change the status?')) {
                    fetch(`{{ url('/admin/general-admin/department/change-status/') }}/${recordId}`, {
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