<div class="modal fade" id="project_modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content add_Customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="thirdModalLabel">Add Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="alert alert-success mt-3" id="alert_message_project" style="display:none"></div>
                <div class="modal-body">
                    <form id="project_form">
                        @csrf
                        <input type="hidden" name="home_id" id="project_home_id" value="{{Auth::user()->home_id}}">
                        <div class="row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Project Ref</label>
                            <div class="col-sm-9">
                                <p class="editInput mb-0" id="project_ref">Project Ref ###</p>
                            </div>
                        </div>
                        <div class="mb-3 row" id="HideShowFieldText" style="display:none">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Customer</label>
                            <div class="col-sm-9">
                            <p id="project_customer_name" class="editInput mb-0"><?php if(!empty($contact_name)){echo $contact_name->contact_name;}?></p>
                            </div>
                            <input type="hidden" id="project_customer_id" name="customer_name" value="<?php if(!empty($contact_name) && $contact_name !=''){echo $contact_name->id;}?>">
                        </div>
                        <div class="mb-3 row" id="HideShowFieldSelect">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Customer <span class="radStar ">*</span></label>
                            <div class="col-sm-9">
                                <select id="project_customer_name_select" name="customer_name" class="form-control editInput ProjectcheckError">
                                    <option selected disabled>Select Customer</option>
                                    @foreach($customers as $procust)
                                        <option value="{{$procust->id}}">{{$procust->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Project Name <span class="radStar ">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control editInput textareaInput ProjectcheckError" name="project_name" id="project_name" placeholder="Enter Project Name">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Start Date <span class="radStar ">*</span></label>
                            <div class="col-sm-7">
                                <input type="date" class="form-control editInput ProjectcheckError"  id="project_start_date" name="start_date">
                            </div>
                            <div class="col-sm-2 calendar_icon">
                                <i class="fa fa-calendar-alt"></i>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">End Date <span class="radStar ">*</span></label>
                            <div class="col-sm-7">
                                <input type="date" class="form-control editInput ProjectcheckError" id="project_end_date" name="end_date">
                            </div>
                            <div class="col-sm-2 calendar_icon">
                                <i class="fa fa-calendar-alt"></i>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Project Value </label>
                            <div class="col-sm-1 pe-0">
                                <div style="background:#e3e3e1;" class="px-2 py-1 text-center">
                                    <span >£</span>
                                </div>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" class="form-control editInput textareaInput" id="project_value" name="project_value" value="0">
                            </div>
                            
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Description</label>
                            <div class="col-sm-9">
                                <textarea id="project_description" name="description" class="form-control textareaInput" placeholder="Enter Project Description"></textarea>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Status</label>
                            <div class="col-sm-9">
                                <select id="project_status" name="status" class="form-control editInput">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="pageTitleBtn">
                            <a href="#" class="profileDrop p-2 crmNewBtn" onclick="save_project()"> Save</a>
                            <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>
    function save_project(){
        var assigned_product=0;
        if($("#customer_yes").is(':checked')){
            assigned_product=1;
        }
        $("#customer_assigned_product").val(assigned_product);
        var emailErr=$("#customeremailErr").text();
        $('.ProjectcheckError').each(function() {
            if ($(this).val() === '' || $(this).val() == null) {
                $(this).css('border','1px solid red');
                $(this).focus();
                return false;
            } else {
                $(this).css('border','');
            }
        });
        $.ajax({
            type: "POST",
            url: "{{url('/project_save')}}",
            data: new FormData($("#project_form")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                console.log(response);
            if(response.vali_error){
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                }else if($.trim(response)=="error"){
                    alert("Something went wrong. Please try later!");
                }else{
                    $(window).scrollTop(0);
                    $('#alert_message_project').text("Project Added Succesfully Done!").show();
                    setTimeout(function() {
                        $('#alert_message_project').text('').hide();
                        getAllproject(response);
                        $("#project_modal").modal('hide');
                    }, 3000);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
            }
        });
    }
    const today = new Date().toISOString().split("T")[0];
    document.getElementById("project_start_date").setAttribute("min", today);
    document.getElementById("project_end_date").setAttribute("min", today);

    $("#project_end_date").change(function () {
      var startDate = document.getElementById("project_start_date").value;
      var endDate = document.getElementById("project_end_date").value;

      if ((Date.parse(startDate) >= Date.parse(endDate))) {
          alert("End date should be greater than Start date");
          document.getElementById("project_end_date").value = "";
      }
  });
  $("#project_start_date").change(function () {
      var startDate = document.getElementById("project_start_date").value;
      var endDate = document.getElementById("project_end_date").value;

      if ((Date.parse(endDate) <= Date.parse(startDate))) {
          alert("Start date should be less than End date");
          document.getElementById("project_start_date").value = "";
      }
  });
</script>