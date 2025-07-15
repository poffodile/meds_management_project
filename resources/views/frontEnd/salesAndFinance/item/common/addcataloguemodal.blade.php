<style>
    .add_catalogue .nav-tabs .nav-item.show .nav-link,
    .add_catalogue .nav-tabs .nav-link.active {
        color: #ffffff;
        background-color: #0877bd;
        border-color: var(--nav-tabs-link-active-border-color);
    }

    .cataloguefrm {
        border: 1px solid #0877bd;
        border-radius: 5px;
    }

    .add_catalogue .nav-tabs .nav-link {
        border: var(--nav-tabs-border-width) solid #0877bd;
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

    .catalogueItemCustomPrice {
        width: 65px;
        text-align: right
    }

    .catalogueitemDelete i {
        color: red;
        font-size: 22px;
        font-weight: 700;
    }

    .image_style {
        cursor: pointer;
    }
</style>

<div class="modal fade" id="itemsAddCatalogueModal" tabindex="-1" data-backdrop="static" data-keyboard="false"
    aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="customerModalLabel">Catalogues</h4>
            </div>
            <div class="modal-body">
                <div class="tab-teaser">
                    <div class="tab-menu">
                        <ul>
                            <li><a href="javascript:void(0)" class="active btn" onclick="tabClick(0)" data-rel="Catalogue">Catalogue</a></li>
                            <li><a href="javascript:void(0)" data-rel="CataloguePrice" class="btn" onclick="tabClick(1)">Catalogue Price</a></li>
                        </ul>
                    </div>
                    <div class="tab-main-box">
                        <div class="tab-box" id="Catalogue" style="display:block;">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="mb-2 col-form-label">Catalogue Name <span class="radStar">*</span></label>
                                        <input type="text" class="form-control editInput" name="product_name" id="productname" value="" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="mb-2 col-form-label">Description</label>
                                        <textarea class="form-control textareaInput" id="description" name="description" rows="3"
                                            placeholder="Description"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="mb-2 col-form-label">Type</label>
                                        <select class="form-control editInput selectOptions" id="type" name="type">
                                            <option value="1">Catalogue Pricing Only</option>
                                            <option value="2">Mixed Pricing</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="mb-2 col-form-label">Status</label>
                                        <select class="form-control editInput selectOptions" id="catalogue_status" name="catalogue_status">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-box" id="CataloguePrice">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="jobsection">
                                        <a href="javascript:void(0)" onclick="openProductListModal()" class="btn btn-warning">Add Item</a>
                                        <a href="javascript:void(0)" class="btn btn-warning crmNewBtn" id="openCallsModel"> Import</a>
                                        <form class="searchForm" action="">
                                            <div class="row">
                                                <div class="col-sm-9 pe-2">
                                                    <input type="text" class="form-control" placeholder="Your Catalogue" name="email">
                                                </div>
                                                <button type="button" class=" col-sm-3 btn btn-primary">Search</button>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- <div class="col-sm-3">
                                        <div class="nav-item dropdown" style="margin-left:100px">
                                            <a href="#" class="nav-link dropdown-toggle btn btn-warning" data-toggle="dropdown" aria-expanded="false">
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
                                <!-- <div class="border mt-2"></div> -->
                                <div class="col-lg-12">
                                    <div class="maimTable productDetailTable mb-4 table-responsive">
                                        <table class="table border-top border-bottom" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Name</th>
                                                    <th>Default Price</th>
                                                    <th>Catalogue Price</th>
                                                    <th></th>
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
            <div class="modal-footer customer_Form_Popup">
                <!-- <input type="hidden" name="productID" id="productID">
                <input type="hidden" name="producttype" id="producttype"> -->
                <input type="hidden" name="TabId" id="TabId" value="0">
                <input type="hidden" name="catalogue_id" id="catalogue_id">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="submitCatalogue">Save</button>
            </div>
        </div>
    </div>
</div>

@include('components.product-list')

<script>
    $('.tab-menu li a').on('click', function() {
        var $this = $(this);
        var target = $this.attr('data-rel');

        // find the closest tab-menu
        var $tabMenu = $this.closest('.tab-menu');
        var $tabContentContainer = $this.closest('.tab-teaser, .tab-container'); // adjust container if needed

        $tabMenu.find('li a').removeClass('active');
        $this.addClass('active');

        // Show the related tab-box inside the same section
        $tabContentContainer.find(".tab-box").hide();
        $tabContentContainer.find("#" + target).fadeIn('slow');

        return false;
    });
</script>

<script>
    function itemsAddCatalogueModal(th, id = null) {
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
                var data = response.data[0];
                var formattedPrice = parseFloat(data.price).toFixed(2);
                var html = `  <tr> 
                                <td scope="row">` + (data.product_code ?? "") + `<input type="hidden" name="product_id" id="product_id" value="` + data.id + `"> <input type="hidden" name="product_type" id="product_type" value="` + data.product_type + `"></td> 
                                <td>` + data.product_name + `</td> 
                                <td class="text-end">£` + data.price + `</td> 
                                <td class="text-center">
                                    <input type="text" value="` + formattedPrice + `" name="item_catalogue_item_prices[]" class="text item_price numericOnly catalogueItemCustomPrice" data-item_id="11" tabindex="1">
                                </td>
                                <td>
                                    <img src="<?php echo url('public/frontEnd/jobs/images/delete.png'); ?>" alt="" class="data_delete image_style">
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
        if (confirm("Are you sure to delete it?")) {
            $(this).closest('tr').remove();
            if (id) {
                $.ajax({
                    url: '{{ url("item/ProductCataloguePriceDelete") }}',
                    method: 'Post',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log(response);
                         if (isAuthenticated(data) == false) {
                            return false;
                        }
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }
        }

    });

    $('#submitCatalogue').on('click', function() {
        var productname = $("#productname").val();
        var description = $("#description").val();
        var status = $('#catalogue_status option:selected').val();

        // alert(status)
        var type = $("#type").val();
        var catalogue_id = $("#catalogue_id").val();
        var TabId = $("#TabId").val();
        let tableData = [];

        if (productname == '') {
            $("#productname").css('border', '1px solid red');
            return false;
        }
        $('#CatalogueData tr').each(function() {
            const row = $(this);
            let rowData = {
                id: row.find('input[name="ProductCatPrice"]').val(),
                product_id: row.find('input[name="product_id"]').val(),
                product_type: row.find('input[name="product_type"]').val(),
                product_code: row.find('td:eq(0)').contents().filter(function() {
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
        var url = "{{url('/item/catalogues_save')}}";
        if (catalogue_id != '') {
            url = "{{url('/item/catalogues_edit')}}";
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                tableData: tableData,
                productname: productname,
                description: description,
                status: status,
                type: type,
                catalogue_id: catalogue_id,
                TabId: TabId
            },
            success: function(response) {
                console.log(response);
                 if (isAuthenticated(data) == false) {
                    return false;
                }
                if (TabId == 0 && response.success == true) {
                    alert('Data saved successfully!');
                    $("#nav-home-tab").removeClass('active');
                    $("#nav-home").removeClass('active show');
                    $("#nav-profile-tab").show().addClass('active');
                    $("#nav-profile").addClass('active show');
                    $("#catalogue_id").val(response.data.id);
                    $("#TabId").val(1);
                } else if (response.success == false) {
                    alert("Something went wrong. Please try later!");
                } else {
                    location.reload();
                }
            },
            error: function(error) {
                alert('An error occurred while saving the data.');
                console.log(error);
            }
        });
    });

    function tabClick(value) {
        $("#TabId").val(value);
    }
</script>