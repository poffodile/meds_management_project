function esc(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

$(document).on('click','.saveMedicationLogBtn',function(){
    let errorMedi = 0;
    $('.checkMediLog').each(function(){
        if($(this).val() == '' || $(this).val() == undefined){
            errorMedi = 1;
            $(this).css('border','1px solid red').focus();
            return false;
        }else{
            errorMedi = 0;
            $(this).css('border','');
        }
    });
    if(errorMedi == 1){
        return false;
    }else{
        var data = new FormData($("#medication_logsForm")[0]);
        data.append('client_id', client_id);
        $.ajax({
            type: "POST",
            url: saveMedicationLogUrl,
            data: data,
            contentType: false,
            cache: false,
            processData: false,
            success: function (response) {
                if (typeof isAuthenticated === "function") {
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                }
                if(response.success === true){
                    $("#medication_logsForm")[0].reset();
                    $("#medication_log_id").val('');
                    $(".medicationLogsForm").hide();
                    showMedicationList();
                }else{
                    alert(response.errors || response.message || 'Something went wrong');
                }
            },
            error: function (xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + error);
            }
        });
    }
});

$(document).on('click','.editMedicationLogBtn',function(){
    var btn = $(this);
    $("#medication_log_id").val(btn.data('id'));
    $("#medication_name").val(btn.data('name'));
    $("#dosage").val(btn.data('dosage'));
    $("#frequesncy").val(btn.data('frequency'));
    $("#administrator_date").val(btn.data('date'));
    $("#status").val(btn.data('status'));
    $("#witnessed_by").val(btn.data('witnessed'));
    $("#medication_log_notes").val(btn.data('notes'));
    $("#side_effect").val(btn.data('sideeffect'));
    $(".medicationLogsForm").show();
});

$(document).on('click','.deleteMedicationLogBtn',function(){
    if(!confirm('Are you sure you want to delete this medication log?')) return;
    var logId = $(this).data('id');
    $.ajax({
        type: "POST",
        url: deleteMedicationLogUrl,
        data: {id: logId, _token: token},
        success: function(response){
            if (typeof isAuthenticated === "function") {
                if (isAuthenticated(response) == false) return false;
            }
            if(response.success === true){
                showMedicationList();
            }else{
                alert(response.message || 'Something went wrong');
            }
        },
        error: function(xhr, status, error){
            alert('Error - ' + xhr.status + ': ' + xhr.statusText);
        }
    });
});

