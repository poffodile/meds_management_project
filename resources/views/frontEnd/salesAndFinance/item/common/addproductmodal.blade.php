<style>
    /* .generate-button {
        height: 32px;
        padding: 0px 9px;
        background: #0877bd;
        border-color: #0877bd;
    }

    .generate-button:hover {
        background-color: #0877bd;
        border-color: #0877bd;
    } */

    .producthidemessage {
        display: none
    }

    .producthidemessagedanger {
        display: none
    }

    .Img_Title {
        font-size: 18px;
        font-weight: 500;
        color: #000;
    }

    .uploadImgSec {
        padding: 0;
        list-style: none;
    }

    .uploadImgSec li {
        width: 112px;
        border: 1px solid #ddd;
        display: inline-block;
        margin-bottom: 4px;
        position: relative;
    }

    .uploadImgSec li img {
        width: 100%;
    }

    .imgclose {
        position: absolute;
        top: -13px;
        right: 0;
        color: #f00;
        font-size: 27px;
        text-decoration: none;
    }

    .productimages {
        display: none;
    }

    .productuploadedit {
        display: none;
    }
</style>
<div class="modal fade" id="itemsAddProductModal" tabindex="-1" data-backdrop="static" data-keyboard="false" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="customerModalLabel">Product</h4>
            </div>
            <form class="needs-validationp" novalidate id="productform">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success text-center productsuccess producthidemessage"></div>                              
                    <div class="alert alert-danger text-center productsuccessdanger producthidemessagedanger"></div>
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="mb-3">
                                <label>Customer</label>
                                <div id="customerlist">
                                    <select class="form-control editInput selectOptions" id="getCustomerList" name="customer_only">
                                        <option value="">-All-</option>
                                    </select>
                                </div>
                                <div id="onecustomer">
                                    <label class="editInput"><input type="checkbox" name="customer_only" value=""> Yes Display for this customer only</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Product  Category</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select class="form-control editInput selectOptions" id="productcategorylist" name="cat_id">
                                            <option value="">- Any Category -</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2 ps-0">
                                        <a href="#!" class="formicon" id="productCetagoryPopup"
                                            onclick="additemsCatagoryModal(2)"><i class="fa fa-plus-square"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Product Name <span class="radStar">*</span></label>
                                <input type="text" class="form-control editInput" name="product_name" id="productname" value="" required>
                            </div>
                            <div class="mb-3">
                                <label>Product Type</label>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <select class="form-control editInput selectOptions" id="product_type" name="product_type">
                                            <option value="1">Product</option>
                                            <option value="2">Services</option>
                                            <option value="3">Consumable</option>
                                        </select>
                                    </div>
                                 <!-- <div class="col-sm-1 ps-0">
                                        <a href="#!" class="formicon"><i class="fa fa-plus-square"></i></a>
                                    </div> -->
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Product Code </label>
                                <div class="row">
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" id="product_code" placeholder="Product Code" name="product_code" value="">
                                    </div>
                                    <div class="col-sm-4 ps-0">
                                        <button type="button" class="btn btn-primary"
                                            id="generateproductcode">Generate</button>
                                    </div>
                                </div>
                                <span class="text-danger productcode_error"></span>
                            </div>
                            <div class="mb-3">
                                <label>Cost Price</label>
                                <input type="text" class="form-control editInput" id="cost_price" name="cost_price" value="">
                            </div>
                            <div class="mb-3">
                                <label>Markup</label>
                                <input type="text" class="form-control editInput" id="Markup" name="Markup" value="">
                            </div>
                            <div class="mb-3">
                                <label>Price <span class="radStar ">*</span></label>
                                <input type="text" class="form-control editInput" id="price" name="price" value="" required>
                            </div>
                            <div class="mb-3">
                                <label>Description</label>
                                <textarea class="form-control textareaInput"  id="description" name="description" rows="3" placeholder="Description"></textarea>
                            </div>
                            <div class="mb-3 d-flex align-items-center gap-3">
                                <label class="mb-0">Show on Template</label>
                                <span class="oNOfswich p-0">
                                    <input type="checkbox" name="show_temp" class="mt-0" id="show_temp" value="1" checked>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="mb-3">
                                <label>Bar Code</label>
                                <input type="text" class="form-control editInput" id="bar_code" value="" name="bar_code">
                            </div>
                            <div class="mb-3">
                                <label>Sales Tax Rate</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select class="form-control editInput selectOptions" id="salestax"
                                            onclick="taxratelist(1)" name="tax_rate">
                                            <!-- <option value="20">Vat-20</option> -->
                                            <option value="">-Please Select-</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2 ps-0">
                                        <a href="#!" class="formicon" id="" onclick="taxrate(1)">
                                            <i class="fa fa-plus-square"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Purchase Tax Rate</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select class="form-control editInput selectOptions" id="purchasetax"
                                            onclick="taxratelist(2)" name="tax_id">
                                            <option value="">-Please Select-</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2 ps-0">
                                        <a href="#!" class="formicon" id="purchaseTaxRatePop"
                                            onclick="taxrate(2)">
                                            <i class="fa fa-plus-square"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Nominal Code</label>
                                <input type="text" class="form-control editInput" id="nominal_code" value="" name="nominal_code">
                            </div>
                            <div class="mb-3">
                                <label>Sales A/c Code</label>
                                <select class="form-control editInput selectOptions" id="salesaccountcode"
                                    onclick="accountcode(1)" name="sales_acc_code">
                                    <option value="">--Please Select--</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Purchase A/c Code</label>
                                <select class="form-control editInput selectOptions" id="purchaseaccountcode"
                                    onclick="accountcode(2)" name="purchase_acc_code">
                                    <option value="">--Please Select--</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Expense A/c Code</label>
                                <select class="form-control editInput selectOptions" id="Expenseaccountcode"
                                    onclick="accountcode(3)" name="expense_acc_code">
                                    <option value="">--Please Select--</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Location</label>
                                <input type="text" class="form-control editInput" id="location" placeholder="Location" name="">
                            </div>
                            <div class="mb-3">
                                <label>Status</label>
                                <select class="form-control editInput selectOptions" id="status" name="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Attachment</label>
                                <!-- <div class="col-md-12 p-0">
                                        <div class="fileupload fileupload-new" data-provides="fileupload">
                                            <div class="fileupload-new thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
                                                <img src="{{url('public/images/noimage.jpg')}}" alt="No Image" />
                                            </div>
                                            <div class="fileupload-preview fileupload-exists thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 20px;"></div>
                                            <div>
                                                <span class="btn btn-white btn-file">
                                                    <span class="fileupload-new"><i class="fa fa-paper-clip"></i> Select image</span>
                                                    <span class="fileupload-exists"><i class="fa fa-undo"></i> Change</span>
                                                    <input name="attachment" type="file" class="default" id="attachment"  onclick="uploadproductimage()"  />
                                                </span>
                                            </div>
                                        </div>
                                    </div> -->
                                <input type="file" class="form-control editInput" id="attachment" name="attachment">
                                <input type="button" class="profileDrop productuploadedit" onclick="uploadproductimage()" value="Upload Image">
                            </div>
                            <div class="mb-2 productimages">
                                <label class="Img_Title">Images</label>
                                <ul class="uploadImgSec" id="image-list">
                                        {{-- <li> 
                                        <img src="{{url('public/product/1729249431.jpg')}}" alt="uploadImg"> 
                                        <a href="#!" class="imgclose">×</a>
                                    </li>
                                    <li> 
                                        <img src="{{url('public/product/1729249431.jpg')}}" alt="uploadImg"> 
                                        <a href="#!" class="imgclose">×</a>
                                    </li>
                                    <li> 
                                        <img src="{{url('public/product/1729249431.jpg')}}" alt="uploadImg"> 
                                        <a href="#!" class="imgclose">×</a>
                                    </li>
                                    <li> 
                                        <img src="{{url('public/product/1729249431.jpg')}}" alt="uploadImg"> 
                                        <a href="#!" class="imgclose">×</a>
                                    </li> --}}
                                </ul>                                       
                            </div>
                        </div>
                        {{-- <div class="productDetailTable">
                            <table class="table" id="containerA">
                                <thead class="table-light">
                                    <tr>
                                        <th>Supplier </th>
                                        <th>Part Number</th>
                                        <th>Cost Price</th>
                                        <th>
                                            <a href="#!" class="formicon" id="openPopupButton">
                                                <i class="fa fa-plus-square"></i>
                                            </a>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                        </div> --}}
                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <input type="hidden" name="productID" id="productID">
                    <input type="hidden" name="producttype" id="producttype">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Save</button>                
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
'use strict';

