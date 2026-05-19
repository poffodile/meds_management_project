$(document).on('click', '.saveClientAlert', function () {
    var errorClientAlert = 0;
    $('.checkClientAlert').each(function () {
        if ($(this).val() == '' || $(this).val() == undefined) {
            $(this).css('border', '1px solid red').focus();
            errorClientAlert = 1;
            return false;
        } else {
            $(this).css('border', '');
            errorClientAlert = 0;
        }
    });
    if (errorClientAlert == 1) {
        return false;
    } else {
        var data = new FormData($("#clientAlertForm")[0]);
        data.append('client_id', client_id);
        $.ajax({
            type: "POST",
            url: saveClientAlertUrl,
            data: data,
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function (response) {
                console.log(response);
                if (typeof isAuthenticated === "function") {
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                }
                if (response.success === true) {
                    location.reload()
                }
            },
            error: function (xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + error);
            }
        });
    }
});

$(document).on('change', '#requires_staff_acknowledgment', function () {
    if ($(this).is(':checked')) {
        $(this).val(1);
    } else {
        $(this).val(0);
    }
});
$(document).ready(function () {
    getAlerts();
});
var selectAllAlerts = false;
function getAlerts(pageUrl = listAlertTypeUrl) {
    var severity_AlertFilter = $('.severity_AlertFilter option:selected').val();
    var status_alertFilter = $('.status_alertFilter option:selected').val();
    var type_alertFilter = $('.type_alertFilter option:selected').val();
    var sortby_alertFilter = $('.sortby_alertFilter option:selected').val();
    $.ajax({
        type: "POST",
        url: pageUrl,
        data: { client_id: client_id, severity: severity_AlertFilter, status: status_alertFilter, type: type_alertFilter, sort_by: sortby_alertFilter, _token: token },
        success: function (response) {

            console.log(response);
            // return false;
            if (typeof isAuthenticated === "function") {
                if (isAuthenticated(response) == false) {
                    return false;
                }
            }
            if (response.success === true) {
                var clientAlerttable = document.getElementById('renderHtmlClientAlert');
                clientAlerttable.innerHTML = '';
                var alertsData = response.data;

                let clientAlertHtmlData = '';
                let activeAlertsCount = 0;
                let criticalAlertsCount = 0;
                var actualRosolveAlertCount = 0;
                let checked = selectAllAlerts ? 'checked' : '';
                alertsData.forEach(function (val) {
                    let clientAlertBorderClass = '';
                    let careTaskTagClass = '';
                    if (val.severity === 'Low') {
                        clientAlertBorderClass = 'blueBorder';
                        careTaskTagClass = 'blueBorderBadg';
                    } else if (val.severity === 'Medium') {
                        clientAlertBorderClass = 'yellowBorder';
                        careTaskTagClass = 'yellowBorderBadg';
                    } else if (val.severity === 'High') {
                        clientAlertBorderClass = 'orangeBorder';
                        careTaskTagClass = 'orangeBorderBadg';
                    } else if (val.severity === 'Critical') {
                        criticalAlertsCount++;
                        clientAlertBorderClass = 'redBorder';
                        careTaskTagClass = 'redBorderBadg';
                    }

                    let statusBadge = '';
                    let status = '';
                    let alertBtnSection = '';
                    if (val.status == 1) {
                        statusBadge = 'greenbadges';
                        status = 'active';
                        activeAlertsCount++;
                        if (val.severity != 'Critical') {
                            actualRosolveAlertCount++;
                        }
                    } else if (val.status == 2) {
                        statusBadge = 'muteBadges';
                        status = 'resolved';
                        alertBtnSection = 'style="display:none"';
                    } else if (val.status == 3) {
                        statusBadge = 'purpleBadges';
                        status = 'archived';
                        alertBtnSection = 'style="display:none"';
                    }
                    let createdDate = moment(val.created_at).format('MMM DD');
                    let staff_acknowledgment_DBcount = val.staff_acknowledgment_count;
                    let staff_acknowledgment_html = '';
                    if (staff_acknowledgment_DBcount) {
                        staff_acknowledgment_html = `(${staff_acknowledgment_DBcount})`;
                    }
                    let styleForResolveDate = 'style="display:none"';
                    if (val.resolve_date) {
                        styleForResolveDate = 'style="display:block"';
                        alertBtnSection = 'style="display:none"';
                    }
                    let resolve_date = moment(val.resolve_date).format('MMM DD, HH:mm');
                    clientAlertHtmlData += `<div class="${clientAlertBorderClass} borderLeftThick  rounded8 urReqSec p-3 manageDSysAlrt mt-2 mb-2">
                                    <div class="dFlexNoAlign">
                                        <div>
                                            <input class="checkBoxHW trans alertCheck" type="checkbox" ${checked}>
                                        </div>
                                        <div class="flex1">
                                            <div class="dFlexNoAlign flexWrap">
                                                <div>
                                                    <i class="bx bx-alert-circle redtext f18"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 h6Head font600 blackText">${val.alert_title}
                                                    </h6>
                                                </div>
                                                <div>
                                                    <span class="carebadg ${careTaskTagClass}">
                                                        ${val.severity}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="careBadg ${statusBadge}" id="statusAlert_${val.id}">
                                                        ${status}
                                                    </span>
                                                </div>
                                                <div class="userMum ">
                                                    <span class="title bgWhite50 mt-0 hoverBg">${val.alert_types.title}</span>
                                                </div>
                                            </div>
                                            <p class="fs12 textGray">${truncateText(val.description, 40)}</p>`;
                    if (val.alert_type_id == 7) {
                        clientAlertHtmlData += `<div>
                                                    <span class="careBadg yellowBorderLight yellowHoverUnset">
                                                        Requires Individual Review
                                                    </span>
                                                </div>`;
                    }
                    if (val.action_required) {
                        clientAlertHtmlData += `<div class="bg-blue-50 fs12 p-2 rounded8 mt-3 mb-2">
                                                    <p class=" font700 darkBlueTextp mb-1">Required Action: </p>
                                                    <p class=" darkBlueTextp mb-0">
                                                        ${truncateText(val.action_required, 40)}
                                                    </p>
                                                </div>`;
                    }
                    clientAlertHtmlData += `<div class="dFlexNoAlign fs12 textGray">

                                                <p class="mb-2">Created: <span class="font600 blackText me-3">${createdDate}</span>by Unknown Staff</p>


                                            </div>
                                            <div>
                                                <p class="mb-2 fs12 textGray verticalCenter"> <i class="bx bx-eye  me-1"></i>Shown on: <span class="font600 blackText ms-1"> dashboard, medication, all</span></p>
                                            </div>`;
                    if (val.requires_staff_acknowledgment == 1) {
                        clientAlertHtmlData += `<div class="bg-yellow-50 P-2 rounded8">
                                                    <div class="flexBw">
                                                        <div>
                                                            <p class="fs12 mb-0 darkOrangeTextp"><i class="bx bx-bell me-2"></i>Requires Acknowledgment <span id="acknowledgmentCount_${val.id}">${staff_acknowledgment_html}</span>
                                                            </p>
                                                        </div>
                                                        <div class="userMum">
                                                            <span class="title bgWhite50 hoverBg mt-0 increase_acknowledge" data-id="${val.id}"><i class="bx bx-check-circle f18 me-2"></i>Acknowledge</span>
                                                        </div>
                                                    </div>
                                                </div>`;
                    }

                    clientAlertHtmlData += `<div class="bg-greenp-50 P-2 rounded8 mt-2" ${styleForResolveDate} id="styleForResolveDate_${val.id}">
                                                    <div>
                                                        <p class="fs12 mb-0 darkGreenTextp"><span class="font700 me-1">Resolved:</span> <span id="resolveDate_${val.id}">${resolve_date} by Unknown Staff</span>
                                                        </p>
                                                    </div>
                                                </div>`;
                    clientAlertHtmlData += `<div class="dFlexNoAlign mt-2 allertMsgBtn" id="alertBtnSection_${val.id}" ${alertBtnSection}>
                                                <div class="userMum">
                                                    <span class="title pgreenBtn hoverBg mt-0 alert_resolve" data-id="${val.id}" style="color: #fff;"><i class="bx bx-check-circle f18 me-2"></i>Resolve</span>
                                                </div>

                                                <div class="userMum ">
                                                    <span class="title bgWhite50 hoverBg mt-0 " onclick="alert_archived(${val.id})"><i class="bx bx-archive-alt f18 me-2"></i>Archive</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                });

                if (response.total > 0) {
                    $("#renderHtmlClientAlert").html(clientAlertHtmlData);
                } else {
                    $("#renderHtmlClientAlert").html(`<div class="leavebanktabCont">
                            <i class='bx  bx-alert-triangle'></i>
                            <p>No alerts match the selected filters</p>
                        </div>`);
                }
                $("#activeAlertsCount").text(activeAlertsCount + " Active");
                $("#criticalAlertsCount").text(criticalAlertsCount + " Critical");
                $("#rosolveAlertCount").text("Resolve (" + actualRosolveAlertCount + ")");
                $("#actualRosolveAlertCount").val(actualRosolveAlertCount);
                var paginationControls = $("#clientAlertPagination");
                paginationControls.empty();

                if (response.pagination.prev_page_url) {
                    paginationControls.append(
                        `<button class="profileDrop me-3" onclick="getAlerts('${response.pagination.prev_page_url}')">Previous</button>`
                    );
                }
                if (response.pagination.next_page_url) {
                    paginationControls.append(
                        `<button class="profileDrop" onclick="getAlerts('${response.pagination.next_page_url}')">Next</button>`
                    );
                }
            } else {
                alert("Something went wrong");
                return false;
            }
        },
        error: function (xhr, status, error) {
            var errorMessage = xhr.status + ': ' + xhr.statusText;
            alert('Error - ' + errorMessage + "\nMessage: " + error);
        }
    });
}
$(document).on('click', '.increase_acknowledge', function () {
    var id = $(this).data('id');
    $.ajax({
        type: "POST",
        url: increaseAcknowledgeUrl,
        data: { id: id, _token: token },
        success: function (response) {
            console.log(response);
            if (typeof isAuthenticated === "function") {
                if (isAuthenticated(response) == false) {
                    return false;
                }
            }
            if (response.success === true) {
                $("#acknowledgmentCount_" + id).text("(" + response.data.staff_acknowledgment_count + ")");
            }
        },
        error: function (xhr, status, error) {
            var errorMessage = xhr.status + ': ' + xhr.statusText;
            alert('Error - ' + errorMessage + "\nMessage: " + error);
        }
    });
});
$(document).on('click', '.alert_resolve', function () {
    var id = $(this).data('id');
    $.ajax({
        type: "POST",
        url: alert_resolveUrl,
        data: { id: id, _token: token },
        success: function (response) {
            console.log(response);
            if (typeof isAuthenticated === "function") {
                if (isAuthenticated(response) == false) {
                    return false;
                }
            }
            if (response.success === true) {
                let date = response.data.resolve_date;
                $("#styleForResolveDate_" + id).show();
                var resolve_date = moment(date).format('MMM DD, HH:mm');
                $("#resolveDate_" + id).text(resolve_date + " by unknown staff");
                $("#alertBtnSection_" + id).hide();
                $("#statusAlert_" + id).text('resolved').attr('class', '').addClass('muteBadges careBadg');
                var resolevedCount = $("#actualRosolveAlertCount").val();
                resolevedCount--;
                $("#rosolveAlertCount").text("Resolve (" + resolevedCount + ")");
                $("#actualRosolveAlertCount").val(resolevedCount);
            }
        },
        error: function (xhr, status, error) {
            var errorMessage = xhr.status + ': ' + xhr.statusText;
            alert('Error - ' + errorMessage + "\nMessage: " + error);
        }
    });
});
function alert_archived(id) {
    $.ajax({
        type: "POST",
        url: alert_archivedUrl,
        data: { id: id, _token: token },
        success: function (response) {
            console.log(response);
            // return false;
            if (typeof isAuthenticated === "function") {
                if (isAuthenticated(response) == false) {
                    return false;
                }
            }
            if (response.success === true) {
                $("#alertBtnSection_" + id).hide();
            }
        },
        error: function (xhr, status, error) {
            var errorMessage = xhr.status + ': ' + xhr.statusText;
            alert('Error - ' + errorMessage + "\nMessage: " + error);
        }
    });
}
$(document).on('click', '.all_acknowledgement', function () {
    $.ajax({
        type: "POST",
        url: increaseAllAcknowledgeUrl,
        data: { _token: token },
        success: function (response) {
            console.log(response);
            // return false;
            if (typeof isAuthenticated === "function") {
                if (isAuthenticated(response) == false) {
                    return false;
                }
            }
            if (response.success === true && response.data > 0) {
                getAlerts();
                selectAllAlerts = true;
            }
        },
        error: function (xhr, status, error) {
            var errorMessage = xhr.status + ': ' + xhr.statusText;
            alert('Error - ' + errorMessage + "\nMessage: " + error);
        }
    });
});
$(document).on('change', '#selectAllAllert', function () {

    let isChecked = $(this).prop('checked');

    $('.alertCheck').prop('checked', isChecked);

    updateSystemAlert();
});
$(document).on('change', '.alertCheck', function () {

    let total = $('.alertCheck').length;
    let checked = $('.alertCheck:checked').length;
    $('#selectAllAllert').prop('checked', total === checked);

    updateSystemAlert();
});

function updateSystemAlert() {

    const count = $('.alertCheck:checked').length;

    if (count > 0) {
        $('#actionBox').show();
    } else {
        $('#actionBox').hide();
    }

    $('#selectedCheckCount').text(count + " selected");
}
$(document).on('click', '#closeActionBox', function () {

    $('#actionBox').hide();

    $('.alertCheck').prop('checked', false);

    $('#selectAllAllert').prop('checked', false);
});
$(document).on('change', '.severity_AlertFilter', function () {
    getAlerts();
});
$(document).on('change', '.status_alertFilter', function () {
    getAlerts();
});
$(document).on('change', '.type_alertFilter', function () {
    getAlerts();
});
$(document).on('change', '.sortby_alertFilter', function () {
    getAlerts();
});