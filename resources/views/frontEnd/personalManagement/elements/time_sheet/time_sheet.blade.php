@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Leave Tracker')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<style>
    .omegaCareAnnual thead tr th, .omegaCareAnnual tbody tr td{
     white-space: nowrap;
    }
    table.tablechange tbody td a.table_iconSize i{
      font-size: 18px !important;
      padding: 0 20px;
    }
    .viewDetails i{
     color: #1f88b5;
    }
    /* .editdetails i{
     color:rgb(32, 133, 2);
    } */
    .trashdetails i{
        color:rgb(181, 41, 31);
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
                            <div class="maimtable productDetailTable mb-4 table-responsive">
                                <table id="exampleOne" class="table border-top border-bottom tablechange omegaCareAnnual" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th>Staf Name</th>
                                            <th>Home</th>
                                            <th>July 2025</th>
                                            <th>View Details</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                        <tr>
                                            <td><strong>1.</strong></td>
                                            <td>Johan bely </td>
                                            <td>Xyz Care</td>
                                            <td>25, july</td>
                                            <td>
                                                <a href="#!" class="table_iconSize viewDetails openModalBtn" data-action='add'><i class="fa fa-eye"></i></a>
                                            </td>
                                            <td>
                                                <a href="#!" class="table_iconSize editdetails"><i class="fa fa-edit (alias)"></i></a>
                                                <a href="#!" class="table_iconSize trashdetails"><i class="fa fa-trash-o"></i></a>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td><strong>2.</strong></td>
                                            <td>Johan bely </td>
                                            <td>Xyz Care</td>
                                            <td>25, july</td>
                                            <td>
                                                <a href="#!" class="table_iconSize viewDetails openModalBtn" data-action='add'><i class="fa fa-eye"></i></a>
                                            </td>
                                            <td>
                                                <a href="#!" class="table_iconSize editdetails"><i class="fa fa-edit (alias)"></i></a>
                                                <a href="#!" class="table_iconSize trashdetails"><i class="fa fa-trash-o"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>3.</strong></td>
                                            <td>Johan bely </td>
                                            <td>Xyz Care</td>
                                            <td>25, july</td>
                                            <td>
                                                <a href="#!" class="table_iconSize viewDetails openModalBtn" data-action='add'><i class="fa fa-eye"></i></a>
                                            </td>
                                            <td>
                                                <a href="#!" class="table_iconSize editdetails"><i class="fa fa-edit (alias)"></i></a>
                                                <a href="#!" class="table_iconSize trashdetails"><i class="fa fa-trash-o"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>4.</strong></td>
                                            <td>Johan bely </td>
                                            <td>Xyz Care</td>
                                            <td>25, july</td>
                                            <td>
                                                <a href="#!" class="table_iconSize viewDetails openModalBtn" data-action='add'><i class="fa fa-eye"></i></a>
                                            </td>
                                            <td>
                                                <a href="#!" class="table_iconSize editdetails"><i class="fa fa-edit (alias)"></i></a>
                                                <a href="#!" class="table_iconSize trashdetails"><i class="fa fa-trash-o"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>5.</strong></td>
                                            <td>Johan bely </td>
                                            <td>Xyz Care</td>
                                            <td>25, july</td>
                                            <td>
                                                <a href="#!" class="table_iconSize viewDetails openModalBtn" data-action='add'><i class="fa fa-eye"></i></a>
                                            </td>
                                            <td>
                                                <a href="#!" class="table_iconSize editdetails"><i class="fa fa-edit (alias)"></i></a>
                                                <a href="#!" class="table_iconSize trashdetails"><i class="fa fa-trash-o"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>6.</strong></td>
                                            <td>Johan bely </td>
                                            <td>Xyz Care</td>
                                            <td>25, july</td>
                                            <td>
                                                <a href="#!" class="table_iconSize viewDetails  openModalBtn" data-action='add'><i class="fa fa-eye"></i></a>
                                            </td>
                                            <td>
                                                <a href="#!" class="table_iconSize editdetails"><i class="fa fa-edit (alias)"></i></a>
                                                <a href="#!" class="table_iconSize trashdetails"><i class="fa fa-trash-o"></i></a>
                                            </td>
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

<!-- Modal -->
<div class="modal fade" id="AddCouncilTax" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content ">
            <div id="error-text"></div>
            <form action="" id="addCouncilTaxForm" class="customerForm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                    <h4 class="modal-title" id="modalTitle">Leave Tracker</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="maimtable productDetailTable mb-4 table-responsive">
                                <table id="myTable2" class="table border-top border-bottom tablechange omegaCareAnnual" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th>Date</th>
                                            <th>Hours</th>
                                            <th>Sleep</th>
                                            <th>Disturbance</th>
                                            <th>Wake Night</th>
                                            <th>Annual Leave</th>
                                            <th>On Call</th>
                                            <th>Comments</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>1.</strong></td>
                                            <td>25th </td>
                                            <td>14</td>
                                            <td>2</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <td><strong>2.</strong></td>
                                            <td>25th </td>
                                            <td>14</td>
                                            <td>2</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <td><strong>3.</strong></td>
                                            <td>25th </td>
                                            <td>14</td>
                                            <td>2</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                        </tr> 
                                         <tr>
                                            <td><strong>3.</strong></td>
                                            <td>25th </td>
                                            <td>14</td>
                                            <td>2</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                        </tr> 
                                        <tr>
                                            <td><strong>3.</strong></td>
                                            <td>25th </td>
                                            <td>14</td>
                                            <td>2</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                        </tr> 
                                        <tr>
                                            <td><strong>3.</strong></td>
                                            <td>25th </td>
                                            <td>14</td>
                                            <td>2</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                        </tr> 
                                        <tr>
                                            <td><strong>3.</strong></td>
                                            <td>25th </td>
                                            <td>14</td>
                                            <td>2</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                        </tr> 
                                        <tr>
                                            <td><strong>3.</strong></td>
                                            <td>25th </td>
                                            <td>14</td>
                                            <td>2</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                        </tr> 
                                        <tr>
                                            <td><strong>3.</strong></td>
                                            <td>25th </td>
                                            <td>14</td>
                                            <td>2</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                        </tr> 
                                        <tr>
                                            <td><strong>3.</strong></td>
                                            <td>25th </td>
                                            <td>14</td>
                                            <td>2</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                        </tr> 
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th>113.75</th>
                                            <th>8</th>
                                            <th>0</th>
                                            <th>0</th>
                                            <th>16</th>
                                            <th>£0</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
            </form>
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
</script>
@endsection