// Fetch all the forms we want to apply custom Bootstrap validation styles to
var forms = document.querySelectorAll('.needs-validationp');

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
                var producttype = $('#producttype').val();
                var productname = $('#productname').val();
                var id=$("#productID").val();
                var url='{{ route("item.saveproductdata") }}';
                if(id !=''){
                    url='{{ route("item.editproductdata") }}';
                }
                fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                   console.log(data);
                   //taxsuccess taxhidemessage   taxsuccessdanger taxhidemessagedanger
                   if(data.success==0){
                    $('.producthidemessagedanger').css('display','block');
                    $('.productsuccessdanger').text(data.message);
                    $(".productsuccessdanger").show('slow' , 'linear').delay(3000).fadeOut();
                   }else{
                    $('.producthidemessage').css('display','block');
                    $('.productsuccess').text(data.message);
                    $(".productsuccess").show('slow' , 'linear').delay(3000).fadeOut(function(){
                        if(producttype==1){
                            location.reload();
                        }
                        if(producttype==3){
                            location.reload();
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
                    $('.productsuccessdanger').text('There was an error submitting the form.');
                });
            }

            form.classList.add('was-validated');
        }, false);
    });
})();
</script>
@include('frontEnd.salesAndFinance.item.common.addtaxrate')
@include('frontEnd.salesAndFinance.item.common.productcategoryaddmodal')
@include('frontEnd.salesAndFinance.item.common.uploadproductimage')

