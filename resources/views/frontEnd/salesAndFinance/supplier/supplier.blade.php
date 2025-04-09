@include('frontEnd.salesAndFinance.jobs.layout.header')

<style>
    table.tablechange tbody td {
    font-size: 12px;
    white-space: nowrap;
}
.image_delete {
    cursor: pointer;
}
.textbox {
    box-sizing: border-box;
    perspective: 500px;
    position: relative;
    text-align: left;
}

.textbox input {
    padding: 10px 14px;
    width: 100%;
}

.textbox input::placeholder {
    color: #ccc;
}

.textbox .autoComplete {
    left: 0;
    position: absolute;
    top: calc(100% + 5px);
    width: 100%;
}

.textbox .autoComplete .item {
    animation: showItem .3s ease forwards;
    background-color: #fff;
    box-shadow: 0 8px 8px -10px rgba(0, 0, 0, .4);
    box-sizing: border-box;
    color: #7C8487;
    cursor: pointer;
    display: block;
    font-size: .8rem;
    opacity: 0;
    outline: none;
    padding: 10px;
    text-decoration: none;
    transform-origin: top;
    /* transform: rotateX(-90deg); */
    transform: translateX(10px);
}

.textbox .autoComplete .item:hover,
.textbox .autoComplete .item:focus {
    background-color: #fafafa;
    color: #D1822B;
}

@keyframes showItem {
    0% {
        opacity: 0;
        /* transform: rotateX(-90deg); */
        transform: translateX(10px);
    }

    100% {
        opacity: 1;
        /* transform: rotateX(0); */
        transform: translateX(0);
    }
}
.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 5px;
    border-radius: 4px;
    border: 1px solid #ced4da;
    font-size: 14px;
}

.select2-container .select2-selection--single .select2-selection__arrow {
    height: 100%;
}

