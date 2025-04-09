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
    <link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
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
$current_url = last(request()->segments());
?>

<body>
    <header>
        <div class="topbaar left-0">
            <div class="container-fluid bg-light p-0">
                <div class="row gx-0 d-none d-lg-flex">
                    <div class="col-lg-3 px-3 text-start">
                        <div class="h-100 d-inline-flex align-items-center py-1 ms-3">
                            <a href="#" class="brand_logo"><img src="{{ url('public/images/ewm_logo.png')}}" alt="ewm_logo"></a>
                        </div>
                    </div>
                    <div class="col-lg-9 px-3 text-end toprigihticon">
                        <?php //if (isset($page) && $page == 'job_index') { 
                        ?>
                        <div class="d-inline-flex align-items-center me-5 topbaarBtn">
                            <a href="{{url('/')}}" class="profileDrop button_green"> <i class="fa fa-home"> </i> Home </a>
                        </div>
                        <?php //} else { 
                        ?>
                      
                        <?php //} 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- <section class="dashbord-main-info">
        <div class="container-fluid p-0">
            <div class="row m-0">
                <div class="col-lg-1 p-0">
                    <div class="left-sidebar-info">
                        <div class="nav-links">
                            <nav>
                                <ul>
                                    <div class="scroll">
                                        <li>
                                            <button href="#" class="openbtn @if(isset($page)) @if($page == 'leads') ActiveBtn @endif @endif" onclick="openNav(event, 'mySidepanel1')">
                                                <span class="plus_icon">
                                                    <i class="material-symbols-outlined"> keep_public </i>
                                                </span>Petty Cash
                                            </button>
                                            <div id="mySidepanel1" class="sidepanel">
                                                <ul>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h5>Petty Cash</h5>
                                                        <a href="javascript:void(0)" class="closebtn" onclick="closeNav('mySidepanel1')">
                                                            <svg width="40px" height="40px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                                                <g id="SVGRepo_iconCarrier">
                                                                    <path d="M6.99486 7.00636C6.60433 7.39689 6.60433 8.03005 6.99486 8.42058L10.58 12.0057L6.99486 15.5909C6.60433 15.9814 6.60433 16.6146 6.99486 17.0051C7.38538 17.3956 8.01855 17.3956 8.40907 17.0051L11.9942 13.4199L15.5794 17.0051C15.9699 17.3956 16.6031 17.3956 16.9936 17.0051C17.3841 16.6146 17.3841 15.9814 16.9936 15.5909L13.4084 12.0057L16.9936 8.42059C17.3841 8.03007 17.3841 7.3969 16.9936 7.00638C16.603 6.61585 15.9699 6.61585 15.5794 7.00638L11.9942 10.5915L8.40907 7.00636C8.01855 6.61584 7.38538 6.61584 6.99486 7.00636Z" fill="#0F0F0F"></path>
                                                                </g>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                    <li>
                                                        <a href="{{ url('/petty-cash/expend-card') }}">
                                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                                <i class="material-symbols-outlined"> add_circle</i>
                                                                <span>Expend Card</span>
                                                            </div>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ url('/petty-cash/petty_cash') }}">
                                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                                <i class="material-symbols-outlined">leaderboard</i>
                                                                <span>Petty Cash</span>
                                                            </div>
                                                        </a>
                                                    </li>
                                                   
                                                </ul>
                                            </div>
                                        </li>
                                        <li>
                                            <a href="{{url('/petty-cash/child_register')}}" class="@if(isset($page)) @if($page == 'expenses') ActiveBtn @endif @endif">
                                                <span class="plus_icon">
                                                    <i class="material-symbols-outlined"> calculate </i>
                                                </span>Child Register
                                            </a>
                                        </li>
                                       
                                    </div>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> -->
    <!-- Button trigger modal -->

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
                                                            <label for="inputJobRef" class="col-sm-3 col-form-label">Task Type <span class="radStar ">*</span></label>
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
                            <label for="calls_type" class="col-sm-3 col-form-label">Type <span class="radStar ">*</span> </label>
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
                            <label for="calls_notes" class="col-sm-3 col-form-label">Notes <span class="radStar ">*</span> </label>
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
                                <label for="user_notifiy" class="col-sm-3 col-form-label">Notify Who? <span class="radStar ">*</span> </label>
                                <div class="col-sm-9">
                                    <select name="notify_user" class="form-control editInput" id="user_notifiy">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <label class="col-sm-3 col-form-label">Send As <span class="radStar ">*</span> </label>
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
                            <label for="type_title" class="col-sm-3 col-form-label">Type <span class="radStar ">*</span> </label>
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
                            <label for="type_title" class="col-sm-3 col-form-label">To <span class="radStar ">*</span> </label>
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
                            <label for="type_title" class="col-sm-3 col-form-label">Subject <span class="radStar ">*</span> </label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control editInput" name="subject" id="">
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Message <span class="radStar ">*</span> </label>
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
                                <label for="user_notifiy" class="col-sm-3 col-form-label">Notify Who? <span class="radStar ">*</span> </label>
                                <div class="col-sm-9">
                                    <select name="notify_user" class="form-control editInput" id="user_notifiy">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <label class="col-sm-3 col-form-label">Send As <span class="radStar ">*</span> </label>
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
                            <label for="type_title" class="col-sm-3 col-form-label">Type <span class="radStar ">*</span> </label>
                            <div class="col-sm-9">
                                <input type="hidden" class="form-control editInput" name="crm_lead_notes_id" id="">
                                <select class="form-control editInput" name="crm_section_type_id" id=""></select>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Notes <span class="radStar ">*</span> </label>
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
                                <label for="user_notifiy" class="col-sm-3 col-form-label">Notify Who? <span class="radStar ">*</span> </label>
                                <div class="col-sm-9">
                                    <select name="user_id" class="form-control editInput" id="">
                                        <option>dfgdfg</option>
                                        <option>dtgerdg</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <label class="col-sm-3 col-form-label">Send As <span class="radStar ">*</span> </label>
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
                            <label for="type_title" class="col-sm-3 col-form-label">Type <span class="radStar ">*</span> </label>
                            <div class="col-sm-9">
                                <input type="hidden" class="form-control editInput" name="crm_lead_complaint_id" id="">
                                <select class="form-control editInput" name="crm_section_type_id" id="">
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="type_title" class="col-sm-3 col-form-label">Notes <span class="radStar ">*</span> </label>
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
                                <label for="user_notifiy" class="col-sm-3 col-form-label">Notify Who? <span class="radStar ">*</span> </label>
                                <div class="col-sm-9">
                                    <select name="user_id" class="form-control editInput" id="user_notifiy">
                                        <option value="">default1</option>
                                        <option value="">default1</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <label class="col-sm-3 col-form-label">Send As <span class="radStar ">*</span> </label>
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