<script>
    function additemsCatagoryModal(th){
        //alert();
        $('#category_name').val('');
        $('#parentcategory').val('');
        $('#product_category_status').val(1);
        $('#productCategoryID').val('');
        $('#productCategorytype').val(th);
        $('#itemsCatagoryModal').modal('show');
    }
</script>

<script>
    var dataLoaded = false;
    $('#productcategorylist').click(function() {
        if (dataLoaded) return;
        var token = "<?= csrf_token() ?>";
        $.ajax({
            type: 'POST',
            url: '{{ route('item.productcategorylist') }}',
            data: {
                _token: token
            },
            success: function(data) {
                console.log(data);
                var $select = $('#productcategorylist');
                $select.empty(); // Clear existing options

                // Loop through the data and append options to the select box
                $.each(data, function(index, category) {
                    $select.append($('<option>', {
                        value: category.id, // Assuming the id field
                        text: category.name // Assuming the name field
                    }));
                });
                dataLoaded = true;
            }

        });

    })
</script>
<script>
    var dataLoadeds = false;
    $('#getCustomerList').click(function() {
        if (dataLoadeds) return;
        var token = "<?= csrf_token() ?>";
        getCustomerList("getCustomerList");
        dataLoadeds = true;
    })
</script>
<script>
    $('#generateproductcode').click(function() {
        var token = "<?= csrf_token() ?>";
        var productname = $('#productname').val();
        if (!productname) {
            $('.productcode_error').text("Please insert a product name");
            return false;
        }
        $('.productcode_error').text("");
        var get_name = hasWhiteSpace(productname);
        console.log(get_name);
        if (get_name == true) {
            var names = productname.split(' ');
            var fname = names[0].charAt(0);
            var lname = names[1].charAt(0);
            shortname = fname + lname;
        }
        if (get_name == false) {
            if (productname.length > 1) {
                fname = productname.slice(0, 2);
            } else {
                fname = productname.charAt(0);
            }

            shortname = fname;
        }
        // if (productname.includes(' ')) {
        //     return 'Double string (contains spaces)';
        // } else {
        //     return 'Single string (no spaces)';
        // }       
        $.ajax({
            type: 'POST',
            url: '{{ route('item.generateproductcode') }}',
            data: {
                productname: shortname,
                _token: token
            },
            success: function(data) {
                console.log(data);
                $("#product_code").val(data);

            }

        });
    })

    function hasWhiteSpace(s) {
        return (/\s/).test(s);
    }
