@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Leave Tracker')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<style>
    table.tablechange tbody td a.table_iconSize i {
        font-size: 18px !important;
        padding: 0 20px;
    }

    .viewDetails i {
        color: #1f88b5;
    }

    /* .editdetails i{
     color:rgb(32, 133, 2);
    } */
    .trashdetails i {
        color: rgb(181, 41, 31);
    }

    .formDtail .text-danger {
        position: absolute;
    }

    .maimTable.productDetailTable table thead tr th,
    .maimTable.productDetailTable table tbody tr td {
        white-space: nowrap;
    }


    .leaveTrackercont {
      display: inline-block;
      border: 1px solid #eee;
      padding: 5px;
          height: 430px;
    overflow: auto;
    }

    .leaveTrackercont .boxes {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: space-between;
    }

    .leaveTrackercont .boxes .box {
      border: 1px solid #eee;
      width: 274px;
      /* height: 200px; */
      padding: 12px;
    }
    .showleaveDetails{
        list-style: none;
        padding: 0;
    }
    .showleaveDetails li{
       margin-bottom: 10px;
    }
</style>

<!--main content start-->
<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        <h4>Leave Tracker</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="jobsection justify-content-end">
                                        <a href="#addLeaveTracker" class="profileDrop openTimeSheetModel" data-toggle="modal"> <i class="fa fa-plus"></i> Add</a>
                                    </div>
                                </div>
                            </div>

                            <div class="maimTable productDetailTable mb-4 table-responsive">
                                <table id="myTable" class="table border-top border-bottom" cellspacing="0" width="100%">
                                    <thead>
                                        <tr class="white_space_nowrap">
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Department</th>
                                            <th>Provision</th>
                                            <th>Start Date</th>
                                            <th>A/L Entitlement </th>
                                            <th>October</th>
                                            <th>November</th>
                                            <th>December </th>
                                            <th>January</th>
                                            <th>February</th>
                                            <th>March</th>
                                            <th>April</th>
                                            <th>May</th>
                                            <th>June</th>
                                            <th>July </th>
                                            <th>August</th>
                                            <th>September</th>
                                            <th>Total A/L Hours Used</th>
                                            <th>Total A/L Hours left</th>
                                            <th>Carried over</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <tr>
                                            <td><strong>1.</strong></td>
                                            <td>Aaron Hill </td>
                                            <td>Residential Care</td>
                                            <td>Mercury</td>
                                            <td>4/3/2023</td>
                                            <td>224</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>0 </td>
                                            <td>224 </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td><strong>2.</strong></td>
                                            <td>Abbey Fairless </td>
                                            <td>Residential Care</td>
                                            <td>Mercury</td>
                                            <td>3/13/2024</td>
                                            <td>280</td>
                                            <td><a href="#viewMonthDate" data-toggle="modal">64</a> </td>
                                            <td></td>
                                            <td><a href="#!">64</a></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>128</td>
                                            <td>152</td>
                                            <td>56</td>
                                        </tr>
                                        <tr>
                                            <td><strong>3.</strong></td>
                                            <td>Abbie Arkwright</td>
                                            <td>Leaving Care</td>
                                            <td>Sygnus</td>
                                            <td>10/28/2014</td>
                                            <td>202</td>
                                            <td></td>
                                            <td></td>
                                            <td><a href="#!">16</a></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>  <a href="#!">16</a></td>
                                            <td>186  </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td><strong>4.</strong></td>
                                            <td>Abby Comber</td>
                                            <td>Residential Care</td>
                                            <td>Sophia</td>
                                            <td>10/1/2024</td>
                                            <td>224</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>0 </td>
                                            <td>224 </td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>





<!--  Add Leave Tracker Modal start here -->
<div class="modal fade" id="addLeaveTracker" tabindex="-1" aria-labelledby="addLeaveTrackerLabel" aria-hidden="true">
    <div class="modal-dialog  ">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="modalTitle">Add Leave Tracker</h4>
            </div>
            <form class="" id="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="mb-3">
                                <label>Name </label>
                                <div id="" style="display: block;">
                                    <select class="form-control editInput selectOptions" id="">
                                        <option value="">Select Name</option>
                                        <option value=""> Name1</option>
                                        <option value=""> Name2</option>
                                        <option value=""> Name3</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Department</label>
                                <div id="" style="display: block;">
                                    <select class="form-control editInput selectOptions" id="">
                                        <option value="">Select Department</option>
                                        <option value=""> Department1</option>
                                        <option value=""> Department2</option>
                                        <option value=""> Department3</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Provision <span class="radStar">*</span></label>
                                <input type="text" class="form-control editInput" name="product_name" id="" required="">
                            </div>

                            <div class="mb-3">
                                <label>Start Date </label>
                                <input type="date" class="form-control editInput" id="" placeholder="Start Date" name="Start Date">
                            </div>
                            <div class="mb-3">
                                <label>A/L Entitlement </label>
                                <input type="text" class="form-control editInput" id="" name="Al_Entitlement " value="">
                            </div>


                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="mb-3">
                                <label>Month</label>
                                <div id="" style="display: block;">
                                    <select class="form-control editInput selectOptions" id="">
                                        <option value="">Select Month</option>
                                        <option value=""> Month1</option>
                                        <option value=""> Month1</option>
                                        <option value=""> Month1</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Total A/L Hours Used <span class="radStar ">*</span></label>
                                <input type="text" class="form-control editInput" id="" name="" value="" required="">
                            </div>
                            <div class="mb-3">
                                <label>Total A/L Hours left</label>
                                <input type="text" class="form-control editInput" id="" name="" value="" required="">
                            </div>

                            <div class="mb-3">
                                <label>Total A/L Hours left</label>
                                <input type="text" class="form-control editInput" id="" name="" value="" required="">
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <button type="button" class="btn btn-default">Cancel</button>
                    <button type="submit" class="btn btn-warning">Save</button>
                </div>
            </form>
         
        </div>
    </div>
