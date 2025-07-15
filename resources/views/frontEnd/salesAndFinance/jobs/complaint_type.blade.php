@include('frontEnd.salesAndFinance.jobs.layout.header')
<style>
     table.dataTable td.select-checkbox:before {
            display: block;
        }
.icon-color span i{
    font-size: 16px;

}
</style>
<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>CRM Section Type</h3>
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
                    <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="jobsection">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="pageTitleBtn p-0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="profileDrop mt-2" id="deleteSelectedRows">Delete</button>
                    <table id="exampleOne" class="display tablechange" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center" style=" width:60px;"><input type="checkbox" id="selectAll"> <label for="selectAll"> All Select</label></th>
                                <th>#</th>
                                <th>Type</th>
                                <th>CRM Section</th>
                                <th>Color Code</th>
                                <th>Icon Preview</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!$crm_sections->isEmpty())
                            @foreach ($crm_sections as $value)
                            <tr>
                                <td><input type="checkbox" id="" class="delete_checkbox" value="{{$value->id}}"></td>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $value->title }}</td>
                                <td>@switch($value->crm_section)
                                    @case(1) Calls @break
                                    @case(2) Emails @break
                                    @case(3) Notes @break
                                    @case(4) Complaints @break
                                    @case(5) Tasks @break
                                    @case(6) Contacts @break
                                    @case(7) History @break
                                    @default {{-- No output if none of the cases match --}}
                                    @endswitch
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <span class="viewColor" style="background-color: {{ $value->color_code }}"></span> <span class="colorCode"></span>
                                    </div>
                                </td>
                                <td> <div class="icon-color"> @switch($value->crm_section)
                                    @case(2) <span style="color: {{ $value->color_code }}"><i class="fa fa-envelope"></i></span> @break
                                    @case(1) <span style="color: {{ $value->color_code }}"><i class="fa fa-phone"></i></span> @break
                                    @case(3) <span style="color: {{ $value->color_code }}"><i class="fa fa-file"></i></span> @break
                                    @case(4) <span style="color: {{ $value->color_code }}"><i class="fa fa-exclamation-triangle"></i></span> @break
                                    @case(5) <span style="color: {{ $value->color_code }}"><i class="fa fa-list-ul"></i></span> @break
                                    @case(6) <span style="color: {{ $value->color_code }}"><i class="fa fa-user"></i></span> @break
                                    @case(7) @break
                                    @default {{-- No output if none of the cases match --}}
                                    @endswitch </div></td>
                                <td> @if($value->status) <span class="grencheck"><i class="fa-solid fa-circle-check"></i></span> @else <span class="grayCheck"><i class="fa-solid fa-circle-check"></i></span> @endif </td>
    <td>
                                    <div class="d-inline-flex align-items-center ">
                                        <div class="nav-item dropdown">
                                            <a href="#" class="nav-link dropdown-toggle profileDrop show" data-bs-toggle="dropdown" aria-expanded="true">Action</a>
                                            <div class="dropdown-menu fade-up m-0">
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#statusModel" data-id="{{ $value->id }}" data-title="{{ $value->title }}" data-status="{{ $value->status }}" data-crm-section="{{ $value->crm_section }}" data-color="{{ $value->color_code }}" class="dropdown-item open-modal">Edit details</a>
                                                <hr class="dropdown-divider">
                                                <a href="{{ url('/lead/crm_section_type/delete').'/'.$value->id }}" class="dropdown-item">Delete</a>
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
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="statusModel" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content add_Customer">
            <div class="modal-header terques-bg">
                <h5 class="modal-title pupTitle">Lead Status - Add</h5>
                <button aria-hidden="true" data-bs-dismiss="modal" class="btn-close" type="button"></button>
            </div>
            <div class="modal-body">
                <form role="form" id="crm_section_type_form">
                    @csrf
                    <div><span id="error-message" class="error"></span></div>
                    <div class="row form-group">
                        <label class="col-lg-3 col-sm-3 col-form-label"> Section <span class="radStar ">*</span> </label>
                        <div class="col-md-9">
                            <input type="hidden" name="section_type_id" id="section_type_id">
                            <select name="crm_section" id="crm_section" class="form-control editInput ">
                                @foreach($crmSec as $value)
                                    <option value="{{ $value->id}}">{{ $value->title}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Type <span class="radStar ">*</span> </label>
                        <div class="col-md-9">
                            <input type="text" name="title" class="form-control editInput " placeholder="CRM Status Type" id="title">
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Color Code </label>
                        <div class="col-md-9">
                            <input type="color" name="color_code" class="form-control editInput " placeholder="CRM Status Type" id="color">
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Status</label>
                        <div class="col-md-9">
                            <select name="status" id="modal_status" class="form-control editInput">
                                <option value="1">Active</option>
                                <option value="0">InActive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="profileDrop" id="saveChanges">Save</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- end Popup  -->
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
            var model='CRMSectionType';
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
 </script>
@include('frontEnd.salesAndFinance.jobs.layout.footer')
<script>
    $(document).ready(function() {
        $('.open-modal').on('click', function() {
            var itemId = $(this).data('id');
            var itemTitle = $(this).data('title');
            var itemStatus = $(this).data('status');
            var crm_section = $(this).data('crm-section');
            var color = $(this).data('color');

            $('#section_type_id').val(itemId);
            $('#title').val(itemTitle);
            $('#modal_status').val(itemStatus);
            $('#crm_section').val(crm_section);
            $('#color').val(color);

            if (itemId) {
                // Editing existing record
                $('#section_type_id').val(itemId);
                $('#title').val(itemTitle);
                $('#modal_status').val(itemStatus);
                $('.modal-title').text('Edit CRM Section Type');
                $('#saveChanges').text('Save Changes');
            } else {
                // Adding new record (clear form fields if needed)
                $('#section_type_id').val('');
                $('#title').val('');
                $('#crm_section').val();
                $('#color').val();
                $('#modal_status').val(1); // Default to Active
                $('.modal-title').text('Add CRM Section Types');
                $('#saveChanges').text('Add');
            }
        });

        $('#saveChanges').on('click', function() {
            var formData = $('#crm_section_type_form').serialize();
            var id=$("#section_type_id").val();
            var url='{{ route("lead.ajax.saveCRMSectionType") }}';
            if(id !=''){
                url='{{ route("lead.ajax.editCRMSectionType") }}';
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