@include('frontEnd.salesAndFinance.jobs.layout.header')

<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>History Type</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="jobsection">
                    <a href="#!" data-bs-toggle="modal" data-bs-target="#histroyTypeModel" class="profileDrop open-modal">Add</a>
                </div>
            </div>
        </div>
        <div class="alert alert-success text-center mt-1" id="msg" style="display:none;height:50px">
            <p id="status_meesage"></p>
        </div>
        <di class="row">
            <div class="col-lg-12">
                <div class="maimTable">
                    <div class="printExpt">
                        <div class="prntExpbtn">
                            <a href="#!">Print</a>
                            <a href="#!">Export</a>
                        </div>
                        <div class="searchFilter">
                            <a href="#!">Show Search Filter</a>
                        </div>
                    </div>
                    <!-- Ram 15/10/2024 here code for bulk delete -->
                    <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="jobsection">
                                    <a href="javascript:void(0)" id="deleteSelectedRows" class="profileDrop">Delete</a>
                                    <!-- <a href="#" class="profileDrop">Mark As completed</a> -->
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="pageTitleBtn p-0">
                                    <!-- <a href="#" class="profileDrop"> <i class="material-symbols-outlined"> settings </i></a> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end here -->
                    <table id="exampleOne" class="display tablechange" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <!-- Ram bulk delete -->
                                <th class="text-center" style=" width:30px;"><input type="checkbox" id="selectAll"> <label for="selectAll"> All Select</label></th>
                                <th>#</th>
                                <th>History Type</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!$lead_notes_type->isEmpty())
                            @foreach ($lead_notes_type as $value)
                            <tr>
                                <!-- Ram bulk delete -->
                                <td><input type="checkbox" id="" class="delete_checkbox" value="{{$value->id}}"></td>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $value->title }}</td>
                                <!-- Ram 15/10/2024 here code for status change -->
                                <td>
                                    <?php if ($value->status == 1) { ?>
                                        <span class="grencheck" onclick="status_change({{$value->id}},{{$value->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                    <?php } else { ?>
                                        <span class="grayCheck" onclick="status_change({{$value->id}},{{$value->status}})"><i class="fa-solid fa-circle-check"></i></span>


                                            <?php }?>
                                        </td>
                                    <!-- end here -->
                                        <td>
                                            <div class="d-inline-flex align-items-center ">
                                                <div class="nav-item dropdown">
                                                    <a href="#" class="nav-link dropdown-toggle profileDrop show" data-bs-toggle="dropdown" aria-expanded="true"> Action</a>
                                                    <div class="dropdown-menu fade-up m-0">
                                                        <a href="#" data-bs-toggle="modal" data-bs-target="#histroyTypeModel" data-id="{{ $value->id }}" data-title="{{ $value->title }}" data-status="{{ $value->status }}"  class="dropdown-item open-modal">Edit details</a>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="10" class="text-center"><strong>Sorry, there are no items available..</strong></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div> <!-- End off main Table -->
            </div>
        </di>
    </div>
</section>
<!-- popup start -->
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="histroyTypeModel" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content add_Customer">

            <div class="modal-header terques-bg">
                <button aria-hidden="true" data-bs-dismiss="modal" class="close" type="button">×</button>
                <h5 class="modal-title pupTitle"></h5>

            </div>
            <div class="modal-body">
                <form action="" id="lead_notes_type_form">
                    @csrf
                    <div><span id="error-message" class="error"></span></div>
                    <div class="row form-group">
                        <label class="col-lg-3 col-sm-3 col-form-label">Histroy Type</label>
                        <div class="col-md-9">
                            <input type="hidden" name="lead_notes_type_id" id="lead_notes_type_id"> 
                            <input type="text" name="title" class="form-control editInput " placeholder="History Type" id="title">
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Status</label>
                        <div class="col-md-9">
                            <select name="status" id="modale_status" class="form-control editInput">
                                <option value="1">Active</option>
                                <option value="0">InActive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn profileDrop" id="saveChanges">Save</button>
                <button type="button" class="btn profileDrop" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- end Popup  -->
@include('frontEnd.salesAndFinance.jobs.layout.footer')
<script>

        $(document).ready(function() {
            $('.open-modal').on('click', function() {
                var itemId = $(this).data('id');
                console.log(itemId);
                var itemTitle = $(this).data('title');
                var itemStatus = $(this).data('status');
                $('#lead_notes_type_id').val('');
                $('#title').val('');
                $('#modale_status').val(1);
                $('.modal-title').text('');
                $('#saveChanges').text('');

                if (itemId) {
                    // Editing existing record
                    $('#lead_notes_type_id').val(itemId);
                    $('#title').val(itemTitle);
                    $('#modale_status').val(itemStatus);
                    $('.modal-title').text('Edit History Type');
                    $('#saveChanges').text('Save Changes');
                } else {
                    // Adding new record (clear form fields if needed)
                    $('.modal-title').text('Add History Type');
                    $('#saveChanges').text('Add');
                }
            });
        });
    });
</script>
<script>
    $("#deleteSelectedRows").on('click', function() {
        let ids = [];

        $('.delete_checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        if (ids.length == 0) {
            alert("Please check the checkbox for delete");
        } else {
            if (confirm("Are you sure to delete?")) {
                // console.log(ids);
                var token = '<?php echo csrf_token(); ?>'
                var model = 'LeadNoteType';
                $.ajax({
                    type: "POST",
                    url: "{{url('/bulk_delete')}}",
                    data: {
                        ids: ids,
                        model: model,
                        _token: token
                    },
                    success: function(data) {
                        console.log(data);
                        if (data) {
                            location.reload();
                        } else {
                            alert("Something went wrong");
                        }
                        // return false;
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText;
                        alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                    }
                });
            }
        }

    });
    $('.delete_checkbox').on('click', function() {
        if ($('.delete_checkbox:checked').length === $('.delete_checkbox').length) {
            $('#selectAll').prop('checked', true);
        } else {
            $('#selectAll').prop('checked', false);
        }
    });

    function status_change(id, status) {
        var token = '<?php echo csrf_token(); ?>'
        var model = "LeadNoteType";
        $.ajax({
            type: "POST",
            url: "{{url('/status_change')}}",
            data: {
                id: id,
                status: status,
                model: model,
                _token: token
            },
            success: function(data) {
                console.log(data);
                if ($.trim(data) == 1) {
                    $("#status_meesage").text("status Changed Successfully Done");
                    $("#msg").show();
                    setTimeout(function() {
                        location.reload();
                    }, 3000);


                }

            }
        });
    }
</script>