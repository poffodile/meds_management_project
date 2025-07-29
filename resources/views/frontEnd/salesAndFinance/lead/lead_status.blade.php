@extends('frontEnd.layouts.master')


<section class="wrapper">
    <div class="panel">
          <header class="panel-heading px-5">
            <h4>Lead Status</h4>
        </header>
        <div class="alert alert-success text-center mt-1" id="msg" style="display:none;height:50px">
            <p id="status_meesage"></p>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="maimTable">
                    <div class="py-4">
                        <div class="jobsection">
                            <a href="#!" data-bs-toggle="modal" data-bs-target="#statusModel" class="profileDrop open-modal">Add</a>
                            <a href="javascript:void(0)" id="deleteSelectedRows" class="profileDrop">Delete</a>
                        </div>
                    </div>
                    <div class="printExpt">
                        <div class="prntExpbtn">
                            <a href="#!">Print</a>
                            <a href="#!">Export</a>
                        </div>
                        <div class="searchFilter">
                            <a href="#!">Show Search Filter</a>
                        </div>
                    </div>
      
                        <!-- end here -->
                    <table id="exampleOne" class="display tablechange" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <!-- Ram Bulk Delete -->
                                <th class="text-center" style=" width:30px;"><input type="checkbox" id="selectAll"> <label for="selectAll"> All Select</label></th>
                                <th>#</th>
                                <th>Lead Status</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!$lead_status->isEmpty())
                                @foreach ($lead_status as $value)
                                    <tr>
                                        <!-- Ram bulk delete -->
                                        <td><input type="checkbox" id="" class="delete_checkbox" value="{{$value->id}}"></td>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $value->title }}</td>
                                        <!-- Ram status change 15/10/2024 -->
                                        <td>
                                            <?php if($value->status == 1){?>
                                                <span class="grencheck" onclick="status_change({{$value->id}},{{$value->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                                <?php } else {?>
                                                <span class="grayCheck" onclick="status_change({{$value->id}},{{$value->status}})"><i class="fa-solid fa-circle-check"></i></span>

                                            <?php }?>
                                         </td>
                                        
                                        <!-- end here -->
                                        <td>
                                            <div class="d-inline-flex align-items-center ">
                                                <div class="nav-item dropdown">
                                                    <a href="#" class="nav-link dropdown-toggle profileDrop show" data-bs-toggle="dropdown" aria-expanded="true">Action</a>
                                                    <div class="dropdown-menu fade-up m-0">
                                                        <a href="#" data-bs-toggle="modal" data-bs-target="#statusModel" data-id="{{ $value->id }}" data-title="{{ $value->title }}" data-status="{{ $value->status }}"  class="dropdown-item open-modal">Edit details</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr> <td colspan="10" class="text-center"><strong>Sorry, there are no items available..</strong></td></tr>
                            @endif
                        </tbody>
                    </table>
                </div> <!-- End off main Table -->
            </div>
        </div>
    </div>
</section>
  <!-- popup start -->
  <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="statusModel" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content add_Customer">
            <div class="modal-header terques-bg">
                <h5 class="modal-title pupTitle">Lead Status - Add</h5>
                <button aria-hidden="true" data-bs-dismiss="modal" class="btn-close" type="button"></button>
            </div>
            <div class="modal-body">
                <form role="form" id="lead_status_form">
                    @csrf
                    <div><span id="error-message" class="error"></span></div>
                    <div class="row form-group">
                        <label class="col-lg-3 col-sm-3 col-form-label">Lead Status</label>
                        <div class="col-md-9">
                            <input type="hidden" name="lead_status_id" id="lead_status_id">
                            <input type="text" name="title" class="form-control editInput " placeholder="Lead Status" id="title">
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
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn profileDrop" id="saveChanges">Save</button>
                <button type="button" class="btn profileDrop" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- end Popup  -->
<script>
    $(document).ready(function() {
        $('.open-modal').on('click', function() {
            var itemId = $(this).data('id');
            var itemTitle = $(this).data('title');
            var itemStatus = $(this).data('status');
            $('#lead_status_id').val(itemId);
            $('#title').val(itemTitle);
            $('#modale_status').val(itemStatus);

            if (itemId) {
                // Editing existing record
                $('#lead_status_id').val(itemId);
                $('#title').val(itemTitle);
                $('#modale_status').val(itemStatus);
                $('.modal-title').text('Edit Lead Status');
                $('#saveChanges').text('Save Changes');
            } else {
                // Adding new record (clear form fields if needed)
                $('#lead_status_id').val('');
                $('#title').val('');
                $('#modale_status').val(1); // Default to Active
                $('.modal-title').text('Add Lead Status');
                $('#saveChanges').text('Add');
            }
        });

        $('#saveChanges').on('click', function() {
            var formData = $('#lead_status_form').serialize();
            var id=$("#lead_status_id").val();
            var url='{{ route("lead.ajax.saveLeadStatus") }}';
            if(id !=''){
                url='{{ route("lead.ajax.editLeadStatus") }}';
            }
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    $('#secondModal').modal('hide');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error(error);
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
    if(ids.length == 0){
        alert("Please check the checkbox for delete");
    }else{
        if(confirm("Are you sure to delete?")){
            // console.log(ids);
            var token='<?php echo csrf_token();?>'
            var model='LeadStatus';
            $.ajax({
                type: "POST",
                url: "{{url('/bulk_delete')}}",
                data: {ids:ids,model:model,_token:token},
                success: function(data) {
                    console.log(data);
                    if(data){
                        location.reload();
                    }else{
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
function status_change(id, status){
            var token='<?php echo csrf_token();?>'
            var model="LeadStatus";
            $.ajax({
                type: "POST",
                url: "{{url('/LeadStatus_status_change')}}",
                data: {id:id,status:status,model:model,_token:token},
                success: function(data) {
                    console.log(data);
                    if($.trim(data)==1){
                        $("#status_meesage").text("Status Changed Successfully Done");
                        $("#msg").show();
                        setTimeout(function() {
                            location.reload();
                        }, 3000);

                        
                    }
                    
                }
            });
        }
 </script> 