<style>
    .prductimghidemessage {
        display: none
    }

    .prductimghidemessagedanger {
        display: none
    }
</style>

<div class="modal fade" id="uploadproductimagemodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploadproductimagemodalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title fs-5" id="uploadproductimagemodalLabel">Add Image <span class="catname"></span></h4>
            </div>
            <form class="needs-validationi" novalidate id="productimage_form">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success text-center prductimgsuccess prductimghidemessage"></div>
                    <div class="alert alert-danger text-center prductimgsuccessdanger prductimghidemessagedanger"></div>
                    <div>
                        <div class="mb-3">
                            <label>Product Name</label>
                            <span class="imgproname"></span>
                        </div>
                        <div class="mb-3">
                            <label>File Name</label>
                            <input type="file" class="form-control editInput" id="tax_rate" name="imagename" value="" required>
                            <!-- <div class="fileupload fileupload-new" data-provides="fileupload">
                                <div class="fileupload-new thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
                                    <img src="{{url('public/images/noimage.jpg')}}" alt="No Image" />
                                </div>
                                <div class="fileupload-preview fileupload-exists thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 20px;"></div>
                                <div>
                                    <span class="btn btn-white btn-file">
                                        <span class="fileupload-new"><i class="fa fa-paper-clip"></i> Select image</span>
                                        <span class="fileupload-exists"><i class="fa fa-undo"></i> Change</span>
                                        <input name="imagename" type="file" class="default" required id="tax_rate" />
                                    </span>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div> <!-- end modal body -->
                <div class="modal-footer customer_Form_Popup">
                    <input type="hidden" name="productID" id="imgproduct_id" value="">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning" id="saveproductimage">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    (function() {
        'use strict';

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validationi');

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
                        var imgproduct_id = $('#imgproduct_id').val();
                        //alert(imgproduct_id)
                        // Here you can handle form submission using AJAX
                        var formData = new FormData(form);
                        fetch('{{ route("item.saveproductimages") }}', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log(data);
                                //taxsuccess taxhidemessage   taxsuccessdanger taxhidemessagedanger
                                if (data.success == 0) {
                                    $('.prductimghidemessagedanger').css('display', 'block');
                                    $('.prductimgsuccessdanger').text(data.message);
                                    $(".prductimgsuccessdanger").show('slow', 'linear').delay(3000).fadeOut();
                                } else {
                                    $('.prductimghidemessage').css('display', 'block');
                                    $('.prductimgsuccess').text(data.message);
                                    $(".prductimgsuccess").show('slow', 'linear').delay(3000).fadeOut(function() {

                                        $('#uploadproductimagemodal').modal('hide');
                                        getallproductimages(imgproduct_id)

                                    });
                                }
                                // Show success message
                                //alert('Form submitted successfully!'); // Replace with your own success message display logic
                            })
                            .catch(error => {
                                // Handle error
                                //console.error('Error:', error);
                                //alert('There was an error submitting the form.');
                                $('.productsuccessdanger').text('There was an error submitting the form.');
                            });
                    }

                    form.classList.add('was-validated');
                }, false);
            });
    })();
</script>