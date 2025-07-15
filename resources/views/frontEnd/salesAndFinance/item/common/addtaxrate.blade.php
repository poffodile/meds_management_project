<style>
    .taxhidemessage {
        display: none
    }

    .taxhidemessagedanger {
        display: none
    }
</style>

<div class="modal fade" id="taxratepopup" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="taxratepopupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="taxratepopupModalLabel">Add Tax Rate <span class="catname"></span></h4>
            </div>
            <form class="needs-validations" novalidate id="taxform">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success text-center taxsuccess taxhidemessage"></div>
                    <div class="alert alert-danger text-center taxsuccessdanger taxhidemessagedanger"></div>
                    <div>
                        <div class="mb-3">
                            <label>Tax Rate Name <span class="radStar ">*</span></label>
                            <input type="text" class="form-control editInput" id="taxratename" name="name" value="" required>
                        </div>
                        <div class="mb-3">
                            <label>Tax Rate <span class="radStar ">*</span></label>
                            <input type="text" class="form-control editInput" id="tax_rate" name="tax_rate" value="">
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select class="form-control editInput selectOptions" id="tax_rate_status" name="status">
                                <option value="1">Active</option>
                                <option value="2">Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>External Tax Code</label>
                            <input type="text" class="form-control editInput" id="tax_code" name="tax_code" value="">
                        </div>
                        <div class="mb-3">
                            <label>Expiry Date</label>
                            <input type="date" class="form-control editInput" id="tax_exp_date" name="exp_date" value="">
                        </div>
                    </div>
                </div> <!-- end modal body -->
                <div class="modal-footer customer_Form_Popup">
                    <input type="hidden" name="taxrateID" id="taxrateID">
                    <input type="hidden" name="taxratetype" id="taxratetype">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning" id="taxratesave">Save</button>
                </div>
            </form>

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
    (function() {
        'use strict';

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validations');

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
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
                                if (data.success == 0) {
                                    $('.taxhidemessagedanger').css('display', 'block');
                                    $('.taxsuccessdanger').text(data.message);
                                    $(".taxsuccessdanger").show('slow', 'linear').delay(3000).fadeOut();
                                } else {
                                    $('.taxhidemessage').css('display', 'block');
                                    $('.taxsuccess').text(data.message);
                                    $(".taxsuccess").show('slow', 'linear').delay(3000).fadeOut(function() {
                                        if (taxratetype == 1) {
                                            $('#salestax').append($('<option>', {
                                                value: data.lastid,
                                                text: taxratename
                                            }));
                                            $('#salestax').val(data.lastid);
                                            $('#taxratepopup').modal('hide');
                                        } else if (taxratetype == 2) {
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