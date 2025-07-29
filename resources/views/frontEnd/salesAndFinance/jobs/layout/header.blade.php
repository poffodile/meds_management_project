<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ PROJECT_NAME }} @yield('title','') </title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('public/images/favicon.ico') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,300,0,0" />
    <link rel="stylesheet" href="https://www.ville-pont-eveque.fr/tools/library/DataTables/media/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://www.ville-pont-eveque.fr/tools/library/DataTables/extensions/Select/css/select.dataTables.css" />
    <!-- <link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" /> -->
    <link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/dashboard.css')}}" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script type="importmap">
        {
        "imports": {
            "my-module": "/path/to/my-module.js"
        }
    }
    </script>
    
    <style>
        table.dataTable td.select-checkbox:before {
            display: none;
        }

        .modal.show .modal-dialog {
            box-shadow: 0px 0px 10px #34343447;
        }

        .numberHifan {
            text-align: center;
            font-size: 20px;
        }

        .table_responsive div#exampleOne_wrapper {
            overflow: auto;
        }

        .calendar_icon {
            color: #e10078;
            display: flex;
            align-items: center;
        }

        .ActiveBtn {
            background-color: #1f88b5;
            color: #fff;
        }

        .ActiveBtn span i {
            color: #fff !important;
        }
    </style>
</head>
<?php
$rights = App\User::where('id', Auth::user()->id)->where('is_deleted', 0)->first()->access_rights;
$access_rights = explode(',', $rights);
$current_url = last(request()->segments());
?>

