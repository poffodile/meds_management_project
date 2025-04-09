<style>    
    .add_catalogue .nav-tabs .nav-item.show .nav-link, .add_catalogue .nav-tabs .nav-link.active {
        color: #ffffff;
        background-color: #0877bd;
        border-color: var(--bs-nav-tabs-link-active-border-color);
    }
    .cataloguefrm{
        border: 1px solid #0877bd;
        border-radius: 5px;
    }
    .add_catalogue .nav-tabs .nav-link {
        border: var(--bs-nav-tabs-border-width) solid #0877bd;
        color: #0877bd;
        border-bottom: none;
    }
    .cataloguetable {
        font-size: 12px;
        /* color: #fff; */
        /* background-color: #EBEBEB !important;
        border-color: #32383e; */
    }
    .cataloguetable th {
        font-weight: 600 !important;
        color: #333333;
        text-align: left;
        background-color: #EBEBEB !important;
        padding: 5px 3px !important;
        border-bottom-width: 1px;
        border-bottom-style: solid;
        border-bottom-color: #999999;
        border-top-width: 1px;
        border-top-style: solid;
        border-top-color: #999;
        margin: 0;
        font-size: 14px;
    }
    .modal-content.add_customer.add_catalogue {
        width: 120%;
    }
    .catalogueItemCustomPrice{
        width: 65px;
        text-align: right
    }
    .catalogueitemDelete i{
        color: red;
        font-size: 22px;
        font-weight: 700;
    }
    
    .image_style {
    cursor: pointer;
}
</style>
<div class="modal fade" id="itemsAddCatalogueModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content add_customer add_catalogue">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Catalogues</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                      <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true" onclick="tabClick(0)">Catalogue</button>
                      <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false" onclick="tabClick(1)" style="display:none">Catalogue Price</button>
                      
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <div class="row cataloguefrm m-1">                            
                            <div class="formDtail mt-2">
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-2 col-form-label">Catalogue Name <span class="radStar">*</span></label>
                                    <div class="col-sm-10">                                        
                                        <input type="text" class="form-control editInput" name="product_name" id="productname" value="" required>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-2 col-form-label">Description</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control textareaInput"  id="description" name="description" rows="3"
                                        placeholder="Description"></textarea>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-2 col-form-label">Type</label>
                                    <div class="col-sm-10">
                                        <select class="form-control editInput selectOptions" id="type" name="type">
                                            <option value="1">Catalogue Pricing Only</option>
                                            <option value="2">Mixed Pricing</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-2 col-form-label">Status</label>
                                    <div class="col-sm-10">
                                        <select class="form-control editInput selectOptions" id="catalogue_status" name="catalogue_status">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>                            
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div class="row cataloguefrm m-1">                            
                            <div class="formDtail mt-2">
                                        <div class="row">
                                            <div class="col-sm-2">
                                                <div class="jobsection">
                                                    <a href="javascript:void(0)" onclick="openProductListModal()" class="profileDrop" >Add Item</a>
                                                </div>
                                            </div>
                                            <div class="col-sm-1">
                                                <div class="jobsection">
                                                    <a href="javascript:void(0)" class="profileDrop p-2 crmNewBtn" id="openCallsModel"> Import</a>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <form class="searchForm" action="">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control editInput" placeholder="Your Catalogue" name="email">
                                                        <button type="button" class="input-group-text editInput">Search</button>
                                                    </div>
                                                </form>
                                            </div>
                                            <!-- <div class="col-sm-3">
                                                <div class="nav-item dropdown" style="margin-left:100px">
                                                    <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Action
                                                    </a>
                                                    <div class="dropdown-menu fade-up m-0" style="display:none">
                                                        <a href="javascript:void(0)" class="dropdown-item">Delete</a>
                                                        <hr class="dropdown-divider">
                                                        <a href="javasrcript:void(0)" class="dropdown-item">Import</a>
                                                        <hr class="dropdown-divider">
                                                        <a href="javascript:void(0)" class="dropdown-item">Export</a>
                                                    </div>
                                                </div>
                                            </div> -->
                                        </div>
                                
                                <div class="border mt-2"></div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="maimTable">
                                            <table class="table cataloguetable" cellspacing="0" width="100%">
                                                <thead class="thead-dark">
                                                  <tr>
                                                    <th scope="col" width="25%">Code</th>
                                                    <th scope="col" width="45%">Name</th>
                                                    <th scope="col" class="text-end">Default Price</th>
                                                    <th scope="col" class="text-center">Catalogue Price</th>
                                                    <th scope="col"></th>
                                                  </tr>
                                                </thead>
                                                <tbody id="CatalogueData">
                                                  
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
                
            <div class="modal-footer customer_Form_Popup">
                <!-- <input type="hidden" name="productID" id="productID">
                <input type="hidden" name="producttype" id="producttype"> -->
                <input type="hidden" name="TabId" id="TabId" value="0">
                <input type="hidden" name="catalogue_id" id="catalogue_id">
                <button type="button" class="profileDrop" id="submitCatalogue">Save & Continue</button>                
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>            
            </div>
        </div>
    </div>
