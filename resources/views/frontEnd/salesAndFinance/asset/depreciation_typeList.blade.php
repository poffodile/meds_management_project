@include('frontEnd.salesAndFinance.jobs.layout.header')
<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Depreciation Type</h3>
                </div>
            </div>
            <div class="col-md-8 col-lg-8 col-xl-8 px-3">
                <!-- <div class="pageTitleBtn">
                    <a href="{{url('purchase-orders-search')}}" class="profileDrop"> Search Purchase Orders</a>
                    <a href="{{url('purchase-order-invoices')}}" class="profileDrop"> Invoice Received</a>
                    <a href="{{url('purchase-order-statements')}}" class="profileDrop"> Statements</a>
                </div> -->
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="jobsection">
                    <div class="d-inline-flex align-items-center ">
                        <div class="nav-item dropdown">
                            <a href="javascript:void(0)" onclick="opendepreciation_typesModal()" class="profileDrop">New</a>
                        </div>
                    </div>
                </div>
            </div>
         </div>
         <div class="alert success-message text-center" id="msg" style="display:none;height:50px">
            <p id="status_meesage"></p>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="maimTable">
                    <div class="printExpt">
                        <div class="prntExpbtn">
                            <a href="#!">Print</a>
                            <a href="#!">Export</a>
                        </div>
                        <div class="searchFilter" style="display:none">
                            <a href="#!" onclick="hideShowDiv()" class="hidebtn">Show Search Filter</a>
                        </div>

                    </div>

                    <div class="searchJobForm" id="divTohide" style="display:none">
                        <form id="search_dataForm" class="p-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">PO Ref:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="po_ref">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Department:</label>
                                        <div class="col-md-8 position-relative">
                                            <input type="text" class="form-control editInput" id="department">
                                            <input type="hidden" id="selectedDeptId" name="selectedDeptId">
                                            <div class="parent-container department-container"></div>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Tag:</label>
                                        <div class="col-md-8 position-relative">
                                            <input type="text" class="form-control editInput" id="tag">
                                            <input type="hidden" id="selectedTagtId" name="selectedTagtId">
                                            <div class="parent-container tag-container"></div>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">

                                        <label class="col-md-4 col-form-label text-end ">
                                            <a href="#!" class="tutor-student-tooltip-col">
                                                EDD From:

                                                <span class="tutor-student-tooltiptext3">Expedcted Delivery Date</span>
                                            </a>
                                        </label>

                                        <div class="col-md-4">
                                            <input type="date" class="form-control editInput" id="edd_startDate">
                                        </div>

                                        <div class="col-md-4">
                                            <input type="date" class="form-control editInput" id="edd_endDate">
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Supplier:</label>
                                        <div class="col-md-8 position-relative">
                                            <input type="text" class="form-control editInput" id="supplier">
                                            <input type="hidden" id="selectedsupplierId" name="selectedsupplierId">
                                            <div class="parent-container supplier-container"></div>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">PO Date From:</label>
                                        <div class="col-md-4">
                                            <input type="date" class="form-control editInput" id="po_startDate">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="date" class="form-control editInput" id="po_endDate">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Customer:</label>
                                        <div class="col-md-8 position-relative">
                                            <input type="text" class="form-control editInput" id="customer">
                                            <input type="hidden" id="selectedCustomerId" name="selectedCustomerId">
                                            <div class="parent-container customer-container"></div>
                                        </div>
                                    </div>

                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Created By:</label>
                                        <div class="col-md-8 position-relative">
                                            <input type="text" class="form-control editInput" id="created_by">
                                            <input type="hidden" id="selectedcreatedById" name="selectedcreatedById">
                                            <div class="parent-container createdBy-container"></div>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">PO Posted:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="po_posted">
                                                <option selected disabled>--Any--</option>
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Project:</label>
                                        <div class="col-md-8 position-relative">
                                            <input type="text" class="form-control editInput" id="project">
                                            <input type="hidden" id="selectedProjectId" name="selectedProjectId">
                                            <div class="parent-container project-container"></div>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Keywords:</label>
                                        <div class="col-md-8 position-relative">
                                            <input type="text" class="form-control editInput" id="keywords">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Delivery Status:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="delivery_status">
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
                    <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="jobsection">
                                    <a href="javascript:void(0)" id="deleteSelectedRows" class="profileDrop">Delete</a>
                                    
                                </div>
                            </div>
                            <!-- <div class="col-md-5">
                                    <div class="pageTitleBtn p-0">
                                        <a href="#" class="profileDrop"> <i class="material-symbols-outlined"> settings </i></a>        
                                    </div>
                                </div> -->
                        </div>
                    </div>

                    <table id="exampleOne" class="display tablechange text-center" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="col-1"><input type="checkbox" id="selectAllCheckBoxes"></th>
                                <th class="col-1">#</th>
                                <th class="col-2">Name</th>
                                <th class="col-2">Percentage(%)</th>
                                <th class="col-2">Status</th>
                                <th class="col-2"></th>
                            </tr>
                        </thead>

                        <tbody id="search_data">
                          <?php foreach($list as $key=>$val){?>
                            <tr>
                                <td>
                                    <div class="text-center"><input type="checkbox" id="" class="delete_checkbox" value="{{$val->id}}"></div>
                                </td>
                                <td>{{++$key}}</td>
                                <td>{{$val->name}}</td>
                                <td>{{$val->percentage}}</td>
                                <td>
                                    <?php if ($val->status == 1) { ?>
                                        <span class="grencheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                    <?php } else { ?>
                                        <span class="grayCheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="d-inline-flex align-items-center ">
                                        <div class="nav-item dropdown">
                                            <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">Action</a>
                                            <div class="dropdown-menu fade-up m-0">
                                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#depreciation_typesModal" class="dropdown-item depreciation_typeModal_dataFetch" data-id="{{ $val->id }}" data-name="{{ $val->name }}" data-percentage="{{ $val->percentage }}" data-status="{{ $val->status }}">Edit Details</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                          <?php }?>
                        </tbody>
                        
                    </table>

                </div> <!-- End off main Table -->
            </div>
        </di>
    </div>
</section>

<!-- Record Payment Modal start here -->
<div class="modal fade" id="depreciation_typesModal" tabindex="-1" aria-labelledby="depreciation_typesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="depreciation_typesModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="formDtail">
                            <div class="col-md-12 col-lg-12 col-xl-12 text-center">
                                <div class="mt-1 mb-0 text-center" id="messagedepreciation_types"></div>
                            </div>
                            <form id="depreciation_typesForm" class="customerForm pt-0">
                                <input type="hidden" name="id" id="id">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 col-lg-12 col-xl-12">
                                        <div class="formDtail">
                                                <div class="mb-2 row">
                                                    <label for="inputName" class="col-sm-3 col-form-label">Name <span class="radStar ">*</span></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput" id="name" name="name" value="">
                                                    </div>
                                                </div>
                                                <div class="mb-2 row">
                                                    <label for="inputName" class="col-sm-3 col-form-label">Percenatge <span class="radStar ">*</span></label>
                                                    <div class="col-sm-1 pe-0">
                                                        <div style="background:#e3e3e1;" class="px-2 py-1 text-center">
                                                            <span>%</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control editInput numberInput" id="percentage" name="percentage" value="">
                                                    </div>
                                                </div>
                                                <div class="mb-2 row">
                                                    <label for="inputProject"
                                                        class="col-sm-3 col-form-label">Status</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control editInput selectOptions"
                                                            id="statusdepreciation_typesModal" name="status">
                                                            <option value="1" >Active</option>
                                                            <option value="0">Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> <!-- End row -->
            </div>
            <div class="modal-footer customer_Form_Popup">

                <button type="button" class="profileDrop" id="savedepreciation_typesModal" onclick="savedepreciation_typesModal()">Save</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- end here -->

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

<script>
    $("#deleteSelectedRows").on('click', function() {
        let ids = [];

        $('.delete_checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        if (ids.length == 0) {
            alert("Please check the checkbox for delete");
        } else {
            if (confirm("Are you sure to delete?")) {
                // console.log(ids);
                var token = '<?php echo csrf_token(); ?>'
                var model = 'DepreciationType';
                $.ajax({
                    type: "POST",
                    url: "{{url('/bulk_delete')}}",
                    data: {
                        ids: ids,
                        model: model,
                        _token: token
                    },
                    success: function(data) {
                        console.log(data);
                        if (data) {
                            location.reload();
                        } else {
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
            $('#selectAllCheckBoxes').prop('checked', true);
        } else {
            $('#selectAllCheckBoxes').prop('checked', false);
        }
    });
    $('#selectAllCheckBoxes').on('click', function () {
        $('.delete_checkbox').prop('checked', $(this).prop('checked'));
  });
  function status_change(id, status) {
      var token = '<?php echo csrf_token(); ?>'
      var model = "DepreciationType";
      $.ajax({
          type: "POST",
          url: "{{url('/status_change')}}",
          data: {
              id: id,
              status: status,
              model: model,
              _token: token
          },
          success: function(data) {
              console.log(data);
              if ($.trim(data) == 1) {
                  $("#status_meesage").text("Status Changed Successfully Done");
                  $("#msg").show();
                  setTimeout(function() {
                      location.reload();
                  }, 3000);


              }

          }
      });
  }
</script>
<script>
    var assetDepreciationTypeSaveUrl="{{url('sales-finance/assets/depreciation-type-save')}}";
</script>
<script src="{{ url('public/js/salesFinance/asset/asset_category.js')}}" defer></script>
@include('frontEnd.salesAndFinance.jobs.layout.footer')