<style>
    .addProduvtBg.costUpdatePop {
        padding: 10px;
        background-color: #edf6fb;
        margin: 0;
        text-align:left;
    }
    .addProduvtBg.costUpdatePop p {
        font-size: 13px;
        margin-bottom: 0;
        color: #168fdb;
    }
    .addProduvtBg.costUpdatePop a.udateBtn{

    }
    .nav-tabs > li:nth-child(1) .productCount {
        background: #0877bd;
        color: #fff; /* Optional: Ensure text is readable on the background */
        padding: 2px 5px; /* Optional: Add some padding for better appearance */
        border-radius: 3px; /* Optional: Round the corners */
    }
    .nav-tabs > li:nth-child(2) .productCount {
        background: #00bb3b;
        color: #fff;
        padding: 2px 5px;
        border-radius: 3px;
    }
    .nav-tabs > li:nth-child(3) .productCount {
        background: #a35001;
        color: #fff;
        padding: 2px 5px;
        border-radius: 3px;
    }
    .nav-tabs > li:nth-child(4) .productCount {
        background: #ff0101;
        color: #fff;
        padding: 2px 5px;
        border-radius: 3px;
    }
    .padd20{
        padding:0 20px;
    }
    .foor-plan > div {
    width: 100%;
    margin-top: 12px;
}
.model-shadow{
    box-shadow: 0px 0px 15px #00000057;
}
</style>

