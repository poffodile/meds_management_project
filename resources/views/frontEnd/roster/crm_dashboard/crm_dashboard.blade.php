<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
@extends('frontEnd.layouts.master')
@section('title','CRM Dashboard')
@section('content')


@include('frontEnd.roster.common.roster_header')



<main class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="staffHeaderp">
                    <div>
                        <h1 class="mainTitlep"> CRM Dashboard </h1>
                        <p class="header-subtitle mb-0"> Client Relationship Management & Intake System</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 m-t-20">
                <div class="card-row cardRow4">
                    <div class="card-col bgWhite">
                        <div class="rounded12 blueBorder borderLeftThick p24">
                            <div class="flexBw align-items-start">
                                <div>
                                    <p class="muteText">New Enquiries</p>
                                    <h3 class="fs30 textBlack font700 mt-0 mb-2">0</h3>
                                    <p class="fs12 textGray500 mb-0">Awaiting contact</p>
                                </div>
                                <div>
                                    <i class="bx bx-group blueText fs23"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-col bgWhite">
                        <div class="rounded12 greenBorder borderLeftThick p24">
                            <div class="flexBw align-items-start">
                                <div>
                                    <p class="muteText">Active Referrals</p>
                                    <h3 class="fs30 textBlack font700 mt-0 mb-2">1</h3>
                                    <p class="fs12 textGray500 mb-0">In progress</p>
                                </div>
                                <div>
                                    <i class="bx bx-trending-up greenTextp fs23"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-col bgWhite">
                        <div class="rounded12 orangeBorder borderLeftThick p-4">
                            <div class="flexBw align-items-start">
                                <div>
                                    <p class="muteText">Pending Documents</p>
                                    <h3 class="fs30 textBlack font700 mt-0 mb-2">0</h3>
                                    <p class="fs12 textGray500 mb-0">Awaiting completion</p>
                                </div>
                                <div>
                                    <i class="bx bx-file-detail orangeText fs23"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-col bgWhite">
                        <div class="rounded12 redBorder borderLeftThick p-4">
                            <div class="flexBw align-items-start">
                                <div>
                                    <p class="muteText">Overdue Follow-ups</p>
                                    <h3 class="fs30 textBlack font700 mt-0 mb-2">34.0</h3>
                                    <p class="fs12 textGray500 mb-0">Require attention</p>
                                </div>
                                <div>
                                    <i class="bx bx-alert-circle redtext fs23"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="leave-card mt-5">
                    <div class="workHoursHeader">
                        <div class="title"> RAG Status Overview </div>
                    </div>
                    <div class="CRMStatusOverview">
                        <ul class="CRMStatusList">
                            <li> <span class="radBgCircle"></span> <strong> Red: 0</strong> Urgent attention needed</li>
                            <li> <span class="orangeBgCircle"></span> <strong> Amber: 0</strong> Monitor closely</li>
                            <li> <span class="greenBgCircle"></span> <strong> Green: 3</strong> On track</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="calendarTabs leaveRequesttabs m-t-20">
                    <div class="tabs">
                        <button class="tab active" data-tab="RAGStatusOverview">
                            Overview
                        </button>

                        <button class="tab" data-tab="RAGStatusEnquiries">
                            Enquiries
                        </button>

                        <button class="tab" data-tab="RAGStatusReferrals">
                            Referrals
                        </button>
                        <button class="tab" data-tab="RAGStatusDocuments">
                            Documents
                        </button>
                        <button class="tab" data-tab="RAGStatusFollowUps">
                            Follow-ups
                        </button>
                    </div>

                    <!-- TAB CONTENT -->
                    <div class="tab-content">
                        <div class="content active" id="RAGStatusOverview">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="leave-card minHeightCard">
                                        <div class="carePlanWrapper">
                                            <div class="workHoursHeader">
                                                <div class="tabTitleAndViewbtn">
                                                    <div class="title"> Recent Enquiries </div>
                                                    <button class="btn bgBtn blackBtn"> View All </button>
                                                </div>
                                            </div>
                                            <div class="overViewSaveDetals">
                                                <div class="taskCard grayBgColor">
                                                    <div class="task-content">
                                                        <div class="task-left">
                                                            <h3 class="task-title">Darren Jones</h3>
                                                            <p class="task-sub">document_chase</p>
                                                            <div class="task-time">
                                                                <span>Due: Apr 21, 2:06 AM</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="taskCard grayBgColor">
                                                    <div class="task-content">
                                                        <div class="task-left">
                                                            <h3 class="task-title">Phil Holt</h3>
                                                            <p class="task-sub">document_chase</p>
                                                            <div class="task-time">
                                                                <span>Due: Apr 21, 2:06 AM</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="leave-card minHeightCard">
                                        <div class="carePlanWrapper">
                                            <div class="workHoursHeader">
                                                <div class="tabTitleAndViewbtn">
                                                    <div class="title"> Urgent Follow-ups </div>
                                                    <button class="btn bgBtn blackBtn"> View All </button>
                                                </div>
                                            </div>
                                            <div class="overViewSaveDetals">
                                                <div class="taskCard grayBgColor">
                                                    <div class="task-content">
                                                        <div class="task-left">
                                                            <h3 class="task-title">Test</h3>
                                                            <p class="task-sub">document_chase</p>
                                                            <div class="task-time">
                                                                <span class="clock-icon"><i class="bx bx-clock"></i></span>
                                                                <span>Due: Apr 21, 2:06 AM</span>
                                                            </div>
                                                        </div>
                                                        <div class="task-right">
                                                            <span class="roundTag radShowbtn"> Overdue</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content" id="RAGStatusEnquiries">
                           
                            <!-- Search end model-->
                            <div class="row mt20">                                
                                <div class="col-md-12">
                                    <div class="searchEnquiriesWithOptionNewEnquiryBtn">
                                       <div class="searchEnquiries">
                                            <div class="">
                                                <div class="input-group searchWithtabs" style="width: 100%;">
                                                    <span class="input-group-addon btn-white">
                                                        <i class="fa fa-search"></i>
                                                    </span>
                                                    <input style="min-width:300px" type="text" id="" class="form-control" placeholder="Search enquiries...">
                                                </div>
                                            </div>
                                            <div class="">
                                                <select class="form-control" id="" style="min-width:200px">
                                                    <option value="">All Status</option>
                                                    <option value="0">New</option>
                                                    <option value="1">Contacted</option>
                                                    <option value="2">Forms Sent</option>
                                                    <option value="3">Forms Panding</option>
                                                </select>
                                            </div>
                                       </div>
                                       <button class="bgBtn blackBtn" id="" type="button" data-toggle="modal" data-target="">  <i class="bx bx-plus"></i> New Enquiry </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Search end model-->

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="carertabcontent">
                                        <div class="profile-card">
                                            <div class="card-header">
                                                <div class="user">
                                                    <div class="info">
                                                        <div class="name">
                                                            <a href="">Darren Jones</a>
                                                        </div>
                                                        <div class="role"> day centre</div>
                                                    </div>
                                                </div>
                                                <span class="status greenShowbtn"> converted </span>
                                            </div>
                                            <div class="details">
                                                <div class="item">
                                                    <i class="fa-solid fa-phone"></i> <span>07800987867</span>
                                                </div>
                                                <div class="item">
                                                    <i class="fa-regular fa-envelope"></i> <span>dj@gmail.com</span>
                                                </div>
                                                <div class="item">
                                                    <i class="bx bx-user"></i><span>For: Judy Smith</span>
                                                </div>
                                            </div>

                                            <div class="actions">
                                                <a href="{{ url('/roster/crm-dashboard-details') }}" class="edit">
                                                    <i class="bx bx-eye"></i> View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="carertabcontent">
                                        <div class="profile-card">
                                            <div class="card-header">
                                                <div class="user">
                                                    <div class="info">
                                                        <div class="name">
                                                            <a href="">Darren Jones</a>
                                                        </div>
                                                        <div class="role"> day centre</div>
                                                    </div>
                                                </div>
                                                <span class="status greenShowbtn"> converted </span>
                                            </div>
                                            <div class="details">
                                                <div class="item">
                                                    <i class="fa-solid fa-phone"></i> <span>07800987867</span>
                                                </div>
                                                <div class="item">
                                                    <i class="fa-regular fa-envelope"></i> <span>dj@gmail.com</span>
                                                </div>
                                                <div class="item">
                                                    <i class="bx bx-user"></i><span>For: Judy Smith</span>
                                                </div>
                                            </div>
                                            <div class="actions">
                                                <a href="{{ url('/roster/crm-dashboard-details') }}" class="edit">
                                                    <i class="bx bx-eye"></i> View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content" id="RAGStatusReferrals"> 

                            <!-- Search end model-->
                            <div class="row mt20">                                
                                <div class="col-md-12">
                                    <div class="searchEnquiriesWithOptionNewEnquiryBtn">
                                       <div class="searchEnquiries">
                                            <div class="">
                                                <div class="input-group searchWithtabs" style="width: 100%;">
                                                    <span class="input-group-addon btn-white">
                                                        <i class="fa fa-search"></i>
                                                    </span>
                                                    <input style="min-width:300px" type="text" id="" class="form-control" placeholder="Search Referrals...">
                                                </div>
                                            </div>
                                            <div class="">
                                                <select class="form-control" id="" style="min-width:200px">
                                                    <option value="">All Status</option>
                                                    <option value="0">Received</option>
                                                    <option value="1">Screening</option>
                                                    <option value="2">Assessment Pending</option>
                                                    <option value="3">Accepted</option>
                                                </select>
                                            </div>
                                       </div>
                                       <button class="bgBtn blackBtn" id="" type="button" data-toggle="modal" data-target="">  <i class="bx bx-plus"></i> New Referral </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Search end model-->

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="carertabcontent">
                                        <div class="profile-card">
                                            <div class="card-header">
                                                <div class="user">
                                                    <div class="info">
                                                        <div class="role">REF-1776761003930</div>
                                                        <div class="name referralName">
                                                           <h5>John Staines</h> 
                                                        </div>
                                                        <div class="role"> domiciliary care</div>
                                                    </div>
                                                </div>
                                                <div class="refGreenRecev">
                                                    <span class="status careBadg"> received </span>
                                                    <span class="status greenShowbtn"> green </span>
                                                </div>
                                            </div>
                                            <div class="details">
                                                <div class="item">
                                                    <i class="bx bx-building"></i> <span>Webnmobapps Solutions Pvt. ltd.</span>
                                                </div>
                                                <div class="item">
                                                    <i class="bx bx-user"></i><span>For: Arjun Kumar</span>
                                                </div>
                                                <div class="task-right pb-3 pt-3">
                                                    <span class="roundTag radShowbtn"> urgent</span>
                                                </div>
                                            </div>

                                            <div class="actions">
                                                <a href="{{ url('/roster/crm-dashboard-details') }}" class="edit">
                                                    <i class="bx bx-eye"></i> View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="carertabcontent">
                                        <div class="profile-card">
                                            <div class="card-header">
                                                <div class="user">
                                                    <div class="info">
                                                        <div class="role">REF-1776761003930</div>
                                                        <div class="name referralName">
                                                            <h5>John Staines</h> 
                                                        </div>                                                        
                                                        <div class="role"> domiciliary care</div>
                                                    </div>
                                                </div>
                                                <div class="refGreenRecev">
                                                    <span class="status careBadg"> received </span>
                                                    <span class="status greenShowbtn"> green </span>
                                                </div>
                                            </div>
                                            <div class="details">
                                                <div class="item">
                                                    <i class="bx bx-building"></i> <span>Webnmobapps Solutions Pvt. ltd.</span>
                                                </div>
                                                <div class="item">
                                                    <i class="bx bx-user"></i><span>For: Arjun Kumar</span>
                                                </div>
                                                <div class="task-right pb-3 pt-3">
                                                    <span class="roundTag radShowbtn"> urgent</span>
                                                </div>
                                            </div>
                                            <div class="actions">
                                                <a href="{{ url('/roster/crm-dashboard-details') }}" class="edit">
                                                    <i class="bx bx-eye"></i> View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="carertabcontent">
                                        <div class="profile-card">
                                            <div class="card-header">
                                                <div class="user">
                                                    <div class="info">
                                                        <div class="role">REF-1776761003930</div>
                                                        <div class="name referralName">
                                                            <h5>John Staines</h> 
                                                        </div>
                                                        <div class="role"> residential care</div>
                                                    </div>
                                                </div>
                                                <div class="refGreenRecev">
                                                    <span class="status careBadg"> received </span>
                                                    <span class="status greenShowbtn"> green </span>
                                                </div>
                                            </div>
                                            <div class="details">
                                                <div class="item">
                                                   <i class="bx bx-building"></i> <span>Webnmobapps Solutions Pvt. ltd.</span>
                                                </div>
                                                <div class="item">
                                                    <i class="bx bx-user"></i><span>For: Arjun Kumar</span>
                                                </div>
                                                <div class="task-right pb-3 pt-3">
                                                    <span class="roundTag radShowbtn"> urgent</span>
                                                </div>
                                            </div>
                                            <div class="actions">
                                                <a href="{{ url('/roster/crm-dashboard-details') }}" class="edit">
                                                    <i class="bx bx-eye"></i> View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                        <div class="content" id="RAGStatusDocuments"> 

                            <!-- Search end model-->
                            <div class="row mt20">                                
                                <div class="col-md-12">
                                    <div class="searchEnquiriesWithOptionNewEnquiryBtn">
                                       <div class="searchEnquiries">
                                            <div class="">
                                                <select class="form-control" id="" style="min-width:200px">
                                                    <option value="">All Documents</option>
                                                    <option value="0">Pending</option>
                                                    <option value="1">Sent</option>
                                                    <option value="2">Viewed</option>
                                                    <option value="3">Completed</option>
                                                    <option value="3">Signed</option>
                                                </select>
                                            </div>
                                       </div>
                                       <button class="bgBtn blackBtn" id="" type="button" data-toggle="modal" data-target="">  <i class="bx bx-plus"></i> New Referral </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Search end model-->

                            <div class="sectionWhiteBgAllUse">
                                <div class="leavebanktabCont">
                                    <h4>No documents found</h4>
                                </div> 
                            </div>
                        </div>
                        <div class="content" id="RAGStatusFollowUps"> 
                                                      
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="rota_dashboard-cards simpleCard pRotaCard">
                                        <div class="rota_dash-card bg-blue-50 p-4  d-block text-center">
                                            <div class="rota_dash-left">
                                                <h2 class="rota_count m-0" id="">1</h2>
                                                <p class="rota_title"> Active</p>                                                
                                            </div>
                                        </div>
                                        <div class="rota_dash-card bg-red-50 p-4 d-block text-center">
                                            <div class="rota_dash-left">
                                                <h2 class="rota_count m-0" id="">0</h2>
                                                <p class="rota_title"> Overdue</p>                                                
                                            </div>
                                        </div>
                                        <div class="rota_dash-card bg-orange-50 p-4 d-block text-center">
                                            <div class="rota_dash-left">
                                                <h2 class="rota_count m-0 orangeText" id="">0</h2>
                                                <p class="rota_title"> Due Today</p>                                                
                                            </div>
                                        </div>                                        
                                        <div class="rota_dash-card bg-purple-50 p-4  d-block text-center">
                                            <div class="rota_dash-left">
                                                <h2 class="rota_count m-0" id="">1</h2>
                                                <p class="rota_title"> Awaiting Response</p>                                                
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>  

                            <!-- Search end model-->
                            <div class="row">                                
                                <div class="col-md-12">
                                    <div class="searchEnquiriesWithOptionNewEnquiryBtn">
                                       <div class="searchEnquiries">
                                            
                                            <div class="">
                                                <select class="form-control" id="" style="min-width:200px">
                                                    <option value="">All Status</option>
                                                    <option value="0">New</option>
                                                    <option value="1">Contacted</option>
                                                    <option value="2">Forms Sent</option>
                                                    <option value="3">Forms Panding</option>
                                                </select>
                                            </div>
                                            <div class="">
                                                <div class="searchWithtabs">
                                                    <button class="btn btn-white">
                                                        <i class="bx bx-refresh-cw fs18"></i>
                                                    </button>
                                                </div>
                                            </div>

                                       </div>
                                       <button class="bgBtn blackBtn" id="" type="button" data-toggle="modal" data-target="">  <i class="bx bx-plus"></i> New Follow-up </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Search end model-->

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="folowUpDatailsSec">
                                        <div class="newFolowDtl lightBorderp rounded8 p-4">
                                            <div class="newFolowUpDetails">
                                                <div>
                                                    <h6 class="h6Head mb-3"><a href="#!" class="clicknewFolowFullDetails"> <i class="bx bx-chevron-right fs23"></i> </a>  <i class="bx bx-file-detail fs18 textGray500 me-2"></i> Test
                                                        <span class="borderBadg ms-2"> <i class="bx bx-group fs13"></i> internal</span>
                                                    </h6>
                                                    <p class="fs13 textGray500 mb-0">Description Description</p>
                                                    <ul class="CRMStatusList">
                                                        <li> <span><i class="bx bx-group fs14"></i></span> Urgent attention needed</li>
                                                        <li> <span><i class="bx bx-clock fs14"></i></span> Monitor closely</li>
                                                    </ul>
                                                    <div class="">
                                                        <span class="borderBadg"> <i class="bx bx-group fs13"></i> Overdue alert sent</span>                                                        
                                                        <span class="roundTag radShowbtn ms-2"> <i class="bx bx-alert-circle fs13"></i> Escalated</span>
                                                    </div>
                                                </div>                                                
                                                <div class="refGreenRecev">
                                                    <span class="roundTag radShowbtn"> overdue </span>
                                                    <span class="careBadg yellowBadges">Medium</span>                                                    
                                                </div>                                                
                                            </div>
                                            <div class="showFolowFullDetails">

                                            

                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div> 



                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</main>


<script>
    const tabs = document.querySelectorAll(".tab");
    const contents = document.querySelectorAll(".content");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            document.querySelector(".tab.active")?.classList.remove("active");
            tab.classList.add("active");

            let tabName = tab.getAttribute("data-tab");

            contents.forEach(content => {
                content.classList.remove("active");
            });

            document.getElementById(tabName).classList.add("active");
        });
    });
</script>

@endsection