</div>
@include('components.product-list')
<script>
    function itemsAddCatalogueModal(th,id=null){        
        // $("#productform")[0].reset();        
        // $(".needs-validationp").removeClass('was-validated');
        // $('#producttype').val(th);
        $("#nav-profile-tab").hide();
        $('#itemsAddCatalogueModal').modal('show');
    }
</script>
<script>
    function getProductData(selectedId) {
        $.ajax({
            url: '{{ route("item.ajax.getProductFromId") }}',
            method: 'Post',
            data: {
                id: selectedId
            },
            success: function(response) {
                console.log(response.data[0]);
                var data=response.data[0];
                var formattedPrice = parseFloat(data.price).toFixed(2);
                var html=`  <tr> 
                                <td scope="row">`+ (data.product_code ?? "") +`<input type="hidden" name="product_id" id="product_id" value="`+ data.id +`"> <input type="hidden" name="product_type" id="product_type" value="`+ data.product_type +`"></td> 
                                <td>`+data.product_name+`</td> 
                                <td class="text-end">£`+data.price+`</td> 
                                <td class="text-center">
                                    <input type="text" value="`+formattedPrice+`" name="item_catalogue_item_prices[]" class="text item_price numericOnly catalogueItemCustomPrice" data-item_id="11" tabindex="1">
                                </td>
                                <td>
                                    <img src="<?php echo url('public/frontEnd/jobs/images/delete.png');?>" alt="" class="data_delete image_style">
                                </td>
                            </tr>`;
                $("#CatalogueData").append(html);
                $("#productModalBAC").modal('hide');
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
    $(document).on('click', '.data_delete', function() {
        
        var id = $(this).data('delete');
        if(confirm("Are you sure to delete it?")){
            $(this).closest('tr').remove();
            if(id){
                $.ajax({
                    url: '{{ url("item/ProductCataloguePriceDelete") }}',
                    method: 'Post',
                    data: {
                        id: id,_token:'{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log(response);
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }
        }
        
    });

    $('#submitCatalogue').on('click', function () {
        var productname=$("#productname").val();
        var description=$("#description").val();
        var status=$('#catalogue_status option:selected').val();
        
        // alert(status)
        var type=$("#type").val();
        var catalogue_id=$("#catalogue_id").val();
        var TabId=$("#TabId").val();
        let tableData = [];
       
        if(productname == ''){
            $("#productname").css('border','1px solid red');
            return false;
        }
        $('#CatalogueData tr').each(function () {
            const row = $(this);
            let rowData = {
                id:row.find('input[name="ProductCatPrice"]').val(),
                product_id: row.find('input[name="product_id"]').val(),
                product_type: row.find('input[name="product_type"]').val(),
                product_code: row.find('td:eq(0)').contents().filter(function () {
                    return this.nodeType === Node.TEXT_NODE;
                }).text().trim(),
                product_name: row.find('td:eq(1)').text().trim(),
                price: row.find('td:eq(2)').text().replace('£', '').trim(),
                custom_price: row.find('input[name="item_catalogue_item_prices[]"]').val()
            };
            tableData.push(rowData);
        });
        console.log(tableData);
        // return false;
       
        $.ajax({
            url: "{{url('/item/catalogues_save')}}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                tableData: tableData,productname:productname,description:description,status:status,type:type,catalogue_id:catalogue_id,TabId:TabId
            },
            success: function (response) {
                console.log(response);
                if(TabId == 0 && response.success == true){
                    alert('Data saved successfully!');
                    $("#nav-home-tab").removeClass('active');
                    $("#nav-home").removeClass('active show');
                    $("#nav-profile-tab").show().addClass('active');
                    $("#nav-profile").addClass('active show');
                    $("#catalogue_id").val(response.data.id);
                    $("#TabId").val(1);
                }else if(response.success == false){
                    alert("Something went wrong. Please try later!");
                }else{
                    location.reload();
                }
            },
            error: function (error) {
                alert('An error occurred while saving the data.');
                console.log(error);
            }
        });
});
function tabClick(value){
    $("#TabId").val(value);
}
</script>

