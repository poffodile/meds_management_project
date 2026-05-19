function esc(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

$(document).on('click','#saveClientDols',function(){
    var dols_status = $("#dols_status").val();
    if(dols_status == ''|| dols_status == undefined){
        $("#dols_status").css('border','1px solid red').focus();
        return false;
    }else{
        $("#dols_status").css('border','');
        var data = new FormData($("#clientDolsForm")[0]);
        data.append('client_id', client_id);
        $.ajax({
            type: "POST",
            url: saveDolsUrl,
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
                    $("#clientDolsForm")[0].reset();
                    $(".dolsSectionFirst").hide();
                    $(".dolsSectionSecond").show();
                    $('.ajax-alert-suc').show();
                    $('.msg').text(response.message);
                    showDolsList();
                    setTimeout(function(){
                        $(".notification-box").fadeOut();
                        $('.msg').text("");
                    }, 5000);
                } else {
                    alert(response.errors || response.message || 'Validation failed');
                }
            },
            error: function (xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage);
            }
        });
    }
});

function showDolsList(pageUrl = dolsListUrl){
    $.ajax({
        type: "POST",
        url: pageUrl,
        data: {client_id:client_id,_token:token},
        success: function (response) {
            if (typeof isAuthenticated === "function") {
                if (isAuthenticated(response) == false) {
                    return false;
                }
            }

            if (response.success === true) {
                var data=response.data.data;
                var table=document.getElementById('dolsRenderList');
                if(data.length === 0){
                    table.innerHTML="<p class='text-muted p-20'>No DoLS records found. Click 'New DoLS Record' to add one.</p>";
                }else{
                    table.innerHTML='';
                    let tableData = '';
                    data.forEach(function(val, key) {
                        let referral_date = val.referral_date ? moment(val.referral_date).format('MMMM Do, YYYY') : '';
                        let start_date = val.authorisation_start_date ? moment(val.authorisation_start_date).format('MMMM Do, YYYY') : '';
                        let end_date = val.authorisation_end_date ? moment(val.authorisation_end_date).format('MMMM Do, YYYY') : '';
                        let badgeColor = badgeColors(val.dols_status);
                        tableData += '<div class="planCard borderleftPurple">' +
                                    '<div class="planTop">' +
                                        '<div class="planTitle">' +
                                            '<span class="roundTag ' + esc(badgeColor) + '">' + esc(val.dols_status) + '</span>' +
                                            (val.authorisation_type ? '<span class="inactive roundTag">' + esc(val.authorisation_type) + '</span>' : '') +
                                        '</div>' +
                                        '<div class="planActions">' +
                                            '<button class="addDolsRecordBtn" data-formtype="edit"' +
                                                ' data-dols_status="' + esc(val.dols_status) + '"' +
                                                ' data-authorisation_type="' + esc(val.authorisation_type) + '"' +
                                                ' data-referral_date="' + esc(val.referral_date) + '"' +
                                                ' data-authorisation_start_date="' + esc(val.authorisation_start_date) + '"' +
                                                ' data-authorisation_end_date="' + esc(val.authorisation_end_date) + '"' +
                                                ' data-review_date="' + esc(val.review_date) + '"' +
                                                ' data-supervisory_body="' + esc(val.supervisory_body) + '"' +
                                                ' data-case_reference="' + esc(val.case_reference) + '"' +
                                                ' data-best_interests_assessor="' + esc(val.best_interests_assessor) + '"' +
                                                ' data-mental_health_assessor="' + esc(val.mental_health_assessor) + '"' +
                                                ' data-reason_for_dols="' + esc(val.reason_for_dols) + '"' +
                                                ' data-imca_appointed="' + esc(val.imca_appointed) + '"' +
                                                ' data-mental_capacity_assessment="' + esc(val.mental_capacity_assessment) + '"' +
                                                ' data-appeal_rights="' + esc(val.appeal_rights) + '"' +
                                                ' data-care_plan_updated="' + esc(val.care_plan_updated) + '"' +
                                                ' data-family_notified="' + esc(val.family_notified) + '"' +
                                                ' data-additional_notes="' + esc(val.additional_notes) + '"' +
                                                ' data-id="' + esc(val.id) + '">' +
                                                '<i class="fa fa-pencil"></i> </button>' +
                                            '<button class="danger deleteDolsBtn" data-id="' + esc(val.id) + '"><i class="fa fa-trash"></i> </button>' +
                                        '</div>' +
                                    '</div>';

                        if(val.referral_date != null || val.authorisation_start_date != null){
                            tableData += '<div class="planMeta">';
                            if(val.referral_date != null){
                                tableData += '<div><strong>Referral Date: </strong> ' + esc(referral_date) + '</div>';
                            }
                            if(val.authorisation_start_date != null){
                                tableData += '<div><strong>Start Date: </strong> ' + esc(start_date) + '</div>';
                            }
                            tableData += '</div>';
                        }
                        if(val.authorisation_end_date != null || val.supervisory_body != null){
                            tableData += '<div class="planMeta">';
                            if(val.authorisation_end_date != null){
                                tableData += '<div><strong>End Date: </strong> ' + esc(end_date) + '</div>';
                            }
                            if(val.supervisory_body != null){
                                tableData += '<div><strong>Supervisory Body: </strong> ' + esc(truncateText(val.supervisory_body)) + '</div>';
                            }
                            tableData += '</div>';
                        }
                        if(val.case_reference != null){
                            tableData += '<div class="planFooter">' +
                                '<span><strong> Case Reference: </strong> ' + esc(truncateText(val.case_reference)) + '</span>' +
                            '</div>';
                        }
                        if(val.reason_for_dols != null){
                            tableData += '<div class="medicationSheet">' +
                                '<div class="reasonBox">' +
                                    '<strong>Reason:</strong> ' +
                                    esc(truncateText(val.reason_for_dols)) +
                                '</div>' +
                            '</div>';
                        }
                        tableData += '</div>';
                    });

                    $("#dolsRenderList").html(tableData);
                    var paginationControls = $("#dolsPagination");
                    paginationControls.empty();
                    if (response.data.prev_page_url) {
                        paginationControls.append('<button class="profileDrop me-3" onclick="showDolsList(\'' + esc(response.data.prev_page_url) + '\')">Previous</button>');
                    }
                    if (response.data.next_page_url) {
                        paginationControls.append('<button class="profileDrop" onclick="showDolsList(\'' + esc(response.data.next_page_url) + '\')">Next</button>');
                    }
                }
            } else {
                alert("Something went wrong");
                return false;
            }
        },
        error: function (xhr, status, error) {
            var errorMessage = xhr.status + ': ' + xhr.statusText;
            alert('Error - ' + errorMessage);
        }
    });
}

