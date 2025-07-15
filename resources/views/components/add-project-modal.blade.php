<div class="modal fade" id="project_modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="thirdModalLabel">Add Project</h4>
            </div>
            <form id="project_form">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success" id="alert_message_project" style="display:none"></div>
                    <input type="hidden" name="home_id" id="project_home_id" value="{{Auth::user()->home_id}}">
                    <div class="mb-3">
                        <label>Project Ref</label>
                        <p class="editInput mb-0" id="project_ref">Project Ref ###</p>
                    </div>
                    <div class="mb-3" id="HideShowFieldText" style="display:none">
                        <label>Customer</label>
                        <p id="project_customer_name" class="editInput mb-0"><?php if(!empty($contact_name)){echo $contact_name->contact_name;}?></p>
                        <input type="hidden" id="project_customer_id" name="customer_name" value="<?php if(!empty($contact_name) && $contact_name !=''){echo $contact_name->id;}?>">
                    </div>
                    <div class="mb-3" id="HideShowFieldSelect">
                        <label>Customer <span class="radStar ">*</span></label>
                        <select id="project_customer_name_select" name="customer_name" class="form-control editInput ProjectcheckError">
                            <option selected disabled>Select Customer</option>
                            @foreach($customers as $procust)
                                <option value="{{$procust->id}}">{{$procust->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Project Name <span class="radStar ">*</span></label>
                        <input type="text" class="form-control editInput textareaInput ProjectcheckError" name="project_name" id="project_name" placeholder="Enter Project Name">
                    </div>
                    <div class="mb-3">
                        <label>Start Date <span class="radStar ">*</span></label>
                        <!-- <input type="date" class="form-control editInput ProjectcheckError"  id="project_start_date" name="start_date"> -->
                        <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                            <input name="project_start_date" id="project_start_date" type="text" value="" autocomplete="off" class="form-control ProjectcheckError">
                            <span class="input-group-btn datetime-picker2 btn_height">
                                <button class="btn btn-primary" type="button" id="openCalendarProjectStartDate">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </button>
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>End Date <span class="radStar ">*</span></label>
                        <!-- <input type="date" class="form-control editInput ProjectcheckError" id="project_end_date" name="end_date"> -->
                        <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                            <input name="project_end_date" id="project_end_date" type="text" value="" autocomplete="off" class="form-control ProjectcheckError">
                            <span class="input-group-btn datetime-picker2 btn_height">
                                <button class="btn btn-primary" type="button" id="openCalendarProjectEndDate">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </button>
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Project Value </label>
                        <div class="row">
                            <div class="col-sm-1 pe-0">
                                <div class="tag_box">
                                    <span>£</span>
                                </div>
                            </div>
                            <div class="col-sm-11">
                                <input type="text" class="form-control editInput textareaInput" id="project_value" name="project_value" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea id="project_description" name="description" class="form-control textareaInput" placeholder="Enter Project Description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select id="project_status" name="status" class="form-control editInput">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="save_project()">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $('#project_start_date').datetimepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true,
        minView: 2
    });

    $('#openCalendarProjectStartDate').click(function() {
        $('#project_start_date').focus();
    });
    $('#project_end_date').datetimepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true,
        minView: 2
    });

    $('#openCalendarProjectEndDate').click(function() {
        $('#project_end_date').focus();
    });

    $("#project_modal").scroll(function() {
        $('#project_start_date').datetimepicker('place');
        $('#project_end_date').datetimepicker('place');
    });
</script>


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
                if (isAuthenticated(response) == false) {
                    return false;
                }
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