<style>
    .cathidemessage {
        display: none
    }

    .cathidemessagedanger {
        display: none
    }
</style>
<div class="modal fade" id="itemsCatagoryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="itemsCatagoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="itemsCatagoryModalLabel">Product Category <span class="catname"></span></h4>
            </div>
            <form class="needs-validation" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success text-center catsuccess cathidemessage"></div>
                    <div class="alert alert-danger text-center catsuccessdanger cathidemessagedanger"></div>

                    <div class="mb-3">
                        <label>Product Category <span class="radStar ">*</span></label>
                        <input type="text" class="form-control editInput" id="category_name" name="name" value="" required>
                        <span class="text-danger" id="category_name_error"></span>
                    </div>
                    <div class="mb-3">
                        <label>Parent Category</label>
                        <select class="form-control editInput selectOptions" id="parentcategory" name="cat_id">
                            <option value="" selected>Choose</option>
                            @foreach($product_categories as $pcategories)
                            <option value="{{$pcategories->id}}">{{$pcategories->name}}</option>
                            @endforeach
                            {{-- <option> None </option>
                                <option> Default </option> --}}
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select class="form-control editInput selectOptions" id="product_category_status" name="status">
                            <option value="1"> Active </option>
                            <option value="0"> Inactive </option>
                        </select>
                    </div>
                </div> <!-- end modal body -->
                <div class="modal-footer customer_Form_Popup">
                    <input type="hidden" name="productCategoryID" id="productCategoryID">
                    <input type="hidden" name="productCategorytype" id="productCategorytype">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning" id="saveproductcategory">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    (function () {
'use strict';

// Fetch all the forms we want to apply custom Bootstrap validation styles to
var forms = document.querySelectorAll('.needs-validation');

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
                var productCategorytype = $('#productCategorytype').val();
                var category_name = $('#category_name').val();
                fetch('{{ route("item.saveProductCategoryData") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                   console.log(data);
                   if(data.success==0){
                    $('.cathidemessagedanger').css('display','block');
                    $('.catsuccessdanger').text(data.message);
                    $(".catsuccessdanger").show('slow' , 'linear').delay(3000).fadeOut();
                   }else{
                    $('.cathidemessage').css('display','block');
                    $('.catsuccess').text(data.message);
                    $(".catsuccess").show('slow' , 'linear').delay(3000).fadeOut(function(){
                        if(productCategorytype!=2){
                            location.reload();
                        }else{                                                       
                            $('#productcategorylist').append($('<option>', {
                                value: data.lastid,
                                text: category_name
                            }));
                            $('#productcategorylist').val(data.lastid);
                            $('#itemsCatagoryModal').modal('hide');
                            // var $newOption = $('<option>', {
                            //     value: data.lastid, // Assuming `data.lastid` contains the new ID
                            //     text: category_name // The name of the new category
                            // });
                            // $('#productcategorylist').append($newOption).val(data.lastid); // Append and set as selected
                            // $('#itemsCatagoryModal').modal('hide');
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
                    $('.catsuccessdanger').text('There was an error submitting the form.');
                });
            }

            form.classList.add('was-validated');
        }, false);
    });
})();
</script>