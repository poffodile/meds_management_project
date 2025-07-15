@extends('backEnd.layouts.master')
@section('title',' Time Sheet')
@section('content')

<!--main content start-->
<section id="main-content">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">
                    <header class="panel-heading">
                        Time Sheet
                    </header>
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            <div class="clearfix clearfix_space">
                                <div class="col-lg-3 col-sm-3">
                                    <input type="hidden" id="tax_id">
                                    <select class="form-control" name="user_id" id="getDataOnUsers">
                                        <option value="0">Please Select</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="btn-group">
                                    <a href="{{url('admin/sales-finance/time-sheet/add')}}" id="editable-sample_new" class="btn btn-primary"> Add New <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="space15"></div>
                            <table class="display table table-bordered table-striped" id="timeSheetTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User</th>
                                        <th>Date</th>
                                        <th>Hours</th>
                                        <th>Sleep</th>
                                        <th>Wake Night </th>
                                        <th>DIsturbance </th>
                                        <th>Annual Leave</th>
                                        <th>On Call</th>
                                        <th>Comments </th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
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
    const getData = "{{ url('/admin/sales-finance/time-sheet/get-data') }}";
    const deleteTimeSheet = "{{ url('/admin/sales-finance/time-sheet/delete') }}";
    const editUrlBase = "{{ url('admin/sales-finance/time-sheet') }}";
    $(document).on('click', '.openTimeSheetModel', function(e) {
        e.preventDefault(); // Prevent default <a> action

        const id = $(this).data('id');

        if (id) {
            window.location.href = `${editUrlBase}/edit/${id}`;
        } else {
            alert('No ID found');
        }
    });
</script>
<script type="text/javascript" src="{{ url('public\frontEnd\js\personal\timeSheet.js') }}"></script>
@endsection