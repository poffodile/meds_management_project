@include('frontEnd.salesAndFinance.jobs.layout.header')

<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Catalogues </h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="jobsection">
                    <a href="javascript:void(0)" class="profileDrop" onclick="itemsAddCatalogueModal(1)">Add</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="maimTable">
                    <div class="printExpt">
                        <div class="prntExpbtn">
                            <a href="#!">Print</a>
                            <!-- <a href="#!">Export</a> -->
                        </div>
                        <!-- <div class="searchFilter">
                            <a href="#!">Show Search Filter</a>
                        </div> -->
                    </div>
                    <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="jobsection">
                                    <a href="javascript:void(0)" id="deleteSelectedRows" class="profileDrop">Delete</a>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="pageTitleBtn p-0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <table id="exampleOne" class="display tablechange" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="" style=" width:60px;"><input type="checkbox" id="selectAll"> <label for="selectAll"> </label></th>
                                <th>#</th>
                                <th>Catalogue</th>
                                <th>Type</th>
                                <th>Item Count</th>
                                <th>Created On</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
    
                        <tbody id="CatalogueDataList">
                            @foreach($catalogues as $key=>$val)
                                <tr>
                                    <td><input type="checkbox" id="" class="delete_checkbox" value="{{$val->id}}"></td>
                                    <td>{{++$key}}</td>
                                    <td>{{$val->name}}</td>
                                    <td>{{($val->catalogue_type == 1 ? 'Catalogue Pricing Only' : 'Mixed Pricing')}}</td>
                                    <td>{{$val->product_catalogue_prices_count}}</td>
                                    <td>{{$val->created_at}}</td>
                                    <td>
                                        @if($val->status == 1)
                                            <span class="grencheck"><i class="fa-solid fa-circle-check"></i></span>
                                        @else
                                            <span class="grayCheck"><i class="fa-solid fa-circle-check"></i></span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="pageTitleBtn p-0">
                                            <div class="nav-item dropdown">
                                                <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Action
                                                </a>
                                                <div class="dropdown-menu fade-up m-0">
                                                    <a href="javascript:void(0)" class="dropdown-item col-form-label fetch_data" data-id="{{$val->id}}" data-name="{{$val->name}}" data-catalogue_type="{{$val->catalogue_type}}" data-status="{{$val->status}}" data-description="{{$val->description}}">Edit</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> <!-- End off main Table -->
            </div>
            </di>
        </div>
</section>
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
            var model='ProductCatalogue';
            $.ajax({
                type: "POST",
                url: "{{url('/bulk_delete')}}",
                data: {ids:ids,model:model,_token:token},
                success: function(data) {
                    console.log(data);
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
$('.delete_checkbox').on('click', function() {
    if ($('.delete_checkbox:checked').length === $('.delete_checkbox').length) {
        $('#selectAll').prop('checked', true);
    } else {
        $('#selectAll').prop('checked', false);
    }
});
 </script> 
 <script>
    $('.fetch_data').on('click', function(){
        var cat_id=$(this).data('id');
        var name=$(this).data('name');
        var catalogue_type=$(this).data('catalogue_type');
        var description=$(this).data('description');
        var status=$(this).data('status');
        $("#nav-profile-tab").show();
        $("#productname").val(name);
        $("#description").val(description);
        $("#type").val(catalogue_type);
        $("#catalogue_status").val(status);
        $("#catalogue_id").val(cat_id);
        $.ajax({
            url: '{{ url("item/ProductCataloguePriceList") }}',
            method: 'Post',
            data: {
                _token:'{{ csrf_token() }}',cat_id:cat_id 
            },
            success: function(response) {
                console.log(response.data);
                var html1='';
                if (response.data.length === 0) {
                    html1 = `<tr><td colspan="5" class="text-center" style="color:#e10078;">Sorry, there are no items available</td></tr>`;
                }else{
                    response.data.forEach((item) => {
                        var formattedPrice1 = parseFloat(item.catalogue_price).toFixed(2); 
                        html1+=`  <tr> 
                                    <td scope="row">`+ (item.product_code ?? "") +`<input type="hidden" name="product_id" id="product_id" value="`+ item.product_id +`"> <input type="hidden" name="product_type" id="product_type" value="`+ item.product_type +`"><input type="hidden" name="ProductCatPrice" id="ProductCatPrice" value="`+item.id+`"></td> 
                                    <td>`+item.product_name+`</td> 
                                    <td class="text-end">£`+item.default_price+`</td> 
                                    <td class="text-center">
                                        <input type="text" value="`+formattedPrice1+`" name="item_catalogue_item_prices[]" class="text item_price numericOnly catalogueItemCustomPrice" data-item_id="11" tabindex="1">
                                    </td>
                                    <td>
                                        <img src="<?php echo url('public/frontEnd/jobs/images/delete.png');?>" alt="" class="data_delete image_style" data-delete="`+item.id+`">
                                    </td>
                                </tr>`;
                        
                    });  
                }
                $("#CatalogueData").html(html1); 
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
        $('#itemsAddCatalogueModal').modal('show');
    });
 </script>
@include('frontEnd.salesAndFinance.item.common.addcataloguemodal')

@include('frontEnd.salesAndFinance.jobs.layout.footer')