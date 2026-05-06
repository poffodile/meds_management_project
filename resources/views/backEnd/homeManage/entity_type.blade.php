@extends('backEnd.layouts.master')
@section('title', ' Entity Type')
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
                                    <!-- <div class="btn-group">
                                                                                <a href="{{ url('admin/homelist/add') }}">
                                                                                    <button id="editable-sample_new" class="btn btn-primary">
                                                                                        Add Home <i class="fa fa-plus"></i>
                                                                                    </button>
                                                                                </a>
                                                                            </div> -->
                                    @include('backEnd.common.alert_messages')
                                </div>

                                <button type="button" class="btn btn-primary m-10 openIncidentTypeModal"
                                    data-form-type="add" style="float:right;">Add Type</button>
                                <div class="space15"></div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div id="editable-sample_length" class="dataTables_length">
                                            <form method='post' action="{{ url('admin/safeguarding-type') }}"
                                                id="records_per_page_form">
                                                <label>
                                                    <select name="limit" size="1" aria-controls="editable-sample"
                                                        class="form-control xsmall select_limit">
                                                        <option value="10" {{ $limit == '10' ? 'selected' : '' }}>10
                                                        </option>
                                                        <option value="20" {{ $limit == '20' ? 'selected' : '' }}>20
                                                        </option>
                                                        <option value="30" {{ $limit == '30' ? 'selected' : '' }}>30
                                                        </option>
                                                    </select> records per page
                                                </label>
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <form method='get' action="{{ url('admin/stafftask-type') }}">
                                            <div class="dataTables_filter" id="editable-sample_filter">
                                                <label>Search: <input name="search" type="text"
                                                        value="{{ $search }}" aria-controls="editable-sample"
                                                        class="form-control medium"></label>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Entity Type</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            @forelse($incidentType as $key=>$label)
                                                <tr>
                                                    <td>{{ ++$key }}</td>
                                                    <td>{{ $label->type }}</td>
                                                    <td>
                                                        @if ($label->status == 1)
                                                            <a href="javascript:"
                                                                onclick="status_change('{{ $label->id }}',0)"
                                                                class="btn btn-success">Active</a>
                                                        @else
                                                            <a href="javascript:" class="btn btn-danger"
                                                                onclick="status_change('{{ $label->id }}',1)">Inactive</a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="javascript:void(0)" class="openIncidentTypeModal"
                                                            data-form-type="edit" data-id="{{ $label->id }}"
                                                            data-type="{{ $label->type }}"
                                                            data-status="{{ $label->status ?? '0' }}"><span
                                                                style = "color: #000"><i data-toggle="tooltip"
                                                                    title="Edit" class="fa fa-edit fa-lg"></i></span></a>
                                                        |
                                                        <a href="{{ url('admin/entity-type/delete/' . $label->id) }}"
                                                            class="delete"><i data-toggle="tooltip" title=""
                                                                class="fa fa-trash-o fa-lg"
                                                                data-original-title="Delete"></i></a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text text-center">No records</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <!-- page end-->
            <div class="modal fade" id="incidentType" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="incidentTypeTitle"> </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="incidentTypeForm">
                                <div class="form-group">
                                    <input type="hidden" name="staff_task_type_id" id="staff_task_type_id">
                                    <label class="">Entity Type<span class="radStar ">*</span></label>
                                    <input type="text" name="type" class="form-control checkVali"
                                        placeholder="Enter Entity Type" id="type">
                                </div>
                                <div class="form-group">
                                    <label class="">Status <span class="radStar ">*</span></label>
                                    <select name="status" id="status" class="form-control checkVali">
                                        <option selected disabled>Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
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
        </section>
    </section>
    <script>
        function status_change(id, status) {
            var token = '<?php echo csrf_token(); ?>'
            $.ajax({
                type: "POST",
                url: "{{ url('admin/entity-type/status-change') }}",
                data: {
                    id: id,
                    status: status,
                    _token: token
                },
                success: function(response) {
                    console.log(response);
                    if (response.success === true) {
                        location.reload();
                    } else {
                        alert("Something went wrong");
                        return false;
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + xhr.message);
                }
            });
        }
        $(document).on('click', '.openIncidentTypeModal', function() {
            var formType = $(this).data('formType');
            if (formType === 'add') {
                $("#incidentTypeTitle").text("Add Entity Type");
                $("#incidentTypeForm")[0].reset();
            } else if (formType === 'edit') {
                $("#incidentTypeTitle").text("Edit Entity Type");
                var type = $(this).data('type');
                var status = $(this).data('status');
                var id = $(this).data('id');

                $("#type").val(type);
                $("#status").val(status);
                $("#staff_task_type_id").val(id);
            } else {
                alert("Something went wrong");
                location.reload();
                return false;
            }
            $("#incidentType").modal('show');
        });
        $(document).on('click', '#saveChanges', function() {
            var type = $("#type").val();
            var status = $("#status").val();
            var incidentType_id = $("#staff_task_type_id").val();
            var url = "{{ url('admin/entity-type/add/') }}";
            if (incidentType_id) {
                url = "{{ url('admin/entity-type/edit/') }}";
            }
            var token = '<?php echo csrf_token(); ?>'
            var error = false;
            $('.checkVali').each(function() {
                if ($(this).val() == '' || $(this).val() == undefined) {
                    $(this).css('border', '1px solid red').focus();
                    error = true;
                    return false;
                } else {
                    $(this).css('border', '');
                    error = false;
                }
            });
            if (error) {
                return false;
            } else {
                $.ajax({
                    type: "POST",
                    url: url,
                    data: {
                        id: incidentType_id,
                        type: type,
                        status: status,
                        _token: token
                    },
                    success: function(response) {
                        // console.log(response);
                        if (response.success === true) {
                            location.reload();
                        } else {
                            alert("Something went wrong");
                            return false;
                        }
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText;
                        alert('Error - ' + errorMessage + "\nMessage: " + xhr.message);
                    }
                });
            }
        });
    </script>
    <!--main content end-->

@endsection