function badgeColors(dols_status){
    if(dols_status == "Not Applicable"){
        return "muteBadges";
    }else if(dols_status == "Screening Required"){
        return "yellowBadges";
    }else if(dols_status == "Application Submitted"){
        return "buleBadges";
    }else if(dols_status == "Standard Authorisation Granted"){
        return "greenbadges";
    }else if(dols_status == "Urgent Authorisation Granted"){
        return "highBadges";
    }else if(dols_status == "Not Authorised" || dols_status == "Expired"){
        return "redbadges";
    }else if(dols_status == "Under Review"){
        return "purpleBadges";
    }else{
        return "muteBadges";
    }
}

$(document).on('change','.dolsCheckbox',function(){
    if($(this).is(':checked')){
        $(this).val(1);
    }else{
        $(this).val(0);
    }
});

$(document).on('click','.deleteDolsBtn',function(){
    if(!confirm('Are you sure you want to delete this DoLS record?')){
        return false;
    }
    var dolsId = $(this).data('id');
    $.ajax({
        type: "POST",
        url: deleteDolsUrl,
        data: {id: dolsId, _token: token},
        success: function(response){
            if (typeof isAuthenticated === "function") {
                if (isAuthenticated(response) == false) {
                    return false;
                }
            }
            if(response.success === true){
                $('.ajax-alert-suc').show();
                $('.msg').text(response.message);
                showDolsList();
                setTimeout(function(){
                    $(".notification-box").fadeOut();
                    $('.msg').text("");
                }, 5000);
            } else {
                alert(response.message || 'Delete failed');
            }
        },
        error: function(xhr, status, error){
            alert('Error - ' + xhr.status + ': ' + xhr.statusText);
        }
    });
});