function setDateTimeFormat(){
    const input = document.getElementById('administrator_date');

    if (!input.value) {
        const now = new Date();
        const formatted =
            now.getFullYear() + '-' +
            String(now.getMonth() + 1).padStart(2, '0') + '-' +
            String(now.getDate()).padStart(2, '0') + 'T' +
            String(now.getHours()).padStart(2, '0') + ':' +
            String(now.getMinutes()).padStart(2, '0');

        input.value = formatted;
    }
}
function getMedication(){
    $("#marSheetBtn").addClass('active').click();
    $("#medicationLogsBtn").removeClass('active');
    showMedicationList();
    getCareTask();
}
function showMedicationList(pageUrl = listMedicationLogUrl){
    $.ajax({
        type: "POST",
        url: pageUrl,
        data: {client_id: client_id, _token: token},
        success: function (response) {
            if (typeof isAuthenticated === "function") {
                if (isAuthenticated(response) == false) {
                    return false;
                }
            }

            if (response.success === true) {
                var data = response.data;
                var table = document.getElementById('renderHtmlMedicalLogs');
                if(data.length === 0){
                    table.innerHTML = '<div class="text-center p-20" style="padding:30px;color:#888;"><i class="fa fa-medkit" style="font-size:40px;margin-bottom:10px;display:block;"></i>No medication logs recorded yet.<br>Click "Log Medication" to add the first entry.</div>';
                }else{
                    $("#countMedicationLogs").text("("+response.total+")");
                    table.innerHTML = '';
                    var STATUS_CONFIG = {
                        1: { label: 'Administered', color: 'greenbadges', icon: 'fa fa-check-circle' },
                        2: { label: 'Refused', color: 'yellowBadges', icon: 'fa fa-times-circle' },
                        3: { label: 'Missed', color: 'redbadges', icon: 'fa fa-exclamation-circle' },
                        4: { label: 'Not Required', color: 'muteBadges', icon: 'fa fa-minus-circle' }
                    };
                    var tableData = '';
                    data.forEach(function(val) {
                        var statusObj = STATUS_CONFIG[val.status] || { label: 'Unknown', color: 'default', icon: 'fa fa-question-circle' };

                        var adminDate = val.administrator_date ? moment(val.administrator_date).format('MMM DD, YYYY') : '';
                        var adminTime = val.administrator_date ? moment(val.administrator_date).format('HH:mm') : '';
                        var editDate = val.administrator_date ? moment(val.administrator_date).format('YYYY-MM-DDTHH:mm') : '';

                        tableData += '<div class="planCard borderleftPurple">';
                        tableData += '<div class="planTop">';
                        tableData += '<div class="planTitle">';
                        tableData += esc(val.medication_name) + ' <span class="careBadg ' + statusObj.color + '"> ' + statusObj.label + '</span>';
                        tableData += '</div>';
                        tableData += '<div class="planActions">';
                        tableData += '<button class="editMedicationLogBtn" title="Edit"'
                            + ' data-id="' + val.id + '"'
                            + ' data-name="' + esc(val.medication_name) + '"'
                            + ' data-dosage="' + esc(val.dosage) + '"'
                            + ' data-frequency="' + esc(val.frequesncy || '') + '"'
                            + ' data-date="' + editDate + '"'
                            + ' data-status="' + val.status + '"'
                            + ' data-witnessed="' + esc(val.witnessed_by || '') + '"'
                            + ' data-notes="' + esc(val.notes || '') + '"'
                            + ' data-sideeffect="' + esc(val.side_effect || '') + '"'
                            + '><i class="fa fa-pencil"></i></button>';
                        tableData += ' <button class="deleteMedicationLogBtn danger" title="Delete" data-id="' + val.id + '"><i class="fa fa-trash"></i></button>';
                        tableData += '</div></div>';

                        tableData += '<div class="planFooter"><span>Dosage:<strong> ' + esc(val.dosage) + ' </strong></span></div>';

                        if(val.frequesncy){
                            tableData += '<div class="planFooter"><span>Frequency: ' + esc(val.frequesncy) + '</span></div>';
                        }

                        tableData += '<div class="planMeta">';
                        tableData += '<div class="aligniconMedication"><i class="fa fa-clock-o"></i> ' + adminDate + ' at ' + adminTime + '</div>';
                        tableData += '<div class="aligniconMedication"><i class="fa fa-user"></i> By: ' + esc(val.user ? val.user.name : 'Staff') + '</div>';
                        tableData += '</div>';

                        if(val.witnessed_by){
                            tableData += '<div class="witnessedBy"><span><strong>Witnessed by:</strong> ' + esc(val.witnessed_by) + '</span></div>';
                        }
                        if(val.notes){
                            tableData += '<div class="witnessedBy witnessedByNotes"><span><strong>Notes:</strong> ' + esc(val.notes) + '</span></div>';
                        }
                        if(val.side_effect){
                            tableData += '<div class="witnessedBy witnessedBySideEffects yellow">';
                            tableData += '<strong class="aligniconMedication"><i class="fa fa-info-circle"></i> Side Effects:</strong>';
                            tableData += '<p>' + esc(val.side_effect) + '</p></div>';
                        }
                        tableData += '</div>';
                    });

                    $("#renderHtmlMedicalLogs").html(tableData);
                    var paginationControls = $("#medicationLogsPagination");
                    paginationControls.empty();
                    if (response.pagination.prev_page_url) {
                        paginationControls.append('<button class="profileDrop me-3" onclick="showMedicationList(\'' + response.pagination.prev_page_url + '\')">Previous</button>');
                    }
                    if (response.pagination.next_page_url) {
                        paginationControls.append('<button class="profileDrop" onclick="showMedicationList(\'' + response.pagination.next_page_url + '\')">Next</button>');
                    }
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
$(document).on('click','.saveCareTask',function(){
    var errorCareTask = 0;
    $('.checkCareTask').each(function(){
        if($(this).val() == '' || $(this).val() == undefined){
            $(this).css('border','1px solid red').focus();
            errorCareTask = 1;
            return false;
        }else{
            $(this).css('border','');
            errorCareTask = 0;
        }
    });
    if(errorCareTask == 1){
        return false;
    }else{
        $.ajax({
            type: "POST",
            url: careTaskFormSaveUrl,
            data: new FormData($("#careTaskForm")[0]),
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
                if(response.success === true){
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
$(document).on('change','.careTaskCheckBox',function(){
    if($(this).is(':checked')){
        $(this).val(1).prop('checked',true);
    }else{
        $(this).val(0).prop('checked',false);
    }
});
function getCareTask(pageUrl = listClientCareTaskUrl){
    $.ajax({
        type: "POST",
        url: pageUrl,
        data: {client_id:client_id,_token:token},
        success: function (response) {

            console.log(response);
            // return false;
            if (typeof isAuthenticated === "function") {
                if (isAuthenticated(response) == false) {
                    return false;
                }
            }

            if (response.success === true) {
                var clientCareTasktable = document.getElementById('renderHtmlClientCareTask');
                clientCareTasktable.innerHTML = '';

                let careClientTaskData = '';
                let groupedData = response.data;
                var taskCriticalCount = 0;
                var taskHighCount = 0;
                var tasktToStaffCount = 0; 
                Object.keys(groupedData).forEach(function(categoryId){
                    let tasks = groupedData[categoryId];
                    if(tasks.length === 0) return;
                    let categoryName = tasks[0].client_task_categorys
                        ? tasks[0].client_task_categorys.title
                        : 'Uncategorised';
                    let categoryCount = tasks.length;
                    careClientTaskData += `
                        <div class="caretasknameandnumber m-b-10">
                            ${categoryName} <span>${categoryCount}</span>
                        </div>
                        <div class="row">
                    `;
                    tasks.forEach(function(val){
                        let careTaskBorderClass = '';
                        let careTaskTagClass = '';
                        if(val.priority === 'Low'){
                            careTaskBorderClass = 'blueborderleft';
                            careTaskTagClass = 'yellow';
                        }else if(val.priority === 'Medium'){
                            careTaskBorderClass = 'blueborderleft';
                            careTaskTagClass = 'yellow';
                        }else if(val.priority === 'High'){
                            taskHighCount++;
                            careTaskBorderClass = 'HighBorderCare';
                            careTaskTagClass = 'orangeClrp';
                        }else if(val.priority === 'Critical'){
                            taskCriticalCount++;
                            careTaskBorderClass = 'redborderleft';
                            careTaskTagClass = 'radShowbtn';
                        }
                        if (val.two_person === 1) {
                            tasktToStaffCount++;
                        }
                        careClientTaskData += `
                            <div class="col-md-6 mb-3">
                                <div class="profile-card careTasksCard ${careTaskBorderClass} mb-0">
                                    <div class="card-header">
                                        <div class="user">
                                            <div class="info">
                                                <div class="name">
                                                    <a href="#!">${truncateText(val.task_title, 40)}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sectionCarer">
                                        <div class="tags">
                                            <span class="${careTaskTagClass|| 'orangeClr'}">${val.priority}</span>
                                            <span class="inactive">${val.frequency ?? ''}</span>
                                        </div>
                                    </div>

                                    <div class="details">
                                        <div class="item">
                                            <i class='bx bx-clock'></i>
                                            <span>${val.duration} minutes</span>
                                        </div>
                                        <div class="item redalrttext">
                                            <i class='bx  bx-alert-circle'></i> <span>Alerts: Missed</span>
                                        </div>
                                    </div>

                                    <div class="actions">
                                        <button type="button" class="edit edit_clientCareTask" data-id="${val.id}">
                                            <i class="fa-regular fa-pen-to-square"></i> Edit
                                        </button>
                                        <button type="button" class="delete delete_clientCareTask" data-id="${val.id}">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    careClientTaskData += `</div>`;
                });
                $("#clientCareTaskTotalCount").text(response.total);
                $("#clientCareTaskCriticalCount").text(taskCriticalCount);
                $("#clientCareTaskHighCount").text(taskHighCount);
                $("#clientCareTaskTwoStaffCount").text(tasktToStaffCount);
                if(response.total >0){
                    $("#renderHtmlClientCareTask").html(careClientTaskData);
                }else{
                    $("#renderHtmlClientCareTask").html(`<div class="leavebanktabCont">
                                <i class="bx bx-checklist"></i>
                                <h4>No care tasks defined yet</h4>
                                <p>Create tasks manually or use AI to generate from care needs

                                </p>
                                <div class="dFlexGap mt-4 justify-content-center">
                                    <button class="borderBtn"><i class="bx bx-sparkles-alt f18 me-2"></i>  Generate from Care Needs</button> 
                                    <button class="bgBtn blackBtn" type="button" onclick="window.location.href='${clientCareTaskAddUrl}'"><i class="bx bx-plus f18 me-2"></i>  Add Manually</button> 
                            </div>`);
                }
                var paginationControls = $("#clientCareTaskPagination");
                paginationControls.empty();

                if (response.pagination.prev_page_url) {
                    paginationControls.append(
                        `<button class="profileDrop me-3" onclick="getCareTask('${response.pagination.prev_page_url}')">Previous</button>`
                    );
                }
                if (response.pagination.next_page_url) {
                    paginationControls.append(
                        `<button class="profileDrop" onclick="getCareTask('${response.pagination.next_page_url}')">Next</button>`
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
function truncateText(text, maxLength = 30) {
    if (!text) return '';
    return text.length > maxLength
        ? text.substring(0, maxLength) + '...'
        : text;
}
$(document).on('click','.edit_clientCareTask',function(){
    var clientCareTaskId = $(this).data('id');
    var url = clientCareTaskEditUrl+"?task_id="+clientCareTaskId;
    // console.log(url);
    window.location.href=url;
});
$(document).on('click','.delete_clientCareTask',function(){
    var clientCareTaskId = $(this).data('id');
    $.ajax({
        type: "POST",
        url: clientCareTaskDeleteUrl,
        data: {id:clientCareTaskId,_token:token},
        success: function (response) {

            console.log(response);
            // return false;
            if (typeof isAuthenticated === "function") {
                if (isAuthenticated(response) == false) {
                    return false;
                }
            }

            if (response.success === true) {
                // location.reload();
                $('.ajax-alert-suc').show();
                $('.msg').text(response.message);
                $("#clientCareTasksTabBtn").click();
                setTimeout(function(){
                    $(".notification-box").fadeOut();
                    $('.msg').text("");
                }, 5000);
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
});