</div>


<!--  Add Leave Tracker Modal start here -->
<div class="modal fade" id="viewMonthDate" tabindex="-1" aria-labelledby="viewMonthDateLabel" aria-hidden="true">
    <div class="modal-dialog  ">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="modalTitle">Date</h4>
            </div>

            <div class="panel-body">
                <div class="leaveTrackercont">
                    <div class="boxes">
                        <div class="box">
                            <ul class="showleaveDetails">
                                <li>
                                    <span><strong>Date :</strong></span>
                                    <span>17/06/2025</span>
                                </li>
                                <li>
                                    <span><strong>Sleep/Hours :</strong></span>
                                    <span>25/hr</span>
                                </li>
                                <li>
                                    <span><strong>Month :</strong></span>
                                    <span>January</span>
                                </li>
                                <li>
                                    <span><strong>Description :</strong></span>
                                    <span>Amerjeet Jatav xyz</span>
                                </li>
                            </ul>
                        </div>
                        <div class="box">
                            <ul class="showleaveDetails">
                                <li>
                                    <span><strong>Date :</strong></span>
                                    <span>17/06/2025</span>
                                </li>
                                <li>
                                    <span><strong>Sleep/Hours :</strong></span>
                                    <span>25/hr</span>
                                </li>
                                <li>
                                    <span><strong>Month :</strong></span>
                                    <span>January</span>
                                </li>
                                <li>
                                    <span><strong>Description :</strong></span>
                                    <span>Amerjeet Jatav xyz</span>
                                </li>
                            </ul>
                        </div>
                        <div class="box">
                            <ul class="showleaveDetails">
                                <li>
                                    <span><strong>Date :</strong></span>
                                    <span>17/06/2025</span>
                                </li>
                                <li>
                                    <span><strong>Sleep/Hours :</strong></span>
                                    <span>25/hr</span>
                                </li>
                                <li>
                                    <span><strong>Month :</strong></span>
                                    <span>January</span>
                                </li>
                                <li>
                                    <span><strong>Description :</strong></span>
                                    <span>Amerjeet Jatav xyz</span>
                                </li>
                            </ul>
                        </div>
                        <div class="box">
                            <ul class="showleaveDetails">
                                <li>
                                    <span><strong>Date :</strong></span>
                                    <span>17/06/2025</span>
                                </li>
                                <li>
                                    <span><strong>Sleep/Hours :</strong></span>
                                    <span>25/hr</span>
                                </li>
                                <li>
                                    <span><strong>Month :</strong></span>
                                    <span>January</span>
                                </li>
                                <li>
                                    <span><strong>Description :</strong></span>
                                    <span>Amerjeet Jatav xyz</span>
                                </li>
                            </ul>
                        </div>
                          <div class="box">
                            <ul class="showleaveDetails">
                                <li>
                                    <span><strong>Date :</strong></span>
                                    <span>17/06/2025</span>
                                </li>
                                <li>
                                    <span><strong>Sleep/Hours :</strong></span>
                                    <span>25/hr</span>
                                </li>
                                <li>
                                    <span><strong>Month :</strong></span>
                                    <span>January</span>
                                </li>
                                <li>
                                    <span><strong>Description :</strong></span>
                                    <span>Amerjeet Jatav xyz</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>         
        </div>
    </div>
</div>

<script src="{{ url('public/js/salesFinance/council_tax.js') }}"></script>

<script>
    deleteURL = "{{ url('finance/delete-council-tax') }}/";
    saveData = "{{ url('finance/save-council-tax') }}";
    editData = "{{ url('finance/edit-council-tax') }}";
    $(document).ready(function() {
        $('#myTable2').DataTable();
    });



    document.getElementById('openModal').addEventListener('click', function() {
        document.getElementById('addLeaveTracker').style.display = 'block';
        document.getElementById('viewMonthDate').style.display = 'block';
    });


</script>
<!-- Add Staff Modal end here -->
@endsection
