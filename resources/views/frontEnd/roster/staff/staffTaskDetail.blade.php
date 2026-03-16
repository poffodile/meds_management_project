@extends('frontEnd.layouts.master')
@section('title', 'Staff Task')
@section('content')
    @include('frontEnd.roster.common.roster_header')
    <main class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="staffHeaderp p-4 gap-5 bgWhite" style="border-bottom:1px solid #ddd">
                        <div>
                            <div class="d-flex gap-3 align-items-center">
                                <i class=" fs23 bx  bx-arrow-left-stroke cursor-pointer"
                                    onclick="window.location='{{ route('roster.staff.task') }}'"></i>
                                <div>

                                    <h1 class="mainTitlep"><?php echo isset($singleData) ? $singleData->title : ''; ?></h1>
                                    <p class="header-subtitle mb-0"> <i
                                            class="bx bx-calendar f18 me-2"></i><?php echo isset($singleData) ? date('d M Y \a\t H:i', strtotime($singleData->created_at)) : ''; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <?php
                            $statusArr = [0 => ['Pending', 'yellowBadges'], 1 => ['Completed', 'greenbadges'], 2 => ['In Progress', 'buleBadges', 3 => ['Resolved', 'yellowBadges']]];
                            ?>
                            <span
                                class="careBadg {{ $statusArr[$singleData->status][1] }}">{{ $statusArr[$singleData->status][0] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt20 d-flex justify-content-center">
                <div class="col-lg-10">
                    <div class="emergencyMain p-4">
                        <p class="competeMentalSt"><?php echo isset($singleData) ? $singleData->description : ''; ?></p>
                    </div>
                </div>
            </div>
            <div class="row d-flex justify-content-center mt20">

                <div class="col-lg-10">
                    <input type="hidden" name="staff_task_id" id="staff_task_id" value="{{ $singleData->id }}">
                    <input type="hidden" name="is_form_filled" id="is_form_filled"
                        value="{{ $singleData->is_form_filled }}">
                    <input type="hidden" name="formid" id="formid" value="{{ $singleData->form_template_id }}">
                    <input type="hidden" id="form_template" value="{{ $singleData->form_template }}">
                    @if (isset($formTemplate))
                        <input type="hidden" id="home_id" value="{{ $singleData->home_id }}">
                        <form id="TopFormss">
                            <div class="emergencyMain aiInciDetaReport rounded8">
                                <div class="cardHeaderp aIInsightsheader p24 rounded8" style="border-bottom:unset">
                                    <div>
                                        <h2 class="h2Head">
                                            {{ $formTemplate->title }}
                                        </h2>
                                        <p class="muteText"><?php echo $formTemplate->detail; ?></p>
                                        {{-- <div class="mt-3">
                                            <span class="careBadg darkBlackBadg healthcare">healthcare</span>
                                        </div> --}}
                                    </div>
                                </div>
                                <div class="p24">
                                    <div class="calendarTabs tabStaffDe">
                                        {{-- LOAD DYNAMIC FORMS --}}
                                        <div id="formiotest"></div>

                                        <div class="d-flex justify-content-between align-items-center mt20 pt24"
                                            style="border-top:1px solid #ddd">
                                            {{-- <div class="d-flex gap-3 flexWrap">
                                            <div>
                                                <button class="borderBtn">Previous</button>
                                            </div>
                                            <div>
                                                <button class="borderBtn">Save Draft</button>
                                            </div>
                                        </div> --}}
                                            <div>
                                                <button type="button" class="bgBtn blackBtn" id="submitForm">
                                                    Submit
                                                </button>
                                            </div>
                                        </div>
                                        <div id="sucsMsg" class="d-none alert alert-success mt-2">

                                        </div>
                                        <!-- END TAB CONTENT -->
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                    <div class="emergencyMain p24 rounded8 mt20">
                        <h5 class="h5Head">Complete Task
                        </h5>
                        <div class="mt20">
                            <form action="">

                                <label class="formLabel">Completion Notes</label>
                                <textarea name="morning" required="" class="form-control" rows="3" cols="20"
                                    placeholder="Additional details..."><?php echo isset($singleData) ? $singleData->complete_notes : ''; ?></textarea>
                                <div class="purpleBox p-4 reportyellowBox mt-4">
                                    <div class="d-flex gap-3 align-items-center">
                                        <div>
                                            <i class="darkyellowIc bx bx-alert-circle f20"></i>
                                        </div>
                                        <div class="">
                                            <p class="mb-0" for="safeguarding"> Please complete and submit the form
                                                above before marking this task as complete.
                                            </p>

                                        </div>
                                    </div>

                                </div>
                                <div class="d-flex justify-content-end gap-3 mt20 ">
                                    <div>
                                        <button class="borderBtn">
                                            Save & Exit
                                        </button>
                                    </div>
                                    <div>
                                        <button class="bgBtn pgreenBtn"><i class="bx bx-check-circle me-3 f18"></i>
                                            Mark Complete</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <script>
            function loaddataontables() {

                let formid = $("#formid").val();
                let home_id = $("#home_id").val();
                var token = "<?= csrf_token() ?>";
                //alert(token);
                var settings = {
                    "url": "{{ url('/service/patterndataformio') }}",
                    "method": "POST",
                    "data": {
                        patterndata: formid,
                        home_id: home_id,
                        _token: token
                    },
                    //dataType: "json",
                };
                $.ajax(settings).done(function(response) {
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                    //console.log(response);
                    Formio.createForm(document.getElementById('formiotest'), {
                        components: JSON.parse(response)
                    });
                });
            }

            function viewdatawithvalueFormios() {
                // console.log($('#dynamic_form_idformio').val());
                let staff_task_id = $("#staff_task_id").val();
                var token = "<?= csrf_token() ?>";
                var settings = {
                    "url": "{{ route('roster.stafftask.form.fetch') }}",
                    "method": "POST",
                    "data": {
                        staff_task_id: staff_task_id,
                        _token: token
                    },
                    //dataType: "json",
                };
                $.ajax(settings).done(function(response) {
                    // console.log(response[0].pattern);
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                    Formio.createForm(document.getElementById('formiotest'), {
                        components: JSON.parse(response.pattern)
                    }, {
                        readOnly: false
                    }).then(function(form) {
                        form.submission = {
                            data: JSON.parse(response.pattern_value)
                        }
                        // form.getComponent('email').setValue('rksonkar356@gmail.com');
                    });

                });
            }
            $(document).ready(function() {
                let is_form_filled = $("#is_form_filled").val() == 0 && $("#formid").val();
                if (is_form_filled) {
                    loaddataontables();
                } else if ($("#is_form_filled").val() == 1 && $("#formid").val()) {
                    // loaddataontables();
                    viewdatawithvalueFormios();
                }

                $(document).on('click', "#submitForm", function() {
                    let staff_task_id = $("#staff_task_id").val();
                    var token = "<?= csrf_token() ?>";
                    let forms = $("#TopFormss").serialize() + "&_token=" +
                        token + "&staff_task_id=" + staff_task_id; // $(this).closest('form').attr('id');
                    // console.log(forms);
                    // return;

                    // return;
                    $.ajax({
                        url: "{{ route('roster.stafftask.form.save') }}", // URL to send the request to
                        type: 'POST', // or 'POST'
                        data: forms, // Data to send with the request
                        beforeSend: function() {},
                        success: function(res) {
                            if (res.status) {

                                $("#sucsMsg")
                                    .attr("tabindex", -1)
                                    .addClass('alert-success')
                                    .removeClass('alert-danger')
                                    .show()
                                    .html(res.message)
                                    .focus().fadeOut(5000);
                            } else {
                                $("#sucsMsg")
                                    .attr("tabindex", -1)
                                    .removeClass('alert-success')
                                    .addClass('alert-danger')
                                    .show()
                                    .html('Something went wrong !!')
                                    .focus().fadeOut(5000);
                            }
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            $("#sucsMsg")
                                .attr("tabindex", -1)
                                .removeClass('alert-success')
                                .addClass('alert-danger')
                                .show()
                                .html('Something went wrong !!')
                                .focus().fadeOut(5000);
                        }
                    });

                });
            })
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

        <!-- <script>
            document.addEventListener("click", function(e) {

                // ADD ROW
                if (e.target.closest(".add-row-btn")) {
                    const wrapper = e.target.closest(".js-dynamic-table");
                    const tbody = wrapper.querySelector("tbody");
                    const template = tbody.querySelector(".js-row-template");

                    const newRow = template.cloneNode(true);

                    // reset fields (inputs, checkboxes, radios, selects)
                    newRow.querySelectorAll("input, select, textarea").forEach(el => {
                        if (el.type === "checkbox" || el.type === "radio") {
                            el.checked = false;
                        } else {
                            el.value = "";
                        }
                    });

                    tbody.appendChild(newRow);
                }

                // DELETE ROW
                if (e.target.closest(".delete-row-btn")) {
                    e.target.closest("tr").remove();
                }

            });
        </script> -->

        <script>
            document.addEventListener("click", function(e) {

                // ADD ROW
                if (e.target.closest(".add-row-btn")) {
                    const wrapper = e.target.closest(".js-dynamic-table");
                    const tbody = wrapper.querySelector("tbody");
                    const template = tbody.querySelector(".js-row-template");

                    if (!template) return; // safety check

                    const newRow = template.cloneNode(true);
                    newRow.classList.remove("js-row-template", "d-none");

                    newRow.querySelectorAll("input, select, textarea").forEach(el => {
                        if (el.type === "checkbox" || el.type === "radio") {
                            el.checked = false;
                        } else {
                            el.value = "";
                        }
                    });

                    tbody.appendChild(newRow);
                }

                // DELETE ROW
                if (e.target.closest(".delete-row-btn")) {
                    const row = e.target.closest("tr");

                    // don't delete template
                    if (!row.classList.contains("js-row-template")) {
                        row.remove();
                    }
                }

            });
        </script>


        <!-- script start -->
        <!-- tab -->

        <!-- tab end -->

        <!-- script end -->
    </main>
@endsection