<!-- Because you are alive, everything is possible. - Thich Nhat Hanh -->
<div class="modal fade in" id="ProductListModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content modal-lg">
            <div class="modal-header terques-bg">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title"> Product List </h4>
            </div>
            <div class="modal-body">
                <div class="row">  
                    <div class="foor-box-wrap foor-plan">
                        <form id="flowForm">
                            
                            @csrf
                            <div class="custom-fieldset"> 
                                <ul class="nav nav-tabs">
                                    <li class="active" id="catalogue"><a data-toggle="tab" href="#ProductList">Product(s) <span class="productCount" id="productCount">00</span></a></li>
                                    <li id="cataloguePrice"><a data-toggle="tab" href="#ServiceList" onclick="getproductsOnType(2, 'setServiceInTable')">Service(s) <span class="productCount" id="serviceCount">00</span></a></li>
                                    <li id="cataloguePrice"><a data-toggle="tab" href="#ConsumableList" onclick="getproductsOnType(3, 'setConsumableInTable')">Consumable(s) <span class="productCount" id="consumableCount">00</span></a></li>
                                    <li id="cataloguePrice"><a data-toggle="tab" href="#ProductGroupList" onclick="getproductsOnType(4, 'setGroupInTable')">Product Group(s) <span class="productCount" id="groupCount">00</span></a></li>
                                </ul>

                                <div class="tab-content">
                                    <div id="ProductList" class="tab-pane fade in active">
                                        <div class="row" style="margin-top:10px;">
                                        <input type="hidden" id="selectedProductIds" name="product_ids" value="[]">
                                            <div class="col-md-4">
                                                <select id="product_categories1" name="product_categories1" class="form-control editInput product_categories">
                                                    
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <div class="input-group">
                                                    <input type="text" placeholder="Search Term" class="form-control">
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-default">Search</button>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-3"></div>
                                        </div>
                                        <table class="table table-striped table-hover table-bordered" id="setProductInTable">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Category</th>
                                                    <th>Product</th>
                                                    <th>Description</th>
                                                </tr>
                                                
                                            </thead>
                                            <tbody>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="ServiceList" class="tab-pane fade">
                                        <div class="row" style="margin-top:10px;">
                                            <div class="col-md-4">
                                                <select id="product_categories2" name="product_categories2" class="form-control editInput product_categories">
                                                    
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <div class="input-group">
                                                    <input type="text" placeholder="Search Term" class="form-control">
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-default">Search</button>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-3"></div>
                                        </div>
                                        <table class="table table-striped table-hover table-bordered" id="setServiceInTable">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Category</th>
                                                    <th>Product</th>
                                                    <th>Description</th>
                                                </tr>
                                                
                                            </thead>
                                            <tbody>
                                                
                                            </tbody>
                                        </table>
                                    </div>

                                    <div id="ConsumableList" class="tab-pane fade">
                                        <div class="row" style="margin-top:10px;">
                                            <div class="col-md-4">
                                                <select id="product_categories3" name="product_categories3" class="form-control editInput product_categories">
                                                    
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <div class="input-group">
                                                    <input type="text" placeholder="Search Term" class="form-control">
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-default">Search</button>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-3"></div>
                                        </div>
                                        <table class="table table-striped table-hover table-bordered" id="setConsumableInTable">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Category</th>
                                                    <th>Product</th>
                                                    <th>Description</th>
                                                </tr>
                                                
                                            </thead>
                                            <tbody>
                                                
                                            </tbody>
                                        </table>
                                    </div>

                                    <div id="ProductGroupList" class="tab-pane fade">
                                        <div class="row" style="margin-top:10px;">
                                            
                                            <div class="col-md-9">
                                                <div class="input-group">
                                                    <input type="text" placeholder="Search Term" class="form-control">
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-default">Search</button>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-3"></div>
                                        </div>
                                        <table class="table table-striped table-hover table-bordered" id="setGroupInTable">
                                            <thead>
                                                <tr>
                                                    <th>Product Group</th>
                                                    <th>Description</th>
                                                    <th></th>
                                                </tr>
                                                
                                            </thead>
                                            <tbody>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                </div>
                            </div>
                            <!-- <div class="noti_button">
                                <input type="button" class="btn btn-primary" value="Save & Continue" onclick="getSaveData()"></input>
                                <a href="javascript:" class="btn btn-primary" onclick="document.getElementById('workflowModal').style.display='none'">Cancel</a>
                            </div> -->
                        </form>
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- CalculatePop Modal -->
<div class="modal fade in" id="calculatePop" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
    <div class="modal-dialog model-shadow">
        <div class="modal-content modal-lg">
            <div class="modal-header terques-bg">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title " id="productGroupModalLabel"></h4>
            </div>
            <div class="modal-body">
                <div class="padd20">
                <h4 class="text-start">Product</h4>
                <div class="row">  
                    <div class="foor-box-wrap foor-plan">

                        <table class="table" id="containerA">
                            <thead class="table-light">
                                <tr>
                                    <th>Group Code</th>
                                    <th>Group Product </th>
                                    <th>Group Description</th>
                                    <th>Qty</th>
                                    <th>Cost Price($)</th>
                                    <th>Price($)</th>
                                    <th>Amount($) </th>
                                    <th>Profit($)</th>
                                </tr>
                            </thead>
                            <tbody id="productGroupModalData">
                            
                            </tbody>
                        </table>
                        <h4 class="text-left">Products</h4>
                        <div class="addProduvtBg costUpdatePop">
                            <div class="row">
                                <div class="col-md-9">
                                    <p>One or moere product prices have been changed. Select 'Update' next to each item to use the default product price.</p>
                                    <p>Alternatively select 'Update All Prices' to use the default product price for all items.</p>
                                </div>
                                <div class="col-md-2">
                                <a href="#!" class="btn btn-primary">Update All Prices </a>
                                </div>
                            </div>
                            
                        </div>
                        <div class="mb-3 mt-2 row">
                            <div class="col-sm-3">
                                <input type="text" class="form-control editInput" id="inputCountry" placeholder="Search Product">
                            </div>
                            <div class="col-sm-7">
                                <div class="plusandText">
                                    <a href="#!" class="formicon" id="cost_product_popup"><i class="fa-solid fa-square-plus"></i></a>
                                    <span class="afterPlusText"> (Type to view product or <a href="#!" onclick="openProductListModal()">Click here</a> to view all assets)</span>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="costProdut"> -->
                            <div class="productDetailTable">
                                <table class="table" id="containerA">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Code</th>
                                            <th>Product </th>
                                            <th>Description</th>
                                            <th>Qty</th>
                                            <th>Cost Price($)</th>
                                            <th>Price($)</th>
                                            <th>Amount($) </th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- <tr>
                                            <td colspan="7">
                                                
                                            </td>
                                        </tr> -->
                                        <tr>
                                            <td colspan="3"></td>
                                            <td>Totale</td>
                                            <td>0</td>
                                            <td>$0.00</td>
                                            <td>$0.00</td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        <!-- </div> -->
                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <button type="button" class="btn btn-primary">Add as Group</button>
                    <button type="button" class="btn btn-primary">Add as Item</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                </div>
