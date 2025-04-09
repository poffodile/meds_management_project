@extends('backEnd.layouts.master')

@section('title','Task Type')

@section('content')
<!--main content start-->
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->

        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            <div class="clearfix">

                                @include('backEnd.common.alert_messages')
                            </div>
                            <div class="space15"></div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div id="editable-sample_length" class="dataTables_length">
                                        <div class="btn-group">
                                            <a href="#" data-toggle="modal" class="open-modal" data-target="#secondModal">
                                                <button id="editable-sample_new" class="btn btn-primary">Add Task Type <i class="fa fa-plus"></i></button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                    <thead>
                                        <tr>
                                            <th>Task Type</th>
                                            <th>Status</th>
                                            <th width="20%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($task_type as $val)
                                        <tr>
                                            <td>{{ $val->title }}</td>
                                            <td>{{ $val->status == 1 ? 'Active' : 'Inactive' }}</td>
                                            <td><a href="#" class="edit"><span style="color: #000;"><i data-toggle="modal" title="Edit" data-id="{{ $val->id }}" data-title="{{ $val->title }}" data-status="{{ $val->status }}" data-target="#secondModal" class="fa fa-edit fa-lg open-modal"></i></a>
                                                <a href="{{ url('admin/general/task_type/delete?key=').base64_encode($val->id) }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o fa-lg"></i></a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->
    </section>
</section>
<!--main content end-->
<!-- The Second Modal -->
<div class="modal fade popupcloseBtn" id="secondModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"> </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" id="region_form">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="home_id" id="home_id" value="{{$home_id}}">
                    <div class="form-group row">
                        <label class="col-lg-3 col-sm-3 ">Task Type <span class="radStar ">*</span></label>
                        <div class="col-sm-9">
                             <input type="text" name="title" class="form-control" placeholder="Task Type" id="title">
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-lg-3 col-sm-3 ">Status</label>
                        <div class="col-sm-9">
                        <select name="status" id="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="saveChanges" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.open-modal').on('click', function() {
            var itemId = $(this).data('id');
            var itemTitle = $(this).data('title');
            var itemStatus = $(this).data('status');
            $('#id').val('');
            $('#title').val('');
            $('#status').val(1);
            $('.modal-title').text('');
            $('#saveChanges').text('');

            if (itemId) {
                // Editing existing record
                $('#id').val(itemId);
                $('#title').val(itemTitle);
                $('#status').val(itemStatus);
                $('.modal-title').text('Edit Payment Type');
                $('#saveChanges').text('Save Changes');
            } else {
                // Adding new record (clear form fields if needed)
              
                $('.modal-title').text('Add Task Type');
                $('#saveChanges').text('Add');
            }
        });

        $('#saveChanges').on('click', function() {
            var formData = $('#region_form').serialize();
            var message="Added Successfully Done";
            var id=$("#id").val();
            if(id !=''){
                message="Eddited Successfully Done";
            }
            $.ajax({
                url: '{{ url("admin/general/saveTaskType") }}',
                method: 'POST',
                data: formData,
                success: function(data) {
                    console.log(data);
                    if(data.vali_error){
                        alert(data.vali_error);
                        $("#title").css('border','1px solid red');
                        return false;
                    }else if(data.data && data.data.original && data.data.original.error){
                        alert(data.data.original.error);
                        return false;
                    }else{
                        alert(message)
                        $('#secondModal').modal('hide');
                        location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });
    });
    
    
</script>
@endsection