.select2-container .select2-selection--single .select2-selection__rendered {
    padding: 4px;
}
.select2-container .select2-selection--multiple{
    min-height:32px !important;
 }
 .parent-container {
    position: absolute;
    background: #fff;
    width:190px;
}
#productList li:hover{
    cursor: pointer;
}
#active_inactive {
    background-color:#474747;
}
</style>
<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Suppliers</h3>
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="jobsection">
                    <a href="{{url('/supplier_add')}}" class="profileDrop">New Supplier</a>
                    <a href="{{url('supplier?list_mode=ACTIVE')}}" class="profileDrop" <?php if($table_status == 1){echo 'id="active_inactive"';}?>>Active Supplier({{$CountActiveSupplier}})</a>
                    <a href="{{url('supplier?list_mode=INACTIVE')}}" class="profileDrop" <?php if($table_status == 0){echo 'id="active_inactive"';}?>>Inactive Supplier({{$CountInactiveSupplier}})</a>
                    <a href="#" class="profileDrop" id="impExpClickbtnPopup">Import</a>
                    <a href="#!">click here</a> to download import template
                </div>
            </div>
        </div>



    



        <di class="row">
        <div class="col-lg-12">
            <div class="maimTable">
                <div class="printExpt">
                    <div class="prntExpbtn">
                        <a href="#!">Print</a>
                        <a href="#!">Export</a>
                    </div>
                    <div class="searchFilter">
                        <!-- <a href="javascript:void(0)" onclick="hideShowDiv()" class="hidebtn">Hide Search Filter</a> -->
                    </div>

                </div>
                <div class="searchJobForm" id="divTohide" style="display:none">
                    <form id="search_dataForm" class="p-4">
                        <div class="row justify-content-center">
                            <div class="col-md-3">
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label text-end">Expense By:</label>
                                    <div class="col-md-8">
                                        <select class="form-control editInput selectOptions" id="expenseBy">
                                            <option selected disabled></option>
                                            <option value="{{Auth::user()->id}}">{{Auth::user()->name}}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label text-end">Expense Date:</label>
                                    <div class="col-md-4">
                                        <input type="date" class="form-control editInput" id="start_date" >
                                    </div>
                                    <div class="col-md-4">
                                        <input type="date" class="form-control editInput" id="end_date">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label text-end">Customer:</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput" id="customer_name" placeholder="Type Customer Name">
                                        <input type="hidden" id="selectedId" name="selectedId">
                                        <div class="parent-container"></div>
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label text-end">Keywords:</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput" id="keywords" keywords="" placeholder="Keywords to seacrh">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label text-end">Billable:</label>
                                    <div class="col-md-8">
                                        <select class="form-control editInput selectOptions" id="billable_search">
                                            <option selected disabled>--Any--</option>
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="pageTitleBtn justify-content-center">
                                    <a href="javascript:void(0)" onclick="searchBtn()" class="profileDrop px-3">Search </a>
                                    <a href="javascript:void(0)" onclick="clearBtn()" class="profileDrop px-3">Clear</a>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>



            </div> <!-- End off main Table -->
        </div>
            <div class="col-lg-12">
                <div class="maimTable mt-2 table_responsive">
                
                    
                    <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="jobsection d-flex">
                                    <a href="javascript:void(0)" id="deleteSelectedRows" class="profileDrop">Delete</a>
                                    <!-- <div class="pageTitleBtn p-0">
                                        <div class="nav-item dropdown">
                                            <a href="#" class="nav-link dropdown-toggle profileDrop"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                Bulk Action </a>
                                            <div class="dropdown-menu fade-up m-0">
                                                <a href="#" class="dropdown-item col-form-label">Set Accont
                                                    Codes</a>
                                                <a href="#" class="dropdown-item col-form-label">Set Tax
                                                    Rats</a>
                                                <a href="#" class="dropdown-item col-form-label">Fix duplicate
                                                    product codes</a>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                            <!-- <div class="col-md-5">
                                <div class="pageTitleBtn p-0">
                                    <a href="#" class="profileDrop"> <i class="material-symbols-outlined">
                                            settings </i></a>
                                </div>
                            </div> -->
                        </div>
                    </div>
                    <div class="alert alert-success text-center" style="display:none">
                        <p>Status Change Successfully Done</p>
                    </div>
                    <table id="exampleOne" class="display tablechange" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center" style=" width:30px;"><input type="checkbox" id="selectAll"> <label for="selectAll"> All Select</label></th>
                                <th>#</th>
                                <th>Supplier Name</th>
                                <th>Address</th>
                                <th>Contact Name</th>
                                <th>Email</th>
                                <th>Telephone</th>
                                <th>Status</th>
                                <th></th>

                            </tr>
                        </thead>
                        <tbody id="supplier_data">
                            @foreach($supplier_list as $key=>$val)
                            <tr>
                                <td><input type="checkbox" id="" class="delete_checkbox" value="{{$val->id}}"></td>
                                <td>{{++$key}}</td>
                                <td>{{$val->name}}</td>
                                <td>{{$val->address}}</td>
                                <td>{{$val->contact_name}}</td>
                                <td>{{$val->email}}</td>
                                <td>{{$val->telephone}}</td>
                                <td>
                                    @if($val->status == 1)
                                        <span class="grencheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                    @else
                                        <span class="grayCheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-inline-flex align-items-center ">
                                        <div class="nav-item dropdown">
                                            <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">
                                                Action
                                            </a>
                                            <div class="dropdown-menu fade-up m-0">
                                                <a href="{{url('supplier_edit?key=')}}{{base64_encode($val->id)}}" class="dropdown-item">Edit Details</a>
                                                <!-- <hr class="dropdown-divider">
                                                <a href="javasrcript:void(0)" onclick="get_modal(1,null)" class="dropdown-item">Record Expense</a> -->
                                                <hr class="dropdown-divider">
                                                <a href="javascript:void(0)" onclick="get_modal(2,{{$val->id}})" class="dropdown-item">CRM / History</a>
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
  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script> -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
  <script>
    function status_change(id, status){
        var token='<?php echo csrf_token();?>'
        var model="Supplier";
        $.ajax({
            type: "POST",
            url: "{{url('/status_change')}}",
            data: {id:id,status:status,model:model,_token:token},
            success: function(data) {
                console.log(data);
                if($.trim(data)==1){
                    $(".alert").show('slow' , 'linear').delay(2000).fadeOut(setTimeout(function() {
                    location.reload();
                }, 3000));

                    
                }
                
            }
        });
    }
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
            var model='Supplier';
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
@include('frontEnd.salesAndFinance.jobs.layout.footer')