</div>

            </div>
        </div>
    </div>
</div>
<!-- End offCalculatePop Modal -->
<script>
    function add_item() {
        $.ajax({
            url: '{{ url("admin/getCategoryList") }}',
            method: 'GET',
            success: function(response) {
                // console.log(response);return false;
                var productCatList = document.querySelectorAll('.product_categories');

                productCatList.forEach(productCat => {
                    productCat.innerHTML = '';
                    let newOption = document.createElement('option');
                    newOption.text = "-Any Category-";
                    productCat.appendChild(newOption);

                    response.data.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.text = user.name;
                        productCat.appendChild(option);
                    });
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });

        getproductsOnType(1, 'setProductInTable');

        $.ajax({
            url: '{{ url("admin/getProductListCounts") }}',
            method: 'GET',
            success: function(response) {
                console.log("CountPeoduct", response.data);
                document.getElementById('productCount').textContent = response.data.product.toString().padStart(2, '0');
                document.getElementById('serviceCount').textContent = response.data.service.toString().padStart(2, '0');
                document.getElementById('consumableCount').textContent = response.data.consumable.toString().padStart(2, '0');
                document.getElementById('groupCount').textContent = response.data.product_group.toString().padStart(2, '0');
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });

        $('#ProductListModal').modal('show');
    }

    function populateTable(data, tableId) {
        // console.log(data.type);return false;
        var type=data.type;
        var data=data.data;
        const tableBody = document.querySelector(`#${tableId} tbody`);
        tableBody.innerHTML = ''; // Clear the table body

        if (data.length === 0) {
            const noDataRow = document.createElement('tr');
            const noDataCell = document.createElement('td');

            noDataCell.setAttribute('colspan', 4);
            noDataCell.textContent = 'No products found';
            noDataCell.style.textAlign = 'center'; // Center the message

            noDataRow.appendChild(noDataCell);
            tableBody.appendChild(noDataRow);
        } else {
            data.forEach(item => {
                const row = document.createElement('tr');
                row.setAttribute('data-id', item.id);
                if(type == 4){
                    row.addEventListener('click', (event) => {
                        if (event.target && event.target.nodeName === 'TD') {
                            const clickedRow = event.target.parentNode;
                            const productInput = clickedRow.querySelector('input.product_id');
                            const productId = parseInt(clickedRow.getAttribute('data-id'));
                            const productIdsInput = document.getElementById('selectedProductIds');
                            let productIds = JSON.parse(productIdsInput.value || '[]');
                            if (!productIds.includes(productId)) {
                                productIds.push(productId);
                            } else {
                                productIds = productIds.filter(id => id !== productId);
                            }
                            productIdsInput.value = JSON.stringify(productIds);
                            
                        }
                    });
                    const nameCell = document.createElement('td');
                    nameCell.textContent = item.name;
                    row.appendChild(nameCell);

                    const descriptionCell = document.createElement('td');
                    descriptionCell.textContent = item.description;
                    row.appendChild(descriptionCell);

                    const ModalCell = document.createElement('td');
                    ModalCell.id='inputPlusCircle';
                    ModalCell.innerHTML = '<a class="javascript:void(0)" onclick="get_modal('+item.id+')"><i class="fa  fa-plus-circle"></i> </a>';
                    row.appendChild(ModalCell);
                }else{
                    row.addEventListener('click', (event) => {
                        if (event.target && event.target.nodeName === 'TD') {
                            const clickedRow = event.target.parentNode;
                            const productInput = clickedRow.querySelector('input.product_id');
                            const productId = parseInt(clickedRow.getAttribute('data-id'));
                            const productIdsInput = document.getElementById('selectedProductIds');
                            let productIds = JSON.parse(productIdsInput.value || '[]');
                            if (!productIds.includes(productId)) {
                                productIds.push(productId);
                            } else {
                                productIds = productIds.filter(id => id !== productId);
                            }
                            productIdsInput.value = JSON.stringify(productIds);
                            getProductData(productId);
                            
                        }
                    });

                    const codeCell = document.createElement('td');
                    codeCell.textContent = item.product_code;
                    row.appendChild(codeCell);

                    const categoryCell = document.createElement('td');
                    categoryCell.innerHTML = item.name;
                    row.appendChild(categoryCell);

                    const productCell = document.createElement('td');
                    productCell.textContent = item.product_name;
                    row.appendChild(productCell);

                    const descriptionCell = document.createElement('td');
                    descriptionCell.textContent = item.description;
                    row.appendChild(descriptionCell);
                }
                tableBody.appendChild(row);
            });
        }
    }

    function getproductsOnType(type, tableId) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: '{{ url("admin/getProduct_List") }}',
            method: 'Post',
            data: {
                type: type
            },
            success: function(response) {
                // console.log(response);return false;
                populateTable(response, tableId);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
    function get_modal(id){
        // alert(id)
        $("#calculatePop").modal('show');
        // return false;
        $.ajax({
            url: '{{ url("admin/ProductGroupProductsdetail") }}',
            method: 'Post',
            data: {
                id: id,_token:'{{ csrf_token() }}'
            },
            success: function(response) {
                // console.log(response);return false;
                if (response.data && response.data.length > 0 && response.data[0].name) {
                    var productGroupModalData=response.data[0];
                    document.getElementById('productGroupModalLabel').innerHTML='Product Group: '+productGroupModalData.name;
                    var productGroupModalDesign='';
                    productGroupModalData.product_group_product.forEach(item => {
                        const amount=item.price*item.quantity;
                        const profit=amount-item.cost_price;
                        productGroupModalDesign+=`<tr>
                                <td>
                                    <div class="CSPlus">
                                        <span class="plusandText">
                                            <input type="text" class="form-control editInput input80" name="modal_code" id="modal_code" value="`+item.product_code+`">
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="">
                                        <input type="text" class="form-control editInput" name="modal_group_product" id="modal_group_product" value="`+item.product_name+`">
                                    </div>
                                </td>
                                <td>
                                    <div class="">
                                        <textarea class="form-control textareaInput rounded-0" name="modal_description" id="modal_description" rows="1" placeholder="Description">`+productGroupModalData.description+`</textarea>
                                    </div>
                                </td>
                                <td>
                                    <div class="">
                                        <input type="text" class="form-control editInput input50" id="modal_qty" name="modal_qty" value="`+item.quantity+`">
                                    </div>
                                </td>
                                <td>
                                    <div class="">
                                        <input type="text" class="form-control editInput input50" id="modal_cost" name="modal_cost" value="`+item.cost_price+`">
                                    </div>
                                </td>
                                <td>
                                    <div class="">
                                        <input type="text" class="form-control editInput input50" id="modal_price" name="modal_price" value="`+item.price+`">
                                    </div>
                                </td>

                                <td>
                                    <div class="">
                                        <input type="text" class="form-control editInput input50" id="modal_amount" name="modal_amount" value="`+amount+`">
                                    </div>
                                </td>
                                <td>
                                    <div class="">
                                        <input type="text" class="form-control editInput input50" id="modal_profit" name="modal_profit" value="`+profit+`">
                                    </div>
                                </td>
                            </tr>`;
                    });
                    $('#productGroupModalData').html(productGroupModalDesign);
                }else{
                    document.getElementById('productGroupModalLabel').innerHTML='Product Group: ';
                    $('#productGroupModalData').html('<tr><td colspan="8" class="text-center" style="color: #e10078; ">Sorry, there are no items available</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>