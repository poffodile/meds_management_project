<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
@extends('frontEnd.layouts.master')
@section('title','Client')
@section('content')

@include('frontEnd.roster.common.roster_header')



 <main class="page-content">
        <div class="container-fluid">

            <div class="topHeaderCont">
                <div>
                    <h1>Clients</h1>
                    <p class="header-subtitle">Manage client information and care plans</p>
                </div>
                <div class="header-actions">
                    <button class="btn" data-toggle="modal" data-target="#addLeaveModal"><i class="fa fa-plus"></i> Add Client</button>
                </div> 
            </div>

            <div class="rota_dashboard-cards simpleCard">
                <div class="rota_dash-card blue">
                    <div class="rota_dash-left">
                        <p class="rota_title">Total Clients</p>
                        <h2 class="rota_count">12</h2>
                    </div>
                </div>

                <div class="rota_dash-card orangeClr">
                    <div class="rota_dash-left">
                        <p class="rota_title">Active</p>
                        <h2 class="rota_count greenText">10</h2>
                    </div>
                </div>

                <div class="rota_dash-card redClr">
                    <div class="rota_dash-left">
                        <p class="rota_title">Inactive</p>
                        <h2 class="rota_count">2</h2>
                    </div>
                </div>

            </div>

             <div class="calendarTabs leaveRequesttabs m-t-20">
                <div class="tabs">
                    <div class="input-group searchWithtabs">
                        <span class="input-group-addon btn-white"><i class="fa fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Username">
                    </div>
                    <button class="tab active" data-tab="allCarerActibity">
                        All 
                    </button>

                    <button class="tab" data-tab="activeCarer">
                        Active 
                    </button>

                    <button class="tab" data-tab="inactiveCarer">
                        Inactive 
                    </button>
                </div>

                <!-- TAB CONTENT -->
                <div class="tab-content carertabcontent">
                    <div class="content active" id="allCarerActibity">
                        <div class="row">
                            <div class="col-md-4">                                 
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">L</div>
                                            <div class="info">
                                                <div class="name">Logan Jones</div>
                                                <div class="role">part time</div>
                                            </div>
                                        </div>
                                        <span class="status greenShowbtn">Active</span>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i> <span>9063258701</span>
                                        </div>
                                      
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="section care-needs">
                                        <div class="label">
                                            <i class="fa-regular fa-heart"></i>
                                            Care Needs:
                                        </div>

                                        <div class="sectionCarer">

                                            <div class="tags">
                                                <span>Dementia Care</span>  <span>Medication Administration</span> <span> Admin</span>
                                                <span> Administration</span>
                                                <span>Medication </span>
                                                <button class="care-more">+10 more</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="actions">
                                        <button class="view">
                                            <i class="fa-regular fa-eye"></i>
                                            View Details
                                        </button>
                                        <button class="edit">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button class="delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>                                
                            </div>
                            <div class="col-md-4"> 
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar avatar-green">M</div>
                                            <div class="info">
                                                <div class="name">Mrs Eleanor Whitfield</div>
                                                <div class="role">self funded</div>
                                            </div>
                                        </div>
                                        <span class="status greenShowbtn">Active</span>
                                    </div>

                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i>
                                            <span>07123456789</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i>
                                            <span>Anytown,</span>
                                        </div>
                                    </div>
                                    <div class="section care-needs">
                                        <div class="label">
                                            <i class="fa-regular fa-heart"></i>
                                            Care Needs:
                                        </div>

                                        <div class="care-list">
                                            <div class="care-item">
                                            Assistance with mobility and transfers (uses wheeled Zimmer frame, requires one carer)
                                            </div>
                                            <div class="care-item">
                                            High falls risk (due to poor balance, muscle weakness, osteoporosis, cognitive impairment)
                                            </div>
                                            <div class="care-item">
                                            Full assistance with personal care (washing, dressing, continence care, toileting)
                                            </div>
                                            <button class="care-more">+10 more</button>
                                        </div>
                                    </div>
                                    <div class="actions">
                                        <button class="view">
                                            <i class="fa-regular fa-eye"></i>
                                            View Details
                                        </button>
                                        <button class="edit">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button class="delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4"> 
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">R</div>
                                            <div class="info">
                                                <div class="name">RRuby Donavan</div>
                                                <div class="role">part time</div>
                                            </div>
                                        </div>
                                        <span class="status greenShowbtn">Active</span>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i> <span>9063258701</span>
                                        </div>
                                      
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="section care-needs">
                                        <div class="label">
                                            <i class="fa-regular fa-heart"></i>
                                            Care Needs:
                                        </div>

                                        <div class="sectionCarer">

                                            <div class="tags">
                                                <span>Dementia Care</span>  <span>Medication Administration</span> <span> Admin</span>
                                                <span> Administration</span>
                                                <span>Medication </span>
                                                <button class="care-more">+10 more</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="actions">
                                        <button class="view">
                                            <i class="fa-regular fa-eye"></i>
                                            View Details
                                        </button>
                                        <button class="edit">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button class="delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div> 
                            </div>
                            <div class="col-md-4">                                 
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">L</div>
                                            <div class="info">
                                                <div class="name">Logan Jones</div>
                                                <div class="role">part time</div>
                                            </div>
                                        </div>
                                        <span class="status greenShowbtn">Active</span>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i> <span>9063258701</span>
                                        </div>
                                      
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="section care-needs">
                                        <div class="label">
                                            <i class="fa-regular fa-heart"></i>
                                            Care Needs:
                                        </div>

                                        <div class="sectionCarer">

                                            <div class="tags">
                                                <span>Dementia Care</span>  <span>Medication Administration</span> <span> Admin</span>
                                                <span> Administration</span>
                                                <span>Medication </span>
                                                <button class="care-more">+10 more</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="actions">
                                        <button class="view">
                                            <i class="fa-regular fa-eye"></i>
                                            View Details
                                        </button>
                                        <button class="edit">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button class="delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>                                
                            </div>
                            <div class="col-md-4"> 
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar avatar-green">M</div>
                                            <div class="info">
                                                <div class="name">Mrs Eleanor Whitfield</div>
                                                <div class="role">self funded</div>
                                            </div>
                                        </div>
                                        <span class="status greenShowbtn">Active</span>
                                    </div>

                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i>
                                            <span>07123456789</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i>
                                            <span>Anytown,</span>
                                        </div>
                                    </div>
                                    <div class="section care-needs">
                                        <div class="label">
                                            <i class="fa-regular fa-heart"></i>
                                            Care Needs:
                                        </div>

                                        <div class="care-list">
                                            <div class="care-item">
                                            Assistance with mobility and transfers (uses wheeled Zimmer frame, requires one carer)
                                            </div>
                                            <div class="care-item">
                                            High falls risk (due to poor balance, muscle weakness, osteoporosis, cognitive impairment)
                                            </div>
                                            <div class="care-item">
                                            Full assistance with personal care (washing, dressing, continence care, toileting)
                                            </div>
                                            <button class="care-more">+10 more</button>
                                        </div>
                                    </div>
                                    <div class="actions">
                                        <button class="view">
                                            <i class="fa-regular fa-eye"></i>
                                            View Details
                                        </button>
                                        <button class="edit">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button class="delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4"> 
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">R</div>
                                            <div class="info">
                                                <div class="name">RRuby Donavan</div>
                                                <div class="role">part time</div>
                                            </div>
                                        </div>
                                        <span class="status greenShowbtn">Active</span>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i> <span>9063258701</span>
                                        </div>
                                      
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="section care-needs">
                                        <div class="label">
                                            <i class="fa-regular fa-heart"></i>
                                            Care Needs:
                                        </div>

                                        <div class="sectionCarer">

                                            <div class="tags">
                                                <span>Dementia Care</span>  <span>Medication Administration</span> <span> Admin</span>
                                                <span> Administration</span>
                                                <span>Medication </span>
                                                <button class="care-more">+10 more</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="actions">
                                        <button class="view">
                                            <i class="fa-regular fa-eye"></i>
                                            View Details
                                        </button>
                                        <button class="edit">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button class="delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div> 
                            </div>
                          
                        </div>
                    </div> <!--End off All Leaves -->

                    <div class="content" id="activeCarer">
                        <div class="row">
                            <div class="col-md-4"> 
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">L</div>
                                            <div class="info">
                                                <div class="name">Logan Jones</div>
                                                <div class="role">part time</div>
                                            </div>
                                        </div>
                                        <span class="status greenShowbtn">Active</span>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i> <span>9063258701</span>
                                        </div>
                                      
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="section care-needs">
                                        <div class="label">
                                            <i class="fa-regular fa-heart"></i>
                                            Care Needs:
                                        </div>

                                        <div class="sectionCarer">

                                            <div class="tags">
                                                <span>Dementia Care</span>  <span>Medication Administration</span> <span> Admin</span>
                                                <span> Administration</span>
                                                <span>Medication </span>
                                                <button class="care-more">+10 more</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="actions">
                                        <button class="view">
                                            <i class="fa-regular fa-eye"></i>
                                            View Details
                                        </button>
                                        <button class="edit">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button class="delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>  
                            </div>   
                        </div>
                    </div>

                    <div class="content" id="inactiveCarer">
                        <div class="leave-card">
                            <div class="leavebanktabCont">
                                <i class="fa fa-calendar-o"></i>
                                <h4>No clients found</h4>
                                <p>Add your first client to get started</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>





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
 </main>
