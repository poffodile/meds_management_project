<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
@extends('frontEnd.layouts.master')
@section('title', 'Carer')
@section('content')

@include('frontEnd.roster.common.roster_header')
<style>
    .action-buttons {
        display: flex;
        gap: 12px;
    }

    /* Export Button */
    .btn-export {
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        color: #374151;
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 6px;
    }

    .btn-export:hover {
        background: #e5e7eb;
    }

    /* Add Carer Button */
    .btn-add-carer {
        background: #2563eb;
        border: none;
        color: #fff;
        font-weight: 500;
        padding: 8px 18px;
        border-radius: 6px;
    }

    .btn-add-carer:hover {
        background: #1d4ed8;
    }
</style>
<main class="page-content">
    <div class="container-fluid">
        <div class="topHeaderCont">
            <div>
                <h1>Carers</h1>
                <p class="header-subtitle">Manage your care team</p>
            </div>
            <div class="action-buttons">
                <button class="btn btn-export add_staff" data-mode="export"><i class="fa fa-download"></i> Export </button>
                <button class="btn btn-add-carer add_staff openStaffModal" data-mode="add"> <i class="fa fa-plus"></i> Add Carer </button>
            </div>

            <!-- <div class="header-actions">
                <button class="btn add_staff openStaffModal" data-mode="add"> Export </button>
                <button class="btn add_staff openStaffModal" data-mode="add"><i class="fa fa-plus"></i> Add Carer</button>
            </div> -->
        </div>

        <div class="rota_dashboard-cards simpleCard">
            <div class="rota_dash-card blue">
                <div class="rota_dash-left">
                    <p class="rota_title">Total Carers</p>
                    <h2 class="rota_count" id="countAll">{{ $counts['all'] }}</h2>
                </div>
            </div>

            <div class="rota_dash-card orangeClr">
                <div class="rota_dash-left">
                    <p class="rota_title">Active</p>
                    <h2 class="rota_count greenText" id="countActive">{{ $counts['active'] }}</h2>
                </div>
            </div>

            <div class="rota_dash-card green">
                <div class="rota_dash-left">
                    <p class="rota_title">On Leave</p>
                    <h2 class="rota_count orangeText" id="countLeave">{{ $counts['on_leave'] }}</h2>
                </div>
            </div>

            <div class="rota_dash-card redClr">
                <div class="rota_dash-left">
                    <p class="rota_title">Inactive</p>
                    <h2 class="rota_count" id="countInactive">{{ $counts['inactive'] }}</h2>
                </div>
            </div>
        </div>

        <div class="calendarTabs leaveRequesttabs m-t-20">
            <div class="tabs">
                <div class="input-group searchWithtabs">
                    <span class="input-group-addon btn-white"><i class="fa fa-search"></i></span>
                    <input type="text" class="form-control" placeholder="Username">
                </div>
                <button class="tab active" data-tab="allCarerActibity">All</button>
                <button class="tab" data-tab="activeCarer">Active</button>
                <button class="tab" data-tab="onLeaveCarer">On Leave</button>
                <button class="tab" data-tab="inactiveCarer">Inactive</button>
            </div>

            <!-- TAB CONTENT -->
            <div class="tab-content carertabcontent">
                <div class="content active" id="allCarerActibity">
                    <div class="row"></div>
                </div>
                <div class="content" id="activeCarer">
                    <div class="row"></div>
                </div>

                <div class="content" id="onLeaveCarer">
                    <div class="row"></div>
                </div>

                <div class="content" id="inactiveCarer">
                    <div class="row"></div>
                </div>
            </div>
            <!-- END TAB CONTENT -->
        </div>
    </div>
    </div>
    </div>

    @include('frontEnd.systemManagement.elements.add_staff')

    <script>
        let currentTab = 'allCarerActibity';
        let searchTimer;

        const tabs = document.querySelectorAll(".tab");
        const contents = document.querySelectorAll(".content");

        function activateTab(tab) {
            // Change active tab UI
            document.querySelector(".tab.active")?.classList.remove("active");
            tab.classList.add("active");

            // Set current tab
            currentTab = tab.getAttribute("data-tab");

            // CLEAR SEARCH INPUT
            $('.searchWithtabs input').val('');
            clearTimeout(searchTimer);

            // Switch content
            contents.forEach(content => content.classList.remove("active"));
            document.getElementById(currentTab).classList.add("active");

            // Load fresh data WITHOUT search
            loadStaff(currentTab, '');
        }


        tabs.forEach(tab => {
            tab.addEventListener("click", () => activateTab(tab));
        });
    </script>

    <script>
        /* -------------------------
                PAGE LOAD
            -------------------------- */
        $(document).ready(function() {
            loadStaff('allCarerActibity');
        });

        /* -------------------------
        SEARCH (USERNAME)
        -------------------------- */
        $('.searchWithtabs input').on('keyup', function() {
            clearTimeout(searchTimer);
            const $input = $(this);

            searchTimer = setTimeout(() => {
                const keyword = $input.val().trim();
                // Request server with current tab and search term; server returns filtered data + counts
                loadStaff(currentTab, keyword);
            }, 300);
        });

        /* -------------------------
        LOAD STAFF (AJAX)
        -------------------------- */

        function loadStaff(type, search = '') {
            $.ajax({
                url: "{{ url('/roster/carer/getStaffByStatus') }}",
                type: "POST",
                data: {
                    type: type,
                    search: search,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (!res.status) return;

                    // DATA
                    renderStaff(type, res.data);
                }
            });
        }


        /* -------------------------
        RENDER STAFF
        -------------------------- */
        function renderStaff(type, staff) {
            const container = document.querySelector(`#${type} .row`);
            container.innerHTML = '';

            if (staff.length === 0) {
                container.innerHTML = `
                        <div class="leave-card">
                            <div class="leavebanktabCont">
                                <h4>No carers found</h4>
                            </div>
                        </div>`;
                return;
            }

            const BASE_URL = "{{ url('/') }}";

            staff.forEach(carer => {

                const ec = carer.emergency_contacts || {};

                container.innerHTML += `
                            <div class="col-md-4">
                                <div class="profile-card">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="avatar">${carer.name.charAt(0)}</div>
                                            <div class="info">
                                              <div class="name">
                                                <a href="${BASE_URL}/roster/carer-details/${carer.id}">
                                                    ${carer.name}
                                                </a>
                                            </div>
                                           <div class="role">
                                            ${({
                                                full_time: 'Full Time',
                                                part_time: 'Part Time',
                                                contract: 'Contract'
                                            }[carer.employment_type] || '')}
                                        </div>


                                        </div>
                                    </div>
                                    <span class="status 
                                        ${carer.status == 1 ? 'greenShowbtn' : carer.status == 2 ? 'radShowbtn' : 'inactive'}">
                                        ${carer.status == 1 ? 'Active' : carer.status == 2 ? 'On Leave' : 'Inactive'}
                                    </span>

                                </div>

                                <div class="details">
                                    <div class="item">
                                        <i class="fa-solid fa-phone"></i> <span>${carer.phone_no ?? '-'}</span>
                                    </div>
                                    <div class="item">
                                        <i class="fa-regular fa-envelope"></i> <span>${carer.email ?? '-'}</span>
                                    </div>
                                    <div class="item">
                                        <i class="fa-solid fa-location-dot"></i><span>${carer.current_location || 'No Address'}</span>
                                    </div>
                                </div>

                                       <!-- ✅ Qualifications -->
                                        <div class="sectionCarer">
                                            <div class="label">Qualifications:</div>
                                            <div class="tags care-list">
                                                ${renderQualifications(carer.qualifications)}
                                            </div>
                                        </div>

                                    <div class="rate">
                                        <div class="label">Hourly Rate</div>
                                        <div class="amount">£${(carer.hourly_rate ?? 0).toFixed(2)}</div>
                                    </div>

                                    <div class="supervision">
                                        <i class="fa-regular fa-clipboard"></i> <span>Supervision: No supervision</span>
                                    </div>

                                    <div class="actions">
                                        <button class="edit openStaffModal"
                                            data-mode="edit"
                                            data-id="${carer.id}"
                                            data-name="${carer.name ?? ''}"
                                            data-username="${carer.user_name ?? ''}"
                                            data-email="${carer.email ?? ''}"
                                            data-phone="${carer.phone_no ?? ''}"
                                            data-status="${carer.status ?? ''}"
                                            data-access-level="${carer.access_level ?? ''}"

                                            data-job-title="${carer.job_title ?? ''}"
                                            data-department="${carer.department ?? ''}"
                                            data-description="${carer.description ?? ''}"
                                            data-employment-type="${carer.employment_type ?? ''}"
                                            data-pay-rate="${carer.pay_rate ?? ''}"

                                            data-image="${carer.image ?? ''}"
                                            data-hourly-rate="${carer.hourly_rate ?? ''}"

                                            data-emergency_contact_name="${ec.name || ''}"
                                            data-emergency_contact_phone="${ec.phone_no || ''}"
                                            data-emergency_contact_relationship="${ec.relationship || ''}"

                                            data-payroll="${carer.payroll ?? ''}"

                                            data-dbs_expiry_date="${carer.dbs_expiry_date ?? ''}"
                                            data-dbs_certificate_number="${carer.dbs_certificate_number ?? ''}"

                                            data-date-of-joining="${carer.date_of_joining ?? ''}"
                                            data-date-of-leaving="${carer.date_of_leaving ?? ''}"

                                            data-holiday-entitlement="${carer.holiday_entitlement ?? ''}"
                                            data-overtime-availability="${carer.available_for_overtime ?? ''}"
                                            data-max-extra-hours="${carer.max_extra_hours ?? ''}"
                                            data-current-location="${carer.current_location ?? ''}"

                                            data-personal-info="${carer.personal_info ?? ''}"
                                            data-banking-info="${carer.banking_info ?? ''}"
                                            data-qualification-info="${carer.qualification_info ?? ''}"

                                            data-qualifications='${JSON.stringify(carer.qualifications ?? [])}'>
                                            <i class="fa-regular fa-pen-to-square"></i> Edit
                                        </button>
                                        <button class="delete deleteCarer"
                                            data-id="${carer.id}">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                        </div>
                                    </div>
                            </div>`;
            });
        }

        function renderQualifications(qualifications) {
            if (!qualifications || qualifications.length === 0) {
                return `<span>No qualifications</span>`;
            }

            return qualifications.map(q => {
                // q can be object or string
                return `<span>${q.name ?? q}</span>`;
            }).join('');
        }

        $(document).on('click', '.deleteCarer', function() {
            let carerId = $(this).data('id');

            if (!confirm('Are you sure you want to delete this carer?')) {
                return;
            }

            $.ajax({
                url: "{{ url('/roster/carer/delete') }}",
                type: "POST",
                data: {
                    carer_id: carerId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.status) {
                        // alert(response.message);
                        location.reload(); // or remove row dynamically
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('Server error. Please try again.');
                }
            });
        });
    </script>

    @endsection
</main>