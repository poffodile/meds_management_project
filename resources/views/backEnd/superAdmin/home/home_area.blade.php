@extends('backEnd.layouts.master')
@section('title','Home Areas')
@section('content')

<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        Home Areas
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-cog"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>
                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            <div class="clearfix">
                                <div class="btn-group">
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#addAreaModal">
                                        Add Area <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                                @include('backEnd.common.alert_messages')
                            </div>
                            <div class="space15"></div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                    <thead>
                                        <tr>
                                            <th>Area Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($home_areas->isEmpty())
                                            <tr>
                                                <td colspan="2" class="text-center">No areas found.</td>
                                            </tr>
                                        @else
                                            @foreach($home_areas as $area)
                                                <tr>
                                                    <td>{{ $area->name }}</td>
                                                    <td class="action-icn">
                                                        <a href="javascript:void(0)" class="edit edit-area" data-id="{{ $area->id }}" data-name="{{ $area->name }}"><i class="fa fa-edit" title="Edit"></i></a>
                                                        <a href="{{ url('admin/system-admin/home/home_area/delete/'.$area->id) }}" class="delete" onclick="return confirm('Are you sure you want to delete this area?')"><i class="fa fa-trash-o" title="Delete"></i></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>

<!-- Add Area Modal -->
<div class="modal fade" id="addAreaModal" tabindex="-1" role="dialog" aria-labelledby="addAreaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add New Home Area</h4>
            </div>
            <form action="{{ url('admin/system-admin/home/home_area/add/'.$home_id) }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Area Name</label>
                        <input type="text" name="area_name" class="form-control" placeholder="Enter Area Name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                    <button class="btn btn-primary" type="submit">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Area Modal -->
<div class="modal fade" id="editAreaModal" tabindex="-1" role="dialog" aria-labelledby="editAreaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Edit Home Area</h4>
            </div>
            <form id="editAreaForm" action="" method="post">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Area Name</label>
                        <input type="text" name="area_name" id="edit_area_name" class="form-control" placeholder="Enter Area Name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                    <button class="btn btn-primary" type="submit">Update changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.edit-area').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            $('#edit_area_name').val(name);
            $('#editAreaForm').attr('action', "{{ url('admin/system-admin/home/home_area/edit') }}/" + id);
            $('#editAreaModal').modal('show');
        });
    });
</script>

@endsection
