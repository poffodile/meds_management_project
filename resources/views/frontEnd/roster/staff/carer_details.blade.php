@extends('frontEnd.layouts.master')
@section('title', 'Carer')
@section('content')

<style>
/* Tabs */

</style>
@include('frontEnd.roster.common.roster_header')

<main class="page-content empoyeeHeader">
     <div class="topHeaderCont">
        <div>
            <h1>Employee: Mick</h1>
            <p class="header-subtitle"><span>Inactive</span> <span>Part Time</span> mobappssolutions131@gmail.com</p>
        </div>
        <div class="header-actions">
            <button class="btn"><i class='bx bx-calendar'></i> View Schedule</button>
            <button class="btn"><i class='bx bx-calendar'></i> Planning</button>
            <button class="btn"><i class='bx  bx-user-x'></i>  Terminate</button>
            <button class="btn"><i class='bx  bx-history'></i>  Audit Log</button>
        </div> 
    </div>
    <div class="container-fluid">

       <div class="calendarTabs leaveRequesttabs employeeDetailsTabs  m-t-20">
            <div class="tabs p-1 ">
                <button class="tab active" data-tab="generalTab">
                    General
                </button>
                <button class="tab" data-tab="availabilityTab">
                    Availability
                </button>
                <button class="tab" data-tab="trainingQualificationsTab">
                    Training & Qualifications <span class="tabNumber">2</span>
                </button>
                <button class="tab" data-tab="supervisionsTab">
                    Supervisions
                </button>
                <button class="tab" data-tab="shiftsTab">
                    Shifts
                </button>
                <button class="tab" data-tab="documentsTab">
                    Documents
                </button>
                <button class="tab" data-tab="notesTab">
                    Notes
                </button>
            </div>

            <!-- TAB CONTENT -->
            <div class="tab-content carertabcontent">
                <div class="content active" id="generalTab">
                    <div class="sectionWhiteBgAllUse">
                        <div class="profile-details-card">
                            <div class="section two-col">
                                <div>
                                    <h3>Personal Information</h3>
                                    <div class="item">
                                        <span class="label">Full Name</span>
                                        <span class="value">Mick</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Email</span>
                                        <span class="value">mobappssolutions131@gmail.com</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Phone</span>
                                        <span class="value">9063258701</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Address</span>
                                        <span class="value">
                                        Aries House, 60 Garmoyle Road,, Liverpool L15 3JH
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <h3>Employment Details</h3>
                                    <div class="item">
                                        <span class="label">Employment Type</span>
                                        <span class="value">Part Time</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Hourly Rate</span>
                                        <span class="value">£15.00/hr</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Status</span>
                                        <span class="value">Inactive</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Overtime Available</span>
                                        <span class="value">No</span>
                                    </div>
                                </div>
                            </div>

                            <div class="divider"></div>

                            <div class="section">
                                <h3>Emergency Contact</h3>
                                <div class="three-col">
                                    <div class="item">
                                        <span class="label">Name</span>
                                        <span class="value">906320120</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Phone</span>
                                        <span class="value">906325870</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Relationship</span>
                                        <span class="value">Brother</span>
                                    </div>
                                </div>
                            </div>

                            <div class="divider"></div>

                            <div class="section">
                                <h3>DBS Information</h3>
                                <div class="item">
                                    <span class="label">DBS Expiry</span>
                                    <span class="value">November 19th, 2025</span>
                                </div>
                            </div>

                        </div>

                    </div>
                </div> 

                <div class="content" id="availabilityTab">    
                    
                    <div class="availabilityTabs">
                        <!-- TAB HEADER -->
                        <div class="availabilityTabs__nav">
                            <button class="availabilityTabs__tab active" data-target="workHoursPanel">
                                Working Hours
                            </button>
                            <button class="availabilityTabs__tab" data-target="unavailabilityPanel">
                                Unavailability
                            </button>
                            <button class="availabilityTabs__tab" data-target="summaryPanel">
                                Summary
                            </button>
                        </div>

                        <!-- TAB CONTENT -->
                        <div class="availabilityTabs__content">

                            <!-- WORKING HOURS -->
                            <div class="availabilityTabs__panel active" id="workHoursPanel">
                                <div class="workHoursCard">

                                    <div class="workHoursHeader">
                                        <div class="title"><i class='bx  bx-history'></i>  Working Hours</div>
                                        <div class="actions">
                                            <span class="badge">8.0 hrs/week</span>
                                            <button class="btn"> <i class='bx  bx-copy'></i>  Apply Mon to Weekdays</button>
                                            <button class="btn-outline"><i class='bx  bx-rotate-ccw'></i>  Reset</button>
                                        </div>
                                    </div>

                                    <div class="schedulePattern">
                                        <label>Schedule Pattern</label>
                                        <select class="form-control">
                                            <option>Standard Weekly Pattern</option>
                                            <option>Alternate Weeks</option>
                                            <option>Choose Specific Dates (next 60 days)</option>
                                        </select>
                                    </div>

                                    <div class="dayRow active">
                                        <span class="day">Monday</span>
                                        <label class="switch">
                                            <input type="checkbox" checked>
                                            <span class="slider"></span>
                                        </label>
                                        <input type="time" value="09:00" class="dayTime form-control">
                                        <span>to</span>
                                        <input type="time" value="17:00" class="dayTime form-control">
                                        <span class="hours">8.0 hrs</span>
                                    </div>

                                    <div class="dayRow">
                                        <span class="day">Tuesday</span>
                                        <label class="switch">
                                            <input type="checkbox">
                                            <span class="slider"></span>
                                        </label>
                                        <span class="notWorking">Not working</span>
                                    </div>

                                    <div class="dayRow">
                                        <span class="day">Wednesday</span>
                                        <label class="switch">
                                            <input type="checkbox">
                                            <span class="slider"></span>
                                        </label>
                                        <span class="notWorking">Not working</span>
                                    </div>

                                    <div class="dayRow">
                                        <span class="day">Thursday</span>
                                        <label class="switch">
                                            <input type="checkbox">
                                            <span class="slider"></span>
                                        </label>
                                        <span class="notWorking">Not working</span>
                                    </div>

                                    <div class="dayRow">
                                        <span class="day">Friday</span>
                                        <label class="switch">
                                            <input type="checkbox">
                                            <span class="slider"></span>
                                        </label>
                                        <span class="notWorking">Not working</span>
                                    </div>

                                    <div class="dayRow weekend">
                                        <span class="day">Saturday</span>
                                        <label class="switch">
                                            <input type="checkbox">
                                            <span class="slider"></span>
                                        </label>
                                        <span class="notWorking">Not working</span>
                                    </div>

                                    <div class="dayRow weekend">
                                        <span class="day">Sunday</span>
                                        <label class="switch">
                                            <input type="checkbox">
                                            <span class="slider"></span>
                                        </label>
                                        <span class="notWorking">Not working</span>
                                    </div>

                               
                                       
                                        
                                    <div class="modal-footer m-t-0"> 
                                        <button class="btn allBtnUseColor validation_staff" type="submit"> Save Working Hours </button>
                                    </div>

                                </div>
                            </div>

                            <!-- UNAVAILABILITY -->
                            <div class="availabilityTabs__panel" id="unavailabilityPanel">
                               <div class="leave-card">
                                    <div class="workHoursHeader">
                                        <div class="title"><i class='bx  bx-calendar-alt-2'></i>   Unavailability Periods</div>
                                        <div class="actions">
                                            <button class="allbuttonDarkClr"> <i class='bx  bx-plus'></i>  Add Unavailability</button>
                                        </div>
                                    </div>
                                    <div class="leavebanktabCont">
                                        <i class="fa fa-calendar-o"></i>
                                        <h4>No clients found</h4>
                                        <p>Add your first client to get started</p>
                                    </div>
                                </div>
                            </div>

                            <!-- SUMMARY -->
                            <div class="availabilityTabs__panel" id="summaryPanel">
                                 <div class="">

                                    <div class="workHoursHeader">
                                       
                                        <div class="actions">
                                             <button class="greenShowbtn"><i class='bx  bx-history'></i>  0 days • 0h/week</button>
                                            <span class="badge">No working hours set</span>
                                           
                                        </div>
                                    </div>
                                 </div>
                            </div>

                        </div>
                    </div>

                
                     <!-- <div class="calendarTabs leaveRequesttabs employeeDetailsTabs  m-t-20">
                        <div class="tabs p-1 ">
                            <button class="tab active" data-tab="workingHoursTab">
                                Working Hours
                            </button>
                            <button class="tab" data-tab="unavailabilityTab">
                                Unavailability

                            </button>
                            <button class="tab" data-tab="summaryTab">
                                Summary
                            </button>
                      
                        </div>

                        <div class="tab-content carertabcontent">
                            <div class="content active" id="workingHoursTab">
                                <div class="sectionWhiteBgAllUse">
                                       <h1> Working Hours</h1>
                                </div>
                            </div> 

                            <div class="content" id="unavailabilityTab">
                                <h1>Unavailability</h1>
                            </div>
                            <div class="content" id="summaryTab">
                                <h1>Summary</h1>
                            </div>
                                
                        </div>
                    </div> -->


                </div>


                <div class="content" id="trainingQualificationsTab">
                   <div class="leave-card">
                        <div class="workHoursHeader">
                            <div class="title">Qualifications</div>
                            <div class="actions">
                                <button class="allbuttonDarkClr"> <i class='bx  bx-education'></i>  Add Qualification</button>
                            </div>
                        </div>
                        
                         <div class="">
                            <div class="certifiedList">
                                <span class="">Certified to administer medications</span>                                
                                <span class="roundBtntag greenShowbtn"> Certified </span>                                
                            </div>
                            <div class="certifiedList">
                                <span class="">Certified to administer medications</span>                                
                                <span class="roundBtntag greenShowbtn"> Certified </span>                                
                            </div>
                            <div class="certifiedList">
                                <span class="">Certified to administer medications</span>                                
                                <span class="roundBtntag greenShowbtn"> Certified </span>                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content" id="supervisionsTab">
                    <div class="leave-card">
                        <div class="workHoursHeader">
                            <div class="title"><i class="bx  bx-clipboard-detail"></i> Supervision History</div>
                        </div>   
                        <div class="leavebanktabCont">
                            <i class="bx  bx-clipboard-detail"></i>
                            <p>No supervision records</p>
                        </div>
                    </div>
                </div>
                <div class="content" id="shiftsTab">
                    <div class="leave-card">
                        <div class="workHoursHeader">
                            <div class="title">Recent Shifts</div>
                            <div class="actions">
                                <button class="allbuttonDarkClr"> <i class='bx bx-calendar'></i> View All</button>
                            </div>
                        </div>
                        <!-- <div class="leavebanktabCont">
                            <p>No shifts recorded</p>
                        </div> -->
                        <div class="">
                            <div class="certifiedList">
                                <span class="">
                                    <div>Wed, Dec 10, 2025</div>
                                    <small>09:00 - 17:00 • 8hrs</small>
                                </span>                                
                                <span class="roundBtntag greenShowbtn"> scheduled </span>                                
                            </div>
                            <div class="certifiedList">
                                <span class="">
                                    <div>Wed, Dec 10, 2025</div>
                                    <small>09:00 - 17:00 • 8hrs</small>
                                </span>                                  
                                <span class="roundBtntag inactive"> published </span>                                
                            </div>
                            <div class="certifiedList">
                                <span class="">
                                    <div>Wed, Dec 10, 2025</div>
                                    <small>09:00 - 17:00 • 8hrs</small>
                                </span>                                  
                                <span class="roundBtntag greenShowbtn"> Certified </span>                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content" id="documentsTab">
                    <div class="leave-card">
                        <div class="workHoursHeader">
                            <div class="title">Documents</div>
                            <div class="actions">
                                <button class="allbuttonDarkClr"> <i class='bx bx-file-detail'></i> Upload Document</button>
                            </div>
                        </div>
                        <div class="leavebanktabCont">
                            <p>No documents uploaded</p>
                        </div>
                    </div>
                </div>
                <div class="content" id="notesTab">
                    <div class="leave-card">
                        <div class="workHoursHeader">
                            <div class="title">Notes</div>
                            <div class="actions">
                                <button class="allbuttonDarkClr"> <i class='bx bx-file-detail'></i> Add Note</button>
                            </div>
                        </div>
                        <div class="leavebanktabCont">
                            <p>No notes recorded </p>
                        </div>
                    </div>
                </div>            
            </div>
            <!-- END TAB CONTENT -->
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

    <script>
        document.querySelectorAll(".availabilityTabs").forEach(wrapper => {

            const tabs = wrapper.querySelectorAll(".availabilityTabs__tab");
            const panels = wrapper.querySelectorAll(".availabilityTabs__panel");

            tabs.forEach(tab => {
                tab.addEventListener("click", () => {

                    tabs.forEach(t => t.classList.remove("active"));
                    panels.forEach(p => p.classList.remove("active"));

                    tab.classList.add("active");
                    wrapper
                        .querySelector("#" + tab.dataset.target)
                        .classList.add("active");
                });
            });

        });
        </script>

@endsection
</main>