</script>
<script>
    //salestax
    var dataLoadedstax = false;
    var dataLoadedptax = false;

    function taxratelist(th) {
        if (th == 1) {
            if (dataLoadedstax) return;
        } else if (th == 2) {
            if (dataLoadedptax) return;
        }

        var token = "<?= csrf_token() ?>";
        $.ajax({
            type: 'POST',
            url: '{{ route('item.taxratelist') }}',
            data: {
                _token: token
            },
            success: function(data) {
                console.log(data);
                if (th == 1) {
                    var $select = $('#salestax');
                    $select.empty(); // Clear existing options

                    // Loop through the data and append options to the select box
                    $.each(data, function(index, category) {
                        $select.append($('<option>', {
                            // value: category.tax_rate, // Assuming the id field
                            value: category.id, // Assuming the id field
                            text: category.name // Assuming the name field
                        }));
                    });
                    dataLoadedstax = true;
                } else if (th == 2) {
                    var $select = $('#purchasetax');
                    $select.empty(); // Clear existing options

                    // Loop through the data and append options to the select box
                    $.each(data, function(index, category) {
                        $select.append($('<option>', {
                            value: category.id, // Assuming the id field
                            text: category.name // Assuming the name field
                        }));
                    });
                    dataLoadedptax = true;
                }

            }

        });
    }
</script>
<script>
    var dataLoadedsac = false;
    var dataLoadedpac = false;
    var dataLoadedeac = false;

    function accountcode(th) {
        if (th == 1) {
            if (dataLoadedsac) return;
        } else if (th == 2) {
            if (dataLoadedpac) return;
        } else if (th == 3) {
            if (dataLoadedeac) return;
        }
        var token = "<?= csrf_token() ?>";
        $.ajax({
            type: 'POST',
            url: '{{ route('item.account_code') }}',
            data: {
                _token: token
            },
            success: function(data) {
                console.log(data);
                //salesaccountcode  purchaseaccountcode  Expenseaccountcode
                if (th == 1) {
                    var $select = $('#salesaccountcode');
                    $select.empty(); // Clear existing options

                    // Loop through the data and append options to the select box
                    $.each(data, function(index, category) {
                        $select.append($('<option>', {
                            value: category.id, // Assuming the id field
                            text: category.name // Assuming the name field
                        }));
                    });
                    dataLoadedsac = true;
                } else if (th == 2) {
                    var $select = $('#purchaseaccountcode');
                    $select.empty(); // Clear existing options

                    // Loop through the data and append options to the select box
                    $.each(data, function(index, category) {
                        $select.append($('<option>', {
                            value: category.id, // Assuming the id field
                            text: category.name // Assuming the name field
                        }));
                    });
                    dataLoadedpac = true;
                } else if (th == 3) {
                    var $select = $('#Expenseaccountcode');
                    $select.empty(); // Clear existing options

                    // Loop through the data and append options to the select box
                    $.each(data, function(index, category) {
                        $select.append($('<option>', {
                            value: category.id, // Assuming the id field
                            text: category.name // Assuming the name field
                        }));
                    });
                    dataLoadedeac = true;
                }

            }

        });
    }
