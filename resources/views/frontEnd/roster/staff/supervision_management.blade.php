@extends('frontEnd.layouts.master')
@section('title', 'Staff Supervisions')
@section('content')
    @include('frontEnd.roster.common.roster_header')
    <main class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="staffHeaderp">
                        <div>
                            <h1 class="mainTitlep"> Staff Supervisions</h1>
                            <p class="header-subtitle mb-0"> Manage and schedule staff supervision sessions </p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <button class="borderBtn scheduleSuperModalBtn" data-toggle="modal"
                                    data-target="#scheduleSuperModal"><i class=" f18 bx bx-calendar-plus me-2"></i>
                                    Schedule Supervision</button>
                            </div>
                            <div>
                                <button class="bgBtn recordSuperModalBtn" type="button" data-toggle="modal"
                                    data-target="#recordSuperModal"><i class="f18 bx  bx-plus me-2"></i> Record
                                    Supervision</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt20">
                <div class="col-lg-3">
                    <div class="quick_action-card bgWhite p-4">
                        <div class="d-flex gap-4 align-items-center">
                            <div class="bgIconStaffT buleBadges">
                                <i class="bx bx-group blueText"></i>
                            </div>
                            <div>
                                <h2 class="cardBoldTitle mb-1" id="totalRecord">0</h2>
                                <p class="muteText">Total Records</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="quick_action-card bgWhite p-4">
                        <div class="d-flex gap-4 align-items-center">
                            <div class="bgIconStaffT greenbadges">
                                <i class="bx bx-check-circle"></i>
                            </div>
                            <div>
                                <h2 class="cardBoldTitle mb-1" id="totalOnTrack">0</h2>
                                <p class="muteText">On Track</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="quick_action-card bgWhite p-4">
                        <div class="d-flex gap-4 align-items-center">
                            <div class="bgIconStaffT orangeBages">
                                <i class="bx bx-clock"></i>
                            </div>
                            <div>
                                <h2 class="cardBoldTitle mb-1" id="totalDueSoon">0</h2>
                                <p class="muteText">Due Soon</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="quick_action-card bgWhite p-4">
                        <div class="d-flex gap-4 align-items-center">
                            <div class="bgIconStaffT redbadges">
                                <i class="bx  bx-alert-triangle"></i>
                            </div>
                            <div>
                                <h2 class="cardBoldTitle mb-1 redtext" id="totalOverdue">0</h2>
                                <p class="muteText">Overdue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt20">
                <div class="col-lg-12">
                    <div class="emergencyMain  bg-red-50 rouded8 p-4 urReqSec d-none" id="overdue_text_wrapper">
                        <div class="d-flex gap-4 align-items-center">
                            <i class="bx  bx-alert-triangle f20 darkRedText"></i>
                            <div>
                                <h6 class="h6Head darkRedText mb-2">Overdue Supervisions</h6>
                                <p class="mb-0 fs13 darkRedText" id="overdue_text"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt20">
                <div class="col-lg-3">
                    <div class="input-group searchWithtabs" style="width:100%">
                        <span class="input-group-addon btn-white"><i class="fa fa-search"></i></span>
                        <input type="text" class="form-control" id="searchText" placeholder="Search by staff name...">
                    </div>
                </div>
                <div class="col-md-3">

                    <select class="form-control" id="statusChangeBtn">
                        <option value="all">All Supervisions</option>
                        <option value="on_track">On Track</option>
                        <option value="due_soon">Due Soon</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-lg-12" id="supervision-list-wrapper">
                    <div class="emergencyContent emergencyMain AllStaffTabC p-4 blueAllTabCard mt-4"">
                        <div class="
                                leavebanktabCont p24">
                            <i class="bx bx-file-detail"></i>
                            <p class="mt-3">Please Wait...</p>
                        </div>
                    </div>
                </div>
                <div id="supervision-pagination" class="mt-3 text-center pagination"></div>
            </div>
            <!-- schedule supervision modal -->
            <div class="modal fade leaveCommunStyle" id="scheduleSuperModal" tabindex="1" role="dialog"
                aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog pModalScroll customModalWidthp">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title"> Schedule Supervision </h4>
                        </div>
                        <div class="modal-body heightScrollModal" style="height: unset;">
                            <div class="carer-form">

                                <form id="recordSupervisionForm1" class="recordSupervisionForm1">
                                    <input type="hidden" name="type" value="schedule">
                                    <div class="row mb-4">
                                        <div class="col-lg-12">
                                            <div class="alert alert-danger d-none errorMsgListWrapper" role="alert">
                                                <ul class="errorMsgList">
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <label>Staff Member *</label>
                                            <select class="form-control member_id" name="member_id" id="member_id">
                                                <option selected disabled>Select Staff</option>
                                                @foreach ($userList as $item)
                                                    <option value="{{ $item->id }}">
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-12 m-t-10">
                                            <label> Supervisor *</label>
                                            <select class="form-control supervisor_id" name="supervisor_id"
                                                id="supervisor_id">
                                                <option selected disabled>Select Supervisor</option>
                                                @foreach ($userList as $item)
                                                    <option value="{{ $item->id }}">
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6  m-t-10">
                                            <label>Date *</label>
                                            <input type="date" name="date" id="name"
                                                min="{{ date('Y-m-d') }}" class="form-control">
                                        </div>
                                        <div class="col-lg-6 m-t-10">
                                            <label>Time</label>
                                            <input type="time" id="times" name="time"
                                                value="{{ date('H:i') }}" class="form-control">

                                        </div>
                                        <div class="col-lg-12 m-t-10">
                                            <label> Supervision Type</label>
                                            <select class="form-control" name="supervision_type" id="supervision_type">
                                                @foreach ($supervisionType as $item)
                                                    <option value="{{ $item['key'] }}">{{ $item['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-12 m-t-10">
                                            <label>Frequency</label>
                                            <select class="form-control" name="frequency" id="frequency">
                                                @foreach ($frenquencyList as $item)
                                                    <option value="{{ $item['key'] }}">{{ $item['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-12 m-t-10">
                                            <label>Notes</label>
                                            <textarea name="note" id="note" class="form-control" rows="3" cols="20"
                                                placeholder="Enter supervision notes and discussion points..."></textarea>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="d-flex gap-3 justify-content-end mt20">
                                                <button type="button" data-dismiss="modal" aria-hidden="true"
                                                    class="borderBtn">Cancel</button>
                                                <button type="button" class="bgBtn darkBg submit saveSupervisionBtn">
                                                    Schedule
                                                    Supervision</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- schedule supervision modal end -->
            <!-- record supervision modal -->
            <div class="modal fade leaveCommunStyle" id="recordSuperModal" tabindex="1" role="dialog"
                aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog pModalScroll">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"
                                aria-hidden="true">&times;</button>
                            <h4 class="modal-title"> Record Supervision </h4>
                        </div>
                        <div class="modal-body heightScrollModal" style="height: unset;">
                            <form id="recordSupervisionForm" class="recordSupervisionForm">
                                <input type="hidden" name="form_id" id="selected_form_id">
                                <input type="hidden" name="form_name" id="selected_form_name">
                                <div class="carer-form">
                                    <input type="hidden" name="type" value="record">
                                    <div class="row mb-4">
                                        <div class="col-lg-12">
                                            <div class="alert alert-danger d-none errorMsgListWrapper" role="alert">
                                                <ul class="errorMsgList">
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <label>Staff Member *</label>
                                            <select class="form-control member_id" name="member_id" id="member_id">
                                                <option selected disabled>Select Staff</option>
                                                @foreach ($userList as $item)
                                                    <option value="{{ $item->id }}">
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="fs11 redtext mt-1 d-none errMsg" id="member_id_error">d</small>
                                        </div>
                                        <div class="col-lg-6">
                                            <label>Supervisor *</label>
                                            <select class="form-control supervisor_id" name="supervisor_id"
                                                id="supervisor_id">
                                                <option selected disabled>Select Supervisor</option>
                                                @foreach ($userList as $item)
                                                    <option value="{{ $item->id }}">
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="fs11 redtext mt-1 d-none errMsg"
                                                id="supervisor_id_error">d</small>

                                        </div>

                                        <div class="col-lg-6  m-t-10">
                                            <label>Supervision Date *</label>
                                            <input type="date" name="date" min="{{ date('Y-m-d') }}"
                                                id="date" class="form-control">
                                            <small class="fs11 redtext mt-1 d-none errMsg" id="date_error">d</small>

                                        </div>
                                        <div class="col-lg-6 m-t-10">
                                            <label> Supervision Type</label>
                                            <select class="form-control" name="supervision_type" id="supervision_type">
                                                @foreach ($supervisionType as $item)
                                                    <option value="{{ $item['key'] }}">{{ $item['name'] }}</option>
                                                @endforeach
                                            </select>
                                            <small class="fs11 redtext mt-1 d-none errMsg"
                                                id="supervision_type_error">d</small>
                                        </div>
                                        <div class="col-lg-12 m-t-10">
                                            <label>Frequency</label>
                                            <select class="form-control" name="frequency" id="frequency">
                                                @foreach ($frenquencyList as $item)
                                                    <option value="{{ $item['key'] }}">{{ $item['name'] }}</option>
                                                @endforeach
                                            </select> <small class="fs11 redtext mt-1 d-none errMsg"
                                                id="frequency_error">d</small>
                                        </div>
                                        <div class="col-lg-12 m-t-10">
                                            <label>Supervisor Notes</label>
                                            <textarea name="note" id="note" class="form-control" rows="3" cols="20"
                                                placeholder="Enter supervision notes and discussion points..."></textarea>
                                            <small class="fs11 redtext mt-1 d-none errMsg" id="note_error">d</small>
                                        </div>
                                        <div class="col-lg-12 m-t-10">
                                            <label>Staff Comments</label>
                                            <textarea name="comment" id="comment" class="form-control" rows="3" cols="20"
                                                placeholder="Staff member's feedback..."></textarea>
                                            <small class="fs11 redtext mt-1 d-none errMsg"id="comment_error">d</small>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="attached-documents">
                                                <div class="header">
                                                    <div class="title">
                                                        <i class="fa fa-paperclip"></i> <span>Attached Documents</span>
                                                    </div>
                                                    <div class="AttachAndCloseBtn">
                                                        <button type="button" id="attach_document" class="close-btn"><i
                                                                class='bx bx-plus'></i> Attach</button>
                                                        <button type="button" id="close_document" class="close-btn"><i
                                                                class='bx bx-x'></i> </button>
                                                    </div>
                                                </div>
                                                <div class="documentContent" id="documentContent">
                                                    <div class="upload-box">
                                                        <div class="" id="availabilityTab">
                                                            <div class="availabilityTabs">
                                                                <!-- TAB HEADER -->
                                                                <div class="availabilityTabs__nav">
                                                                    <button type="button"
                                                                        class="availabilityTabs__tab active"
                                                                        data-target="selectfromSystem"> 📁
                                                                        Select from System</button>
                                                                    <button type="button" class="availabilityTabs__tab"
                                                                        data-target="uploadFiles"> <i
                                                                            class="fa fa-upload"></i> Upload File</button>
                                                                </div>

                                                                <div class="availabilityTabs__content">
                                                                    <div class="availabilityTabs__panel active"
                                                                        id="selectfromSystem">
                                                                        <div class="selectfromSystemTabCont">
                                                                            <div class="input-group selectfromSearch">
                                                                                <span
                                                                                    class="input-group-addon btn-white"><i
                                                                                        class="fa fa-search"></i></span>
                                                                                <input type="text" id="systemSearch"
                                                                                    class="form-control"
                                                                                    placeholder="Search entries...">
                                                                            </div>

                                                                            <div class="addSystemList">
                                                                                <p id="noResults" style="display:none;">No
                                                                                    results found</p>

                                                                                @foreach ($dynamic_form_builder as $form)
                                                                                    <div class="systemList addFormItem"
                                                                                        data-form-id="{{ $form['id'] }}">
                                                                                        <span class="blueText"><i
                                                                                                class='bx bx-file-detail'></i></span>
                                                                                        <div class="helthcareText">
                                                                                            <p>{{ $form['title'] }}</p>
                                                                                            <div class="inactive roundTag">
                                                                                                {{ $form['title'] }} </div>
                                                                                        </div>
                                                                                        <span><i
                                                                                                class='bx bx-plus'></i></span>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="availabilityTabs__panel" id="uploadFiles">
                                                                        <div class="row">
                                                                            <div class="col-md-6">
                                                                                <label>Document Name</label>
                                                                                <input type="text" name="doc_name"
                                                                                    class="form-control"
                                                                                    placeholder="e.g. Supervision Note">
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label>Document Type</label>
                                                                                <select class="form-control"
                                                                                    name="doc_type">
                                                                                    <option>Other</option>
                                                                                    <option>Supervision Form</option>
                                                                                    <option>Care Plan</option>
                                                                                    <option>Risk Assessment</option>
                                                                                    <option>Medication Chart</option>
                                                                                    <option>Daily Notes Template</option>
                                                                                    <option>Incident Form</option>
                                                                                    <option>Consent Form</option>
                                                                                    <option>Assessment</option>
                                                                                    <option>Training Record</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                        <div class="checkbox">
                                                                            <label>
                                                                                <input type="checkbox"
                                                                                    name="doc_required"> Requires
                                                                                completion during shift
                                                                            </label>
                                                                        </div>
                                                                        <button class="upload-btn" type="button"> <i
                                                                                class="fa fa-upload"></i> Upload & Attach
                                                                        </button>
                                                                        <input type="file" id="imageUpload"
                                                                            name="doc_file" accept="image/*"
                                                                            style="display:none;">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="m-t-15 pendingCompletionSection"
                                                        id="pendingCompletionSection">
                                                        <div class="pendingCompletion" id="pendingCompletion">
                                                            <div class="header" id="pendingHeader">Pending Completion
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="empty-state attachDocumentSection" id="attachDocumentSection">
                                                    <div class="icon"> <i class="fa fa-paperclip"></i> </div>
                                                    <p><strong>No documents attached</strong></p>
                                                    <p class="hint">Click “Attach” to add documents</p>
                                                </div>

                                            </div>

                                        </div>

                                        <div class="col-lg-12">
                                            <div class="mt20 d-flex gap-3 justify-content-end">
                                                <button type="button" data-dismiss="modal" aria-hidden="true"
                                                    class="borderBtn">Cancel</button>
                                                <button type="button" class="bgBtn darkBg submit saveSupervisionBtn">
                                                    Save
                                                    Supervision</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
            <!-- record supervision modal end -->
            <!-- supervision detail modal -->
            <div class="modal fade leaveCommunStyle" id="superDetailModal" tabindex="1" role="dialog"
                aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog pModalScroll">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"
                                aria-hidden="true">&times;</button>
                            <h4 class="modal-title"> Supervision Details </h4>
                        </div>
                        <div class="modal-body heightScrollModal">
                            <div class="row" id="supervisionDetailsWrapper">

                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-start" id="supervisionFooterDetailsWrapper">
                            {{-- <div>
                                <p class="muteText text-left">Next Supervision Due</p>
                                <p class="h7Head text-left">
                                    27 December 2025
                                </p>
                            </div> --}}
                        </div>
                    </div>

                </div>
            </div>

            <!-- supervision detail modal end -->
            <input type="hidden" id="current_page">
        </div>
        <!-- pratima script -->
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
        <script>
            let selectedFormIds = [];
            let selectedAttachmentIds = [];
            document.querySelector('.upload-btn').addEventListener('click', function() {
                document.getElementById('imageUpload').click();
            });
            document.getElementById('imageUpload').addEventListener('change', function() {

                if (this.files.length === 0) return;

                const file = this.files[0];
                const fileName = file.name;
                const today = new Date().toISOString().split('T')[0];

                const docName = document.querySelector('input[name="doc_name"]').value;
                const docType = document.querySelector('select[name="doc_type"]').value;
                const docRequired = document.querySelector('input[name="doc_required"]').checked;

                let uniqueId = Date.now(); // simple unique id

                // 🔥 PUSH INTO ARRAY
                selectedAttachmentIds.push({
                    id: uniqueId,
                    file: file,
                    fileName: fileName,
                    doc_name: docName,
                    doc_type: docType,
                    doc_required: docRequired ? 1 : 0
                });

                // console.log("Added:", selectedAttachmentIds);

                let newSection = `
                    <div class="card pendingCard" data-id="${uniqueId}">
                        <div class="left">
                            <div class="icon blueText">
                                <i class='bx bx-file'></i>
                            </div>
                            <div class="info">
                                <div class="title">${fileName}</div>
                                <div class="meta">
                                    <div class="inactive roundTag">${docType}</div>
                                    <span class="date">${today}</span>
                                </div>
                            </div>
                        </div>

                        <div class="actions">
                            <span class="delete attachment-doc-delete"><i class='bx bx-trash'></i></span>
                        </div>
                    </div>
                `;

                document.getElementById('pendingCompletionSection').style.display = 'block';
                document.getElementById('pendingCompletion').insertAdjacentHTML('beforeend', newSection);

                this.value = ''; // reset file input
            });
            document.addEventListener('click', function(e) {

                let deleteBtn = e.target.closest('.attachment-doc-delete');
                if (!deleteBtn) return;

                let card = deleteBtn.closest('.pendingCard');
                let id = Number(card.dataset.id);

                // 🔥 REMOVE FROM ARRAY
                selectedAttachmentIds = selectedAttachmentIds.filter(item => item.id !== id);

                // console.log("After Remove:", selectedAttachmentIds);

                // 🔥 REMOVE FROM DOM
                card.remove();
            });
            // document.getElementById('imageUpload').addEventListener('change', function() {
            //     if (this.files.length === 0) return;

            //     const btn = document.querySelector('.upload-btn');
            //     const originalHtml = btn.innerHTML;
            //     btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
            //     btn.disabled = true;

            //     setTimeout(() => {
            //         btn.innerHTML = originalHtml;
            //         btn.disabled = false;
            //     }, 1000);

            //     const file = this.files[0];
            //     const fileName = file.name;
            //     const today = new Date().toISOString().split('T')[0];

            //     let newSection = `<div class="card pendingCard">
    //                         <div class="left">
    //                             <div class="icon blueText">
    //                                 <i class='bx bx-file'></i>
    //                             </div>
    //                             <div class="info">
    //                                 <div class="title">${fileName}</div>
    //                                 <div class="meta">
    //                                     <div class="inactive roundTag">Attachment</div>
    //                                     <span class="date">${today}</span>
    //                                 </div>
    //                             </div>
    //                         </div>

    //                         <div class="actions">
    //                             <span class="approve"><i class='bx bx-check-circle'></i></span>
    //                             <span class="delete"><i class='bx bx-trash'></i></span>
    //                         </div>
    //                     </div>
    //                 `;

            //     document.getElementById('pendingCompletionSection').style.display = 'block';
            //     document.getElementById('attachDocumentSection').style.display = 'none';
            //     document.getElementById('pendingCompletion').insertAdjacentHTML('beforeend', newSection);

            //     updatePendingCount();

            //     document.getElementById('close_document').style.display = 'none';
            //     document.querySelector('.upload-box').style.display = 'none';
            //     document.getElementById('attach_document').style.display = 'inline-block';

            //     // reset input so same file can be selected again
            //     // this.value = '';
            // });
            const attach_document = document.getElementById('attach_document');
            const close_document = document.getElementById('close_document');
            const documentContent = document.getElementById('documentContent');


            attach_document.addEventListener('click', function() {
                documentContent.style.display = 'block';
                attach_document.style.display = 'none';
                close_document.style.display = 'inline-block';
                document.querySelector('.upload-box').style.display = 'block';
            });

            close_document.addEventListener('click', function() {
                documentContent.style.display = 'none';
                close_document.style.display = 'none';
                attach_document.style.display = 'inline-block';
            });
            document.getElementById('systemSearch').addEventListener('keyup', function() {
                let searchValue = this.value.toLowerCase();
                let systemLists = document.querySelectorAll('.addSystemList .systemList');
                let noResults = document.getElementById('noResults');
                let hasVisible = false;

                systemLists.forEach(function(item) {
                    let titleText = item.querySelector('.helthcareText p').innerText.toLowerCase();

                    if (titleText.includes(searchValue)) {
                        item.style.display = 'flex';
                        hasVisible = true;
                    } else {
                        item.style.display = 'none';
                    }
                });

                noResults.style.display = hasVisible ? 'none' : 'block';
            });
            document.addEventListener('click', function(e) {
                if (e.target.closest('.addFormItem')) {
                    let clickedItem = e.target.closest('.addFormItem');
                    let formId = clickedItem.getAttribute('data-form-id');
                    let title = clickedItem.querySelector('.helthcareText p').innerText;
                    formId = Number(formId);
                    if (!selectedFormIds.includes(formId)) {
                        selectedFormIds.push(formId);
                    }
                    // console.log(selectedFormIds);
                    document.getElementById('selected_form_id').value = formId;
                    document.getElementById('selected_form_name').value = title;

                    let today = new Date().toISOString().split('T')[0];

                    let newSection = `
                            <div class="card pendingCard">
                                <div class="left">
                                    <div class="icon blueText"><i class='bx bx-file'></i></div>
                                    <div class="info">
                                        <div class="title">${title}</div>
                                        <div class="meta">
                                            <div class="inactive roundTag">${title}</div>
                                            <span class="date">${today}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="actions">
                                    <span class="approve"><i class='bx bx-check-circle'></i></span>
                                    <span class="delete" data-formid="${formId}"><i class='bx bx-trash'></i></span>
                                </div>
                            </div>
                        `;

                    document.getElementById('pendingCompletionSection').style.display = 'block';
                    document.getElementById('attachDocumentSection').style.display = 'none';
                    document.getElementById('pendingCompletion').insertAdjacentHTML('beforeend', newSection);

                    updatePendingCount();
                    document.getElementById('close_document').style.display = 'none';
                    document.querySelector('.upload-box').style.display = 'none';
                    document.getElementById('attach_document').style.display = 'inline-block';

                }

                if (e.target.closest('.delete')) {
                    let card = e.target.closest('.pendingCard');
                    let deleteBtn = e.target.closest('.delete');
                    let formId = $(deleteBtn).data('formid');
                    console.log(formId);

                    if (card) {
                        selectedFormIds = selectedFormIds.filter(id => id !== formId);
                        card.remove();
                        updatePendingCount();

                        // If no cards left, show the empty state again
                        if (document.querySelectorAll('#pendingCompletion .pendingCard').length === 0) {
                            document.getElementById('pendingCompletionSection').style.display = 'none';
                            document.getElementById('attachDocumentSection').style.display = 'block';
                            document.querySelector('.upload-box').style.display = 'block';
                            document.getElementById('attach_document').style.display = 'none';

                            // Clear hidden inputs
                            document.getElementById('selected_form_id').value = '';
                            document.getElementById('selected_form_name').value = '';
                        }
                    }
                }
                // console.log(selectedFormIds);
            });

            function updatePendingCount() {
                let count = document.querySelectorAll('#pendingCompletion .pendingCard').length;
                document.getElementById('pendingHeader').textContent = 'Pending Completion (' + count + ')';
            }
            $(document).ready(function() {
                $('.supervisor_id').each(function() {
                    let modal = $(this).closest('.modal');

                    $(this).select2({
                        dropdownParent: modal,
                        width: '100%',
                        placeholder: "Select Supervisor"
                    });
                });
                $('.member_id').each(function() {
                    let modal = $(this).closest('.modal');

                    $(this).select2({
                        dropdownParent: modal,
                        width: '100%',
                        placeholder: "Select Member"
                    });
                });
                $(".saveSupervisionBtn").click(function() {
                    // console.log('btn clicked');
                    $(".errorMsgListWrapper").addClass('d-none');
                    let btn = $(this);

                    // alert('Record Supervision Saved Successfully!');
                    let forms = btn.closest('form'); // correct form
                    let modals = btn.closest('.modal');
                    let formDATA = new FormData(forms[0]);
                    formDATA.append('dynamic_form_id', JSON.stringify(selectedFormIds));
                    selectedAttachmentIds.forEach((item, index) => {

                        formDATA.append(`attachments[${index}][file]`, item.file);
                        formDATA.append(`attachments[${index}][doc_name]`, item.doc_name);
                        formDATA.append(`attachments[${index}][doc_type]`, item.doc_type);
                        formDATA.append(`attachments[${index}][doc_required]`, item.doc_required ? 1 :
                            0);

                    });
                    let obj = {};

                    formDATA.forEach((value, key) => {
                        obj[key] = value;
                    });

                    // console.log(obj);
                    // return false;

                    $.ajax({
                        url: "{{ route('roster.supervision.save') }}", // URL to send the request to
                        type: 'POST', // or 'POST'
                        data: formDATA,
                        processData: false,
                        contentType: false,
                        beforeSend: function() {
                            btn.html('Please Wait...').prop('disabled', true);
                        },
                        success: function(res) {
                            btn.html('Save Supervision').prop('disabled', false);
                            $(".errorMsgListWrapper").addClass('d-none');
                            // Code to run on successful response
                            alert(res.message);
                            fetch_supervisions(1);
                            modal.modal('hide');
                            form.trigger('reset');
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            btn.html('Save Supervision').prop('disabled',
                                false);
                            $(".errorMsgListWrapper").removeClass('d-none');
                            var errors = xhr.responseJSON.message;
                            // let target = $("#errorMsgListWrapper")[0].offsetTop;
                            $(".errorMsgList").html('');
                            if (xhr.status == 422) {

                                let htm = '';

                                $.each(errors, function(key, value) {
                                    htm += `<li>${value[0]}</li>`;

                                });
                                $(".member_id").focus();
                                $(".errorMsgList").html(htm);

                                // scroll AFTER DOM updated
                                setTimeout(() => {
                                    let target = $("#errorMsgListWrapper")[0].offsetTop;

                                    $('#recordSuperModal .modal-body').animate({
                                        scrollTop: target
                                    }, 400);
                                }, 50);

                                return false;
                            } else if (xhr.status == 404) {
                                $(".errorMsgList").html('<li>Data not found</li>');
                            } else {
                                $(".errorMsgList").html(
                                    '<li>An error occurred while saving the supervision record.</li>'
                                );
                                // alert('An error occurred while saving the supervision record.');
                            }
                        }
                    });

                });

                $(document).on('click', '.viewBtn', function() {

                    let id = $(this).data('id');

                    $.ajax({
                        url: "{{ route('roster.supervision.details') }}", // URL to send the request to
                        type: 'POST', // or 'POST'
                        data: {
                            id: id,
                            _token: "{{ @csrf_token() }}"
                        }, // Data to send with the request
                        beforeSend: function() {},
                        success: function(res) {
                            if (typeof isAuthenticated === "function") {
                                if (isAuthenticated(res) == false) {
                                    return false;
                                }
                            }
                            if (res.status) {
                                openModal(res.data);
                            } else if (res === '"unauthorize"') {
                                alert('You have not access rights for this page');
                                return;
                            } else {
                                alert(res.message ?? "Something went wrong !!");
                                return;
                            }
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            if (xhr.status === 500) {
                                alert('Internal Server Error');
                            } else if (xhr.status === 422) {
                                alert(xhr.responseJSON.message ?? "Data Not Found !!")
                            }
                        }
                    });



                });

                function openModal(el) {
                    $("#supervisionDetailsWrapper").empty();
                    $("#supervisionFooterDetailsWrapper").empty();
                    // console.log(el.attachments);
                    let attachmentsHtml = '';
                    $.each(el.attachments, function(key, item) {

                        attachmentsHtml += `<div class="card pendingCard">
                                <div class="left">
                                    <div class="icon blueText"><i class='bx bx-file'></i></div>
                                    <div class="info">
                                        <div class="title">${item.doc_name}</div>
                                        <div class="meta">
                                            <div class="inactive roundTag">${item.doc_type}</div>
                                            <span class="date">${item.created_at}</span>
                                            </div>
                                            </div>
                                            </div>
                                </div>`;
                    });
                    // console.log(attachmentsHtml);
                    colorText = el.status == 'On Track' ? "greenbadges" : (el
                        .status == 'Due Soon' ? "yellowbadges" : "redbadges");
                    let html = `<div class="col-lg-6">
                                    <p class="muteText">Staff Member</p>
                                    <p class="h7Head">${el.member_name}</p>
                                </div>
                                <div class="col-lg-6 m-t-10">
                                    <p class="muteText">Supervisor</p>
                                    <p class="h7Head">${el.supervisor_name}</p>
                                </div>
                                <div class="col-lg-6 m-t-10">
                                    <p class="muteText">Date</p>
                                    <p class="h7Head">${el.date}</p>
                                </div>
                                <div class="col-lg-6 m-t-10">
                                    <p class="muteText">Type</p>
                                    <div> <span class="careBadg ${colorText}">${el.status}</span> </div>
                                </div>
                                <div class="col-lg-12 m-t-10">
                                    <p class="muteText">Supervisor Note</p>
                                    <div class="muteBg rounded8 p-3">
                                        <p class="mb-0 text-sm para">${el.note}</p>
                                    </div>
                                </div>`;
                    if (el.type === 'record') {
                        html += `<div class="col-lg-12 m-t-10"><p class="muteText">Supervisor Comments</p>
                                    <div class="lightBlueBg rounded8 p-3">
                                        <p class="mb-0 text-sm para">${el.comment}</p>
                                    </div>
                                </div>`;
                    }
                    if (el.attachments.length > 0) {
                        html += `<div class="col-lg-12 m-t-10"><p class="muteText">Attached Documents</p>
                                    <div class=" rounded8 p-3">${attachmentsHtml}</div>
                                </div>`;
                    }
                    let footerText = `<div>
                                <p class="muteText text-left">Next Supervision Due</p>
                                <p class="h7Head text-left">
                                   ${el.next_due}
                                </p>
                            </div>`;


                    $("#supervisionFooterDetailsWrapper").html(footerText);
                    $("#supervisionDetailsWrapper").html(html);
                    $("#superDetailModal").modal('show');
                }
                $(document).on('click', '.page-btn', function() {
                    let page = $(this).data('page');
                    fetch_supervisions(page);
                });
                $(document).on('click', '.deleteBtn', function() {
                    let id = $(this).data('id');
                    let confirms = confirm("Are you sure, you want to delete?");
                    if (confirms) {
                        $.ajax({
                            url: "{{ route('roster.supervision.delete') }}", // URL to send the request to
                            type: 'POST', // or 'POST'
                            data: {
                                id: id,
                                _token: "{{ @csrf_token() }}"
                            }, // Data to send with the request
                            beforeSend: function() {},
                            success: function() {
                                // Code to run on successful response
                                fetch_supervisions(parseInt($("#current_page").val()))
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                // Code to run if the request fails
                            }
                        });
                    }
                    // fetch_supervisions(parseInt($("#current_page").val()))
                });
                $("#statusChangeBtn").change(function() {
                    fetch_supervisions(1);
                });

                fetch_supervisions(1);

                function fetch_supervisions(page = 1) {
                    let statusVal = $("#statusChangeBtn option:selected").val();
                    let searchText = $("#searchText").val();
                    $("#overdue_text_wrapper").addClass('d-none');
                    $.ajax({
                        url: "{{ route('roster.fetch_supervision.list') }}?page=" +
                            page + "&status=" + statusVal + "&search=" +
                            searchText, // URL to send the request to
                        type: 'GET',
                        beforeSend: function() {
                            // $("#supervision-list-wrapper").html(`<div class="emergencyContent emergencyMain AllStaffTabC p-4 blueAllTabCard mt-4"">
                    //     <div class="
                    //     leavebanktabCont p24">
                    //         <i class="bx bx-file-detail"></i>
                    //         <p class="mt-3">Please Wait...</p>
                    //     </div>
                    // </div>`);
                            // $("#totalRecord").html(0);
                            // $("#totalOnTrack").html(0);
                            // $("#totalDueSoon").html(0);
                            // $("#totalOverdue").html(0);
                        },
                        success: function(res) {
                            if (typeof isAuthenticated === "function") {
                                if (isAuthenticated(res) == false) {
                                    return false;
                                }
                            }
                            if (res.success) {
                                if (res.counts.overdue > 0) {
                                    $("#overdue_text_wrapper").removeClass('d-none');
                                } else {
                                    $("#overdue_text_wrapper").addClass('d-none');

                                }
                                $("#supervision-pagination").empty();
                                $("#supervision-list-wrapper").empty();
                                $("#totalRecord").html(res.counts.total);
                                $("#totalOnTrack").html(res.counts.on_track);
                                $("#totalDueSoon").html(res.counts.due_soon);
                                $("#totalOverdue").html(res.counts.overdue);
                                $("#overdue_text").html(res.counts.overdue_text);
                                let htm = '';
                                if (res.data.length == 0) {
                                    $("#supervision-list-wrapper").html(`<div class="emergencyContent emergencyMain AllStaffTabC p-4 blueAllTabCard mt-4"">
                                        <div class="
                                        leavebanktabCont p24">
                                            <i class="bx bx-file-detail"></i>
                                            <p class="mt-3">No supervision records</p>
                                        </div>
                                    </div>`);
                                    return;
                                }
                                $.each(res.data, function(index, item) {
                                    colorText = item.status == 'On Track' ? "greenbadges" : (item
                                        .status == 'Due Soon' ? "yellowbadges" : "redbadges");
                                    htm += `<div class="emergencyMain emergencyContent AllStaffTabC p-4 blueAllTabCard mt-4">

                                        <div class="d-flex justify-content-between  align-items-center">
                                            <div class="d-flex gap-4 align-items-center">
                                                <div class="bgIconStaffT rounded50">
                                                    <h5 class="h5Head blueText mb-0">${item.member_name[0]}</h5>
                                                </div>
                                                <div>
                                                    <div class="mb-2">
                                                        <h5 class="mb-2">${item.member_name}</h5>
                                                        <p class="muteText">Supervised by ${item.supervisor_name}</p>
                                                    </div>
                                                    <div class="d-flex align-items-center flexWrap gap-3">
                                                        <div class="userMum">
                                                            <span class="title mt-0">
                                                                ${item.date}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <span class='careBadg ${colorText}'>${item.status}</span>

                                                        </div>
                                                        <div class="userMum">
                                                            <span class="title mt-0">
                                                                ${item.supervision_type}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <p class="mt-3 muteText">Next due: ${item.next_due}</p>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-4">
                                                    <div>
                                                        <button class="borderBtn viewBtn" data-id="${item.id}"><i
                                                                class="bx bx-eye me-2"></i>View </button>
                                                    </div>
                                                    <div class="deleteIcon delete-row-btn deleteBtn" data-id="${item.id}"> <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>`;
                                });
                                // console.log(htm);
                                $("#supervision-list-wrapper").html(htm);
                                let p = res.pagination;
                                let paginationHtml = '';

                                $("#current_page").val(p.current_page < p.total_pages ? p.current_page : 1);

                                if (p.total_pages > 1) {
                                    paginationHtml += `
                                    <button class="page-btn btn ${p.current_page == 1 ? 'disabled' : ''}"
                                        data-page="${p.current_page - 1}">
                                        Prev
                                    </button>`;
                                    for (let i = 1; i <= p.total_pages; i++) {

                                        paginationHtml += `
                                        <button class="page-btn btn ${i == p.current_page ? 'btn-primary disabled' : ''}"
                                            data-page="${i}">
                                            ${i}
                                        </button>`;
                                    }

                                    paginationHtml += `
                                    <button class="page-btn btn ${p.current_page == p.total_pages ? 'disabled' : ''}"
                                        data-page="${p.current_page + 1}">
                                        Next
                                    </button>
                                `;
                                }

                                $("#supervision-pagination").html(paginationHtml);

                            } else {
                                $("#supervision-list-wrapper").html(`<div class="emergencyContent emergencyMain AllStaffTabC p-4 blueAllTabCard mt-4"">
                                        <div class="
                                        leavebanktabCont p24">
                                            <i class="bx bx-file-detail"></i>
                                            <p class="mt-3">No supervision records</p>
                                        </div>
                                    </div>`);
                                return;
                            }

                        },
                        error: function(xhr, ajaxOptions, thrownError) {

                            if (xhr.status == 404) {
                                $("#supervision-pagination").empty();
                                $("#supervision-list-wrapper").html(`<div class="emergencyContent emergencyMain AllStaffTabC p-4 blueAllTabCard mt-4"">
                                    <div class="
                                    leavebanktabCont p24">
                                        <i class="bx bx-file-detail"></i>
                                        <p class="mt-3">No supervision records</p>
                                    </div>
                                </div>`);
                            }
                        }
                    });

                }

                $(document).on('click', '.scheduleSuperModalBtn, .recordSuperModalBtn', function() {

                    // alert($(this).hasClass('scheduleSuperModalBtn') ? 1 : 2);
                    $(".errorMsgListWrapper").addClass('d-none');
                    $(".errorMsgList").empty();
                    $("#recordSupervisionForm, #recordSupervisionForm1").trigger('reset');
                    $('.member_id').val('').trigger('change');
                    $('.supervisor_id').val('').trigger('change');
                });

                let searchTimer;
                $("#searchText").on('keyup', function() {
                    clearTimeout(searchTimer);

                    searchTimer = setTimeout(() => {
                        fetch_supervisions(1);
                    }, 500);
                });
            });
        </script>
        <!-- pratima script end -->
    </main>
@endsection
