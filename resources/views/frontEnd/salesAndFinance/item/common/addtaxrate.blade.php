<style>
    .taxhidemessage{display:none}
    .taxhidemessagedanger{display:none}    
</style>

<div class="modal fade" id="taxratepopup" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="taxratepopupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
    <div class="modal-content add_Customer">
        <div class="modal-header">
        <h5 class="modal-title fs-5" id="taxratepopupModalLabel">Add Tax Rate <span class="catname"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
            <div class="modal-body "> 
                <form class="row g-3 needs-validations" novalidate id="taxform">
                    @csrf
                    <div class="alert alert-success text-center taxsuccess taxhidemessage"></div>                              
                    <div class="alert alert-danger text-center taxsuccessdanger taxhidemessagedanger"></div>                                 
                <div class="contantbodypopup p-0">                                                                                                
                    <div class="mb-2 row">
                        <label for="taxratename"
                            class="col-sm-3 col-form-label">Tax
                            Rate Name <span class="radStar ">*</span></label>
                        <div class="col-sm-9">
                            <input type="text"
                                class="form-control editInput"
                                id="taxratename" name="name" value="" required>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="inputCity"
                            class="col-sm-3 col-form-label">Tax
                            Rate <span class="radStar ">*</span></label>
                        <div class="col-sm-9">
                            <input type="text"
                                class="form-control editInput"
                                id="tax_rate" name="tax_rate" value="">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="tax_rate_status"
                            class="col-sm-3 col-form-label">Status</label>
                        <div class="col-sm-9">
                            <select
                                class="form-control editInput selectOptions"
                                id="tax_rate_status" name="status">
                                <option value="1">Active</option>
                                <option value="2">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="tax_code"
                            class="col-sm-3 col-form-label">External
                            Tax Code</label>
                        <div class="col-sm-9">
                            <input type="text"
                                class="form-control editInput"
                                id="tax_code" name="tax_code" value="">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="tax_exp_date"
                            class="col-sm-3 col-form-label">Expiry
                            Date</label>
                        <div class="col-sm-7">
                            <input type="date"
                                class="form-control editInput"
                                id="tax_exp_date" name="exp_date" value="">
                        </div>
                    </div>
                </div>                                        
            </div> <!-- end modal body -->
            <div class="modal-footer customer_Form_Popup">
                <input type="hidden" name="taxrateID" id="taxrateID">
                <input type="hidden" name="taxratetype" id="taxratetype">
                <button type="button" class="btn profileDrop" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn profileDrop" id="taxratesave">Save</button>
            </form>                              
            </div>
        
    </div>
    </div>
</div>

<script>
    function taxrate(th) {
        $("#taxform")[0].reset();
        $(".needs-validations").removeClass('was-validated');
        $('#taxratetype').val(th);
        //$('#taxratepopup').css('display','block');
        $('#taxratepopup').modal('show');
    }

    // $(".closetaxratepopup").click(function(){
    //     //alert()
    //     $('#taxratepopup').css('display','none');
    // })
</script>
<script>
    (function () {
'use strict';

// Fetch all the forms we want to apply custom Bootstrap validation styles to
var forms = document.querySelectorAll('.needs-validations');

// Loop over them and prevent submission
Array.prototype.slice.call(forms)
    .forEach(function (form) {
        form.addEventListener('submit', function (event) {
            // Check if the form is valid
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                // Prevent the default form submission
                event.preventDefault();

                // Here you can handle form submission using AJAX
                var formData = new FormData(form);
                var taxratetype = $('#taxratetype').val();
                var taxratename = $('#taxratename').val();
                fetch('{{ route("item.saveTaxrateData") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                   console.log(data);
                   //taxsuccess taxhidemessage   taxsuccessdanger taxhidemessagedanger
                   if(data.success==0){
                    $('.taxhidemessagedanger').css('display','block');
                    $('.taxsuccessdanger').text(data.message);
                    $(".taxsuccessdanger").show('slow' , 'linear').delay(3000).fadeOut();
                   }else{
                    $('.taxhidemessage').css('display','block');
                    $('.taxsuccess').text(data.message);
                    $(".taxsuccess").show('slow' , 'linear').delay(3000).fadeOut(function(){
                        if(taxratetype==1){
                            $('#salestax').append($('<option>', {
                                value: data.lastid,
                                text: taxratename
                            }));
                            $('#salestax').val(data.lastid);
                            $('#taxratepopup').modal('hide');
                        }else if(taxratetype==2){                                                       
                            $('#purchasetax').append($('<option>', {
                                value: data.lastid,
                                text: taxratename
                            }));
                            $('#purchasetax').val(data.lastid);
                            $('#taxratepopup').modal('hide');                            
                        }
                        
                    });
                   }
                    // Show success message
                    //alert('Form submitted successfully!'); // Replace with your own success message display logic
                })
                .catch(error => {
                    // Handle error
                    //console.error('Error:', error);
                    //alert('There was an error submitting the form.');
                    $('.taxsuccessdanger').text('There was an error submitting the form.');
                });
            }

            form.classList.add('was-validated');
        }, false);
    });
})();
</script>