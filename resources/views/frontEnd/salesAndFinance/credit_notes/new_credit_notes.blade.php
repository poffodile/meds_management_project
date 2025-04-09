@include('frontEnd.salesAndFinance.jobs.layout.header')
<style>
    .currency {
    padding: 2px 3px 2px 5px;
    line-height: 17px;
    text-shadow: 0 1px 0 #ffffff;
    border: 1px solid #ccc;
    background-color: #efefef;
    margin-right: 5px;
}
.calendar_icon {
    color:#e10078; 
    display: flex;
    align-items: center;
}
.disabled-tab {
    pointer-events: none;
    opacity: 0.5;
}

.productDetailTable table.table thead tr th, .productDetailTable table.table tbody tr td, .productDetailTable table.table tfoot tr td {
    font-size: 12px;
    line-height: 22px;
}

.totlepayment {
    width: 300px;
    margin-left: 46%;
    text-align: end;
}
.image_style {
    cursor: pointer;
}
ul#purchase_qoute_refList {
    padding: 0 5px;
    height: 156px;
    overflow: auto;
}
.unclicked{
    pointer-events: none;
}
.input_style table tbody textarea{
    resize: none;
    overflow: hidden;
    min-height: 50px;
  }

</style>
<div class="main_wrapper">
    <section class="main_section_page px-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4 col-lg-4 col-xl-4 ">
                    <div class="pageTitle">
                    <?php if(isset($credit_note->credit_ref) && $credit_note->credit_ref!=''){?>
                        <h3>{{$credit_note->credit_ref}}</h3>
                    <?php }else{?>
                        <h3>New Credit Note</h3>
                    <?php }?>
                    </div>
                </div>
                <div class="col-md-4 col-lg-4 col-xl-4">
                    <div class="mt-1 mb-0 text-center" id="message_save"></div>
                </div>
                <div class="col-md-4 col-lg-4 col-xl-4 px-3">
                    <div class="pageTitleBtn">
                        <a href="javascript:void(0)" onclick="save_all_data()" class="profileDrop"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                        <a href="{{url('credit_notes?list_mode=Approved')}}" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Back</a>
                        <!-- <a href="#" class="profileDrop dropdown-toggle"><i class="fa-solid fa-gear"></i> Actions</a> -->
                    </div>
                </div>
            </div>
        <form class="customerForm" id="credit_form">
            <div class="row">
                <div class="col-lg-12">
                    <div class="newJobForm">
                        <div class="row">
                            <div class="col-md-4 col-lg-4 col-xl-4">
                                <div class="formDtail">
                                    <h4 class="contTitle">Supplier Details</h4>
                                        @csrf
                                        <input type="hidden" id="credit_id" name="id" value="<?php if(isset($credit_note)){echo $credit_note->id;}?>">
                                        <div class="mb-3 row">
                                            <label for="" class="col-sm-3 col-form-label">Supplier <span class="radStar">*</span></label>
                                            <div class="col-sm-7">
                                                <select class="form-control editInput selectOptions CreditNotescheckError" id="credit_supplier_id" name="supplier_id" onchange="get_supplier_details()">
                                                    <option selected disabled>Select Supplier</option>
                                                    <?php foreach($suppliers as $suppval){?>
                                                        <option value="{{$suppval->id}}" <?php if(isset($credit_note) && $credit_note->supplier_id == $suppval->id){echo 'selected';}?>>{{$suppval->name}}</option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                            <div class="col-sm-1">
                                                <a href="#!" class="formicon" data-bs-toggle="modal"
                                                    data-bs-target="#customerPop"><i
                                                        class="fa-solid fa-square-plus"></i></a>
                                            </div>
                                            <div class="col-sm-1">
                                                <a href="#!" class="formicon"><i class="fa-solid fa-clock"></i></a>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="" class="col-sm-3 col-form-label">Contact</label>
                                            <div class="col-sm-7">
                                                <select class="form-control editInput selectOptions" id="credit_contact_id" name="contact_id">
                                                    <option selected disabled>Select Supplier First</option>
                                                    @foreach($additional_contact as $addContact)
                                                        <option value="{{$addContact->id}}" <?php if(isset($credit_note) && $credit_note->contact_id == $addContact->id){echo 'selected';}?>>{{$addContact->contact_name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-2">
                                                <a href="#!" class="formicon"><i
                                                        class="fa-solid fa-square-plus"></i></a>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="" class="col-sm-3 col-form-label">Name <span
                                                    class="radStar">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput CreditNotescheckError" id="credit_name" name="name" value="<?php if(isset($credit_note) && $credit_note !=''){echo $credit_note->name;}?>">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="" class="col-sm-3 col-form-label">Email</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" id="credit_email"
                                                    name="email" value="<?php if(isset($credit_note) && $credit_note !=''){echo $credit_note->email;}?>" onchange="credit_check_email()">
                                                <span style="color:red" id="creditemailErr"></span>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="" class="col-sm-3 col-form-label">Telephone</label>
                                            <div class="col-sm-2">
                                                <select class="form-control editInput selectOptions" id="credit_tele_code" name="telephone_code">
                                                @foreach($country as $Codeval)
                                                    <option value="{{$Codeval->id}}" <?php if(isset($credit_note) && $credit_note->telephone_code == $Codeval->id){echo 'selcted';}else if($Codeval->id == 230){echo 'selected';}?>>+{{$Codeval->code}} - {{$Codeval->name}}</option>
                                                @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control editInput" id="credit_telephone" name="telephone" placeholder="Telephone" onkeypress="return event.charCode>=48&&event.charCode<=57 && value.length<10" value="<?php if(isset($credit_note) && $credit_note !=''){echo $credit_note->telephone;}?>">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="" class="col-sm-3 col-form-label">Mobile</label>
                                            <div class="col-sm-2">
                                                <select class="form-control editInput selectOptions" id="credit_mobile_code" name="mobile_code">
                                                @foreach($country as $Codeval)
                                                    <option value="{{$Codeval->id}}" <?php if(isset($credit_note) && $credit_note->mobile_code == $Codeval->id){echo 'selcted';}else if($Codeval->id == 230){echo 'selected';}?>>+{{$Codeval->code}} - {{$Codeval->name}}</option>
                                                @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control editInput" id="credit_mobile"
                                                    name="mobile" placeholder="Mobile" onkeypress="return event.charCode>=48&&event.charCode<=57 && value.length<10" value="<?php if(isset($credit_note) && $credit_note !=''){echo $credit_note->mobile;}?>">
                                            </div>
                                        </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-4 col-xl-4">
                                <div class="formDtail">
                                    <h4 class="contTitle">Address Details</h4>

                                        <div class="mb-3 row">
                                            <label for="" class="col-sm-3 col-form-label">Address <span
                                                    class="radStar">*</span></label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control textareaInput CreditNotescheckError" name="address" id="credit_address"
                                                    rows="3" placeholder=""><?php if(isset($credit_note) && $credit_note !=''){echo $credit_note->address;}?></textarea>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="" class="col-sm-3 col-form-label">City</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" id="credit_city" name="city" value="<?php if(isset($credit_note) && $credit_note !=''){echo $credit_note->city;}?>">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="" class="col-sm-3 col-form-label">County</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" id="credit_county"
                                                    name="county" placeholder="County" value="<?php if(isset($credit_note) && $credit_note !=''){echo $credit_note->county;}?>">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="" class="col-sm-3 col-form-label">Postcode
                                            </label>
                                            <div class="col-sm-6">
                                                <input type="text" class="form-control editInput textareaInput"
                                                    id="credit_post_code" name="post_code" value="<?php if(isset($credit_note) && $credit_note !=''){echo $credit_note->post_code;}?>">
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="plusandText">
                                                    <a href="#!" class="formicon"><i
                                                            class="fa-solid fa-magnifying-glass-location"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-4 col-xl-4">
                                <div class="formDtail">
                                    <h4 class="contTitle">Credit Note Details</h4>
                                        <div class="mb-3 row">
                                            <label for="" class="col-sm-3 col-form-label">Credit Note
                                                Ref</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control-plaintext editInput" id="credit_ref"
                                                    value="<?php if(isset($credit_note) && $credit_note !=''){echo $credit_note->credit_ref;}else{echo 'Auto generate';}?>" readonly>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label for="" class="col-sm-3 col-form-label">Date
                                                <span class="radStar">*</span></label>
                                            <div class="col-sm-4">
                                                <input type="date" class="form-control editInput textareaInput CreditNotescheckError" id="credit_date" name="date" value="<?php if(isset($credit_note) && $credit_note !=''){echo $credit_note->date;}?>">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="" class="col-sm-3 col-form-label">Supplier
                                                Ref</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput textareaInput"
                                                    id="credit_supplier_ref" name="supplier_ref" placeholder="" value="<?php if(isset($credit_note) && $credit_note !=''){echo $credit_note->supplier_ref;}?>">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label for="" class="col-sm-3 col-form-label">Status</label>
                                            <div class="col-sm-9">
                                                <select class="form-control editInput selectOptions" id="credit_status" name="status">
                                                    <option value="1" <?php if(isset($credit_note) && $credit_note->status == 1){echo 'selected';}?>>Approved</option>
                                                    <option value="2" disabled <?php if(isset($credit_note) && $credit_note->status == 2){echo 'selected';}?>>Paid</option>
                                                    <option value="0" disabled <?php if(isset($credit_note) && $credit_note->status == 0){echo 'selected';}?>>Cancelled</option>
                                                </select>
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- End  off newJobForm -->

                    <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Item Details</label>
                            <div class="row">
                                <div class="col-sm-9">
                                    <div class="mb-3 row">
                                        <label for="inputCountry" class="col-sm-2 col-form-label">Select product</label>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control editInput textareaInput" id="search-product" placeholder="Type to add product">
                                            <div class="parent-container"></div>
                                        </div>
                                        <div class="col-sm-7">
                                            <div class="plusandText">
                                                <a href="javascript:void(0)" class="formicon" onclick="get_model(7)"><i class="fa-solid fa-square-plus"></i>
                                                </a>
                                                <span class="afterPlusText"> (Type to view product or <a href="Javascript:void(0)" onclick="openProductmodal();">Click
                                                        here</a> to view all assets)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="pageTitleBtn p-0">
                                        <!-- <a href="#" class="profileDrop">Add Title</a> -->
                                        <!-- <a href="#" class="profileDrop">Show Variations</a>
                                        <a href="#" class="profileDrop bg-secondary">Export</a> -->

                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="productDetailTable table-responsive input_style">
                                        <table class="table" id="result">
                                            <thead class="table-light">
                                                <tr>
                                                    <!-- <th>Job </th> -->
                                                    <th>Code</th>
                                                    <th>Item </th>
                                                    <th>Description </th>
                                                    <th>Account Code <a href="javascript:void(0)" class="formicon" onclick="openAccountCodeModal(null)"><i class="fa-solid fa-square-plus"></i>
                                                    </a> </th>
                                                    <th>QTY</th>
                                                    <th>Price</th>
                                                    <th>VAT(%) <a href="javascript:void(0)" class="formicon" onclick="get_model(9)"><i class="fa-solid fa-square-plus"></i>
                                                    </a></th>
                                                    <th style="width:120px">VAT </th>
                                                    <th>Amount</th>
                                                    <!-- <th>Delivered QTY</th> -->
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="product_result">
                                                 
                                            </tbody>
                                        <!-- </table> -->
                                        <!-- <table class="table totlepayment" > -->
                                            <tfoot class="item_table" id="product_calculation" style="display:none">
                                                <tr>
                                                    <td colspan="5"></td>
                                                    <td colspan="3">Sub Total (exc. VAT)</td>
                                                    <td id="exact_vat"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5"></td>
                                                    <td colspan="3">VAT</td>
                                                    <td id="vat"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5"></td>
                                                    <td colspan="3"><strong>Total(inc.VAT)</strong></td>
                                                    <td><strong id="total_vat"></strong></td>
                                                </tr>
                                                <!-- <tr>
                                                    <td colspan="5"></td>
                                                    <td colspan="3">Paid</td>
                                                    <td>-£0.00</td>
                                                </tr> -->
                                                <tr>
                                                    <td colspan="5"></td>
                                                    <td colspan="3" class="item_row"><strong>Remaining Credit</strong></td>
                                                    <td class="item_row"><strong id="outstanding_vat"></strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        <div id="pagination-controls-Produc-details"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <div class="newJobForm mt-4">
                        <label class="upperlineTitle">Notes</label>
                        <div class="row mt-3">
                            <div class="col-sm-6">
                                <div class="">
                                    <h4 class="contTitle text-start">Supplier Notes <small>(Will be included in
                                            credit note)</small></h4>
                                    <div class="mt-3">
                                        <textarea cols="40" rows="5" id="supplier_notes" name="supplier_notes"><?php if(isset($credit_note) && $credit_note !=''){echo $credit_note->supplier_notes;}?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="">
                                    <h4 class="contTitle text-start">Internal Notes</h4>
                                    <div class="mt-3">
                                        <textarea cols="40" rows="5" id="internal_notes" name="internal_notes"><?php if(isset($credit_note) && $credit_note !=''){echo $credit_note->internal_notes;}?> </textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div><!-- End  off newJobForm -->
                </div>
            </div>
        </form>
            <div class="row">
                <!-- <div class="col-md-4 col-lg-4 col-xl-4 ">
                    <div class="pageTitle">
                        <h3>New Jobs</h3>
                    </div>
                </div> -->
                <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                    <div class="pageTitleBtn">
                        <a href="javascript:void(0)" onclick="save_all_data()" class="profileDrop"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                        <a href="{{url('credit_notes?list_mode=Approved')}}" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Back</a>
                        <!-- <a href="#" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Action</a> -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</div><!-- End off main_wrappper -->
<!-- Models Start Here -->

@include('components.add-supplier-modal')
@include('components.contact-modal')
@include('components.product-list')
@include('components.account-code')
@include('frontEnd.salesAndFinance.item.common.addproductmodal')

<x-vat-tax-rate 
    modalId="VatTaxRateModal"
    modalTitle="Add Tax Rate"
    formId="vattaxrateform"
    id="vattaxrate_id"
    name="vat_tax_name"
    taxRate="vat_tax_rate"
    taxCode="vat_tax_code"
    expDate="vat_tax_expdate"
    status="vat_tax_satatus"
    saveButtonId="saveVatTaxRate"
/>

<!-- End here -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.3.2/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script>
        //Text Editer

var editor_config = {
  toolbar: [
      {name: 'basicstyles', items: ['Bold','Italic','Underline','Strike','-','RemoveFormat']},
      {name: 'format', items: ['Format']},
      {name: 'paragraph', items: ['Indent','Outdent','-','BulletedList','NumberedList']},
      {name: 'link', items: ['Link','Unlink']},
{name: 'undo', items: ['Undo','Redo']}
  ],
};

CKEDITOR.replace('supplier_notes', editor_config );
CKEDITOR.replace('internal_notes', editor_config );
//Text Editer
</script>
<script>
    function getAllSupplier(data){
        $("#credit_supplier_id").append('<option value="'+data.id+'">'+data.name+'</option>');
    }
    function GetAllContact(contact_data){
        $("#purchase_contact_id").append('<option value="'+contact_data.data.id+'">'+contact_data.data.contact_name+'</option>');
    }
    function get_supplier_details(){
        var supplier_id = $("#credit_supplier_id").val();
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('get_supplier_details')}}",
            data: {
                supplier_id: supplier_id,
                _token: token
            },
            success: function(response) {
                console.log(response);
                // return false;
                const data=response.data;
                // $('#purchase_contact_id').removeAttr('disabled');
                $('#creditemailErr').text("");
                var contactSelect=document.getElementById("credit_contact_id");
                $("#credit_name").val(data.contact_name);
                $("#credit_email").val(data.email);
                $("#credit_telephone").val(data.telephone);
                $("#credit_mobile").val(data.mobile);
                $("#credit_address").val(data.address);
                $("#credit_city").val(data.city);
                $("#credit_county").val(data.county);
                $("#credit_post_code").val(data.postcode);
                $.ajax({
                    url: '{{ route("ajax.getCountriesList") }}',
                    method: 'GET',
                    success: function(response1) {
                        // console.log(response1.Data);
                        const selectElement=$("#credit_tele_code")[0];
                        const selectElement1=$("#credit_mobile_code")[0];
                        selectElement.innerHTML = '';
                        selectElement1.innerHTML = '';
                        const defaultOptionTelephone = document.createElement("option");
                        const defaultOptionMobile = document.createElement("option");
                        // defaultOptionTelephone.value = "0";
                        defaultOptionTelephone.text = "Please Select";
                        defaultOptionTelephone.disabled = true;
                        defaultOptionTelephone.selected = true;
                        selectElement.appendChild(defaultOptionTelephone);

                        // defaultOptionMobile.value = "0";
                        defaultOptionMobile.text = "Please Select";
                        defaultOptionMobile.disabled = true;
                        defaultOptionMobile.selected = true;
                        selectElement1.appendChild(defaultOptionMobile);

                        response1.Data.forEach(user => {
                            const option1 = document.createElement('option');
                            option1.value = user.id;
                            option1.text =  user.name + " " + "(+" + user.code +")";
                            selectElement.appendChild(option1);
                        });
                        response1.Data.forEach(user1 => {
                            const option1 = document.createElement('option');
                            option1.value = user1.id;
                            option1.text =  user1.name + " " + "(+" + user1.code +")";
                            selectElement1.appendChild(option1);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
                // $("#contact_customer_id").val(response.data.id);
                // $("#contact_customer_name").text(response.data.name);
                // $("#task_supplier_id").val(response.data.id);
                // $(".customer_name").text(response.data.name);
                const all_contact=response.data.contacts;
                contactSelect.innerHTML='';
                const defaultOption = document.createElement("option");
                defaultOption.value = "0";
                defaultOption.text = "Default";
                defaultOption.disabled = false;
                defaultOption.selected = false;
                contactSelect.appendChild(defaultOption);
                if (all_contact && all_contact.length > 0) {
                    all_contact.forEach((cont) => {
                        const option = document.createElement("option");
                        option.value = cont.id;
                        option.text = `${cont.contact_name}`;
                        contactSelect.appendChild(option);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    }
</script>

<script>
    function get_model(modal){  
        var supplier_select_check=$("#credit_supplier_id").val();
        var modal_array=[1,7];
        var customer_id=$("#purchase_customer_id").val();
        if(supplier_select_check == null && modal_array.includes(modal)){
            alert("Please select Supplier");
            return false;
        }else{
            if(modal == 1){
                get_supplier_details();
                $("#contact_form")[0].reset();
                $('#contactModalLabel').text("Add Supplier Contact");
                $('#contactLabel').text("Supplier");
                $('#userType').val(2);
                $("#contact_billing_radio").hide();
                $("#contact_modal").modal('show');
            }else if(modal == 2){
                $("#AddCustomerModal")[0].reset();
                $("#job_title_plusIcon").hide();
                $("#customerPop").modal('show');
            }else if(modal == 7){
                itemsAddProductModal(1);
            }else if(modal == 9){
                $("#vattaxrateform")[0].reset();
                $("#VatTaxRateModal").modal('show');
            }
        }
        
    }
    function open_customer_type_modal(){
        $('#cutomer_type_modal').modal('show');
    }
    function openProductmodal(){
        var supplier_select_check=$("#credit_supplier_id").val();
        if(supplier_select_check == null){
            alert("Please select supplier first");
            return false;
        }else{
            openProductListModal();
        }
    }
 </script>
 <script>
    function credit_check_email(){
        var email= $('#credit_email').val();
        validRegExp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if (email.search(validRegExp) == -1){
            $('#creditemailErr').text("Please enter correct email address");
            return false;
        }else{
            $('#creditemailErr').text("");
        }
  }
    function save_all_data(){
        for (var instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }
        var emailErr=$("#creditemailErr").text();
        $('.CreditNotescheckError').each(function() {
            if ($(this).val() === '' || $(this).val() == null) {
                $(this).css('border','1px solid red');
                $(this).focus();
                return false;
            } else {
                $(this).css('border','');
            }
        });
        if(emailErr.length >0){
            
            return false;
        }else{
            var credit_date = $("#credit_date").val();
            var formattedDate = moment(credit_date, "DD/MM/YYYY").format("YYYY-MM-DD");

            var formData = new FormData($("#credit_form")[0]);
            formData.append('date', formattedDate);
            $.ajax({
                type: "POST",
                url: "{{url('/credit_notes_save')}}",
                data: formData,
                async: false,
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    // return false;
                if(response.vali_error){
                        alert(response.vali_error);
                        $(window).scrollTop(0);
                        return false;
                    }else if(response.success === true){
                        $(window).scrollTop(0);
                        $('#message_save').addClass('success-message').text(response.message).show();
                        setTimeout(function() {
                            $('#message_save').removeClass('success-message').text('').hide();
                            location.href='<?php echo url('credit_notes?list_mode=Approved');?>'
                        }, 3000);
                    }else if(response.success === false){
                        $('#message_save').addClass('error-message').text(response.message).show();
                        setTimeout(function() {
                            $('#error-message').text('').fadeOut();
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errorMessage = 'Validation Errors:\n';
                        for (let field in errors) {
                            alert(errors[field]);
                            return false;
                        }
                    }else{
                        var errorMessage = xhr.status + ': ' + xhr.statusText;
                        alert('Error - ' + errorMessage + "\nMessage: " + error);
                    }
                }
            });
        }
    }
 </script>
 <script>
    function getProductData(selectedId) {
        selectProduct(selectedId);
    }
    var GrandPrice=0;
    var totalAmount=0;
    
    function selectProduct(id) {
        var token = '<?php echo csrf_token(); ?>'
        var key='order';
        $.ajax({
            type: "POST",
            url: "{{url('result_product_calculation')}}",
            data: {
                id: id, key:key, _token: token
            },
            success: function(data) {
                // console.log(data);return false;
                const tableBody = document.querySelector(`#result tbody`);
        
                if (data.length === 0) {
                    const noDataRow = document.createElement('tr');
                    noDataRow.id='EmptyError'
                    const noDataCell = document.createElement('td');

                    noDataCell.setAttribute('colspan', 4);
                    noDataCell.textContent = 'No products found';
                    noDataCell.style.textAlign = 'center'; 

                    noDataRow.appendChild(noDataCell);
                    tableBody.appendChild(noDataRow);
                } else {
                    const emptyErrorRow = document.getElementById('EmptyError');
                    if (emptyErrorRow) {
                        emptyErrorRow.remove();
                    }
                        const row = document.createElement('tr');
                        // job dropdown
                        // const dropdownJob = document.createElement('td');

                        // const selectDropdownJob = document.createElement('select');
                        // selectDropdownJob.name = 'job_id[]';
                        // selectDropdownJob.className="form_control";

                        // const defaultOptionJob = document.createElement('option');
                        // defaultOptionJob.value = '';
                        // defaultOptionJob.text = '-Not Selected-';
                        // selectDropdownJob.appendChild(defaultOptionJob);

                        // const optionsJob = data.job;
                        // optionsJob.forEach(optionJob => {
                        //     const optJob = document.createElement('option');
                        //     optJob.value = optionJob.id;
                        //     optJob.textContent = optionJob.name;
                        //     selectDropdownJob.appendChild(optJob);
                        // });

                        // dropdownJob.appendChild(selectDropdownJob);

                        // row.appendChild(dropdownJob);
                        // end

                        const codeCell = document.createElement('td');
                        // codeCell.textContent = data.product_detail.product_code;
                        const inputCode = document.createElement('input');
                        inputCode.className = 'product_code form-control';
                        inputCode.name = 'product_code[]';
                        inputCode.value = '';
                        codeCell.appendChild(inputCode);
                        row.appendChild(codeCell);

                        const nameCell = document.createElement('td');
                        nameCell.innerHTML = data.product_detail.product_name;
                        row.appendChild(nameCell);

                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.className = 'product_id';
                        hiddenInput.name = 'product_id[]';
                        hiddenInput.value = data.product_detail.id;
                        row.appendChild(hiddenInput);

                        const descriptionCell = document.createElement('td');
                        const inputDescription = document.createElement('textarea');
                        inputDescription.setAttribute('rows','1');
                        inputDescription.className = 'description form-control';
                        inputDescription.name = 'description[]';
                        // inputDescription.value = data.product_detail.description;
                        inputDescription.value = '';
                        inputDescription.addEventListener('input', function() {
                            auto_grow(this);
                        });
                        descriptionCell.appendChild(inputDescription);
                        row.appendChild(descriptionCell);

                        const dropdownAccountCode = document.createElement('td');
                        const selectDropdownAccountCode = document.createElement('select');
                        selectDropdownAccountCode.className='accountCode_id form-control';
                        selectDropdownAccountCode.name = 'accountCode_id[]';

                        const optionsAccountCode = data.accountCode;

                        const defaultOptionAccountCode = document.createElement('option');
                        defaultOptionAccountCode.value = '';
                        defaultOptionAccountCode.text = '-No Department-';
                        selectDropdownAccountCode.appendChild(defaultOptionAccountCode);

                        optionsAccountCode.forEach(optionJob => {
                        const optAccountCode = document.createElement('option');
                        optAccountCode.value = optionJob.id;
                        optAccountCode.textContent = optionJob.name;
                        selectDropdownAccountCode.appendChild(optAccountCode);
                        });
                        dropdownAccountCode.appendChild(selectDropdownAccountCode);
                        row.appendChild(dropdownAccountCode);

                        const qtyCell = document.createElement('td');
                        const inputQty = document.createElement('input');
                        inputQty.type = 'text';
                        inputQty.className = 'qty input50 form-control';
                        inputQty.addEventListener('input', function() {
                            this.value = this.value.replace(/[^0-9.]/g, '');
                            if ((this.value.match(/\./g) || []).length > 1) {
                                this.value = this.value.slice(0, -1);
                            }
                            updateAmount(row);
                        });
                        inputQty.name = 'qty[]';
                        inputQty.value = '1';
                        qtyCell.appendChild(inputQty);
                        row.appendChild(qtyCell);

                        const priceCell = document.createElement('td');
                        const inputPrice = document.createElement('input');
                        inputPrice.type = 'text';
                        inputPrice.className = 'product_price input50 form-control';
                        // inputPrice.addEventListener('input', function() {
                        //     updateAmount(row);
                        // });
                        inputPrice.addEventListener('input', function () {
                            this.value = this.value.replace(/[^0-9.]/g, '');
                            if ((this.value.match(/\./g) || []).length > 1) {
                                this.value = this.value.slice(0, -1);
                            }
                            updateAmount(row);
                        });
                        inputPrice.name = 'price[]'; 
                        inputPrice.value = data.product_detail.price;
                        GrandPrice=GrandPrice+Number(data.product_detail.price);
                        priceCell.appendChild(inputPrice);
                        row.appendChild(priceCell);

                        const dropdownVat = document.createElement('td');
                        const selectDropdownVat = document.createElement('select');
                        selectDropdownVat.addEventListener('change', function() {
                            // alert(`You selected: ${this.options[this.selectedIndex].text}`);
                            getIdVat($(this).val(),row);
                        });
                        selectDropdownVat.name = 'vat_id[]';
                        selectDropdownVat.className='vat_id form-control';
                        const optionsVat =data.tax;
                        var tax_rate='00';
                        optionsVat.forEach(optionVat => {
                        const optVat = document.createElement('option');
                        optVat.value = optionVat.id;
                        if(optionVat.id == data.product_detail.tax_rate){
                            tax_rate=optionVat.tax_rate;
                            optVat.setAttribute("selected", "selected");
                        }
                        optVat.textContent = optionVat.name;
                        selectDropdownVat.appendChild(optVat);
                        });
                        const inputVatRate = document.createElement('input');
                        inputVatRate.type = 'hidden';
                        inputVatRate.className = 'vat_ratePercentage';
                        inputVatRate.name = 'vat_ratePercentage[]'; 
                        inputVatRate.value = tax_rate;
                        dropdownVat.appendChild(inputVatRate);
                        dropdownVat.appendChild(selectDropdownVat);
                        row.appendChild(dropdownVat);

                        const vatCell = document.createElement('td');
                        const inputVat = document.createElement('input');
                        inputVat.type = 'text';
                        inputVat.className = 'vat form-control';
                        inputVat.setAttribute('disabled','disabled');
                        inputVat.addEventListener('input', function() {
                            updateAmount(row);
                        });
                        inputVat.name = 'vat[]'; 
                        inputVat.value = parseFloat(tax_rate).toFixed(2);
                        vatCell.appendChild(inputVat);
                        row.appendChild(vatCell);

                        const amountCell = document.createElement('td');
                        amountCell.innerHTML = '£'+ parseFloat(data.product_detail.price).toFixed(2);
                        amountCell.className = "price";
                        row.appendChild(amountCell);
                        totalAmount=totalAmount+Number(data.product_detail.price);

                        // const delveriQTYCell = document.createElement('td');
                        // delveriQTYCell.innerHTML='-';
                        // delveriQTYCell.className ='text-center';
                        // row.appendChild(delveriQTYCell);

                        const deleteCell = document.createElement('td');
                        deleteCell.innerHTML = '<i class="fas fa-times fa-2x deleteRow" style="color: red;"></i>';
                        deleteCell.addEventListener('click', function() {
                            removeRow(this);
                        });
                        row.appendChild(deleteCell);

                        tableBody.appendChild(row);
                        updateAmount(row)
                        $("#product_calculation").show();
                }

            }
        });
    }
    function removeRow(button,id=null) {
        // console.log(button);
        const table = document.getElementById("result");
        const tbody = table.querySelector("tbody");
        const rowCount = tbody ? tbody.rows.length : 0;
        if(rowCount <= 1){
            $("#product_calculation").hide();
        }
        var row = button.parentNode;
        
        if(id){
            var token = '<?php echo csrf_token(); ?>'
            $.ajax({
                type: "POST",
                url: "{{url('purchase_productsDelete')}}",
                data: {id:id,_token: token},
                success: function(data) {
                    // console.log(data);
                    if(data.success != true){
                        alert("Something went wrong! Please try later");
                        return false;
                    }else{
                        row.parentNode.removeChild(row);
                        updateAmount(row);
                    }
                }
            });
        }else{
            row.parentNode.removeChild(row);
            updateAmount(row);
        }
    }
    $(document).on("keyup", ".quantity", function() {
        var qty = $(this).val();
        var row = $(this).closest('tr');
        updateAmount(row);
        
    });

    function updateAmount(row) {
        // console.log(row)
        // const priceInput = row.querySelector('.price');
        const priceInput = row.querySelector('.product_price');
        const qtyInput = row.querySelector('.qty');
        const amountCell = row.querySelector('td:nth-last-child(2)');
        const price = parseFloat(priceInput.value) || 0;
        const qty = parseInt(qtyInput.value) || 0;
        // alert(qty)
        const amount = price * qty;
        amountCell.textContent = '£'+amount.toFixed(2);
        const vat_ratePercentage = row.querySelector('.vat_ratePercentage').value;
        const vat = row.querySelector('.vat');
        const percentage=amount*vat_ratePercentage/100;
        // alert(percentage)
        vat.value=percentage.toFixed(2);

        var calculation=0;
        $('.price').each(function () {
            const priceText = $(this).text();
            const numericValue = parseFloat(priceText.replace(/[^\d.]/g, ''));
            // console.log(typeof(numericValue));
            calculation=calculation+numericValue;
        });
        var vat_amount=0;
        $('.vat').each(function () {
            const vat = $(this).val();
            vat_amount=vat_amount+Number(vat);
        });
        totalAmount=calculation;
        // console.log(typeof(vat_amount));
        // document.getElementById('GrandTotalAmount').innerHTML='$'+totalAmount.toFixed(2);
        $("#productPrice").val(totalAmount.toFixed(2));
        $("#exact_vat").text('£'+totalAmount.toFixed(2));
        $("#vat").text('£'+vat_amount.toFixed(2));
        var total_vat=totalAmount+vat_amount;
        $("#total_vat").text('£'+total_vat.toFixed(2));
        $("#outstanding_vat").text('£'+total_vat.toFixed(2));
    }
    function getIdVat(vat_id,row){
        var token='<?php echo csrf_token();?>'
        $.ajax({
            type: "POST",
            url: "{{url('/vat_tax_details')}}",
            data: {vat_id:vat_id,_token:token},
            success: function(response) {
                // console.log(response);
                if(response){
                    const vat_value=Number(response.data);
                    const vat_ratePercentage = row.querySelector('.vat_ratePercentage').value=vat_value;
                    // var td=row.querySelector('td:nth-last-child(4)');
                    // var input = td.querySelector('.vat');
                    // // console.log(typeof(vat_value));
                    // input.value = vat_value.toFixed(2) || 0;
                    updateAmount(row);
                }else{
                    alert("Something went wrong");
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
            }
        });
    }
 </script>
 <script>
    $(document).ready(function() {

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$('#search-product').on('keyup', function() {
    let query = $(this).val();
    const divList = document.querySelector('.parent-container');

    if (query === '') {
        divList.innerHTML = '';
    }

    // Make an AJAX call only if query length > 2
    if (query.length > 2) {
        $.ajax({
            url: "{{ route('item.ajax.searchProduct') }}", // Laravel route
            method: 'GET',
            data: {
                query: query
            },
            success: function(response) {
                // console.log(response);
                // $('#results').html(response);
                divList.innerHTML = "";
                const div = document.createElement('div');
                div.className = 'container'; // Optional: Add a class to the div for styling

                // Step 2: Create a ul (unordered list)
                const ul = document.createElement('ul');
                ul.id = "productList";
                // Step 3: Loop through the data and create li (list item) for each entry
                response.forEach(item => {
                    const li = document.createElement('li'); // Create a new li element
                    li.textContent = item.product_name; // Set the text of the li item
                    li.id = item.id;
                    li.className = "editInput";
                    ul.appendChild(li); // Append the li to the ul
                });

                // Step 4: Append the ul to the div
                div.appendChild(ul);

                // Step 5: Append the div to the parent container in the HTML
                divList.appendChild(div);

                ul.addEventListener('click', function(event) {
                    divList.innerHTML = '';
                    document.getElementById('search-product').value = '';
                    // Check if the clicked element is an <li> (to avoid triggering on other child elements)
                    if (event.target.tagName.toLowerCase() === 'li') {
                        const selectedId = event.target.id; // Get the ID of the clicked <li>
                        console.log('Selected Product ID:', selectedId); // Print the ID of the selected product
                        getProductData(selectedId);
                    }
                });

            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    } else {
        $('#results').empty(); // Clear results if the input is empty
    }
});

});
 </script>
 <script>
    $(document).ready(function(){
        var crediId='<?php if(isset($credit_note)){echo $credit_note->id;}?>'
        getProductDetail(crediId,'{{ url("getCreditProduct") }}')
    });
   
    function getProductDetail(id,pageUrl = '{{ url("getCreditProduct") }}'){
        var token='<?php echo csrf_token();?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {id: id,_token:token},
            success: function(response) {
                console.log(response);
                // return false;
                var data=response.data[0];
                const tableBody = document.querySelector(`#result tbody`);
                var credit_products=data.product_details.credit_note_products;
                // console.log(credit_products);return false;
                if (credit_products.length === 0) {
                    const noDataRow = document.createElement('tr');
                    noDataRow.id='EmptyError'
                    const noDataCell = document.createElement('td');

                    noDataCell.setAttribute('colspan', 4);
                    noDataCell.textContent = 'No products found';
                    noDataCell.style.textAlign = 'center'; 

                    noDataRow.appendChild(noDataCell);
                    tableBody.appendChild(noDataRow);
                }else{
                    const emptyErrorRow = document.getElementById('EmptyError');
                    if (emptyErrorRow) {
                        emptyErrorRow.remove();
                    }
                    credit_products.forEach(product => {
                        const row = document.createElement('tr');

                        
                         // job dropdown
                        // const dropdownJob = document.createElement('td');

                        // const selectDropdownJob = document.createElement('select');
                        // selectDropdownJob.name = 'job_id[]';
                        // selectDropdownJob.className="form_control";

                        // const defaultOptionJob = document.createElement('option');
                        // defaultOptionJob.value = '';
                        // defaultOptionJob.text = '-Not Selected-';
                        // selectDropdownJob.appendChild(defaultOptionJob);

                        // const optionsJob = data.all_job;
                        // optionsJob.forEach(optionJob => {
                        //     const optJob = document.createElement('option');
                        //     optJob.value = optionJob.id;
                        //     optJob.textContent = optionJob.name;
                        //     selectDropdownJob.appendChild(optJob);
                        // });
                        // dropdownJob.appendChild(selectDropdownJob);
                        // row.appendChild(dropdownJob);
                        // end
                        const codeCell = document.createElement('td');
                        // codeCell.textContent = data.crediProduct_detail.product_code;
                        const inputCode = document.createElement('input');
                        inputCode.className = 'product_code form-control';
                        inputCode.name = 'product_code[]';
                        inputCode.value = product.code;
                        codeCell.appendChild(inputCode);
                        row.appendChild(codeCell);

                        const nameCell = document.createElement('td');
                        nameCell.innerHTML = data.crediProduct_detail.product_name;
                        row.appendChild(nameCell);

                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.className = 'product_id';
                        hiddenInput.name = 'product_id[]';
                        hiddenInput.value = data.crediProduct_detail.id;
                        row.appendChild(hiddenInput);
                        // purchase order product hidden id if not duplicate is null
                        <?php if(isset($credit_note) && $credit_note !=''){?>
                        const hiddenID = document.createElement('input');
                        hiddenID.type = 'hidden';
                        hiddenID.className = 'creditProduct_id form-control';
                        hiddenID.name = 'creditProduct_id[]';
                        hiddenID.value = product.id;
                        row.appendChild(hiddenID);
                        <?php }?>
                    // end

                        const descriptionCell = document.createElement('td');
                        const inputDescription = document.createElement('textarea');
                        inputDescription.setAttribute('rows','1');
                        inputDescription.className = 'description form-control';
                        inputDescription.name = 'description[]';
                        inputDescription.value = product.description;
                        inputDescription.addEventListener('input', function() {
                            auto_grow(this);
                        });
                        descriptionCell.appendChild(inputDescription);
                        row.appendChild(descriptionCell);

                        const dropdownAccountCode = document.createElement('td');
                        const selectDropdownAccountCode = document.createElement('select');
                        selectDropdownAccountCode.className='accountCode_id form-control';
                        selectDropdownAccountCode.name = 'accountCode_id[]';
                        // selectDropdownAccountCode.addEventListener('click', function() {
                        //     var elements = document.getElementsByClassName('accountCode_id');
                        //     getAccountCode(elements);
                        // });

                        const optionsAccountCode = data.accountCode;

                        const defaultOptionAccountCode = document.createElement('option');
                        defaultOptionAccountCode.value = '';
                        defaultOptionAccountCode.text = '-No Department-';
                        selectDropdownAccountCode.appendChild(defaultOptionAccountCode);

                        optionsAccountCode.forEach(optionJob => {
                        const optAccountCode = document.createElement('option');
                        optAccountCode.value = optionJob.id;
                        optAccountCode.textContent = optionJob.name;
                        selectDropdownAccountCode.appendChild(optAccountCode);
                        });
                        dropdownAccountCode.appendChild(selectDropdownAccountCode);
                        row.appendChild(dropdownAccountCode);

                        const qtyCell = document.createElement('td');
                        const inputQty = document.createElement('input');
                        inputQty.type = 'text';
                        inputQty.className = 'qty input50 form-control';
                        inputQty.addEventListener('input', function() {
                            this.value = this.value.replace(/[^0-9.]/g, '');
                            if ((this.value.match(/\./g) || []).length > 1) {
                                this.value = this.value.slice(0, -1);
                            }
                            updateAmount(row);
                        });
                        inputQty.name = 'qty[]';
                        inputQty.value = product.qty;
                        qtyCell.appendChild(inputQty);
                        row.appendChild(qtyCell);

                        const priceCell = document.createElement('td');
                        const inputPrice = document.createElement('input');
                        inputPrice.type = 'text';
                        inputPrice.className = 'product_price input50 form-control';
                        inputPrice.addEventListener('input', function() {
                            this.value = this.value.replace(/[^0-9.]/g, '');
                            if ((this.value.match(/\./g) || []).length > 1) {
                                this.value = this.value.slice(0, -1);
                            }
                            updateAmount(row);
                        });
                        inputPrice.name = 'price[]'; 
                        inputPrice.value = product.price;
                        GrandPrice=GrandPrice+Number(product.price);
                        priceCell.appendChild(inputPrice);
                        row.appendChild(priceCell);

                        const dropdownVat = document.createElement('td');
                        const selectDropdownVat = document.createElement('select');
                        selectDropdownVat.addEventListener('change', function() {
                            getIdVat($(this).val(),row);
                        });
                        selectDropdownVat.name = 'vat_id[]';
                        selectDropdownVat.className='vat_id form-control';
                        const optionsVat =data.tax;
                        var tax_rate='00';
                        optionsVat.forEach(optionVat => {
                        const optVat = document.createElement('option');
                        optVat.value = optionVat.id;
                        if(optionVat.id == product.vat_id){
                            tax_rate=optionVat.tax_rate;
                            optVat.setAttribute("selected", "selected");
                        }
                        optVat.textContent = optionVat.name;
                        selectDropdownVat.appendChild(optVat);
                        });
                        const inputVatRate = document.createElement('input');
                        inputVatRate.type = 'hidden';
                        inputVatRate.className = 'vat_ratePercentage form-control';
                        inputVatRate.name = 'vat_ratePercentage[]'; 
                        inputVatRate.value = tax_rate;
                        dropdownVat.appendChild(inputVatRate);
                        dropdownVat.appendChild(selectDropdownVat);
                        row.appendChild(dropdownVat);

                        const vatCell = document.createElement('td');
                        const inputVat = document.createElement('input');
                        inputVat.type = 'text';
                        inputVat.className = 'vat form-control';
                        inputVat.setAttribute('disabled','disabled');
                        inputVat.addEventListener('input', function() {
                            updateAmount(row);
                        });
                        inputVat.name = 'vat[]'; 
                        inputVat.value = parseFloat(tax_rate).toFixed(2);
                        vatCell.appendChild(inputVat);
                        row.appendChild(vatCell);

                        const amountCell = document.createElement('td');
                        amountCell.innerHTML = '£'+ parseFloat(product.price).toFixed(2);
                        amountCell.className = "price";
                        row.appendChild(amountCell);
                        totalAmount=totalAmount+Number(product.price);

                        // const delveriQTYCell = document.createElement('td');
                        // delveriQTYCell.innerHTML='-';
                        // delveriQTYCell.className ='text-center';
                        // row.appendChild(delveriQTYCell);

                        const deleteCell = document.createElement('td');
                        deleteCell.innerHTML = '<i class="fas fa-times fa-2x deleteRow" style="color: red;"></i>';
                        deleteCell.addEventListener('click', function() {
                            removeRow(this,product.id);
                        });
                        row.appendChild(deleteCell);

                        tableBody.appendChild(row);
                        updateAmount(row)
                    });
                    $("#product_calculation").show();
                    
                }
                
                // var paginationProductDetails = response.pagination;

                // var paginationControlsProductDetail = $("#pagination-controls-Produc-details");
                // paginationControlsProductDetail.empty();
                // if (paginationProductDetails.prev_page_url) {
                //     paginationControlsProductDetail.append('<button type="button" class="profileDrop" onclick="getProductDetail(' + id + ', \'' + paginationContact.prev_page_url + '\')">Previous</button>');
                // }
                // if (paginationProductDetails.next_page_url) {
                //     paginationControlsProductDetail.append('<button type="button" class="profileDrop" onclick="getProductDetail(' + id + ', \'' + paginationContact.next_page_url + '\')">Next</button>');
                // }
            },
            error: function(xhr, status, error) {
                console.error(error);
                // location.reload();
            }
        });
    }
    function auto_grow(element) {
        console.log("Here")
        element.style.height = "5px";
        element.style.height = (element.scrollHeight) + "px";
    }
    $(document).on('input', '.product_code', function () {
        let input = $(this).val();
        if (input.length > 50) {
            $(this).val(input.substring(0, 50));
        }
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
            var model='PoAttachment';
            $.ajax({
                type: "POST",
                url: "{{url('/bulk_delete')}}",
                data: {ids:ids,model:model,_token:token},
                success: function(data) {
                    // console.log(data);
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
$(document).on('click', '.delete_checkbox', function() {
    if ($('.delete_checkbox:checked').length === $('.delete_checkbox').length) {
        $('#selectAll').prop('checked', true);
    } else {
        $('#selectAll').prop('checked', false);
    }
});

 </script>
 <script>
    function bgColorChange(button){
        $('.bgColour').removeAttr('style');
        $("#recurringHideShow").hide();
        $("#taskHideShow").hide();
        if(button ==1){
            $("#taskHideShow").show();
            $("#task_active_inactive").css('background-color','#474747');
        }else{
            $("#recurringHideShow").show();
            $("#recurring_active_inactive").css('background-color','#474747');
        }
    }
    
 </script>
@include('frontEnd.salesAndFinance.jobs.layout.footer')
<script>
<?php if(isset($credit_note->date) && $credit_note->date!=''){?>
    flatpickr("#credit_date", {
        dateFormat: "d/m/Y",
        defaultDate: "<?php echo \Carbon\Carbon::parse($credit_note->date)->format('d/m/Y');?>"
    });
<?php }else{?>
    flatpickr("#credit_date", {
        dateFormat: "d/m/Y",
        defaultDate: new Date()
    });
<?php } ?>
</script>