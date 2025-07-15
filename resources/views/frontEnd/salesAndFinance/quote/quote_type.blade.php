@include('frontEnd.salesAndFinance.jobs.layout.header')
<meta name="csrf-token" content="{{ csrf_token() }}">

<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Quote Type</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="jobsection">
                    <a href="#!" data-bs-toggle="modal" data-bs-target="#statusModel" class="profileDrop open-modal">Add</a>
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
                                <th class="text-center" style=" width:30px;"><input type="checkbox" id="selectAll"> <label for="selectAll"> All Select</label></th>
                                <th>#</th>
                                <th>Quote Type</th>
                                <th>Default Expiration Date</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Ram hide 10/12/2024 -->
                            <!-- @if(!$quote_type->isEmpty()) -->
                            @foreach ($quote_type as $value)
                            <tr>
                                <td><input type="checkbox" id="" class="delete_checkbox" value="{{$value->id}}"></td>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $value->title }}</td>
                                <td>{{ $value->number_of_days }}</td>
                                <!-- Ram 15/10/2024 here code for status change -->
                                <td> 
                                    <?php if($value->status == 1){?>
                                        <span class="grencheck" onclick="status_change({{$value->id}},{{$value->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                        <?php } else {?>
                                        <span class="grayCheck" onclick="status_change({{$value->id}},{{$value->status}})"><i class="fa-solid fa-circle-check"></i></span>

                                    <?php }?>
                                </td>
                                <td>
                                    <div class="d-inline-flex align-items-center ">
                                        <div class="nav-item dropdown">
                                            <a href="#" class="nav-link dropdown-toggle profileDrop show" data-bs-toggle="dropdown" aria-expanded="true">Action</a>
                                            <div class="dropdown-menu fade-up m-0">
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#statusModel" data-id="{{ $value->id }}" data-title="{{ $value->title }}" data-status="{{ $value->status }}" data-number_of_days="{{ $value->number_of_days}}" class="dropdown-item open-modal">Edit details</a>
                                                <a href="javascript:void(0);" class="dropdown-item" onclick="confirmDelete('{{ $value->id }}')">Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            <!-- Ram hide 10/12/2024 -->
                            <!-- @else
                            <tr>
                                <td colspan="6" class="text-center"><strong>Sorry, there are no items available..</strong></td>
                            </tr>
                            @endif -->
                        </tbody>
                    </table>
                </div> <!-- End off main Table -->
            </div>
        </di>
    </div>
</section>
<!-- popup start -->
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="statusModel" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content add_Customer">
            <div class="modal-header terques-bg">
                <h5 class="modal-title pupTitle">Quote Type - Add</h5>
                <button aria-hidden="true" data-bs-dismiss="modal" class="btn-close" type="button"></button>
            </div>
            <div class="modal-body">
                <form role="form" id="quote_type_form">
                    @csrf
                    <div><span id="error-message" class="error"></span></div>
                    <div class="row form-group">
                        <label class="col-lg-3 col-sm-3 col-form-label">Quote Type <span class="radStar ">*</span></label>
                        <div class="col-md-9">
                            <input type="hidden" name="quote_type_id" id="quote_type_id">
                            <input type="text" name="title" class="form-control editInput " placeholder="" id="title">
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Number of days</label>
                        <div class="col-md-9">
                            <input type="text" name="number_of_days" class="form-control editInput " placeholder="" id="number_of_days" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
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
@include('frontEnd.salesAndFinance.jobs.layout.footer')
<script>
    $(document).ready(function() {
        $('.open-modal').on('click', function() {
            var itemId = $(this).data('id');
            var itemTitle = $(this).data('title');
            var itemStatus = $(this).data('status');
            var number_of_days = $(this).data('number_of_days');

            $('#quote_type_id').val(itemId);
            $('#title').val(itemTitle);
            $('#modale_status').val(itemStatus);
            $('#number_of_days').val(number_of_days);
            if (itemId) {
                // Editing existing record
                $('#quote_type_id').val(itemId);
                $('#title').val(itemTitle);
                $('#modale_status').val(itemStatus);
                $('#number_of_days').val(number_of_days);
                $('.modal-title').text('Quote Type - Edit');
                $('#saveChanges').text('Save Changes');
            } else {
                // Adding new record (clear form fields if needed)
                $('#quote_type_id').val('');
                $('#title').val('');
                $('#number_of_days').val();
                $('#modale_status').val(1); // Default to Active
                $('.modal-title').text('Quote Type - Add');
                $('#saveChanges').text('Add');
            }
        });

        $('#saveChanges').on('click', function() {
            var formData = $('#quote_type_form').serialize();
            var id=$("#quote_type_id").val();
            var url='{{ route("quote.ajax.saveQuoteType") }}';
            if(id !=''){
                url='{{ route("quote.ajax.editQuoteType") }}';
            }
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    $('#secondModal').modal('hide');
                    window.location.reload();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });
    });

    function confirmDelete(id) {
        let confirmation = confirm("Are you sure you want to delete this record?");

        if (confirmation) {
            deleteRow(id);
        } else {
            console.log("Delete action canceled.");
        }
    }

    function deleteRow(id) {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
        console.log("Deleting row with ID:", id);
        // You can perform an AJAX request here to delete the record from the backend
        $.ajax({
            url: '{{ route("quote.ajax.deleteQuoteType") }}',
            method: 'POST',
            data: {
                id: id
            },
            success: function(response) {
                console.log(response);
                if (response.success) {
                    console.log('Record soft deleted successfully');
                    location.reload();

                } else {
                    console.error('Error deleting record');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
        // Code to delete the row
    }
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
            var model='QuoteType';
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
            var model="QuoteType";
            $.ajax({
                type: "POST",
                url: "{{url('/QuoteType_status_change')}}",
                data: {id:id,status:status,model:model,_token:token},
                success: function(data) {
                    console.log(data);
                    if($.trim(data)==1){
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