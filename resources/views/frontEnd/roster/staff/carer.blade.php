<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
@extends('frontEnd.layouts.master')
@section('title','Carer')
@section('content')


@include('frontEnd.roster.common.roster_header')





 <main class="page-content">
        <div class="container-fluid">

            <div class="topHeaderCont">
                <div>
                    <h1>Carers</h1>
                    <p class="header-subtitle">Manage your care team</p>
                </div>
                <div class="header-actions">
                    <button class="btn" data-toggle="modal" data-target="#addLeaveModal"><i class="fa fa-plus"></i> Add Carer</button>
                </div> 
            </div>

            <div class="rota_dashboard-cards simpleCard">
                <div class="rota_dash-card blue">
                    <div class="rota_dash-left">
                        <p class="rota_title">Total Carers</p>
                        <h2 class="rota_count">01</h2>
                    </div>
                </div>

                <div class="rota_dash-card orangeClr">
                    <div class="rota_dash-left">
                        <p class="rota_title">Active</p>
                        <h2 class="rota_count greenText">12</h2>
                    </div>
                </div>

                <div class="rota_dash-card green">
                    <div class="rota_dash-left">
                        <p class="rota_title">On Leave</p>
                        <h2 class="rota_count orangeText">22</h2>
                    </div>
                </div>

                <div class="rota_dash-card redClr">
                    <div class="rota_dash-left">
                        <p class="rota_title">Inactive</p>
                        <h2 class="rota_count">1</h2>
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

                    <button class="tab" data-tab="onLeaveCarer">
                        On Leave 
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
                                            <div class="avatar">M</div>
                                            <div class="info">
                                                <div class="name">Mick</div>
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
                                            <i class="fa-regular fa-envelope"></i> <span>mobappssolutions131@gmail.com</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="sectionCarer">
                                        <div class="label">Qualifications:</div>
                                        <div class="tags">
                                            <span>Dementia Care</span>  <span>Medication Administration</span>
                                        </div>
                                    </div>
                                    <div class="rate">
                                        <div class="label">Hourly Rate</div>
                                        <div class="amount">£15.00</div>
                                    </div>
                                    <div class="supervision">
                                        <i class="fa-regular fa-clipboard"></i> <span>Supervision: No supervision</span>
                                    </div>
                                    <div class="actions">
                                        <button class="edit"> <i class="fa-regular fa-pen-to-square"></i>  Edit </button>
                                        <button class="delete"> <i class="fa-regular fa-trash-can"></i> </button>
                                    </div>
                                </div>                                
                            </div>
                            <div class="col-md-4"> 
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">M</div>
                                            <div class="info">
                                                <div class="name">Mick</div>
                                                <div class="role">part time</div>
                                            </div>
                                        </div>
                                        <span class="status inactive">inactive</span>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i> <span>9063258701</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-regular fa-envelope"></i> <span>mobappssolutions131@gmail.com</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="sectionCarer">
                                        <div class="label">Qualifications:</div>
                                        <div class="tags">
                                            <span>Dementia Care</span>  <span>Medication Administration</span>
                                        </div>
                                    </div>
                                    <div class="rate">
                                        <div class="label">Hourly Rate</div>
                                        <div class="amount">£15.00</div>
                                    </div>
                                    <div class="supervision">
                                        <i class="fa-regular fa-clipboard"></i> <span>Supervision: No supervision</span>
                                    </div>
                                    <div class="actions">
                                        <button class="edit"> <i class="fa-regular fa-pen-to-square"></i>  Edit </button>
                                        <button class="delete"> <i class="fa-regular fa-trash-can"></i> </button>
                                    </div>
                                </div>                              
                            </div>
                            <div class="col-md-4"> 
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">M</div>
                                            <div class="info">
                                                <div class="name">Mick</div>
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
                                            <i class="fa-regular fa-envelope"></i> <span>mobappssolutions131@gmail.com</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="sectionCarer">
                                        <div class="label">Qualifications:</div>
                                        <div class="tags">
                                            <span>Dementia Care</span>  <span>Medication Administration</span>
                                        </div>
                                    </div>
                                    <div class="rate">
                                        <div class="label">Hourly Rate</div>
                                        <div class="amount">£15.00</div>
                                    </div>
                                    <div class="supervision">
                                        <i class="fa-regular fa-clipboard"></i> <span>Supervision: No supervision</span>
                                    </div>
                                    <div class="actions">
                                        <button class="edit"> <i class="fa-regular fa-pen-to-square"></i>  Edit </button>
                                        <button class="delete"> <i class="fa-regular fa-trash-can"></i> </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4"> 
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">M</div>
                                            <div class="info">
                                                <div class="name">Mick</div>
                                                <div class="role">part time</div>
                                            </div>
                                        </div>
                                        <span class="status inactive">inactive</span>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i> <span>9063258701</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-regular fa-envelope"></i> <span>mobappssolutions131@gmail.com</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="sectionCarer">
                                        <div class="label">Qualifications:</div>
                                        <div class="tags">
                                            <span>Dementia Care</span>  <span>Medication Administration</span>
                                        </div>
                                    </div>
                                    <div class="rate">
                                        <div class="label">Hourly Rate</div>
                                        <div class="amount">£15.00</div>
                                    </div>
                                    <div class="supervision">
                                        <i class="fa-regular fa-clipboard"></i> <span>Supervision: No supervision</span>
                                    </div>
                                    <div class="actions">
                                        <button class="edit"> <i class="fa-regular fa-pen-to-square"></i>  Edit </button>
                                        <button class="delete"> <i class="fa-regular fa-trash-can"></i> </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4"> 
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">M</div>
                                            <div class="info">
                                                <div class="name">Mick</div>
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
                                            <i class="fa-regular fa-envelope"></i> <span>mobappssolutions131@gmail.com</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="sectionCarer">
                                        <div class="label">Qualifications:</div>
                                        <div class="tags">
                                            <span>Dementia Care</span>  <span>Medication Administration</span>
                                        </div>
                                    </div>
                                    <div class="rate">
                                        <div class="label">Hourly Rate</div>
                                        <div class="amount">£15.00</div>
                                    </div>
                                    <div class="supervision">
                                        <i class="fa-regular fa-clipboard"></i> <span>Supervision: No supervision</span>
                                    </div>
                                    <div class="actions">
                                        <button class="edit"> <i class="fa-regular fa-pen-to-square"></i>  Edit </button>
                                        <button class="delete"> <i class="fa-regular fa-trash-can"></i> </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">                                 
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">M</div>
                                            <div class="info">
                                                <div class="name">Mick</div>
                                                <div class="role">part time</div>
                                            </div>
                                        </div>
                                        <span class="status inactive">inactive</span>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i> <span>9063258701</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-regular fa-envelope"></i> <span>mobappssolutions131@gmail.com</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="sectionCarer">
                                        <div class="label">Qualifications:</div>
                                        <div class="tags">
                                            <span>Dementia Care</span>  <span>Medication Administration</span>
                                        </div>
                                    </div>
                                    <div class="rate">
                                        <div class="label">Hourly Rate</div>
                                        <div class="amount">£15.00</div>
                                    </div>
                                    <div class="supervision">
                                        <i class="fa-regular fa-clipboard"></i> <span>Supervision: No supervision</span>
                                    </div>
                                    <div class="actions">
                                        <button class="edit"> <i class="fa-regular fa-pen-to-square"></i>  Edit </button>
                                        <button class="delete"> <i class="fa-regular fa-trash-can"></i> </button>
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
                                            <div class="avatar">M</div>
                                            <div class="info">
                                                <div class="name">Mick</div>
                                                <div class="role">part time</div>
                                            </div>
                                        </div>
                                        <span class="status inactive">inactive</span>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i> <span>9063258701</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-regular fa-envelope"></i> <span>mobappssolutions131@gmail.com</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="sectionCarer">
                                        <div class="label">Qualifications:</div>
                                        <div class="tags">
                                            <span>Dementia Care</span>  <span>Medication Administration</span>
                                        </div>
                                    </div>
                                    <div class="rate">
                                        <div class="label">Hourly Rate</div>
                                        <div class="amount">£15.00</div>
                                    </div>
                                    <div class="supervision">
                                        <i class="fa-regular fa-clipboard"></i> <span>Supervision: No supervision</span>
                                    </div>
                                    <div class="actions">
                                        <button class="edit"> <i class="fa-regular fa-pen-to-square"></i>  Edit </button>
                                        <button class="delete"> <i class="fa-regular fa-trash-can"></i> </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4"> 
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">M</div>
                                            <div class="info">
                                                <div class="name">Mick</div>
                                                <div class="role">part time</div>
                                            </div>
                                        </div>
                                        <span class="status inactive">inactive</span>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i> <span>9063258701</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-regular fa-envelope"></i> <span>mobappssolutions131@gmail.com</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="sectionCarer">
                                        <div class="label">Qualifications:</div>
                                        <div class="tags">
                                            <span>Dementia Care</span>  <span>Medication Administration</span>
                                        </div>
                                    </div>
                                    <div class="rate">
                                        <div class="label">Hourly Rate</div>
                                        <div class="amount">£15.00</div>
                                    </div>
                                    <div class="supervision">
                                        <i class="fa-regular fa-clipboard"></i> <span>Supervision: No supervision</span>
                                    </div>
                                    <div class="actions">
                                        <button class="edit"> <i class="fa-regular fa-pen-to-square"></i>  Edit </button>
                                        <button class="delete"> <i class="fa-regular fa-trash-can"></i> </button>
                                    </div>
                                </div>
                            </div>   
                        </div>
                    </div>

                    <div class="content" id="onLeaveCarer">
                         <div class="row">
                            <div class="col-md-4"> 
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">M</div>
                                            <div class="info">
                                                <div class="name">Mick</div>
                                                <div class="role">part time</div>
                                            </div>
                                        </div>
                                        <span class="status inactive">inactive</span>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i> <span>9063258701</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-regular fa-envelope"></i> <span>mobappssolutions131@gmail.com</span>
                                        </div>
                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i> <span>Liverpool</span>
                                        </div>
                                    </div>
                                    <div class="sectionCarer">
                                        <div class="label">Qualifications:</div>
                                        <div class="tags">
                                            <span>Dementia Care</span>  <span>Medication Administration</span>
                                        </div>
                                    </div>
                                    <div class="rate">
                                        <div class="label">Hourly Rate</div>
                                        <div class="amount">£15.00</div>
                                    </div>
                                    <div class="supervision">
                                        <i class="fa-regular fa-clipboard"></i> <span>Supervision: No supervision</span>
                                    </div>
                                    <div class="actions">
                                        <button class="edit"> <i class="fa-regular fa-pen-to-square"></i>  Edit </button>
                                        <button class="delete"> <i class="fa-regular fa-trash-can"></i> </button>
                                    </div>
                                </div>
                            </div> 
                        </div>
                    </div>

                    <div class="content" id="inactiveCarer">
                        <div class="leave-card">
                            <div class="leavebanktabCont">
                                <i class="fa fa-calendar-o"></i>
                                <h4>No carers found</h4>
                                <p>Add your first carer to get started</p>
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






