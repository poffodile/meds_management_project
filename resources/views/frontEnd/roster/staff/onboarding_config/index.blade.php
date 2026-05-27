<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
@extends('frontEnd.layouts.master') @section('title', 'Client Onboadrding')
@section('content') @include('frontEnd.roster.common.roster_header')
    <main class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="staffHeaderp">
                        <div>
                            <h1 class="mainTitlep">Onboarding Configuration</h1>
                            <p class="header-subtitle mb-0">
                                Configure onboarding workflows for your organisation
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt20">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="calendarTabs onBoardConTabHor">
                            <div class="tabs p-1">
                                <div class="dFlexGap onBoardTabBtn">
                                    <button class="tab configureBtn active" data-type="staff">
                                        <i class="bx bx-group f18"></i> Staff Workflows
                                    </button>
                                    <button class="tab configureBtn" data-type="client">
                                        <i class="bx bx-user-circle f18"></i> Client Workflows
                                    </button>
                                </div>
                            </div>
                            <div class="mt20">
                                <label for="" class="formLabel">Care Setting</label>
                                <select name="care_setting" id="care_setting" class="form-control"
                                    style="background: transparent;">
                                    <option value="all" data-caresettingname="All Care Setting">All Care Setting</option>
                                    @foreach ($company_departments as $item)
                                        <option value="{{ $item->id }}" data-caresettingname="{{ $item->name }}">
                                            {{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt20">
                                <div class="flexBw align-items-center">
                                    <p class="mb-0 fs13 textGray500" id="countText">
                                        0 workflow(s) for All Care Setting
                                    </p>
                                    <div>
                                        <button class="bgBtn blackBtn" id="createWorkflowBtn">
                                            <i class="bx bx-plus me-2"></i>Create Workflow
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- TAB CONTENT -->
                            <div>
                                <div class="row mt20">
                                    <div class="col-md-12">
                                        <div class="noData d-none" id="noworkflowdata">
                                            <div>
                                                <i class="bx bx-cog"></i>
                                                <p class="mb-0">No Workflow data</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div>
                                            <div>
                                                <!-- staff list  -->
                                                <div class="workflowList" id="workflow-data-wapper">

                                                </div>
                                                <!-- end  staff list -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="noData d-none" id="noselectedworkflow">
                                            <div>
                                                <i class="bx bx-cog"></i>
                                                <p class="mb-0">Select a workflow to configure</p>
                                            </div>
                                        </div>
                                        <div class="workflowDetails">
                                            <!-- staff details -->
                                            <div class="wfContent detailsWorkflow" id="wf">
                                                <div class="emergencyMain p24">
                                                    <div class="flexBw">
                                                        <h6 class="h6Head mb-0" id="titleText">
                                                            Domiciliary Staff Onboarding
                                                        </h6>
                                                        <div class="dFlexGap">
                                                            <div>
                                                                <button type="button" id="deactivateBtn"
                                                                    class="borderBtn">Deactivate</button>
                                                            </div>
                                                            <div>
                                                                <button class="bgBtn redBtn" type="button"
                                                                    id="workFlowDelBtn">
                                                                    <i class="bx bx-trash" style="font-size: 17px"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt20">
                                                        <div class="flexBw align-items-center">
                                                            <p class="mb-0 h6Head">Workflow Stages</p>
                                                            <div>
                                                                <button class="bgBtn blackBtn" data-toggle="modal"
                                                                    data-target="#addStage" type="button" id="addStageBtn">
                                                                    <i class="bx bx-plus me-2"></i>Add Stage
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- workflow list detail -->
                                                    <div class="mt20" id="set-stages-wrapper">
                                                        <div class="noData">
                                                            <div>
                                                                <i class="bx bx-cog"></i>
                                                                <p class="mb-0">Select a workflow to configure</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- workflow details end -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- END TAB CONTENT -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- add stage -->
            <div class="modal fade" id="addStage" tabindex="1" role="dialog" aria-labelledby="myModalLabel"
                aria-hidden="true">
                <div class="modal-dialog pModalScroll">
                    <div class="modal-content">

                        <form id="add_stage">
                            @csrf
                            <input type="hidden" id="workflow_id" name="workflow_id">
                            <input type="hidden" id="stage_id" name="stage_id">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"
                                    aria-hidden="true">&times;</button>
                                <h4 class="modal-title"> Add New Stage</h4>
                            </div>
                            <div class="modal-body heightScrollModal">
                                <div class="row">
                                    <div class="col-lg-12 alert d-none alert-success sucMsg p-4">dd</div>
                                    <div class="col-lg-12">
                                        <label for="">Stage Name</label>
                                        <input type="text" id="stage_name" name="stage_name" class="form-control"
                                            placeholder="e.g., Pre-Employment Checks">
                                        <span id="stage_name_error" class="errMsg d-none text-danger"></span>
                                    </div>
                                    <div class="col-lg-12 m-t-10">
                                        <label for="">Description</label>
                                        <textarea id="description" name="description" class="form-control" rows="3" cols="20"
                                            placeholder="Describe what needs to be completed"></textarea>
                                        <span id="description_error" class="errMsg d-none text-danger"></span>
                                    </div>
                                    <div class="col-lg-6 m-t-10">
                                        <label for="">Entity Type </label>
                                        <select name="entity_type_id" id="entity_type_id" class="form-control">
                                            @foreach ($entityType as $item)
                                                <option value="{{ $item->id }}">{{ $item->type }}</option>
                                            @endforeach

                                        </select>
                                        <span id="entity_type_id_error" class="errMsg d-none text-danger"></span>
                                    </div>
                                    <div class="col-lg-6 m-t-10">
                                        <label for="">Status Name</label>
                                        <input type="text" class="form-control" name="status_name" id="status_name"
                                            placeholder="e.g., Pre-Employment Checks">
                                        <span id="status_name_error" class="errMsg d-none text-danger"></span>
                                    </div>
                                    <div class="col-lg-12 mt-4">
                                        <div class="flexBw mb-3">
                                            <p class="fs13 font700 mb-0">Required Stage</p>
                                            <label class="mySwitch">
                                                <input type="checkbox" value="1" name="required_stage"
                                                    id="required_stage">
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                        <div class="flexBw mb-3">
                                            <p class="fs13 font700 mb-0">Auto-create Task</p>
                                            <label class="mySwitch">
                                                <input type="checkbox" value="1" name="auto_create_task"
                                                    id="auto_create_task">
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer dFlexGap justify-content-end">
                                <div>
                                    <button class="borderBtn" data-dismiss="modal" type="button"
                                        id="cancelBtn">Cancel</button>
                                </div>
                                <div><button class="bgBtn blackBtn" type="button" id="saveStageBtn"><i
                                            class="bx bx-save f18"></i> Save
                                        Stage</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- end add stage -->
        </div>

        <script>
            $("#addStageBtn").click(function() {
                resetFormFunc();
                $("#add_stage").find('.modal-title').html('Add New Stage')
            })
            $("#cancelBtn").click(function() {
                console.log(1);

                resetFormFunc();
            })

            function resetFormFunc() {
                let workflow_id = $('#add_stage').find('input[name="workflow_id"]').val();

                $('#add_stage').find('input, textarea').val('');
                $('#add_stage').find('input:checkbox, input:radio').prop('checked', false);
                $('#add_stage').find('select[name="entity_type_id"]')
                    .prop('selectedIndex', 0)
                    .trigger('change');
                // restore
                $('#add_stage').find('input[name="workflow_id"]').val(workflow_id);
                 $('#add_stage').find('input[name="required_stage"]').val(1);
                $('#add_stage').find('input[name="auto_create_task"]').val(1);
                $(".sucMsg").addClass('d-none')
                $(".errMsg").addClass('d-none')
            }

            function alertMsg(selector = 'err', msg) {
                $('.ajax-alert-' + selector).find('.msg').text(
                    msg);
                $('.ajax-alert-' + selector).show();

                setTimeout(function() {
                    $(".ajax-alert-" + selector).fadeOut()
                }, 5000);
            }
            let activeTab = $('.configureBtn.active').data('type');
            let activeCareSetting = $('#care_setting').val();
            let CARE_SETTING = {
                all: "All Care Setting",
                residential: "Residential Care",
                domiciliary: "Domiciliary Care",
                supported_living: "Supported living",
                day_centre: "Day centre"
            };
            // document.querySelectorAll(".workflowItem").forEach((item) => {
            //     item.addEventListener("click", function() {
            //         let id = this.dataset.id;
            //         let target = this.dataset.target; // wf
            //         console.log(id, "target: ", target);

            //         // remove active from all menu
            //         document
            //             .querySelectorAll(".workflowItem")
            //             .forEach((el) => el.classList.remove("active"));

            //         this.classList.add("active");

            //         // hide all contents
            //         document
            //             .querySelectorAll(".wfContent")
            //             .forEach((c) => c.classList.remove("active"));

            //         // show selected content
            //         let content = document.getElementById(target + id); // wf1
            //         if (content) content.classList.add("active");
            //     });
            // });

            document.querySelectorAll(".configureBtn").forEach((btn) => {
                btn.addEventListener("click", function() {
                    document
                        .querySelectorAll(".configureBtn")
                        .forEach((b) => b.classList.remove("active"));

                    // add active to clicked
                    let tabName = this.getAttribute("data-type");
                    activeTab = tabName;
                    this.classList.add("active");
                    document.querySelectorAll(".workflowItem")
                        .forEach((el) => el.classList.remove("active"));
                    $(this).addClass('active');
                    $(".detailsWorkflow").removeClass('active')
                    $('#workflow-data-wapper').empty()
                    loadWorkFlow();
                });
            });
            $('#care_setting').change(function() {
                $('#workflow-data-wapper').empty()
                activeCareSetting = $(this).val();
                document.querySelectorAll(".workflowItem")
                    .forEach((el) => el.classList.remove("active"));
                $(this).addClass('active');
                $(".detailsWorkflow").removeClass('active')
                loadWorkFlow()
            });

            function addWorkFlow() {

                $.ajax({
                    url: "{{ route('roster.onboarding-config.add') }}",
                    type: 'POST',
                    data: {
                        care_type: activeCareSetting,
                        type: activeTab,
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        if (typeof isAuthenticated === "function") {
                            if (isAuthenticated(res) == false) {
                                return false;
                            }
                        }
                        if (res.status) {
                            alertMsg('suc', res.message)
                            loadWorkFlow()
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        let errMsg = xhr.responseJSON.message ?? "Internal Server Error";
                        alertMsg(errMsg)

                    }
                });

            }
            $("#createWorkflowBtn").click(function() {

                addWorkFlow();
            });
            loadWorkFlow();

            function loadWorkFlow() {
                $("#noselectedworkflow").removeClass('d-none')
                let caresettingname = $('#care_setting option:selected').data('caresettingname');;

                $.ajax({
                    url: "{{ route('roster.onboarding.workflow.loadData') }}",
                    type: 'POST',
                    data: {
                        type: activeTab,
                        care_type: activeCareSetting,
                        _token: "{{ @csrf_token() }}"
                    },
                    beforeSend: function() {
                        $("#noworkflowdata").removeClass('d-none');
                        $("#noselectedworkflow").addClass('d-none');
                    },
                    success: function(res) {
                        if (typeof isAuthenticated === "function") {
                            if (isAuthenticated(res) == false) {
                                return false;
                            }
                        }
                        $("#countText").html(`${res.data.length} workflow(s) for ${caresettingname}`)
                        setWorkFlowHtml(res.data)
                    },
                    error: function(xhr, ajaxOptions, thrownError) {}
                });
            }

            function loadWorkFlowStages(WORKFLOW_ID) {
                $.ajax({
                    url: "{{ route('roster.onboarding.stages.loadData') }}",
                    type: 'POST',
                    data: {
                        workflow_id: WORKFLOW_ID,
                        _token: "{{ @csrf_token() }}"
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        if (typeof isAuthenticated === "function") {
                            if (isAuthenticated(res) == false) {
                                return false;
                            }
                        }
                        setWorkFlowStagesHtml(res.data)
                    },
                    error: function(xhr, ajaxOptions, thrownError) {}
                });
            }


            function setWorkFlowHtml(res) {
                $("#noworkflowdata").addClass('d-none');
                $("#noselectedworkflow").removeClass('d-none');
                $('#workflow-data-wapper').empty()
                if (res.length == 0) {
                    $("#noworkflowdata").removeClass('d-none');
                    $("#noselectedworkflow").addClass('d-none');
                    return;
                }
                // $("#workflow-data-wapper").empty();
                let html = '';
                $.each(res, function(key, val) {

                    let CARE_SETTING_STATUS = val.departments ? val.departments.name : "All Care Setting";
                    html += `<div class="workflowItem" data-id="${val.id}" data-title="${val.title}" data-statuscode="${val.status}" data-target="wf">
                                                        <div class="emergencyMain p-4">
                                                            <div class="flexBw mb-2">
                                                                <h6 class="h6Head mb-0">${val.title}</h6>
                                                                <div>
                                                                    <span class="workflowStatusText careBadg ${val.status ==0?'muteBadges' : 'darkGreenBadges'}">${val.status ==0?'Inactive' : 'Active'}</span>
                                                                </div>
                                                            </div>
                                                            <p class="mb-0 muchsmallText">${val.getstages_count} stage . ${CARE_SETTING_STATUS}</p>
                                                        </div>
                                                    </div>`;

                });
                $('#workflow-data-wapper').html(html)
                // $("#workflow-data-wapper").html(html);
            }

            function setWorkFlowStagesHtml(res) {
                $('#set-stages-wrapper').html(`<div class="noData">
                                                            <div>
                                                                <i class="bx bx-cog"></i>
                                                                <p class="mb-0">No Stages Founds</p>
                                                            </div>
                                                        </div>`)
                // $("#workflow-data-wapper").empty();
                if (res.length == 0) {
                    $('#set-stages-wrapper').html(`<div class="noData">
                                                            <div>
                                                                <i class="bx bx-cog"></i>
                                                                <p class="mb-0">No Stages Founds</p>
                                                            </div>
                                                        </div>`);
                    return;
                }
                let html = '';
                $.each(res, function(key, val) {
                    let isFirstDisabledBtn = '';
                    let isLastDisabledBtn = '';
                    if (key === 0) {
                        // console.log("First:", val);
                        isFirstDisabledBtn = 'disabled';
                    }

                    // LAST element
                    if (key === res.length - 1) {
                        // console.log("Last:", val);
                        isLastDisabledBtn = 'disabled';
                    }
                    let CARE_SETTING_STATUS = val.departments ? val.departments.name : "All Care Setting";
                    html += `<div class="emergencyMain p-4 bottomSpace" data-id="${val.id}" id="stage-data-${val.id}">
                                                            <div class="flexBw">
                                                                <h6 class="h6Head mb-0">
                                                                    ${val.order_no}. ${val.stage_name}
                                                                    <span class="borderBadg ms-2">${val.required_stage==1?'Required':'Optional'} </span>
                                                                </h6>
                                                                <div class="dFlexGap onBoardIcons mb-3">
                                                                    <button class="hoverBtn" ${isFirstDisabledBtn} onclick="orderingFunc('${val.id}','asc','${val.onboarding_config_id}')">
                                                                        <i class="bx bx-arrow-left f20"
                                                                            style="transform: rotate(90deg)"></i>
                                                                    </button>
                                                                    <button class="hoverBtn" ${isLastDisabledBtn} onclick="orderingFunc('${val.id}','desc','${val.onboarding_config_id}')">
                                                                        <i class="bx bx-arrow-left f20"
                                                                            style="transform: rotate(-90deg)"></i>
                                                                    </button>
                                                                    <button class="hoverBtn" onclick="editStageFunc('${val.id}','stage-data-${val.id}')">
                                                                        <i class="fa fa-pencil-square-o f20"> </i>
                                                                    </button>
                                                                    <button class="hoverBtn" onclick="stageDeleteFunc('${val.id}','stage-data-${val.id}')">
                                                                        <i class="fa fa-trash-o f20"> </i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <p class="muteText mb-2">
                                                                ${val.description??''}
                                                            </p>
                                                            <p class="muchsmallText mb-0">
                                                                Entity: ${val.entitydata?val.entitydata.type:''}
                                                            </p>
                                                        </div>`;

                });
                $('#set-stages-wrapper').html(html)
                // $("#workflow-data-wapper").html(html);
            }
            $(document).on('click', '.workflowItem', function() {
                workFlowDetails(this)

            });

            function workFlowDetails(el) {
                $("#noselectedworkflow").addClass('d-none')
                let WORKFLOW_ID = $(el).data('id');
                let WORKFLOW_TITLE = $(el).data('title');
                let status = $(el).data('statuscode');
                let DEACTIVATE_STATUS_HTML = status == 1 ? "Deactivate" : "Activate";
                $("#workflow_id").val(WORKFLOW_ID);
                $("#titleText").html(WORKFLOW_TITLE)
                $("#deactivateBtn").html(DEACTIVATE_STATUS_HTML)
                // $("#add_stage") // FORM
                document.querySelectorAll(".workflowItem")
                    .forEach((el) => el.classList.remove("active"));

                $(el).addClass('active');
                $(".detailsWorkflow")
                    .addClass('active')
                    .attr('data-workflowid', WORKFLOW_ID);
                loadWorkFlowStages(WORKFLOW_ID)
            }
            $("#saveStageBtn").click(function() {
                $.ajax({
                    url: "{{ route('roster.onboarding.stage.add') }}", // URL to send the request to
                    type: 'POST', // or 'POST'
                    data: $("#add_stage").serialize(), // Data to send with the request
                    beforeSend: function() {},
                    success: function(res) {
                        $(".sucMsg").addClass('d-none');
                        if (res.status) {
                            alertMsg('suc', res.message)
                            loadWorkFlowStages($("#workflow_id").val());
                            $("#addStage").modal('hide')
                            resetFormFunc()
                        }

                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        let errMsg = xhr.responseJSON.message ?? "Internal Server Error";
                        let errKey = xhr.responseJSON.key ?? null;
                        $(".errMsg").addClass('d-none')
                        if (xhr.status == 500) {
                            alertMsg(errMsg)
                        } else {
                            // $(".errMsg").removeClass('d-none')
                            if (errKey) {
                                $("#" + errKey + "_error").removeClass('d-none').html(errMsg)
                            }
                        }

                    }
                });

            });
            $(document).on('click', "#deactivateBtn", function() {
                let WORKFLOW_ID = $("#wf").attr('data-workflowid');
                // return;
                $.ajax({
                    url: "{{ route('roster.onboarding.workflow.status') }}", // URL to send the request to
                    type: 'POST', // or 'POST'
                    data: {
                        id: WORKFLOW_ID,
                        type: 'workflow',
                        _token: "{{ @csrf_token() }}"
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        if (typeof isAuthenticated === "function") {
                            if (isAuthenticated(res) == false) {
                                return false;
                            }
                        }
                        if (res.status) {
                            let status = res.data;
                            let STATUS_HTML = status == 1 ? "Active" : "Inactive";
                            let DEACTIVATE_STATUS_HTML = status == 1 ? "Deactivate" : "Activate";
                            let STATUS_HTML_COLOR = status == 0 ? 'muteBadges' : 'darkGreenBadges';
                            let STATUS_HTML_REMOVE_COLOR = status == 1 ? 'muteBadges' : 'darkGreenBadges';
                            let elem = $('.workflowItem[data-id="' + WORKFLOW_ID + '"]');

                            elem.find('.workflowStatusText').removeClass(STATUS_HTML_REMOVE_COLOR).addClass(
                                STATUS_HTML_COLOR).html(STATUS_HTML)

                            $("#deactivateBtn").html(DEACTIVATE_STATUS_HTML)
                            alertMsg('suc', res.message)
                        }
                        // loadWorkFlow();
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        let errMsg = xhr.responseJSON.message ?? "Internal Server Error";
                        alertMsg(errMsg)
                    }
                });



            })
            $(document).on('click', "#workFlowDelBtn", function() {
                let WORKFLOW_ID = $("#wf").attr('data-workflowid');
                let confirms = confirm('Are you sure you want to delete workflow ?');
                if (!confirms) {
                    return;
                }
                // return;
                $.ajax({
                    url: "{{ route('roster.onboarding.workflow.delete') }}", // URL to send the request to
                    type: 'POST', // or 'POST'
                    data: {
                        workflow_id: WORKFLOW_ID,
                        _token: "{{ @csrf_token() }}"
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        if (typeof isAuthenticated === "function") {
                            if (isAuthenticated(res) == false) {
                                return false;
                            }
                        }
                        if (res.status) {
                            document.querySelectorAll(".workflowItem")
                                .forEach((el) => el.classList.remove("active"));
                            $(this).addClass('active');
                            $(".detailsWorkflow").removeClass('active')
                            loadWorkFlow()
                            alertMsg('suc', errMsg)
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        let errMsg = xhr.responseJSON.message ?? "Internal Server Error";
                        alertMsg(errMsg)
                    }
                });
            })

            function stageDeleteFunc(id, elem) {
                let WORKFLOW_ID = $("#wf").attr('data-workflowid');
                let confirms = confirm('Are you sure you want to delete stage ?');
                if (!confirms) {
                    return;
                }
                $.ajax({
                    url: "{{ route('roster.onboarding.stages.delete') }}", // URL to send the request to
                    type: 'POST', // or 'POST'
                    data: {
                        id: id,
                        workflow_id: WORKFLOW_ID,
                        _token: "{{ @csrf_token() }}"
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        if (typeof isAuthenticated === "function") {
                            if (isAuthenticated(res) == false) {
                                return false;
                            }
                        }
                        if (res.status) {
                            alertMsg('suc', res.message)
                            loadWorkFlowStages(WORKFLOW_ID)
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        let errMsg = xhr.responseJSON.message ?? "Internal Server Error";
                        alertMsg(errMsg)
                    }
                });

            }

            function orderingFunc(id, order, workflow_id) {
                console.log("id :", id, 'Order: ', order, "workflow_id :", workflow_id);
                // return;
                $.ajax({
                    url: "{{ route('roster.onboarding.stages.ordering') }}", // URL to send the request to
                    type: 'POST', // or 'POST'
                    data: {
                        id: id,
                        order: order,
                        _token: "{{ @csrf_token() }}"
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        if (typeof isAuthenticated === "function") {
                            if (isAuthenticated(res) == false) {
                                return false;
                            }
                        }
                        if (res.status) {
                            alertMsg('suc', res.message)
                            loadWorkFlowStages(workflow_id);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        let errMsg = xhr.responseJSON.message ?? "Internal Server Error";
                        alertMsg(errMsg)
                    }
                });
            }

            function editStageFunc(id, elem) {
                $("#add_stage").find('.modal-title').html('Update Stage')
                $.ajax({
                    url: "{{ route('roster.onboarding.stages.details') }}", // URL to send the request to
                    type: 'POST', // or 'POST'
                    data: {
                        id: id,
                        _token: "{{ @csrf_token() }}"
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        resetFormFunc()
                        if (typeof isAuthenticated === "function") {
                            if (isAuthenticated(res) == false) {
                                return false;
                            }
                        }
                        if (res.status) {
                            $("#addStage").modal('show')
                            let stage_id = res.data.id;
                            let workflow_id = res.data.onboarding_config_id;
                            let stage_name = res.data.stage_name;
                            let status_name = res.data.status_name;
                            let entity_type_id = res.data.entity_type_id;
                            let description = res.data.description;
                            let required_stage = res.data.required_stage;
                            let auto_create_task = res.data.auto_create_task;
                            $("#stage_id").val(stage_id);
                            $("#workflow_id").val(workflow_id);
                            $("#stage_name").val(stage_name);
                            $("#status_name").val(status_name);
                            $("#description").val(description);
                            $("#entity_type_id").val(entity_type_id).change();
                            $('input[name="required_stage"]').prop('checked', required_stage == 1 ? true : false)
                                .val(1);
                            $('input[name="auto_create_task"]').prop('checked', auto_create_task == 1 ? true :
                                false).val(1);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        let errMsg = xhr.responseJSON.message ?? "Internal Server Error";
                        alertMsg(errMsg)
                    }
                });
            }
        </script>
    </main>
@endsection
