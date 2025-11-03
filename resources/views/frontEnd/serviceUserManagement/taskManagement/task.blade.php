@if (!isset($page_title))
    $page_title = '';
@endif
@extends('frontEnd.layouts.master')
@section('title', 'Service Calendar ' . $page_title)
@section('content')
    <style type="text/css">
        .event_label_close.alert-danger {
            font-size: 25px;
            margin-top: -5px;
        }

        .calenderScroll {
            height: 500px;
            overflow: auto;
        }

        .cus-calendar .external-event {
            position: relative;
        }

        .external-event .fa {
            color: #fff;
            text-align: center;
            border-radius: 100%;
            cursor: pointer;
            position: absolute;
            right: 8px;
            top: 25%;
        }

        .label-mandatory {
            background-color: #7f2222b0;
        }

        .maroonList {
            background: #7f2222b0 !important;
        }

        .calenderScroll {
            height: 500px;
            overflow: unset !important;
        }
    </style>

    <section id="main-content">
        <section class="wrapper">
            <!-- page start-->
            <section class="panel cus-calendar">
                <div class="panel-body">
                    <!-- page start-->
                    <div class="row">
                        <div class="col-md-12 p-0">
                            <div class="panel">
                                <header class="panel-heading px-5">
                                    <h4> {{ $page_title }}</h4>
                                </header>
                                <div class="panel-body">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="jobsection justify-content-end">
                                                    <a href="javaScript:void(0)" type="button"
                                                        class="profileDrop openServiceUserTaskModel" data-action="add"> <i
                                                            class="fa fa-plus"></i> Add</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="productDetailTable mb-4 table-responsive">
                                            <table class="table border-top border-bottom tablechange" id="timeSheetTable">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Task</th>
                                                        <th>Date</th>
                                                        <th>Time</th>
                                                        <th>Status</th>
                                                        <th>Comments </th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($tasks as $task)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $task->task }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($task->date)->format('d-m-Y') }}
                                                            </td>
                                                            <td>{{ date('h:i A', strtotime($task->time)) }}</td>
                                                            <td>
                                                                @if (trim(strtolower($task->status)) === 'active')
                                                                    <a href="javascript:void(0)"
                                                                        class="openTaskCommentModel"
                                                                        data-action="add">Active</a>
                                                                @else
                                                                    Inactive
                                                                @endif
                                                            </td>
                                                            <td>{{ $task->comments }}</td>
                                                            <td><a href="javascript:void(0)"
                                                                    class="editTask openServiceUserTaskModel"
                                                                    data-action="edit" data-id="{{ $task->id }}"
                                                                    data-task="{{ $task->task }}"
                                                                    data-date="{{ $task->date }}"
                                                                    data-time="{{ $task->time }}"
                                                                    data-status="{{ $task->status }}"
                                                                    data-comments="{{ $task->comments }}" title="Edit"><i
                                                                        class="fa fa-pencil text-primary"></i></a>
                                                                &nbsp;
                                                                <a href="javascript:void(0)" class="deleteTask"
                                                                    data-id="{{ $task->id }}" title="Delete"><i
                                                                        class="fa fa-trash text-danger"></i></a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <!-- End off main Table -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- page end-->
                </div>
            </section>
            <!-- page end-->
        </section>
    </section>

    <div class="modal fade" id="addServiceUserTaskModal" tabindex="-1" aria-labelledby="addServiceUserTaskModalLabel"
        aria-hidden="true">
        <div class="modal-dialog  modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                    <h4 class="modal-title" id="modalTitle">Add Task</h4>
                </div>
                <div class="modal-body">
                    <form action="" id="service_user_task" class="customerForm">
                        <div class="row">
                            <div class="col-md-12 col-lg-12 col-xl-12">
                                <div class="row formDtail ps-4 pe-4">
                                    <div class="col-md-6 form-group">
                                        <label> Task <span class="radStar">*</span> </label>
                                        <input type="hidden" id="task_id" name="id">
                                        <input type="text" class="form-control editInput checkInput" id="task"
                                            name="task">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label> Date <span class="radStar">*</span></label>
                                        <input type="text" class="form-control editInput checkInput" id="su_task_dt"
                                            name="date">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label> Time <span class="radStar">*</span> </label>
                                        <input type="time" class="form-control editInput checkInput" id="task_time"
                                            name="time">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="mb-2 col-form-label">Status </label>
                                        <select class="form-control editInput selectOptions checkInput" id="status"
                                            name="status">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 form-group" id="comments_section">
                                        <label> Comments </label>
                                        <textarea class="form-control textareaInput checkInput" placeholder="Type your comments..." rows="3"
                                            id="comments" name="comments"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer customer_Form_Popup">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-warning" id="save_time_sheet">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addTaskCommentModal" tabindex="-1" aria-labelledby="addTaskCommentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog  modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                    <h4 class="modal-title" id="modalTitle">Add Comment</h4>
                </div>
                <div class="modal-body">
                    <form action="" id="time_sheet" class="customerForm ">
                        <input type="hidden" id="time_sheetId" name="id">
                        <div class="row">
                            <div class="col-md-12 col-lg-12 col-xl-12">
                                <div class="row formDtail ps-4 pe-4">
                                    <div class="col-md-12 form-group">
                                        <label> Status </label>
                                        <select class="form-control editInput selectOptions checkInput" id="status"
                                            name="status">
                                            <option value="active">Active</option>
                                            <option value="inactive" selected>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 form-group">
                                        <label> Comments <span class="radStar">*</span></label>
                                        <textarea class="form-control textareaInput checkInput" placeholder="Type your comments..." rows="3"
                                            id="comments" name="comments"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer customer_Form_Popup">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-warning" id="">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        $(document).on('click', '.openServiceUserTaskModel', function() {
            const action = $(this).data('action');
            if (action === 'add') {
                $('#modalTitle').text('Add Task');
                $('#service_user_task')[0].reset();
                document.getElementById('comments_section').style.display = 'none';
            } else if (action === 'edit') {
                document.getElementById('comments_section').style.display = 'block';
                $('#modalTitle').text('Edit Task');
                $('#task_id').val($(this).data('id'));
                $('#task').val($(this).data('task'));
                // Convert time from HH:MM:SS to HH:MM format for input time field
                const timeValue = $(this).data('time');
                if (timeValue) {
                    const timeParts = timeValue.split(':');
                    const formattedTime = `${timeParts[0]}:${timeParts[1]}`;
                    $('#task_time').val(formattedTime);
                }
                $('#status').val($(this).data('status')).trigger('change');
                const date = $(this).data('date');
                if (date) {
                    const dateParts = date.split('-'); // [2025, 04, 24]
                    const jsDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]); // months are 0-based
                    $('#su_task_dt').datepicker('setDate', jsDate);
                }
                $('#comments').val($(this).data('comments'));
            }
            $('#addServiceUserTaskModal').modal('show');

        });

        $(document).on('click', '.openTaskCommentModel', function() {
            const $row = $(this).closest('tr');
            const taskId = $row.find('.deleteTask').data('id');
            const status = $(this).text().trim() === 'Active' ? '1' : '2';
            const comments = $row.find('td').eq(5).text(); // Get existing comments

            $('#time_sheetId').val(taskId);
            $('#status').val(status);
            $('#comments').val(comments);
            $('#addTaskCommentModal').modal('show');
        });
    </script>

    <script>
        $(document).on('click', '.deleteTask', function() {
            var btn = $(this);
            var id = btn.data('id');
            if (!confirm('Are you sure you want to delete this task?')) return;
            $.ajax({
                url: "{{ url('/service/task-management/delete/') }}" + "/" + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    btn.closest('tr').remove();
                    alert(res.message || 'Task deleted');
                },
                error: function(xhr) {
                    let msg = 'Failed to delete task';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                }
            });
        });
    </script>

    <script>
        // Handle task comment form submission
        $(document).on('click', '#addTaskCommentModal', function(e) {
            e.preventDefault();

            const $form = $('#addTaskCommentModal #time_sheet');
            const taskId = $('#time_sheetId').val();

            // Validate required fields
            var error = 0;
            $form.find('.checkInput').each(function() {
                if (!$(this).val()) {
                    $(this).css('border', '1px solid red');
                    error = 1;
                } else {
                    $(this).css('border', '');
                }
            });
            if (error) return;

            $.ajax({
                url: "{{ url('/service/task-management/update') }}/" + taskId,
                type: 'POST',
                data: $form.serialize() + "&_token={{ csrf_token() }}",
                success: function(res) {
                    if (res.message) {
                        alert(res.message);
                    }
                    // Update the row in the table
                    if (res.task) {
                        const $row = $('.deleteTask[data-id="' + taskId + '"]').closest('tr');
                        $row.find('td').eq(4).html(
                            '<a href="javascript:void(0)" class="openTaskCommentModel" data-action="add">' +
                            (res.task.status == 1 ? 'Active' : 'Inactive') + '</a>');
                        $row.find('td').eq(5).text(res.task.comments);
                    }
                    $('#addTaskCommentModal').modal('hide');
                },
                error: function(xhr) {
                    let msg = 'Failed to update task';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alert(msg);
                }
            });
        });

        $('#su_task_dt').datetimepicker({
            format: 'dd-mm-yyyy',
            minView: 2,
            autoclose: true,
        });

        $(document).on('click', '#save_time_sheet', function(e) {
            e.preventDefault();

            var $form = $('#service_user_task');
            if (!$form.length) return;

              // Get the task ID to determine if this is an add or edit
            const taskId = $('#task_id').val();
            var url = taskId ? 
                "{{ url('/service/task-management/update') }}/" + taskId :
                "{{ url('/service/save-task-management/' . $service_user_id) }}";
            
            var data = $form.serialize() + "&_token={{ csrf_token() }}";
            

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(resp) {
                    if (resp && resp.message) {
                        alert(resp.message);
                    }
                    // close modal and reset
                    $('#addServiceUserTaskModal').modal('hide');
                    $form[0].reset();
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        // show first validation error via alert and inline messages
                        var errors = xhr.responseJSON.errors;
                        var first = Object.keys(errors)[0];
                        alert(errors[first][0]);
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        alert(xhr.responseJSON.message);
                    } else {
                        alert('An unexpected error occurred');
                    }
                }
            });
        });
    </script>


@endsection