<body>
    <header>
        <div class="topbaar">
            <div class="container-fluid bg-light p-0">
                <div class="row gx-0 d-none d-lg-flex">
                    <div class="col-lg-3 px-3 text-start">
                        <div class="h-100 d-inline-flex align-items-center py-1 ms-3">
                            <a href="{{url('sales-finance/dashboard')}}" class="brand_logo"><img src="{{ url('public/images/ewm_logo.png')}}" alt="ewm_logo"></a>
                        </div>
                    </div>
                    <!-- <div class="col-lg-9 px-3 text-end toprigihticon">
                        <?php //if (isset($page) && $page == 'job_index') { 
                        ?>
                        <div class="d-inline-flex align-items-center me-5 topbaarBtn">
                            <a href="#!" class="profileDrop"> <i class="material-symbols-outlined"> storage</i> My Diary</a>
                            <a href="#!" class="profileDrop" data-bs-toggle="modal" data-bs-target="#CRMHeaderPopup"> <i class="material-symbols-outlined"> dashboard</i> CRM</a>
                            <a href="#!" class="profileDrop"> <i class="material-symbols-outlined"> contact_support </i> Help Desk <span class="notifiNumberRadColor">2</span></a>
                            <a href="#!" class="profileDrop"> <i class="material-symbols-outlined"> mail </i> Messages </a>
                            <a href="#!" class="profileDrop"> <i class="material-symbols-outlined"> notifications_active </i> Notifications <span class="notifiNumberRadColor">23</span> </a>
                            <a href="#!" class="profileDrop"> <i class="material-symbols-outlined"> handshake </i> Partners </a>
                            <a href="{{url('/')}}" class="profileDrop"> <i class="fa fa-home"> </i> Home </a>
                        </div>
                        <?php //} else {
                        ?>
                        <div class="h-100 d-inline-flex align-items-center me-5">
                                <a class="btn btn-sm-square bg-white text-primary me-1" href="#!"><i class="material-symbols-outlined"> group</i></a>
                                <a class="btn btn-sm-square bg-white text-primary me-1" href="#!"><i class="material-symbols-outlined"> dns</i></a>
                                <a class="btn btn-sm-square bg-white text-primary me-1" href="#!"><i class="material-symbols-outlined"> mail </i></a>
                                <a class="btn btn-sm-square bg-white text-primary me-0" href="#!"><i class="material-symbols-outlined"> notifications </i></a>
                            </div>
                        <?php //} 
                        ?>
                    </div> -->
                </div>
            </div>
        </div>

        <!-- <div class="menubaar">
            <div class="container-fluid">
                <nav class="navbar navbar-expand-lg navbar-light sticky-top px-3">
                    <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarCollapse">
                        <div class="navbar-nav ml-auto p-4 p-lg-0">
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-item nav-link dropdown-toggle @if(isset($page)) @if($page == 'leads') active @endif @endif" data-bs-toggle="dropdown">
                                    <span><i class="material-symbols-outlined"> keep_public </i></span>
                                    Lead
                                </a>
                                <div class="dropdown-menu fade-up m-0">
                                    <a href="{{ url('/leads/add') }}" class="dropdown-item"><span><i class="fa fa-plus"></i> New Lead </span></a>
                                    <a href="{{ url('/leads/leads') }}" class="dropdown-item"><span><i class="fa fa-list"></i> All Lead </span></a>
                                    <a href="{{ url('/lead/myLeads') }}" class="dropdown-item"><span><i class="fa fa-list"></i> My Leads </span></a>
                                    <a href="{{ url('/leads/unassigned') }}" class="dropdown-item"><span><i class="fa fa-list"></i> Unassigned Lead </span></a>
                                    <a href="{{ url('/lead/actioned') }}" class="dropdown-item"><span><i class="fa fa-list"></i> Actioned Lead </span></a>
                                    <a href="{{ url('/lead/rejected') }}" class="dropdown-item"><span><i class="fa fa-list"></i> Rejected Lead </span></a>
                                    <a href="{{ url('/lead/authorization') }}" class="dropdown-item"><span><i class="fa fa-list"></i> Authorization </span></a>
                                    <a href="{{ url('/leads/converted') }}" class="dropdown-item"><span><i class="fa fa-list"></i> Converted Lead </span></a>
                                    <a href="{{ url('leads/search') }}" class="dropdown-item"><span><i class="fa fa-search"></i> Search Lead </span></a>
                                    <a href="{{ url('/leads/tasks') }}" class="dropdown-item"><span><i class="fa fa-list-ol"></i> Lead Task </span></a>
                                </div>
                            </div>


                            <div class="nav-item1 dropdown1">
                                <a class="nav-link dropdown-toggle @if(isset($page)) @if($page == 'quotes') active @endif @endif" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span><i class="material-symbols-outlined"> description </i></span> Quotes
                                </a>
                                <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                    <li class="nav-item1"><a href="{{ url('/quote/dashboard') }}" class="dropdown-item"><span><i class="fa fa-dashboard"></i> Dashboard</span></a></li>
                                    <li class="nav-item1"><a href="{{ url('/quote/add') }}" class="dropdown-item"><span><i class="fa fa-plus"></i> New Quote</span></a></li>
                                    <li class="nav-item1"><a href="{{ url('/quote/draft') }}" class="dropdown-item"><span><i class="fa fa-list"></i> Draft Quote</span></a></li>
                                    <li class="nav-item1"><a href="{{ url('/quote/actioned') }}" class="dropdown-item"><span><i class="fa fa-file-text"></i> Actioned Quote</span></a></li>
                                    <li class="nav-item1"><a href="{{ url('/quote/callBack') }}" class="dropdown-item"><span><i class="fa fa-file-text"></i> Call Back Quote</span></a></li>
                                    <li class="nav-item1"><a href="{{ url('/quote/accepted') }}" class="dropdown-item"><span><i class="fa fa-file-text"></i> Accepted Quote</span></a></li>
                                    <li class="nav-item1"><a href="#!" class="dropdown-item"><span><i class="fa fa-file-text"></i> Converted Quote</span></a></li>
                                    <li class="nav-item1"><a href="#!" class="dropdown-item"><span><i class="fa fa-file-text"></i> Reminders</span></a></li>
                                    <li class="nav-item1"><a href="{{ url('quote/search') }}" class="dropdown-item"><span><i class="fa fa-search"></i> Search Quote</span></a></li>

                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span><i class="fa fa-calendar"></i> Appointments</span> <i class="fa-solid fa-angle-right"></i>
                                        </a>

                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i> Sales Appointments</span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span><i class="fa fa-repeat"></i> Recurring Quote</span> <i class="fa-solid fa-angle-right"></i>
                                        </a>

                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i> New Recurring Quote </span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i> Recurring Quote </span></a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>

                            <div class="nav-item dropdown">
                                <a href="#!" class="nav-item nav-link dropdown-toggle @if(isset($page)) @if($page == 'jobs') active @endif @endif" data-bs-toggle="dropdown">
                                    <span><i class="material-symbols-outlined">work </i></span>
                                    Jobs
                                </a>
                                <div class="dropdown-menu fade-up m-0">
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-dashboard"></i>Dashboard</span></a>
                                    <a href="{{url('/jobs_create')}}" class="dropdown-item"><span><i class="fa fa-plus"></i>New Jobs</span></a>
                                    <a href="{{url('jobs_list')}}" class="dropdown-item"><span><i class="fa fa-road"></i>Active Jobs</span></a>

                                    <?php
                                    if (array_key_exists(314, $access_rights)) { ?>
                                        <a href="{{url('job_type')}}" class="dropdown-item"><span><i class="fa fa-list"></i>Job Type</span></a>
                                    <?php } ?>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-list"></i>Unassined Jobs</span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-exclamation-triangle"></i>Action Required Jobs</span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-clock"></i>Overdue Jobs</span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-thumbs-up"></i>Authorization Jobs</span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-pause"></i>On Hold</span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-search"></i>Search Jobs</span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-calendar"></i>Appointments</span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-repeat"></i>Recurring Jobs</span></a>
                                </div>
                            </div>

                            
                            <div class="nav-item dropdown">
                                <a href="#!" class="nav-item nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                    <span><i class="material-symbols-outlined"> planner_review </i></span>
                                    Planner
                                </a>
                                <div class="dropdown-menu fade-up m-0">
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-clock"></i> Time Planner</span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-briefcase"></i> Project Planner</span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-globe"></i> Geo Planner</span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-truck"></i> Vehicle Tracking</span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-mobile"></i> Mobile Tracking</span></a>
                                </div>
                            </div>
                            <div class="nav-item dropdown">
                                <a href="#!" class="nav-item nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                    <span><i class="material-symbols-outlined"> business_center</i></span>
                                    Projects
                                </a>
                                <div class="dropdown-menu fade-up m-0">
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-plus"></i> New Project</span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-briefcase"></i> Active Projects</span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-briefcase"></i> Inactive Projects</span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-briefcase"></i> Completed Projects</span></a>
                                </div>
                            </div>

                            <div class="nav-item1 dropdown1">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><span><i class="material-symbols-outlined">finance_mode </i></span> Finance</a>
                                <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span><i class="fa fa-list"></i> Invoices </span> <span><i class="fa-solid fa-angle-right"></i></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-columns"></i>Dashboard</span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-plus"></i>New Invoice</span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-file"></i>Draft Invoices</span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa-solid fa-clock"></i>Outstanding Invoices</span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa-solid fa-stopwatch"></i>Overdue Invoices</span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-thumbs-up"></i>Paid Invoices</span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-search"></i>Search Invoices</span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i>Account Statements</span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa-solid fa-stopwatch"></i>Reminders</span></a></li>
                                            <li class="nav-item1 dropend">
                                                <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span><i class="fa fa-rotate-right"></i>Recurring Invoices</span> <span><i class="fa-solid fa-angle-right"></i></span>
                                                </a>
                                                <ul class="dropdown-menu1">
                                                    <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i> New Recurring Invoice</span></a></li>
                                                    <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i> Recurring Invoices</span></a></li>
                                                </ul>
                                            </li>
                                            <li class="nav-item1 dropend">
                                                <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span><i class="fa fa-list"></i>Credit Notes</span> <span><i class="fa-solid fa-angle-right"></i></span>
                                                </a>
                                                <ul class="dropdown-menu1">
                                                    <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i>New Credit Note</span></a></li>
                                                    <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i>Draft Credit Notes</span></a></li>
                                                    <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i>Awaiting Approval Credit Notes</span></a></li>
                                                    <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i>Approval Credit Notes</span></a></li>
                                                    <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i>Paid Credit Notes</span></a></li>
                                                    <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i>Cancelled Credit Notes</span></a></li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span><i class="fa fa-file"></i>Purchase Orders</span> <span><i class="fa-solid fa-angle-right"></i></span>
                                        </a>

                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-columns"></i>Dashboard</span></a></li>
                                            <li><a class="dropdown-item" href="{{url('purchase_order')}}"><span><i class="fa fa-plus"></i>New Purchase Order</span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('draft_purchase_order') }}"><span><i class="fa fa-list"></i>Draft Purchase Orders</span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('draft_purchase_order?list_mode=AwaitingApprivalPurchaseOrders') }}"><span><i class="fa-solid fa-hourglass"></i>Awaiting Approval Purchase Orders</span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('draft_purchase_order?list_mode=Approved') }}"><span><i class="fa fa-check"></i>Approved Purchase Orders </span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('draft_purchase_order?list_mode=Rejected') }}"><span><i class="fa fa-check"></i>Rejected Purchase Orders </span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('draft_purchase_order?list_mode=Actioned') }}"><span><i class="fa fa-check"></i>Actioned Purchase Orders</span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('draft_purchase_order?list_mode=Paid') }}"><span><i class="fa fa-thumbs-up"></i>Paid Purchase Orders</span></a></li>
                                            <li><a class="dropdown-item" href="{{url('purchase-orders-search')}}"><span><i class="fa fa-search"></i>Search Purchase Orders</span></a></li>
                                            <li><a class="dropdown-item" href="{{url('purchase-order-invoices')}}"><span><i class="fa fa-file"></i>Invoices Received</span></a></li>
                                            <li><a class="dropdown-item" href="{{url('purchase-order-statements')}}"><span><i class="fa fa-file"></i>Purchase Orders Statements</span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-rotate-right"></i>Recurring Purchase Orders</span></a></li>
                                            <li class="dropend"><a class="dropdown-item" href="credit_notes.html" data-bs-toggle="dropdown" aria-expanded="false"><span><i class="fa fa-list"></i>Credit Notes</span> <i class="fa-solid fa-angle-right"></i></a>
                                                <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                                    <li><a class="dropdown-item" href="{{url('new_credit_notes')}}"><span><i class="fa fa-plus"></i> New Credit Note</span></a></li>
                                                    <li><a class="dropdown-item" href="{{url('credit_notes?list_mode=Approved')}}"><span><i class="fa fa-check"></i>Approved Credit Notes</span></a></li>
                                                    <li><a class="dropdown-item" href="{{url('credit_notes?list_mode=Paid')}}"><span><i class="fa fa-thumbs-up"></i>Paid Credit Notes </span></a></li>
                                                    <li><a class="dropdown-item" href="{{url('credit_notes?list_mode=Cancelled')}}"><span><i class="fa fa-times"></i>Cancelled Credit Notes</span></a></li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                            <div class="nav-item1 dropdown1">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span><i class="material-symbols-outlined"> contact_support </i></span> Contacts
                                </a>
                                <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span><i class="fa fa-users"></i>Customers</span> <span><i class="fa-solid fa-angle-right"></i></span>
                                        </a>

                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <li><a class="dropdown-item" href="{{url('customer_add_edit')}}"><span><i class="fa fa-plus"></i>New Customers</span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('customers?list_mode=ACTIVE') }}"><span><i class="fa fa-check-circle"></i>Active Customers</span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('customers?list_mode=INACTIVE') }}"><span><i class="fa fa-times-circle"></i>Inactive Customers</span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i>Customers Logins</span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> <span><i class="fa fa-users"></i> Suppliers</span> <span><i class="fa-solid fa-angle-right"></i></span></a>
                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <li><a class="dropdown-item" href="{{url('supplier_add')}}"><span><i class="fa fa-plus"></i>New Supllier</span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('supplier?list_mode=ACTIVE') }}"><span><i class="fa fa-check-circle"></i>Active Suppliers</span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('supplier?list_mode=INACTIVE') }}"><span><i class="fa fa-times-circle"></i>Inactive Suppliers</span></a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                            <div class="nav-item dropdown">
                                <a href="#!" class="nav-item nav-link dropdown-toggle @if(isset($page)) @if($page == 'item') active @endif @endif" data-bs-toggle="dropdown">
                                    <span><i class="material-symbols-outlined"> production_quantity_limits</i></span>Items
                                </a>
                                <div class="dropdown-menu fade-up m-0">
                                    <a href="{{ url('/item/product_categories') }}" class="dropdown-item">Product Categories</a>
                                    <a href="{{ url('/item/products') }}" class="dropdown-item">Products</a>
                                    <a href="{{ url('/item/catalogues') }}" class="dropdown-item">Catalogues</a>
                                    <a href="{{ url('/item/item-groups') }}" class="dropdown-item">Product Groups</a>
                                </div>
                            </div>
                            <a href="{{url('/expenses')}}" class="nav-item nav-link"><span><i class="material-symbols-outlined"> calculate </i></span>Expenses</a>
                            <div class="nav-item dropdown">
                                <a href="#!" class="nav-item nav-link dropdown-toggle" data-bs-toggle="dropdown"><span><i class="material-symbols-outlined"> diversity_2 </i></span>Users</a>
                                <div class="dropdown-menu fade-up m-0">
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-user"></i>Users </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-users"></i>Team Members </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-clock"></i>Timeoff </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-list"></i>Lone Worker Active List </span></a>
                                </div>
                            </div>
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-item nav-link dropdown-toggle" data-bs-toggle="dropdown"> <span><i class="material-symbols-outlined"> partner_reports </i></span>Reports</a>
                                <div class="dropdown-menu fade-up m-0">
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-users"></i> Customers </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-file-text"></i> Quote </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-road"></i> Jobs </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-file"></i> Invoices </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-users"></i> Users </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-list"></i> Purchase Orders </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-shopping-cart"></i> Products </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-truck"></i> Vehicle Tracking </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-comment"></i> SMS Report </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-envelope"></i> Email Report </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-bell"></i> Reminder Report </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-list"></i> Task Report </span></a>
                                    <a href="#!" class="dropdown-item"><span><i class="fa fa-list"></i> Report Builder </span></a>
                                </div>
                            </div>
                            <div class="nav-item dropdown">
                                <a href="#!" class="nav-item nav-link dropdown-toggle" data-bs-toggle="dropdown"> <span><i class="material-symbols-outlined"> bookmark_manager</i></span>File Manager</a>
                                <div class="dropdown-menu fade-up m-0">
                                    <a href="#!" class="dropdown-item"> <span><i class="fa fa-paperclip"></i> Attachments </span></a>
                                    <a href="#!" class="dropdown-item"> <span><i class="fa fa-print"></i> Digital Documents</span></a>
                                    <a href="#!" class="dropdown-item"> <span><i class="fa fa-list"></i> Completed Questionnaires</span> </a>
                                </div>
                            </div>
                            <div class="nav-item1 dropdown1">
                                <a class="nav-link dropdown-toggle @if(isset($page)) @if($page == 'setting') active @endif @endif" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span><i class="material-symbols-outlined"> construction</i></span> Settings
                                </a>
                                <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                    <li class="nav-item1"><a class="dropdown-item" href="#" role="button"><span><i class="fa fa-gear"></i> General Settings</span></a></li>
                                    <li class="nav-item1"><a class="dropdown-item" href="#" role="button"><span><i class="fa fa-file-text"></i> Digital Doc. Manager</span></a></li>
                                    <li class="nav-item1"><a class="dropdown-item" href="#" role="button"><span><i class="fa fa-list"></i> Questionnaires</span></a></li>
                                    <li class="nav-item1"><a class="dropdown-item" href="#" role="button"><span><i class="fa fa-edit"></i> Template Editor</span></a></li>
                                    <li class="nav-item1"><a class="dropdown-item" href="#" role="button"><span><i class="fa fa-table"></i> Custom Field Management</span></a></li>
                                    <li class="nav-item1"><a class="dropdown-item" href="#" role="button"><span><i class="fa fa-terminal"></i> Triggers</span></a></li>
                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><span><i class="fa fa-bullhorn"></i>
                                                Leads</span> <span><i class="fa-solid fa-angle-right"></i></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-list-ul"></i>Lead Settings</span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('/lead/lead_sources') }}"> <span><i class="fa fa-list-ul"></i> Lead Sources</span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('/lead/lead_status') }}"><span><i class="fa fa-list-ul"></i>Lead Status</span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('/lead/lead_task_type') }}"><span><i class="fa fa-list-ul"></i>Lead Task Types</span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('/lead/lead_reject_types') }}"><span><i class="fa fa-list-ul"></i>Lead Reject Types</span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('/lead/lead_notes_type') }}"><span><i class="fa fa-list-ul"></i>Lead Notes Type</span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><span><i class="fa fa-file-text"></i>
                                                Quotes</span> <span><i class="fa-solid fa-angle-right"></i></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <li><a class="dropdown-item" href="{{ url('/quote/quote_type') }}"><span><i class="fa fa-list-ul"></i>Quote Type </span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('/quote/quote_sources') }}"><span><i class="fa fa-list-ul"></i>Quote Source </span></a></li>
                                            <li><a class="dropdown-item" href="{{ url('/quote/quote_reject_types') }}"><span><i class="fa fa-list-ul"></i>Quote Reject Type </span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span><i class="fa fa-road"></i>
                                                Jobs </span>
                                            <span><i class="fa-solid fa-angle-right"></i></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <?php if (array_key_exists(314, $access_rights)) { ?>
                                                <li><a class="dropdown-item" href="{{url('job_type')}}"><span><i class="fa fa-list"></i>Job Type </span></a></li>
                                            <?php } ?>
                                            <li><a class="dropdown-item" href="{{url('job_appointment_type_list')}}"><span><i class="fa fa-list"></i>Job Appointment Type </span></a></li>
                                            <li><a class="dropdown-item" href="{{url('appointment_rejection_cat_list')}}"><span><i class="fa fa-list"></i>Appointment Rejection Categories </span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><span><i class="fa fa-list"></i>
                                                Invoices</span> <span><i class="fa-solid fa-angle-right"></i></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <li><a class="dropdown-item" href="{{url('/account_codes')}}"><span><i class="fa fa-list"></i>Account Codes </span></a></li>
                                            <li><a class="dropdown-item" href="{{url('/tax_rate')}}"><span><i class="fa fa-list"></i>Tax Rate </span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><span><i class="fa fa-file"></i>
                                                Purchase Orders</span> <span><i class="fa-solid fa-angle-right"></i></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <li><a class="dropdown-item" href="{{url('departments')}}"><span><i class="fa fa-list"></i>Departments </span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><span><i class="fa fa-users"></i>
                                                Customers</span> <span><i class="fa-solid fa-angle-right"></i></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <li><a class="dropdown-item" href="{{url('customer_type')}}"><span><i class="fa fa-list"></i>Customer Type </span></a></li>
                                            <li><a class="dropdown-item" href="{{url('job_titles')}}"><span><i class="fa fa-list"></i>Customer Job Title </span></a></li>
                                            <li><a class="dropdown-item" href="{{url('complaint_type')}}"><span><i class="fa fa-list"></i>Complaint Type </span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><span><i class="fa fa-user"></i>
                                                Users</span> <span><i class="fa-solid fa-angle-right"></i></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i>User Type </span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i>User Profiles </span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i>User Working Areas </span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i>Personal Time Type </span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i>User Contractors </span></a></li>
                                            <li><a class="dropdown-item" href="#"><span><i class="fa fa-list"></i>User Location </span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><span><i class="fa fa-comments"></i>
                                                CRM</span> <span><i class="fa-solid fa-angle-right"></i></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <li><a class="dropdown-item" href="{{ url('/lead/CRM_section_types') }}"><span><i class="fa fa-list-ul"></i> CRM Section Types </span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item1 dropend">
                                        <a class="dropdown-item" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><span><i class="fa fa-list"></i>
                                                General</span> <span><i class="fa-solid fa-angle-right"></i></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu1 fade-up m-0">
                                            <li><a class="dropdown-item" href="{{url('/attachments_types')}}"><span><i class="fa fa-paperclip"></i>Attachment Types </span></a></li>
                                            <li><a class="dropdown-item" href="{{url('/Payment_type')}}"><span><i class="nav-icon fas fa-money-bill-alt"></i>Payment Types </span></a></li>
                                            <li><a class="dropdown-item" href="{{url('/regions')}}"><span><i class="fa fa-globe"></i>Regions </span></a></li>
                                            <li><a class="dropdown-item" href="{{url('/task_types')}}"><span><i class="fa fa-list-ul"></i>Task Types </span></a></li>
                                            <li><a class="dropdown-item" href="{{url('/tags')}}"><span><i class="fa fa-tags"></i>Tags </span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item1"><a class="dropdown-item" href="#" role="button"><span><i class="fa fa-check-circle"></i> Quick Setup Wizard</span></a></li>
                                </ul>
                            </div>
                            <div class="nav-item dropdown ">
                                <a href="#!" class="nav-item nav-link dropdown-toggle @if(isset($page) && $page == 'dayBook') active @endif" data-bs-toggle="dropdown"><span><i class="material-symbols-outlined">  book_5 </i></span>Day Book</a>
                                <div class="dropdown-menu fade-up m-0">
                                    <a href="{{ url('/sales/sales-day-book') }}" class="dropdown-item"> <span><i class="fa fa-book"></i> Sales Day Book </span></a>
                                    <a href="{{ url('/purchase/purchase-day-book') }}" class="dropdown-item"> <span><i class="fa fa-book"></i> Purchasee Day book</span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </div> -->

    </header>


    <!-- Modal -->
    <div class="modal CRMFullModel fade" id="CRMHeaderPopup" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content add_Customer">
                <div class="modal-header terques-bg">
                    <h5 class="modal-title pupTitle" id="CRMHeaderPopupLabel">CRM View</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body crmModelCont pt-2">
                    <div class="jobsection pb-2 hideandshow">
                        <button class="profileDrop" id="onclickbtnHideShow">Hide/Show</button>
                    </div>
                    <div id="showDivCont">
                        <div class="newJobForm mb-4 p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="">
                                        <h4 class="contTitle text-center">Contact Details</h4>
                                    </div>
                                    <div class="row">
                                        <label class="col-md-4 col-form-label"> <strong>Full Name:</strong></label>
                                        <div class="col-md-8">
                                            <span id="" class="editInput"> Arjun Kumar</span>
                                        </div>
                                    </div>
                                    <div class="row pt-2">
                                        <label class="col-md-4 col-form-label"> <strong>Email Address:</strong></label>
                                        <div class="col-md-8">
                                            <span id="" class="editInput">arjun@gmail.com</span>
                                        </div>
                                    </div>
                                    <div class="row pt-2">
                                        <label class="col-md-4 col-form-label"> <strong>Telephone:</strong></label>
                                        <div class="col-md-8">
                                            <span id="" class="editInput">+91-1234567890</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="">
                                        <h4 class="contTitle text-center"> <strong>Lead Details</strong></h4>
                                    </div>
                                    <div class="row">
                                        <label class="col-md-4 col-form-label"><strong>Lead Ref.:</strong></label>
                                        <div class="col-md-8">
                                            <span id="" class="editInput">Default</span>
                                            <input type="hidden" id="" name="">
                                        </div>
                                    </div>
                                    <div class="row pt-2">
                                        <label class="col-md-4 col-form-label"> <strong>Lead Status:</strong></label>
                                        <div class="col-md-8">
                                            <span id="" class="editInput">425</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <ul class="nav nav-pills mb-3 mt-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-userCRMfullHistory-tab" data-bs-toggle="pill" data-bs-target="#CRMpills-fullHistory" type="button" role="tab" aria-controls="pills-fullHistory" aria-selected="true">Full History</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="" data-bs-toggle="pill" data-bs-target="#CRMpills-Calls" type="button" role="tab" aria-controls="pills-Calls" aria-selected="false" tabindex="-1">Calls</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="" data-bs-toggle="pill" data-bs-target="#CRMpills-emails" type="button" role="tab" aria-controls="pills-emails" aria-selected="false" tabindex="-1">Emails</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="" data-bs-toggle="pill" data-bs-target="#CRMpills-tasks" type="button" role="tab" aria-controls="pills-tasks" aria-selected="false" tabindex="-1">Tasks</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="" data-bs-toggle="pill" data-bs-target="#CRMpills-notes" type="button" role="tab" aria-controls="pills-notes" aria-selected="false" tabindex="-1">Notes</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="" data-bs-toggle="pill" data-bs-target="#CRMpills-complaints" type="button" role="tab" aria-controls="pills-complaints" aria-selected="false" tabindex="-1">Complaints</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="CRMpills-fullHistory" role="tabpanel" aria-labelledby="pills-userCRMfullHistory-tab" tabindex="0">
                            <div class="newJobForm mt-4">
                                <label class="upperlineTitle">Full History</label>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <form class="searchForm" action="">
                                            <div class="input-group mb-3 mt-3">
                                                <input type="text" class="form-control editInput ps-3" placeholder="Your Email" name="email">
                                                <button type="button" class="input-group-text profileDrop">Search</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="productDetailTable">
                                            <table class="table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>By</th>
                                                        <th>Contact</th>
                                                        <th>Type</th>
                                                        <th>Note(s)</th>
                                                        <th>Status</th>
                                                        <th>Customer Visible</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>08/08/2024 06:16</td>
                                                        <td>Abhi - (mobappssolutions131@gmail.com) </td>
                                                        <td>1234567890</td>
                                                        <td> System</td>
                                                        <td>New Task 'Swapnil Task add' created for '08/08/2024 00:10'</td>
                                                        <td>New Task</td>
                                                        <td>..</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="CRMpills-Calls" role="tabpanel" aria-labelledby="pills-userCRMCalls-tab" tabindex="0">
                            <div class="newJobForm mt-4">
                                <label class="upperlineTitle">Calls History</label>
                                <div class="row">
                                    <div class="col-sm-1">
                                        <div class="jobsection  mt-3">
                                            <a href="#" class="profileDrop p-2 crmNewBtn" id="userCRMCallsModel"> New</a>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <form class="searchForm" action="">
                                            <div class="input-group mb-3  mt-3">
                                                <input type="text" class="form-control editInput" placeholder="Your Email" name="email">
                                                <button type="button" class="input-group-text sarchBtn">Search</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="productDetailTable">
                                            <table class="table" id="">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>By</th>
                                                        <th>Contact</th>
                                                        <th>Type</th>
                                                        <th>Note(s)</th>
                                                        <th>Customer Visible</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>

                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="CRMpills-emails" role="tabpanel" aria-labelledby="pills-userCRMemails-tab" tabindex="0">
                            <div class="newJobForm mt-4">
                                <label class="upperlineTitle">Emails History</label>
                                <div class="row">
                                    <div class="col-sm-1">
                                        <div class="jobsection  mt-3">
                                            <a href="#" class="profileDrop p-2 crmNewBtn" id="userNewEmail"> New</a>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <form class="searchForm" action="">
                                            <div class="input-group mb-3  mt-3">
                                                <input type="text" class="form-control editInput" placeholder="Your Email" name="email">
                                                <button type="button" class="input-group-text sarchBtn">Search</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="productDetailTable">
                                            <table class="table" id="">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>By</th>
                                                        <th>Contact</th>
                                                        <th>Type</th>
                                                        <th>Note(s)</th>
                                                        <th>Status</th>
                                                        <th>Customer Visible</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="CRMpills-tasks" role="tabpanel" aria-labelledby="pills-userCRMtasks-tab" tabindex="0">
                            <div class="newJobForm mt-4">
                                <label class="upperlineTitle">Tasks</label>
                                <div class="row">
                                    <div class="col-sm-1">
                                        <div class="jobsection  mt-3">
                                            <a href="#" class="profileDrop p-2 crmNewBtn open-modal" data-target="bd-example-modal-lg" id="userNewTaskpop"> New</a>
                                        </div>
                                    </div>
                                    <!-- task popup****************************** -->


                                    <!-- Second Modal -->
                                    <div class="modal fade bd-example-modal-lg" id="TasksecondModal" tabindex="-1" aria-labelledby="secondModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg mediamSizePopup">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="">New Task</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeTaskpopup"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <!-- tab -->
                                                    <nav>
                                                        <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
                                                            <button class="nav-link active" id="CRMnav-Task-tab" data-bs-toggle="tab" data-bs-target="#CRMnav-Task" type="button" role="tab" aria-controls="nav-Task" aria-selected="true">Task</button>
                                                            <button class="nav-link" id="CRMnav-Timer-tab" data-bs-toggle="tab" data-bs-target="#CRMnav-Timer" type="button" role="tab" aria-controls="nav-Timer" aria-selected="false">Timer</button>
                                                        </div>
                                                    </nav>
                                                    <div class="tab-content p-3 border bg-light" id="nav-tabContent">
                                                        <div class="tab-pane fade active show" id="CRMnav-Task" role="tabpanel" aria-labelledby="CRMnav-Task-tab">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <form>
                                                                        <div class="mb-3 row">
                                                                            <label for="staticEmail" class="col-sm-4 col-form-label">Task User</label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control editInput" name="" id="">
                                                                                    <option>task1</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="mb-3 row">
                                                                            <label for="staticEmail" class="col-sm-4 col-form-label">Title</label>
                                                                            <div class="col-sm-8">
                                                                                <input type="text" class="form-control editInput" id="staticEmail" value="">
                                                                            </div>
                                                                        </div>
                                                                        <div class="mb-3 row">
                                                                            <label for="staticEmail" class="col-sm-4 col-form-label">Task Type</label>
                                                                            <div class="col-sm-6">
                                                                                <select class="form-control editInput" name="" id="">

                                                                                </select>
                                                                            </div>
                                                                            <div class="col-sm-2">
                                                                                <a href="#!" class="formicon" id="userThirdModal"><i class="fa-solid fa-square-plus"></i></a>
                                                                            </div>
                                                                        </div>
                                                                        <div class="mb-3 row">
                                                                            <label for="staticEmail" class="col-sm-4 col-form-label">Start Date</label>
                                                                            <div class="col-sm-4">
                                                                                <input type="date" class="form-control editInput" id="staticEmail" value="">
                                                                            </div>
                                                                            <div class="col-sm-4">
                                                                                <input type="time" class="form-control editInput" id="staticEmail" value="">
                                                                            </div>
                                                                        </div>
                                                                        <div class="mb-3 row">
                                                                            <label for="staticEmail" class="col-sm-4 col-form-label">End Date</label>
                                                                            <div class="col-sm-4">
                                                                                <input type="date" class="form-control editInput" id="staticEmail" value="">
                                                                            </div>
                                                                            <div class="col-sm-4">
                                                                                <input type="time" class="form-control editInput" id="staticEmail" value="">
                                                                            </div>
                                                                        </div>
                                                                        <!-- <div class="mb-3 row">
                                                                            <label class="col-sm-4 col-form-label">Is Reccurring Task ?</label>
                                                                            <div class="col-sm-4">
                                                                               <input type="checkbox" class="form-check-input editInput" id="exampleCheck1">
                                                                            </div>
                                                                        </div> -->
                                                                    </form>
                                                                </div>
                                                                <div class="col-6">
                                                                    <form>
                                                                        <div class="mb-3 row">
                                                                            <label for="staticEmail" class="col-sm-3 col-form-label">Notify ? </label>
                                                                            <div class="col-sm-9">
                                                                                <div class="row">
                                                                                    <div class="col-sm-3">
                                                                                        <div class="form-check form-check-inline me-0">
                                                                                            <input class="form-check-input" type="checkbox" name="inlinecheckOptions" id="CRMyeson" value="option1" required="">
                                                                                            <label class="col-form-label " for="CRMyeson">Yes, On</label>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="col-sm-9">
                                                                                        <div class="row">
                                                                                            <div class="col-sm-6">
                                                                                                <input type="date" class="form-control editInput" id="notify_date" name="notify_date">
                                                                                            </div>
                                                                                            <div class="col-sm-6">
                                                                                                <input type="time" class="form-control editInput" id="notify_time" name="notify_time">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-3"></div>
                                                                            <div class="col-sm-9">
                                                                                <div id="CRMoptionsDiv">
                                                                                    <label class="editInput"><input type="checkbox" value="1" id="notificationCheckbox" name="notification"> Notification</label>
                                                                                    <label class="editInput"><input type="checkbox" value="1" id="emailCheckbox" name="email_notify"> Email</label>
                                                                                    <label class="editInput"><input type="checkbox" value="1" id="smsCheckbox" name="sms_notify"> SMS</label>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="mb-3 row">
                                                                            <label for="related_to" class="col-sm-3 col-form-label">Related To</label>
                                                                            <div class="col-sm-9">
                                                                                <span class="editInput" id=""></span>

                                                                            </div>
                                                                        </div>
                                                                        <div class="mb-3 row">
                                                                            <label for="staticEmail" class="col-sm-3 col-form-label">Notes</label>
                                                                            <div class="col-sm-9">
                                                                                <textarea name="" class="form-control textareaInput" rows="5" id=""></textarea>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                            <!-- <form class=""></form> -->
                                                        </div>
                                                        <div class="tab-pane fade" id="CRMnav-Timer" role="tabpanel" aria-labelledby="CRMnav-Timer-tab">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <form>
                                                                        <div class="mb-3 row">
                                                                            <label for="staticEmail" class="col-sm-4 col-form-label">Task User</label>
                                                                            <div class="col-sm-8">
                                                                                <input type="text" class="form-control" id="staticEmail" value="">
                                                                            </div>
                                                                        </div>
                                                                        <div class="mb-3 row">
                                                                            <label for="staticEmail" class="col-sm-4 col-form-label">Title</label>
                                                                            <div class="col-sm-8">
                                                                                <input type="text" class="form-control" name="" id="staticEmail" value="">
                                                                            </div>
                                                                        </div>
                                                                        <div class="mb-3 row">
                                                                            <label for="staticEmail" class="col-sm-4 col-form-label">Timer</label>
                                                                            <div class="col-sm-8">
                                                                                <button class="profileDrop" id=""><i class="fa fa-play"></i> Start</button>
                                                                                <span id="">00:00:00</span>
                                                                            </div>
                                                                        </div>

                                                                        <div class="mb-3 row">
                                                                            <label for="related_to" class="col-sm-4 col-form-label">Related To</label>
                                                                            <div class="col-sm-8">
                                                                                <span class="editInput" id=""></span>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                                <div class="col-6">
                                                                    <form>
                                                                        <div class="mb-3 row">
                                                                            <label for="staticEmail" class="col-sm-4 col-form-label">Task Type</label>
                                                                            <div class="col-sm-6">
                                                                                <select class="form-control editInput" name="" id="">
                                                                                    <option>Select</option>
                                                                                </select>

                                                                            </div>
                                                                            <div class="col-sm-2">
                                                                                <a href="#!" class="formicon" id=""><i class="fa-solid fa-square-plus"></i></a>
                                                                            </div>
                                                                        </div>
                                                                        <div class="mb-3 row">
                                                                            <label for="staticEmail" class="col-sm-4 col-form-label">Notes</label>
                                                                            <div class="col-sm-8">
                                                                                <textarea rows="5" name="" class="form-control textareaInput" id=""></textarea>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- tab -->
                                                    <div class="pageTitleBtn">
                                                        <a href="#" class="profileDrop p-2 crmNewBtn"> Save</a>
                                                        <button type="button" class="profileDrop" data-bs-dismiss="modal" id="closeTaskpopup">Cancel</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end task popup****************************** -->

                                    <!-- ******************************add task third type******************************* -->

                                    <!-- Third Modal -->
                                    <div class="modal fade" id="third3Modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content ">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="thirdModalLabel">Add Task Type</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close3Taskpopup"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="" id="">
                                                        <div class="mb-3 row">
                                                            <label for="inputJobRef" class="col-sm-3 col-form-label">Task Type <span class="radStar">*</span></label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="title" class="form-control editInput" id="inputJobRef" value="" placeholder="Task Type">
                                                            </div>
                                                        </div>
                                                        <div class="mb-3 row">
                                                            <label for="inputJobRef" class="col-sm-3 col-form-label">Status</label>
                                                            <div class="col-sm-9">
                                                                <select id="status" name="status" class="form-control editInput">
                                                                    <option value="1">Active</option>
                                                                    <option value="0">Inactive</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="pageTitleBtn">
                                                            <a href="#" class="profileDrop p-2 crmNewBtn" id=""> Save</a>
                                                            <button type="button" class="profileDrop" data-bs-dismiss="modal" id="close3Taskpopup">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- ******************************End add task third type******************************* -->


                                    <div class="col-sm-3">
                                        <form class="searchForm" action="">
                                            <div class="input-group mb-3  mt-3">
                                                <input type="text" class="form-control editInput" placeholder="Keywords to search" name="email">
                                                <button type="button" class="input-group-text sarchBtn">Search</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="overdue mt-3 ms-3">
                                            <span class="yeloColrbox"></span><label>Overdue</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="pageTitleBtn">
                                            <a href="{{ url('') }}" class="profileDrop p-2 crmNewBtn">All</a>
                                            <a href="#" class="profileDrop p-2 crmNewBtn">Today</a>
                                            <a href="#" class="profileDrop p-2 crmNewBtn">This Week</a>
                                            <a href="#" class="profileDrop p-2 crmNewBtn">Overdue</a>
                                            <a href="#" class="profileDrop p-2 crmNewBtn">Completed</a>
                                            <a href="#" class="profileDrop p-2 crmNewBtn">Recurring</a>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="productDetailTable">
                                            <table class="table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>By</th>
                                                        <th>Contact</th>
                                                        <th>Type</th>
                                                        <th>Note(s)</th>
                                                        <th>Status</th>
                                                        <th>Customer Visible</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="CRMpills-notes" role="tabpanel" aria-labelledby="pills-userCRMnotes-tab" tabindex="0">
                            <div class="newJobForm mt-4">
                                <label class="upperlineTitle">Notes</label>
                                <div class="row">
                                    <div class="col-sm-1">
                                        <div class="jobsection  mt-3">
                                            <a href="#!" class="profileDrop p-2 crmNewBtn" id="userNotesModel"> New</a>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <form class="searchForm" action="">
                                            <div class="input-group mb-3  mt-3">
                                                <input type="text" class="form-control editInput" placeholder="Your Email" name="email">
                                                <button type="button" class="input-group-text sarchBtn">Search</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="productDetailTable">
                                            <table class="table" id="">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>By</th>
                                                        <th>Contact</th>
                                                        <th>Type</th>
                                                        <th>Note(s)</th>
                                                        <th>Status</th>
                                                        <th>Customer Visible</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="CRMpills-complaints" role="tabpanel" aria-labelledby="pills-userCRMcomplaints-tab" tabindex="0">
                            <div class="newJobForm mt-4">
                                <label class="upperlineTitle">Complaints History</label>
                                <div class="row">
                                    <div class="col-sm-1">
                                        <div class="jobsection  mt-3">
                                            <a href="#" class="profileDrop p-2 crmNewBtn" id="userCompliantsModal"> New</a>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <form class="searchForm" action="">
                                            <div class="input-group mb-3  mt-3">
                                                <input type="text" class="form-control editInput" placeholder="Keyword to search" name="email">
                                                <button type="button" class="input-group-text sarchBtn">Search</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="productDetailTable">
                                            <table class="table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>By</th>
                                                        <th>Contact</th>
                                                        <th>Type</th>
                                                        <th>Note(s)</th>
                                                        <th>Status</th>
                                                        <th>Customer Visible</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
                                                        <td>.</td>
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
                <div class="modal-footer">
                    <button type="button" class="btn profileDrop" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn profileDrop">Understood</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Calls History new Popup -->

    <!-- Calls Modal -->
    <div class="modal fade" id="userCallsModal" tabindex="-1" aria-labelledby="callsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content add_Customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Calls</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeUserCallsModels"></button>
                </div>
                <div class="modal-body">
                    <form action="" class="customerForm" id="">
                        <div class="mb-2 row">
                            <input type="hidden" name="crm_lead_calls_id" id="crm_lead_calls_id">
                            <label for="calls_telephone" class="col-sm-3 col-form-label">Direction </label>
                            <div class="col-sm-9">
                                <input class="form-check-input" type="radio" name="direction" id="direction_radio1" value="0" checked>
                                <label class="form-check-label editInput" for="direction_radio1"> Call Out </label>
                                <input class="form-check-input" type="radio" name="direction" id="direction_radio2" value="1">
                                <label class="form-check-label editInput" for="direction_radio2"> Call In </label>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="calls_telephone" class="col-sm-3 col-form-label">Telephone </label>
                            <div class="col-sm-2">
                                <select class="form-control editInput selectOptions" required="" name="country_code" id="">
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="col-sm-7">
                                <input type="text" class="form-control editInput" id="" name="telephone" placeholder="Telephone">
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="calls_type" class="col-sm-3 col-form-label">Type <span class="radStar">*</span> </label>
                            <div class="col-sm-7">
                                <select name="crm_type_id" class="form-control editInput">
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <a href="#!" class="formicon" id="userCRMTypeModel"><i class="fa-solid fa-square-plus"></i></a>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="calls_notes" class="col-sm-3 col-form-label">Notes <span class="radStar">*</span> </label>
                            <div class="col-sm-9">
                                <div id="UserEditor">
                                </div>
                                <textarea name="content" id="" style="display: none;"></textarea>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="calls_lead_ref" class="col-sm-3 col-form-label">Related To </label>
                            <div class="col-sm-9">
                                <span class="editInput">LEAD-0021</span>
                                <input type="hidden" name="lead_ref">
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="calls_telephone" class="col-sm-3 col-form-label">Notify? </label>
                            <div class="col-sm-9">
                                <input class="form-check-input notify_radio1" type="radio" name="notify_radio" id="" value="0" checked>
                                <label class="form-check-label editInput" for=""> No </label>
                                <input class="form-check-input notify_radio2" type="radio" name="notify_radio" id="" value="1">
                                <label class="form-check-label editInput" for=""> Yes </label>
                            </div>
                        </div>
                        <div class="">
                            <div class="mb-2 row">
                                <label for="user_notifiy" class="col-sm-3 col-form-label">Notify Who? <span class="radStar">*</span> </label>
                                <div class="col-sm-9">
                                    <select name="notify_user" class="form-control editInput" id="user_notifiy">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <label class="col-sm-3 col-form-label">Send As <span class="radStar">*</span> </label>
                                <div class="col-sm-9">
                                    <label for="calls_notify_who1" class="editInput"><input type="checkbox" name="notification" id="calls_notify_who1" value="1"> Notification (User Only) </label>
                                    <label for="calls_notify_who2" class="editInput"><input type="checkbox" name="sms" id="calls_notify_who2" value="1"> SMS </label>
                                    <label for="calls_notify_who3" class="editInput"><input type="checkbox" name="email" id="calls_notify_who3" value="1"> Email </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="inputCity" class="col-sm-3 col-form-label">Customer Visible? </label>
                            <div class="col-sm-9">
                                <input class="form-check-input" type="radio" name="customer_visible" id="" value="0" checked>
                                <label class="form-check-label editInput" for="">No</label>
                                <input class="form-check-input" type="radio" name="customer_visible" id="" value="1">
                                <label class="form-check-label editInput" for="">Yes</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="profileDrop" id="">Save</button>
                    <button type="button" class="profileDrop" id="closeUserCallsModels" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- end Calls Modal -->

    <!-- CRM Types Modal Start -->
    <div class="modal fade" id="typeCrmModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content add_Customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add - CRM Section Types</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeCrmModalBtn" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" id="">
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Type <span class="radStar">*</span> </label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control editInput" name="title" id="type_title" value="">
                                <input type="hidden" class="form-control editInput" name="crm_section" id="" value="1">
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="colour_code" class="col-sm-3 col-form-label">Colour Code </label>
                            <div class="col-sm-9">
                                <input type="color" class="form-control editInput" name="colour_code" id="colour_code" value="">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="profileDrop" id="closeCrmModalBtn">Close</button>
                    <button type="button" class="profileDrop" id="">Save</button>
                </div>
            </div>
        </div>
    </div>
    <!-- CRM Types Modal End -->

    <!-- CRM Add Email Modal Start -->
    <div class="modal fade" id="userEmailModel" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content add_Customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="closepopupBtn" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" id="" enctype='multipart/form-data'>
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">To <span class="radStar">*</span> </label>
                            <div class="col-sm-9">
                                <input type="hidden" class="form-control editInput" name="crm_lead_email_id" id="">
                                <input type="text" class="form-control editInput" name="to" id="" value="">
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Cc </label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control editInput" name="cc" id="">
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Subject <span class="radStar">*</span> </label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control editInput" name="subject" id="">
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Message <span class="radStar">*</span> </label>
                            <div class="col-sm-9">
                                <div class="col-form-label">
                                    <div id="">
                                    </div>
                                    <textarea name="message" id="" style="display: none;"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Attachment </label>
                            <div class="col-sm-9">
                                <input type="file" class="editInput" name="attachment" id="image">
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Related To </label>
                            <div class="col-sm-9">
                                <span class="editInput" id=""></span>
                                <input type="hidden" id="" name="lead_id">
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="calls_telephone" class="col-sm-3 col-form-label">Notify? </label>
                            <div class="col-sm-9">
                                <input class="form-check-input" type="radio" name="notify" id="" value="0" checked>
                                <label class="form-check-label editInput" for=""> No </label>
                                <input class="form-check-input" type="radio" name="notify" id="" value="1">
                                <label class="form-check-label editInput" for=""> Yes </label>
                            </div>
                        </div>
                        <div id="user_notification_email_div">
                            <div class="mb-2 row">
                                <label for="user_notifiy" class="col-sm-3 col-form-label">Notify Who? <span class="radStar">*</span> </label>
                                <div class="col-sm-9">
                                    <select name="notify_user" class="form-control editInput" id="user_notifiy">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <label class="col-sm-3 col-form-label">Send As <span class="radStar">*</span> </label>
                                <div class="col-sm-9">
                                    <label for="calls_notify_who1" class="editInput">
                                        <input type="checkbox" name="notification" id="calls_notify_who1" value="1"> Notification (User Only)
                                    </label>
                                    <label for="calls_notify_who2" class="editInput">
                                        <input type="checkbox" name="sms" id="calls_notify_who2" value="1"> SMS
                                    </label>
                                    <label for="calls_notify_who3" class="editInput">
                                        <input type="checkbox" name="email" id="calls_notify_who3" value="1"> Email
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="inputCity" class="col-sm-3 col-form-label">Customer Visible? </label>
                            <div class="col-sm-9">
                                <input class="form-check-input" type="radio" name="customer_visible" id="" value="0" checked>
                                <label class="form-check-label editInput" for="">No</label>
                                <input class="form-check-input" type="radio" name="customer_visible" id="" value="1">
                                <label class="form-check-label editInput" for="">Yes</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="profileDrop" data-bs-dismiss="modal" id="closepopupBtn">Close</button>
                    <button type="button" class="profileDrop" id="">Save</button>
                </div>
            </div>
        </div>
    </div>
    <!-- CRM Add Email Modal End -->

    <!-- CRM Add Notes Modal Start -->
    <div class="modal fade" id="NotesCrmModel" tabindex="-1" role="dialog" aria-labelledby="notesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content add_Customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="notesModalLabel">Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeNotespopup" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" id="">
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Type <span class="radStar">*</span> </label>
                            <div class="col-sm-9">
                                <input type="hidden" class="form-control editInput" name="crm_lead_notes_id" id="">
                                <select class="form-control editInput" name="crm_section_type_id" id=""></select>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Notes <span class="radStar">*</span> </label>
                            <div class="col-sm-9">
                                <div class="col-form-label">
                                    <div id="userNotesEditor">
                                    </div>
                                    <textarea name="notes" id="userCRMNotes" style="display: none;"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Related To </label>
                            <div class="col-sm-9">
                                <span class="editInput" id=""></span>
                                <input type="hidden" id="" name="lead_id">
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="calls_telephone" class="col-sm-3 col-form-label">Notify? </label>
                            <div class="col-sm-9">
                                <input class="form-check-input" type="radio" name="notify" id="" value="0" checked>
                                <label class="form-check-label editInput" for=""> No </label>
                                <input class="form-check-input" type="radio" name="notify" id="" value="1">
                                <label class="form-check-label editInput" for=""> Yes </label>
                            </div>
                        </div>
                        <div id="user_notification_notes_div">
                            <div class="mb-2 row">
                                <label for="user_notifiy" class="col-sm-3 col-form-label">Notify Who? <span class="radStar">*</span> </label>
                                <div class="col-sm-9">
                                    <select name="user_id" class="form-control editInput" id="">
                                        <option>dfgdfg</option>
                                        <option>dtgerdg</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <label class="col-sm-3 col-form-label">Send As <span class="radStar">*</span> </label>
                                <div class="col-sm-9">
                                    <label for="calls_notify_who1" class="editInput">
                                        <input type="checkbox" name="notification" id="calls_notify_who1" value="1"> Notification (User Only)
                                    </label>
                                    <label for="calls_notify_who2" class="editInput">
                                        <input type="checkbox" name="sms" id="calls_notify_who2" value="1"> SMS
                                    </label>
                                    <label for="calls_notify_who3" class="editInput">
                                        <input type="checkbox" name="email" id="calls_notify_who3" value="1"> Email
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="inputCity" class="col-sm-3 col-form-label">Customer Visible? </label>
                            <div class="col-sm-9">
                                <input class="form-check-input" type="radio" name="customer_visibility" id="" value="0" checked>
                                <label class="form-check-label editInput" for="">No</label>
                                <input class="form-check-input" type="radio" name="customer_visibility" id="" value="1">
                                <label class="form-check-label editInput" for="">Yes</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="profileDrop" id="closeNotespopup">Close</button>
                    <button type="button" class="profileDrop" id="">Save</button>
                </div>
            </div>
        </div>
    </div>
    <!-- CRM Add Notes Modal End -->

    <!-- CRM Add compliants Modal Start -->
    <div class="modal fade" id="CompHistoryModal" tabindex="-1" role="dialog" aria-labelledby="compliantsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content add_Customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="compliantsModalLabel">Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeUserComplaintpopup" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" id="">
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Type <span class="radStar">*</span> </label>
                            <div class="col-sm-9">
                                <input type="hidden" class="form-control editInput" name="crm_lead_complaint_id" id="">
                                <select class="form-control editInput" name="crm_section_type_id" id="">
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Notes <span class="radStar">*</span> </label>
                            <div class="col-sm-9">
                                <div class="col-form-label">
                                    <div id="userComplaintEditor">
                                    </div>
                                    <textarea name="compliant" id="userCRMComplaint" style="display: none;"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Related To </label>
                            <div class="col-sm-9">
                                <span class="editInput" id=""></span>
                                <input type="hidden" id="" name="lead_id">
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="" class="col-sm-3 col-form-label">Notify? </label>
                            <div class="col-sm-9">
                                <input class="form-check-input" type="radio" name="notify" id="" value="0" checked>
                                <label class="form-check-label editInput" for=""> No </label>
                                <input class="form-check-input" type="radio" name="notify" id="" value="1">
                                <label class="form-check-label editInput" for=""> Yes </label>
                            </div>
                        </div>
                        <div id="">
                            <div class="mb-2 row">
                                <label for="user_notifiy" class="col-sm-3 col-form-label">Notify Who? <span class="radStar">*</span> </label>
                                <div class="col-sm-9">
                                    <select name="user_id" class="form-control editInput" id="user_notifiy">
                                        <option value="">default1</option>
                                        <option value="">default1</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <label class="col-sm-3 col-form-label">Send As <span class="radStar">*</span> </label>
                                <div class="col-sm-9">
                                    <label for="calls_complaint_who1" class="editInput">
                                        <input type="checkbox" name="notification" id="" value="1"> Notification (User Only)
                                    </label>
                                    <label for="calls_complaint_who2" class="editInput">
                                        <input type="checkbox" name="sms" id="" value="1"> SMS
                                    </label>
                                    <label for="calls_complaint_who3" class="editInput">
                                        <input type="checkbox" name="email" id="" value="1"> Email
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="profileDrop" id="">Close</button>
                    <button type="button" class="profileDrop" id="">Save</button>
                </div>
            </div>
        </div>
    </div>
    <!-- CRM Add compliants Modal End -->

    <!-- Script For adding CK editor start -->
    <script type="importmap">
        {
        "imports": {
            "ckeditor5": "https://cdn.ckeditor.com/ckeditor5/43.0.0/ckeditor5.js",
            "ckeditor5/": "https://cdn.ckeditor.com/ckeditor5/43.0.0/"
        }
    }
