<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
@extends('frontEnd.layouts.master')
@section('title', 'Client')
@section('content')

    @include('frontEnd.roster.common.roster_header')

<style>
    .nci-drop-zone {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        background: #fafafa;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .nci-drop-zone:hover {
        border-color: #337ab7;
        background: #f0f7ff;
    }
    .nci-file-item {
        transition: all 0.2s ease;
    }
    .nci-file-item:hover {
        background: #f9f9f9;
    }
    .borderBtn {
        background: transparent;
        border: 1px solid #1f88b5;
        color: #1f88b5 !important;
        margin-right: 10px;
    }
    .borderBtn:hover {
        background: #1f88b5;
        color: #fff !important;
    }
    .nci-category {
        transition: all 0.2s ease;
    }
    .nci-category:hover {
        background: #fcfcfc;
    }
</style>

    <main class="page-content">
        <div class="container-fluid">

            <div class="topHeaderCont">
                <div>
                    <h1>{{ getTerm('Service User') }}s</h1>
                    <p class="header-subtitle">Manage {{ strtolower(getTerm('Service User')) }} information and care plans</p>
                </div>
                <div class="header-actions">
                    <button class="btn borderBtn" onclick="openNewClientImportModal()"><i class="fa fa-file-text"></i> Import Client</button>
                    <button class="btn addClient" onclick="childCourseData()" data-toggle="modal"
                        data-target="#addServiceUserModal"><i class="fa fa-plus"></i> Add {{ getTerm('Service User') }}</button>
                </div>
            </div>

            <div class="rota_dashboard-cards simpleCard">
                <div class="rota_dash-card blue">
                    <div class="rota_dash-left">
                        <p class="rota_title">Total {{ getTerm('Service User') }}s</p>
                        <h2 class="rota_count" id="total_clientsCount">{{ count($child) }}</h2>
                    </div>
                </div>

                <div class="rota_dash-card orangeClr">
                    <div class="rota_dash-left">
                        <p class="rota_title">Active</p>
                        <h2 class="rota_count greenText" id="active_clientsCount">{{ count($active_child_count) }}</h2>
                    </div>
                </div>

                <div class="rota_dash-card redClr">
                    <div class="rota_dash-left">
                        <p class="rota_title">Inactive</p>
                        <h2 class="rota_count" id="inactive_clientsCount">{{ count($inactive_child_count) }}</h2>
                    </div>
                </div>

            </div>

            <div class="calendarTabs leaveRequesttabs m-t-20">
                <div class="tabs">
                    <div class="input-group searchWithtabs">
                        <span class="input-group-addon btn-white"><i class="fa fa-search"></i></span>
                        <input type="text" id="clientSeacrh" class="form-control" placeholder="Username">
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
                <?php
                if (!function_exists('merge_data')) {
                    function merge_data($arr1, $arr2)
                    {
                        $array_data1 = [];
                        $array_data2 = [];
                        foreach ($arr1 as $val1) {
                            if ($val1) {
                                $array_data1[] = $val1;
                            }
                        }
                        foreach ($arr2 as $val2) {
                            $array_data2[] = $val2->title;
                        }
                        return array_merge($array_data1, $array_data2);
                    }
                }
                ?>
                <!-- TAB CONTENT -->
                <div class="tab-content carertabcontent clientCardBox">
                    <div class="content active" id="allCarerActibity">
                        <div class="row all_ClienData careRow">
                            @forelse($child as $childVal)
                                <?php
                                $preferredCarerHtml = '';
                                
                                if (!empty($childVal->carers)) {
                                    $cnames = [];
                                
                                    foreach ($childVal->carers as $row) {
                                        if (!empty($row->carers_data->name)) {
                                            $cnames[] = $row->carers_data->name;
                                        }
                                    }
                                
                                    $preferredCarerHtml = implode(', ', $cnames);
                                }
                                
                                ?>
                                <div class="col-md-4">
                                    <div class="profile-card">
                                        <div class="card-header">
                                            <div class="user">
                                                <div class="avatar">{{ strtoupper(substr($childVal->name, 0, 1)) }}</div>
                                                <div class="info">
                                                    <div class="name"><a
                                                            href="{{ url('roster/client-details/' . $childVal->id) }}">
                                                            {{ $childVal->name }}</a></div>
                                                    <div class="role">{{ $childVal->suFundingType }}</div>
                                                </div>
                                            </div>
                                            @if ($childVal->status == 1)
                                                <span class="status greenShowbtn">Active</span>
                                            @else
                                                <span class="status radShowbtn">Inactive</span>
                                            @endif
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <i class="fa-solid fa-phone"></i> <span>{{ $childVal->phone_no }}</span>
                                            </div>

                                            <div class="item">
                                                <i class="fa-solid fa-location-dot"></i>
                                                <span>{{ strlen($childVal->street) > 30 ? substr($childVal->street, 0, 30) . '...' : strip_tags($childVal->street) }}</span>
                                            </div>
                                        </div>
                                        <div class="section care-needs">
                                            <div class="label">
                                                <i class="fa-regular fa-heart"></i>
                                                Care Needs:
                                            </div>

                                            <div class="sectionCarer">

                                                <div class="tags care-list">
                                                    <?php 
                                                    $exAll=[];
                                                    if(!empty($childVal->care_needs)){
                                                        $exAll=explode(',',$childVal->care_needs);
                                                    }
                                                    $moreNeedsAll=0;
                                                    $data = merge_data($exAll,$childVal->courses);
                                                    // echo "<pre>";print_r($data[1]);die;
                                                    $moreNeedsAll=count($data)-5;
                                                    for($i=0;$i<5;$i++){ 
                                                       if(!empty($data[$i])){
                                                ?>
                                                    <span>{{ $data[$i] }}</span>
                                                    <?php }}if($moreNeedsAll > 0){?>
                                                    <button class="care-more" type="button">+{{ $moreNeedsAll }}
                                                        more</button>
                                                    <?php }?>
                                                </div>
                                            </div>
                                        </div>
                                        @if ($preferredCarerHtml)
                                            <div class="bg-blue-50 rounded5 p-3 ">
                                                <p class="fs13 blueText mb-2">Preferred Carers:</p>
                                                <h5 class="darkBlueTextp font600 fs14 my-0">{{ $preferredCarerHtml }}</h5>
                                            </div>
                                        @endif
                                        <div class="actions">
                                            <button class="view" type="button"
                                                onclick="location.href='{{ url('roster/client-details/' . $childVal->id) }}'">
                                                <i class="fa-regular fa-eye"></i>
                                                View Details
                                            </button>
                                            <button class="edit" type="button" data-toggle="modal"
                                                data-target="#addServiceUserModal" data-child_id="{{ $childVal->id }}">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <button class="delete client_delete" type="button"
                                                data-child_id="{{ $childVal->id }}">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="leave-card">
                                    <div class="leavebanktabCont">
                                        <i class="fa fa-calendar-o"></i>
                                        <h4>No {{ strtolower(getTerm('Service User')) }}s found</h4>
                                        <p>Add your first {{ strtolower(getTerm('Service User')) }} to get started</p>
                                    </div>
                                </div>
                            @endforelse

                        </div>
                    </div> <!--End off All Leaves -->

                    <div class="content" id="activeCarer">
                        <div class="row active_Clientdata">
                            @forelse($active_child_count as $activeVal)
                                <?php
                                $apreferredCarerHtml = '';
                                
                                if (!empty($activeVal->carers)) {
                                    $anames = [];
                                
                                    foreach ($activeVal->carers as $row) {
                                        if (!empty($row->carers_data->name)) {
                                            $anames[] = $row->carers_data->name;
                                        }
                                    }
                                
                                    $apreferredCarerHtml = implode(', ', $anames);
                                }
                                
                                ?>
                                <div class="col-md-4">
                                    <div class="profile-card">
                                        <div class="card-header">
                                            <div class="user">
                                                <div class="avatar">{{ strtoupper(substr($activeVal->name, 0, 1)) }}</div>
                                                <div class="info">
                                                    <div class="name"><a
                                                            href="{{ url('roster/client-details/' . $activeVal->id) }}">{{ $activeVal->name }}</a>
                                                    </div>
                                                    <div class="role">{{ $activeVal->suFundingType }}</div>
                                                </div>
                                            </div>
                                            <span class="status greenShowbtn">Active</span>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <i class="fa-solid fa-phone"></i> <span>{{ $activeVal->phone_no }}</span>
                                            </div>

                                            <div class="item">
                                                <i class="fa-solid fa-location-dot"></i>
                                                <span>{{ strlen($activeVal->street) > 30 ? substr($activeVal->street, 0, 30) . '...' : strip_tags($activeVal->street) }}</span>
                                            </div>
                                        </div>
                                        <div class="section care-needs">
                                            <div class="label">
                                                <i class="fa-regular fa-heart"></i>
                                                Care Needs:
                                            </div>

                                            <div class="sectionCarer">

                                                <div class="tags care-list">
                                                    <?php 
                                                    $exActive=[];
                                                    if(!empty($activeVal->care_needs)){
                                                        $exActive=explode(',',$activeVal->care_needs);
                                                    }
                                                    $moreNeedsActive=0;
                                                    $data_active = merge_data($exActive,$activeVal->courses);
                                                    // echo "<pre>";print_r($data_active[1]);die;
                                                    $moreNeedsActive=count($data_active)-5;
                                                    for($ai=0;$ai<5;$ai++){ 
                                                       if(!empty($data_active[$ai])){
                                                ?>
                                                    <span>{{ $data_active[$ai] }}</span>
                                                    <?php }}if($moreNeedsActive > 0){?>
                                                    <button class="care-more" type="button">+{{ $moreNeedsActive }}
                                                        more</button>
                                                    <?php }?>
                                                </div>
                                            </div>
                                        </div>
                                        @if ($apreferredCarerHtml)
                                            <div class="bg-blue-50 rounded5 p-3 ">
                                                <p class="fs13 blueText mb-2">Preferred Carers:</p>
                                                <h5 class="darkBlueTextp font600 fs14 my-0">{{ $apreferredCarerHtml }}
                                                </h5>
                                            </div>
                                        @endif
                                        <div class="actions">
                                            <button class="view"
                                                onclick="location.href='{{ url('roster/client-details/' . $activeVal->id) }}'">
                                                <i class="fa-regular fa-eye"></i>
                                                View Details
                                            </button>
                                            <button class="edit" data-toggle="modal" data-target="#addServiceUserModal"
                                                data-child_id="{{ $activeVal->id }}">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <button class="delete client_delete" type="button"
                                                data-child_id="{{ $activeVal->id }}">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="leave-card">
                                    <div class="leavebanktabCont">
                                        <i class="fa fa-calendar-o"></i>
                                        <h4>No {{ strtolower(getTerm('Service User')) }}s found</h4>
                                        <p>Add your first {{ strtolower(getTerm('Service User')) }} to get started</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="content inactive_Clientdata" id="inactiveCarer">
                        @forelse($inactive_child_count as $inactiveVal)
                            <?php
                            $ipreferredCarerHtml = '';
                            
                            if (!empty($inactiveVal->carers)) {
                                $names = [];
                            
                                foreach ($inactiveVal->carers as $row) {
                                    if (!empty($row->carers_data->name)) {
                                        $names[] = $row->carers_data->name;
                                    }
                                }
                            
                                $ipreferredCarerHtml = implode(', ', $names);
                            }
                            
                            ?>
                            <div class="col-md-4">
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">{{ strtoupper(substr($inactiveVal->name, 0, 1)) }}</div>
                                            <div class="info">
                                                <div class="name"><a
                                                        href="{{ url('roster/client-details/' . $inactiveVal->id) }}">{{ $inactiveVal->name }}</a>
                                                </div>
                                                <div class="role">{{ $inactiveVal->suFundingType }}</div>
                                            </div>
                                        </div>
                                        <span class="status radShowbtn">Inactive</span>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <i class="fa-solid fa-phone"></i> <span>{{ $inactiveVal->phone_no }}</span>
                                        </div>

                                        <div class="item">
                                            <i class="fa-solid fa-location-dot"></i>
                                            <span>{{ strlen($inactiveVal->street) > 30 ? substr($inactiveVal->street, 0, 30) . '...' : strip_tags($inactiveVal->street) }}</span>
                                        </div>
                                    </div>
                                    <div class="section care-needs">
                                        <div class="label">
                                            <i class="fa-regular fa-heart"></i>
                                            Care Needs:
                                        </div>

                                        <div class="sectionCarer">

                                            <div class="tags care-list">
                                                <?php 
                                                $ex_inactive=[];
                                                if(!empty($inactiveVal->care_needs)){
                                                    $ex_inactive=explode(',',$inactiveVal->care_needs);
                                                }
                                                $moreNeedsInactive=0;
                                                $data_inactive = merge_data($ex_inactive,$inactiveVal->courses);
                                                // echo "<pre>";print_r($data_inactive[1]);die;
                                                $moreNeedsInactive=count($data_inactive)-5;
                                                for($in=0;$in<5;$in++){ 
                                                    if(!empty($data_inactive[$in])){
                                            ?>
                                                <span>{{ $data_inactive[$in] }}</span>
                                                <?php }}if($moreNeedsInactive > 0){?>
                                                <button class="care-more" type="button">+{{ $moreNeedsInactive }}
                                                    more</button>
                                                <?php }?>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($ipreferredCarerHtml)
                                        <div class="bg-blue-50 rounded5 p-3 ">
                                            <p class="fs13 blueText mb-2">Preferred Carers:</p>
                                            <h5 class="darkBlueTextp font600 fs14 my-0">{{ $ipreferredCarerHtml }}</h5>
                                        </div>
                                    @endif
                                    <div class="actions">
                                        <button class="view"
                                            onclick="location.href='{{ url('roster/client-details/' . $inactiveVal->id) }}'">
                                            <i class="fa-regular fa-eye"></i>
                                            View Details
                                        </button>
                                        <button class="edit" data-toggle="modal" data-target="#addServiceUserModal"
                                            data-child_id="{{ $inactiveVal->id }}">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button class="delete client_delete" type="button"
                                            data-child_id="{{ $inactiveVal->id }}">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="leave-card">
                                <div class="leavebanktabCont">
                                    <i class="fa fa-calendar-o"></i>
                                    <h4>No {{ strtolower(getTerm('Service User')) }}s found</h4>
                                    <p>Add your first {{ strtolower(getTerm('Service User')) }} to get started</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>





        <!-- add Clients Modal -->
        <div class="modal fade leaveCommunStyle" id="addClientsModal" tabindex="1" role="dialog"
            aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New {{ getTerm('Service User') }}</h4>
                    </div>
                    <div class="modal-body approveLeaveModal">
                        <div class="carer-form">
                            <form>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Full Name *</label>
                                        <input type="text" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Date of Birth *</label>
                                        <input type="date" class="form-control">
                                    </div>
                                    <div class="col-md-6 m-t-10">
                                        <label>Phone *</label>
                                        <input type="text" class="form-control">
                                    </div>
                                    <div class="col-md-6 m-t-10">
                                        <label>Email *</label>
                                        <input type="email" class="form-control">
                                    </div>
                                    <div class="col-md-6  m-t-10">
                                        <label>Status</label>
                                        <select class="form-control">
                                            <option>Active</option>
                                            <option>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6  m-t-10">
                                        <label>Funding Type</label>
                                        <select class="form-control">
                                            <option>Full Time</option>
                                            <option>Part Time</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6  m-t-10">
                                        <label>Mobility</label>
                                        <select class="form-control">
                                            <option>Full Time</option>
                                            <option>Part Time</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 m-t-10">
                                        <label>Hourly Rate (£)</label>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <input type="text" placeholder="Street" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" placeholder="City" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" placeholder="Postcode" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12  m-t-10">
                                        <label>Notes (Optional)</label>
                                        <textarea class="form-control" rows="3" placeholder="Add any notes for the staff member..."
                                            name="approve_note"></textarea>
                                    </div>
                                </div>
                                <div class="qualifications m-t-10">
                                    <h4>Qualifications</h4>
                                    <div class="checkbox-grid">
                                        <label><input type="checkbox"> NVQ Level 2 Health & Social Care</label>
                                        <label><input type="checkbox"> NVQ Level 3 Health & Social Care</label>
                                        <label><input type="checkbox"> First Aid Certificate</label>
                                        <label><input type="checkbox"> Dementia Care Specialist</label>
                                        <label><input type="checkbox"> Medication Administration</label>
                                        <label><input type="checkbox"> Care Certificate</label>
                                        <label><input type="checkbox"> Dementia Care</label>
                                        <label><input type="checkbox"> First Aid</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>Medical Notes</label>
                                        <textarea class="form-control" rows="4" placeholder="Important Medical information" name="approve_note"></textarea>
                                    </div>
                                    <div class="col-md-12 m-t-10">
                                        <label>Emergency Contact</label>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <input type="text" placeholder="Name" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" placeholder="Phone" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" placeholder="Relationship" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="actions">
                                    <button type="button" class="cancel">Cancel</button>
                                    <button type="submit" class="submit">Create {{ getTerm('Service User') }}</button>
                                </div>

                            </form>
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
        <script>
            $(document).on('click', '.edit', function() {
                let childId = $(this).data('child_id');
                $("#ClientModalTitle").text("Edit {{ getTerm('Service User') }}");
                $("#care_needs").val('');
                $("#tags").html('');
                $.ajax({
                    url: "{{ url('/roster/child-courses') }}/" + childId,
                    type: "GET",
                    success: function(res) {
                        console.log(res);
                        var userData = res.data;
                        if (userData.image) {

                            var $fileupload = $('.fileupload');
                            var $preview = $fileupload.find('.fileupload-preview');
                            var imgUrl = "{{ url('public/images/serviceUserProfileImages') }}";
                            $preview.html(
                                '<img src="' + imgUrl + '/' + userData.image + '" ' +
                                'style="max-height:150px; max-width:200px;" />'
                            );
                            $fileupload.removeClass('fileupload-new').addClass('fileupload-exists');
                        }
                        $("#suClientId").val(userData.id);
                        $("#su_name").val(userData.name);
                        $("#su_user_name").val(userData.user_name);
                        $("#date_of_birth").val(userData.date_of_birth);
                        $("#phone_no").val(userData.phone_no);
                        $("#hair_and_eyes").val(userData.hair_and_eyes);
                        $("#markings").val(userData.markings);
                        $("#start_date").val(userData.start_date);
                        $("#end_date").val(userData.end_date);
                        $("#suEmail").val(userData.email);
                        $("#department").val(userData.department);
                        // $("#weekly_rate").val(userData.weekly_rate);
                        // $("#subs").val(userData.subs);
                        // $("#extra").val(userData.extra);
                        $("#local_authority").val(userData.local_authority);
                        $("#admission_number").val(userData.admission_number);
                        $("#suStatus").val(userData.status);
                        $("#suMobility").val(userData.suMobility);
                        $("#suFundingType").val(userData.suFundingType);
                        $("#section").val(userData.section);
                        $("#ethnicity_id").val(userData.ethnicity_id);
                        $("#short_description").val(userData.short_description);
                        $("#street").val(userData.street);
                        $("#city").val(userData.city);
                        $("#postcode").val(userData.postcode);
                        // $("#care_needs").val(userData.care_needs);
                        $("#medical_notes").val(userData.medical_notes);
                        $("#em_name").val(userData.em_name);
                        $("#em_phone").val(userData.em_phone);
                        $("#relationship").val(userData.relationship);
                        $("#height_unit").val(userData.height_unit);
                        $("#height_dropdown").val(userData.height_ft);
                        $("#height_in_dropdown").val(userData.height_in);
                        $("#weight_unit").val(userData.weight_unit);
                        $("#weight_dropdown").val(userData.weight);
                        // let selectedCourses = res.data.courses.map(c => c.coursenumber);
                        let selectedCourses = res.data.courses;
                        let selectedCarers = res.data.carers;
                        childCourseData(null, function() {
                            autoCheckCourses(selectedCourses);
                            autoCheckCarers(selectedCarers);
                        });

                        let other_careNeeds = userData.care_needs;
                        if (other_careNeeds) {
                            $("#other_careNeeds").show();
                            let tagsArray = other_careNeeds.split(',');

                            tagsArray.forEach(function(tagText) {

                                tagText = tagText.trim();

                                if (tagText !== '') {

                                    let tag = document.createElement('li');
                                    tag.innerText = tagText;
                                    tag.innerHTML +=
                                        '<button class="delete-button" type="button"><i class="fa fa-times"></i></button>';

                                    document.getElementById('tags').appendChild(tag);
                                }
                            });
                            $("#care_needs").val(other_careNeeds);
                        }
                    }
                });
            });

            function autoCheckCourses(selectedCourses) {

                $('.course_qualifications').each(function() {

                    let checkbox = $(this);
                    let courseNumber = checkbox.data('coursenumber');
                    let matched = selectedCourses.find(c => c.coursenumber == courseNumber);

                    if (matched) {

                        checkbox.prop('checked', true);

                        let box = checkbox.closest('.course-box');
                        box.find('[data-name]').each(function() {
                            $(this).attr('name', $(this).data('name'));
                        });

                        box.find('.table_id_input').val(matched.id);
                    }
                });
            }
        </script>
        <script>
            $(document).on('click', '.client_delete', function() {
                if (confirm("Are you sure to delete?")) {
                    var id = $(this).data('child_id');
                    $.ajax({
                        url: "{{ url('/roster/client-delete') }}",
                        type: "post",
                        data: {
                            id: id,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            console.log(res);
                            if (typeof isAuthenticated === "function") {
                                if (isAuthenticated(res) == false) {
                                    return false;
                                }
                            }
                            if (res.success == false) {
                                alert(res.errors);
                            } else {
                                location.reload();
                            }
                        }
                    });
                }
            });
            $(document).on('keyup', '#clientSeacrh', function() {
                var clientSearchText = $(this).val();
                var tab_data = $('.tab.active').data('tab');
                $.ajax({
                    url: "{{ url('/roster/client-search') }}",
                    type: "post",
                    data: {
                        user_name: clientSearchText,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        console.log(res);
                        if (res.success == false) {
                            alert(res.errors);
                        } else {
                            var all_data = res.allHtml_data;
                            var all_active_data = res.activeHtml_data;
                            var all_inactive_data = res.InactiveHtml_data;
                            var no_data = `<div class="leave-card">
                                <div class="leavebanktabCont">
                                    <i class="fa fa-calendar-o"></i>
                                    <h4>No {{ strtolower(getTerm('Service User')) }}s found</h4>
                                    <p>Add your first {{ strtolower(getTerm('Service User')) }} to get started</p>
                                </div>
                            </div>`;
                            $("#total_clientsCount").html(res.query_AllSqlData.length);
                            $("#active_clientsCount").html(res.query_ActiveSqlData.length);
                            $("#inactive_clientsCount").html(res.query_InactiveSql.length);
                            if (all_data.length == 0) {
                                $(".all_ClienData").html(no_data);
                            } else {
                                $(".all_ClienData").html(all_data);
                            }
                            if (all_active_data.length == 0) {
                                $('.active_Clientdata').html(no_data);
                            } else {
                                $('.active_Clientdata').html(all_active_data);
                            }
                            if (all_inactive_data.length == 0) {
                                $('.inactive_Clientdata').html(no_data);
                            } else {
                                $('.inactive_Clientdata').html(all_inactive_data);
                            }
                        }
                    }
                });
                // }
            });
            $(document).on('click', '.addClient', function() {
                $("#care_needs").val('');
                $("#tags").html('');
                $("#other_careNeeds").hide();
                $("#deleted_course_ids").val('');
            });

            // $(document).on('click','.tab', function(){
            //     $("#clientSeacrh").val('');
            // })
            function redirectLocation(id) {
                var url = '{{ url('roster/client-details/') }}/' + id;
                window.location.href = url;
            }
        </script>


    