</script>
<script>
    function itemsAddProductModal(th,id=null){
        // alert(th)
        if(th==1){
            $('#attachment,#generateproductcode,#customerlist').css('display','block');
            $('.productimages,.productuploadedit,#onecustomer').css('display','none');
            $("#productform")[0].reset();
        }else if(th==3){
            $('#attachment,#generateproductcode,#onecustomer').css('display','none');
            $('.productimages,.productuploadedit,#customerlist').css('display','block');
            getproductdetailbyID(id);
            getallproductimages(id);
        }else if(th==2){            
            $('#attachment,#generateproductcode,#onecustomer').css('display','block');
            $('.productimages,.productuploadedit,#customerlist').css('display','none');
            $("#productform")[0].reset();
        }        
        $(".needs-validationp").removeClass('was-validated');
        $('#producttype').val(th);
        $('#itemsAddProductModal').modal('show');
    }

    function getproductdetailbyID(id){
        var token = "<?= csrf_token() ?>";
        $.ajax({
            type: 'POST',
            url: '{{ route('item.getproductdata') }}',
            data: {
                product_id:id,
                _token: token
            },
            success: function(data) {
                console.log(data.product_name);
                $('#getCustomerList').html($('<option>', {
                    value: data.customer_only, // Assuming the id field
                    text: data.customer_name // Assuming the name field
                }));
                $('#productcategorylist').html($('<option>', {
                    value: data.cat_id, // Assuming the id field
                    text: data.cat_name // Assuming the name field
                }));
                $('#product_type').val(data.product_type);
                // $('#product_type').html($('<option>', {
                //     value: data.product_type, // Assuming the id field
                //     text: data.product_type_name // Assuming the name field
                // }));
                $('#productname').val(data.product_name);
                $('#product_code').val(data.product_code);
                $('#cost_price').val(data.cost_price);
                $('#margin').val(data.margin);
                $('#price').val(data.price);
                $('#description').val(data.description);
                $('#show_temp').val(data.show_temp);
                $('#bar_code').val(data.bar_code);
                $('#nominal_code').val(data.nominal_code);
                $('#location').val(data.location);

                $('#salestax').html($('<option>', {
                    value: data.tax_rate, // Assuming the id field
                    text: data.tax_rate_name // Assuming the name field
                }));
                $('#purchasetax').html($('<option>', {
                    value: data.tax_id, // Assuming the id field
                    text: data.ptax_rate_name // Assuming the name field
                }));
                $('#salesaccountcode').html($('<option>', {
                    value: data.sales_acc_code, // Assuming the id field
                    text: data.sales_acc_name // Assuming the name field
                }));
                $('#purchaseaccountcode').html($('<option>', {
                    value: data.purchase_acc_code, // Assuming the id field
                    text: data.purchase_acc_name // Assuming the name field
                }));
                $('#Expenseaccountcode').html($('<option>', {
                    value: data.expense_acc_code, // Assuming the id field
                    text: data.expense_acc_name // Assuming the name field
                }));
                $('#status').val(data.status)
                $('#productID').val(data.id)
                
            }

        });
    }

    function getallproductimages(id){
        var token = "<?= csrf_token() ?>";
        $.ajax({
            type: 'POST',
            url: '{{ route('item.getproductimage') }}',
            data: {
                product_id:id,
                _token: token
            },
            success: function(data) {
                console.log(data);
                // Assuming `data` is an array of image URLs
                var imageList = $('#image-list'); // Change this to your actual <ul> or <ol> ID
                imageList.empty(); // Clear existing images

                // Iterate over the image URLs and create <li> elements
                data.forEach(function(imageUrl) {
                    console.log(imageUrl.imagename)
                    imgurl = "{{url('public/product')}}/"+imageUrl.imagename;
                    var image = '<li><img src="'+imgurl+'" alt="uploadImg"><a href="#!" class="imgclose" onclick="deleteimage('+id+','+imageUrl.id+')">×</a></li>';
                    // var listItem = $('<li></li>');
                    // var image = $('<img>').attr('src', imageUrl);
                    
                    // listItem.append(image);
                    imageList.append(image);
                });
                
            }

        });

    }
</script>
<script>
    function uploadproductimage(){
        $("#productimage_form")[0].reset();
        $(".needs-validationi").removeClass('was-validated');
        var productname = $('#productname').val();
        var productid = $('#productID').val();
        $(".imgproname").text(productname);
        $("#imgproduct_id").val(productid);
        $('#uploadproductimagemodal').modal('show');
    }
</script>
<script>
    function deleteimage(productid,productimgid){
        //alert(productid)
        //alert(productimgid)
        var token = "<?= csrf_token() ?>";
        if(confirm("Are you sure want to delete this?")){
            $.ajax({
                type: 'POST',
                url: '{{ route('item.deleteproductimage') }}',
                data: {
                    productimgid:productimgid,
                    _token: token
                },
                success: function(data) {
                    console.log(data);
                    getallproductimages(productid)
                    
                }

            });
        }
    }
</script>