</script>

    <script>
        function openNav(event, panelId) {
            event.stopPropagation();
            closeAllPanels();
            document.getElementById(panelId).classList.add('remove_add');
        }

        function closeNav(panelId) {
            document.getElementById(panelId).classList.remove('remove_add');
        }

        function closeAllPanels() {
            let panels = document.querySelectorAll('.sidepanel');
            panels.forEach(panel => panel.classList.remove('remove_add'));
        }

        window.onclick = function() {
            closeAllPanels();
        };
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const dropdownButtons = document.querySelectorAll(".dropbtn");

            dropdownButtons.forEach((button) => {
                button.addEventListener("click", function(event) {
                    event.stopPropagation();
                    let dropdownContent = this.nextElementSibling;

                    // Close all other dropdowns
                    document.querySelectorAll(".dropdown_content").forEach((menu) => {
                        if (menu !== dropdownContent) {
                            menu.classList.remove("show");
                            setTimeout(() => {
                                menu.style.display = "none"; // Hide after animation
                            }, 10);
                        }
                    });

                    // Toggle clicked dropdown
                    if (dropdownContent.classList.contains("show")) {
                        dropdownContent.classList.remove("show");
                        setTimeout(() => {
                            dropdownContent.style.display = "none"; // Hide after animation
                        }, 300);
                    } else {
                        dropdownContent.style.display = "block"; // Show first
                        setTimeout(() => {
                            dropdownContent.classList.add("show");
                        }, 10);
                    }
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener("click", function() {
                document.querySelectorAll(".dropdown_content").forEach((menu) => {
                    if (menu.classList.contains("show")) {
                        menu.classList.remove("show");
                        setTimeout(() => {
                            menu.style.display = "none"; // Hide after animation
                        }, 10);
                    }
                });
            });
        });
    </script>
    <!-- calls CK editor Js -->
    <script type="module">
        import {
            ClassicEditor,
            Essentials,
            Paragraph,
            Bold,
            Italic,
            Font,
            Underline,
            Alignment
        } from 'ckeditor5';

        ClassicEditor
            .create(document.querySelector('#UserEditor'), {
                plugins: [Essentials, Paragraph, Bold, Italic, Underline, Font, Alignment],
                toolbar: [
                    'undo', 'redo', '|', 'bold', 'italic', 'underline', 'alignment', '|',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor'
                ]
            })
            .then(editor => {
                window.editor = editor;
                // Add a click event listener to the save button
                document.getElementById('saveCRMCallsModelData').addEventListener('click', function() {

                    // Get the CKEditor content
                    document.getElementById('calls_notes').value = editor.getData();
                    // console.log(document.getElementById('calls_notes').value);
                    // var formData = $('#CRM_calls_form').serialize(); 


                });
            })
            .catch(error => {
                console.error(error);
            });
    </script>
    <!-- Script For adding CK editor End -->

    <!-- End Calls History new Popup -->

    <script>
        // CRM Complaint Js Start for model show
        const userCRMCallsModel = document.getElementById('userCRMCallsModel');
        const userCallsModal = document.getElementById('userCallsModal');
        const closeUserCallsModels = document.getElementById('closeUserCallsModels');

        // When the user clicks the button, open the modal 
        userCRMCallsModel.onclick = function() {
            $('#userCallsModal').modal('show');
        }

        // When the user clicks on <span> (x), close the modal
        closeUserCallsModels.onclick = function() {
            $('#userCallsModal').modal('hide');
        }

        // window.onclick = function(event) {
        //     if (event.target === userCallsModal) {
        //         $('#userCallsModal').modal('hide');
        //     }
        // }
        // CRM Complaint Js End for model show


        // CRM Section Type Js Start for model show
        const userCRMTypeModel = document.getElementById('userCRMTypeModel');
        const typeCrmModel = document.getElementById('typeCrmModel');
        const closeCrmModalBtn = document.getElementById('closeCrmModalBtn');

        // When the user clicks the button, open the modal 
        userCRMTypeModel.onclick = function() {
            $('#typeCrmModel').modal('show');
        }

        // When the user clicks on <span> (x), close the modal
        closeCrmModalBtn.onclick = function() {
            $('#typeCrmModel').modal('hide');
        }

        // window.onclick = function(event) {
        //     if (event.target === typeCrmModel) {
        //         $('#typeCrmModel').modal('hide');
        //     }
        // }
        // CRM Section Type Js End for model show


        // CRM email Js Start for model show
        const userNewEmail = document.getElementById('userNewEmail');
        const userEmailModel = document.getElementById('userEmailModel');
        const closepopupBtn = document.getElementById('closepopupBtn');

        // When the user clicks the button, open the modal 
        userNewEmail.onclick = function() {
            $('#userEmailModel').modal('show');
        }

        // When the user clicks on <span> (x), close the modal
        closepopupBtn.onclick = function() {
            $('#userEmailModel').modal('hide');
        }

        // window.onclick = function(event) {
        //     if (event.target === userEmailModel) {
        //         $('#userEmailModel').modal('hide');
        //     }
        // }
        // CRM email Js End for model show


        // CRM task  Js Start for model show
        const userNewTaskpop = document.getElementById('userNewTaskpop');
        const TasksecondModal = document.getElementById('TasksecondModal');
        const closeTaskpopup = document.getElementById('closeTaskpopup');

        // When the user clicks the button, open the modal 
        userNewTaskpop.onclick = function() {
            $('#TasksecondModal').modal('show');
        }

        // When the user clicks on <span> (x), close the modal
        closeTaskpopup.onclick = function() {
            $('#TasksecondModal').modal('hide');
        }

        // window.onclick = function(event) {
        //     if (event.target === TasksecondModal) {
        //         $('#TasksecondModal').modal('hide');
        //     }
        // }
        // CRM task Js End for model show

        // CRM task third Js Start for model show
        const userThirdModal = document.getElementById('userThirdModal');
        const third3Modal = document.getElementById('third3Modal');
        const close3Taskpopup = document.getElementById('close3Taskpopup');

        // When the user clicks the button, open the modal 
        userThirdModal.onclick = function() {
            $('#third3Modal').modal('show');
        }

        // When the user clicks on <span> (x), close the modal
        close3Taskpopup.onclick = function() {
            $('#third3Modal').modal('hide');
        }

        // window.onclick = function(event) {
        //     if (event.target === compliantsModal) {
        //         $('#third3Modal').modal('hide');
        //     }
        // }
        // CRM task third Js End for model show

        // CRM Notes Js Start for model show
        const userNotesModel = document.getElementById('userNotesModel');
        const NotesCrmModel = document.getElementById('NotesCrmModel');
        const closeNotespopup = document.getElementById('closeNotespopup');

        // When the user clicks the button, open the modal 
        userNotesModel.onclick = function() {
            $('#NotesCrmModel').modal('show');
        }

        // When the user clicks on <span> (x), close the modal
        closeNotespopup.onclick = function() {
            $('#NotesCrmModel').modal('hide');
        }

        // window.onclick = function(event) {
        //     if (event.target === NotesCrmModel) {
        //         $('#NotesCrmModel').modal('hide');
        //     }
        // }
        // CRM Notes Js End for model show


        // CRM Complaints History Start for model show
        const userCompliantsModal = document.getElementById('userCompliantsModal');
        const CompHistoryModal = document.getElementById('CompHistoryModal');
        const closeUserComplaintpopup = document.getElementById('closeUserComplaintpopup');

        // When the user clicks the button, open the modal 
        userCompliantsModal.onclick = function() {
            $('#CompHistoryModal').modal('show');
        }

        // When the user clicks on <span> (x), close the modal
        closeUserComplaintpopup.onclick = function() {
            $('#CompHistoryModal').modal('hide');
        }

        // window.onclick = function(event) {
        //     if (event.target === compliantsModal) {
        //         $('#CompHistoryModal').modal('hide');
        //     }
        // }

        // Function to set z-index
        function setZIndexForBackdrop(zIndex) {
            const modalBackdrop = document.querySelector('.modal-backdrop');
            if (modalBackdrop) {
                modalBackdrop.style.zIndex = zIndex;
            }
        }

        // When the user clicks the button, open the modal 
        userCompliantsModal.onclick = function() {
            $('#CompHistoryModal').modal('show');
            // Set the z-index for the backdrop
            setZIndexForBackdrop(1055); // Adjust this value as necessary
        }
        // CRM Complaints History End for model show

        const CRMmainCheckbox = document.getElementById('CRMyeson');
        const CRMoptionsDiv = document.getElementById('CRMoptionsDiv');

        CRMmainCheckbox.addEventListener('change', function() {
            if (CRMmainCheckbox.checked) {
                CRMoptionsDiv.style.display = 'block';
            } else {
                CRMoptionsDiv.style.display = 'none';
            }
        });

        $(document).ready(function() {
            const CRMmainCheckbox = document.getElementById('CRMyeson');
            const CRMoptionsDiv = document.getElementById('CRMoptionsDiv');
            CRMoptionsDiv.style.display = 'none';

            CRMmainCheckbox.addEventListener('change', function() {
                if (CRMmainCheckbox.checked) {
                    CRMoptionsDiv.style.display = 'block';
                } else {
                    CRMoptionsDiv.style.display = 'none';
                }
            });
        });

        function getCountriesList(selectElement) {
            $.ajax({
                url: '{{ route("ajax.getCountriesList") }}',
                method: 'GET',
                success: function(response) {
                    console.log(response.Data);
                    // const selectElement = document.getElementById('countries');
                    selectElement.innerHTML = '';
                    response.Data.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.text = "+" + " " + user.code + " - " + " " + user.name;
                        selectElement.appendChild(option);
                    });
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        function getCountriesListWithNameCode(selectElement) {
            $.ajax({
                url: '{{ route("ajax.getCountriesList") }}',
                method: 'GET',
                success: function(response) {
                    console.log(response.Data);
                    selectElement.innerHTML = '';
                    response.Data.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.text = user.name + " " + "(+" + user.code + ")";
                        selectElement.appendChild(option);
                    });
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }
    </script>