<!-- AI New Client Import Modal -->
<div class="modal fade leaveCommunStyle" id="aiNewClientImportModal" tabindex="-1" role="dialog" aria-labelledby="aiNewClientImportLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="aiNewClientImportLabel">Import New Client from Documents</h4>
            </div>
            <div class="modal-body" id="aiNewClientImportBody">
                <p class="text-muted">Upload client documentation to automatically create a new client record with care data.</p>

                <!-- Step 1: Upload -->
                <div id="nciStep1">
                    <div id="nciDropZone" class="nci-drop-zone">
                        <i class="fa fa-cloud-upload" style="font-size:36px;color:#999;"></i>
                        <p style="margin:10px 0 5px;">Drag & drop files here or click to browse</p>
                        <p class="text-muted" style="font-size:12px;">PDF or Word (.docx) &bull; Max 10MB each &bull; Multiple files allowed</p>
                        <input type="file" id="nciFileInput" multiple accept=".pdf,.docx,.doc" style="display:none;">
                    </div>

                    <div id="nciFileList" style="margin-top:15px;display:none;">
                        <h5>Uploaded Files</h5>
                        <div id="nciFileItems"></div>
                    </div>

                    <div style="margin-top:15px;">
                        <button class="btn btn-primary" id="nciExtractBtn" style="display:none;" onclick="uploadAndExtract()">
                            <i class="fa fa-magic"></i> Extract Client & Care Data
                        </button>
                    </div>
                </div>

                <!-- Step 2: Review -->
                <div id="nciStep2" style="display:none;"></div>

                <!-- Step 3: Success -->
                <div id="nciStep3" style="display:none;"></div>

                <!-- Loading overlay -->
                <div id="nciLoading" style="display:none;text-align:center;padding:40px;">
                    <i class="fa fa-spinner fa-spin" style="font-size:32px;color:#337ab7;"></i>
                    <p id="nciLoadingText" style="margin-top:15px;color:#666;"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>var nciBaseUrl = '{{ url("/roster") }}'; var nciCsrfToken = '{{ csrf_token() }}';</script>
<script src="{{ url('public/js/roster/ai-new-client-import.js') }}"></script>

@endsection
</main>
