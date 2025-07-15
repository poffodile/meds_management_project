$(document).ready(function(){
    calculate();
});
$(document).on('input', '.numberInput', function () {
    let val = $(this).val().replace(/[^0-9.]/g, '');
    if ((val.match(/\./g) || []).length > 1) {
        val = val.slice(0, -1);
    }
    $(this).val(val);
});

function calculate() {
    var cost_bfwd = parseFloat($("#cost_bfwd").val()) || 0;
    var cost_addition = parseFloat($("#cost_addition").val()) || 0;
    var cost_disposal = parseFloat($("#cost_disposal").val()) || 0;

    var depreciation_bfwd = parseFloat($("#depreciation_bfwd").val()) || 0;
    var depreciation = parseFloat($("#depreciation").val()) || 0;
    var depreciation_type = parseFloat($("#depreciation_type").val()) || 0;
    var selectedOption = $("#depreciation_type option:selected");
    var depreciation_type = selectedOption.data("attr") || 0;
    // alert(typeof(depreciation_type))

    var costCalculation = cost_bfwd + cost_addition + cost_disposal;
    var depreciationSum = depreciation_bfwd + depreciation;
    var percentage = costCalculation * depreciation_type / 100;
    var depreciationCalculation = depreciationSum + percentage;

    var nbv_cfwdCalculation = costCalculation - depreciationCalculation;
    var nbv_bfwdCalculation = cost_bfwd - depreciation_bfwd;

    $("#cost_fwd").val(costCalculation.toFixed(2));
    $("#charge").val(percentage.toFixed(2));
    $("#depreciation_cfwd").val(depreciationCalculation.toFixed(2));
    $("#nbv_cfwd").val(nbv_cfwdCalculation.toFixed(2));
    $("#nbv_bfwd").val(nbv_bfwdCalculation.toFixed(2));
}
function getSaveData() {
    var asset_name = $("#asset_name").val();
    var asset_type = $("#asset_type").val();
    var date = $("#date").val();
    if (asset_name == '') {
        $("#asset_name").css('border', '1px solid red');
        return false;
    } else if (asset_type == '') {
        $("#asset_name").css('border', '');
        $("#asset_type").css('border', '1px solid red');
        return false;
    } else if (date == '') {
        $("#asset_name").css('border', '');
        $("#asset_type").css('border', '');
        $("#date").css('border', '1px solid red');
        return false;
    } else {
        $("#asset_name").css('border', '');
        $("#asset_type").css('border', '');
        $("#date").css('border', '');
        $.ajax({
            type: "POST",
            url: assetSaveUrl,
            data: new FormData($("#assetRegisterFormData")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function (response) {
                console.log(response);
                // return false;
                if (isAuthenticated(response) == false) {
                    return false;
                }
                if (response.vali_error) {
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                } else if (response.success === true) {
                    $(window).scrollTop(0);
                    $('#message_save').addClass('success-message').text(response.message).show();
                    $(".popup_success").fadeIn();
                    $('.popup_success_txt').text(response.message).show();
                    setTimeout(function () {
                        $('#message_save').removeClass('success-message').text('').hide();
                        $(".popup_success").fadeOut();
                        location.href = redirectUrl;
                    }, 3000);
                } else if (response.success === false) {
                    $('#message_save').addClass('error-message').text(response.message).show();
                    $(".popup_error").fadeIn();
                    $('.popup_success_txt').text(response.message).show();
                    setTimeout(function () {
                        $('#error-message').text('').fadeOut();
                        $(".popup_error").fadeOut();
                    }, 3000);
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
}
function openAssetCategoryModal() {
    $("#assetCategoryModalLabel").text("Add Asset Categories");
    $("#assetCategoryForm")[0].reset();
    $("#id").val('');
    $("#assetCategoryModal").modal('show');
}
function saveassetCategoryModal() {
    var name = $("#name").val();
    var id=$("#id").val();
    var cat_url=assetCatSaveUrl;
    if(id !=''){
        cat_url=assetCatEditUrl;
    }
    if (name == '') {
        $("#name").css('border', '1px solid red');
        return false;
    } else {
        $("#name").css('border', '');
        $.ajax({
            type: "POST",
            url: cat_url,
            data: new FormData($("#assetCategoryForm")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function (response) {
                console.log(response);
                // return false;
                if (isAuthenticated(response) == false) {
                    return false;
                }
                if (response.vali_error) {
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                } else if (response.success === true) {
                    $(window).scrollTop(0);
                    // $('#messageAssetCategory').addClass('success-message').text(response.message).show();
                    $(".popup_success").fadeIn();
                    $('.popup_success_txt').text(response.message).show();
                    setTimeout(function () {
                        // $('#messageAssetCategory').removeClass('success-message').text('').hide();
                        // $('.popup_success_txt').text('').hide();
                        $(".popup_success").fadeOut();
                        location.reload();
                    }, 3000);
                } else if (response.success === false) {
                    // $('#messageAssetCategory').addClass('error-message').text(response.message).show();
                    $(".popup_error").fadeIn();
                    $('.popup_success_txt').text(response.message).show();
                    setTimeout(function () {
                        $(".popup_error").fadeOut();
                    }, 3000);
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
}
$(document).on('click', '.assetCatemodal_dataFetch', function () {
    // alert()
    $("#assetCategoryModalLabel").text("Edit Asset Categories");
    var id = $(this).data('id');
    var name = $(this).data('name');
    var status = $(this).data('status');

    $("#id").val(id);
    $("#name").val(name);
    $("#statusAssetModal").val(status);
});
function opendepreciation_typesModal() {
    $("#depreciation_typesForm")[0].reset();
    $("#depreciation_typesModalLabel").text("Add Depreciation Type");
    $("#depreciation_typesModal").modal('show');
}
$(document).on('click', '.depreciation_typeModal_dataFetch', function () {
    $("#depreciation_typesModalLabel").text("Edit Depreciation Type");
    var id = $(this).data('id');
    var name = $(this).data('name');
    var percentage = $(this).data('percentage');
    var status = $(this).data('status');

    $("#id").val(id);
    $("#name").val(name);
    $("#percentage").val(percentage);
    $("#statusdepreciation_typesModal").val(status);
});

function savedepreciation_typesModal() {
    var name = $("#name").val();
    var percentage = $("#percentage").val();
    var id=$("#id").val();
    var assetDepreciationTypeUrl=assetDepreciationTypeSaveUrl;
    if(id !=''){
        assetDepreciationTypeUrl=assetDepreciationTypeEditUrl;
    }
    if (name == '') {
        $("#name").css('border', '1px solid red');
        return false;
    } else if (percentage == '') {
        $("#name").css('border', '');
        $("#percentage").css('border', '1px solid red');
        return false;
    } else {
        $("#name").css('border', '');
        $("#percentage").css('border', '');
        $.ajax({
            type: "POST",
            url: assetDepreciationTypeUrl,
            data: new FormData($("#depreciation_typesForm")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function (response) {
                console.log(response);
                // return false;
                if (isAuthenticated(response) == false) {
                    return false;
                }
                if (response.vali_error) {
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                } else if (response.success === true) {
                    $(window).scrollTop(0);
                    $('#messagedepreciation_types').addClass('success-message').text(response.message).show();
                    $(".popup_success").fadeIn();
                    $('.popup_success_txt').text(response.message).show();
                    setTimeout(function () {
                        $('#messagedepreciation_types').removeClass('success-message').text('').hide();
                        $(".popup_success").fadeOut();
                        location.reload();
                    }, 3000);
                } else if (response.success === false) {
                    $('#messagedepreciation_types').addClass('error-message').text(response.message).show();
                    $(".popup_error").fadeIn();
                    $('.popup_success_txt').text(response.message).show();
                    setTimeout(function () {
                        $('#error-message').text('').fadeOut();
                        $(".popup_error").fadeOut();
                    }, 3000);
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
}
function searchBtn() {
    var edd_startDate = $("#edd_startDate").val();
    var edd_endDate = $("#edd_endDate").val();
    if (edd_startDate == '' && edd_endDate == '') {
        alert("Please fill all field before searching.");
        return false;
    }
    if (edd_startDate != '' && edd_endDate == '') {
        alert("Please choose both date");
        return false;
    }
    if (edd_startDate == '' && edd_endDate != '') {
        alert("Please choose both date");
        return false;
    }

    $.ajax({
        type: "POST",
        url: searchUrl,
        data: new FormData($("#search_dataForm")[0]),
        async: false,
        contentType: false,
        cache: false,
        processData: false,
        success: function (response) {

            console.log(response);
            // return false;

            if (response.success === true) {
                var data=response.data;
                var table=document.getElementById('assetRegisterList');
                if(data.length === 0){
                    $("#footer_table").hide();
                    table.innerHTML="<tr><td class='text text-center text-danger' colspan='15'>Data Not Found</td></tr>";
                }else{
                    table.innerHTML='';
                    let cost_bfwd = 0, cost_disposal = 0, cost_addition = 0, cost_fwd = 0;
                    let depreciation_bfwd = 0, depreciation = 0, charge = 0, depreciation_cfwd = 0;
                    let nbv_bfwd = 0, nbv_cfwd = 0;
                    let tableData = '';
                    data.forEach(function(val, key) {
                    cost_bfwd += val.cost_bfwd;
                    cost_disposal += val.cost_disposal;
                    cost_addition += val.cost_addition;
                    cost_fwd += val.cost_fwd;
                    depreciation_bfwd += val.depreciation_bfwd;
                    depreciation += val.depreciation;
                    charge += val.charge;
                    depreciation_cfwd += val.depreciation_cfwd;
                    nbv_bfwd += val.nbv_bfwd;
                    nbv_cfwd += val.nbv_cfwd;

                    tableData += `<tr>
                        <td>${key + 1}</td>
                        <td>${val.asset_name}</td>
                        <td>${new Date(val.date).toLocaleDateString('en-GB')}</td>
                        <td>${val.cost_bfwd ? '£' + val.cost_bfwd : ''}</td>
                        <td>${val.cost_disposal ? '£' + val.cost_disposal : ''}</td>
                        <td>${val.cost_addition ? '£' + val.cost_addition : ''}</td>
                        <td>${val.cost_fwd ? '£' + val.cost_fwd : ''}</td>
                        <td>${val.depreciation_bfwd ? '£' + val.depreciation_bfwd : ''}</td>
                        <td>${val.depreciation ? '£' + val.depreciation : ''}</td>
                        <td>${val.charge ? '£' + val.charge : ''}</td>
                        <td>${val.depreciation_cfwd ? '£' + val.depreciation_cfwd : ''}</td>
                        <td>${val.nbv_bfwd ? '£' + val.nbv_bfwd : ''}</td>
                        <td>${val.nbv_cfwd ? '£' + val.nbv_cfwd : ''}</td>
                        <td>
                        <a href="sales-finance/assets/asset-register-edit?key=${btoa(val.id)}" class="openModalBtn">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </a> |
                        <a href="#!" class="register_delete" data-id="${val.id}">
                            <i class="fa fa-trash text-danger" aria-hidden="true"></i>
                        </a>
                        </td>
                    </tr>`;
                    });

                    $("#assetRegisterList").append(tableData);
                    
                    

                    // $("#search_data").show();
                    $("#footer_table").show();
                    // alert(typeof(response.cost_bfwd))
                    // $("#assetRegisterList").html(response.html_data);
                    $("#tablecost_bfwd").text("£"+response.cost_bfwd.toFixed(2));
                    $("#tablecost_disposal").text("£"+response.cost_disposal.toFixed(2));
                    $("#tablecost_addition").text("£"+response.cost_addition.toFixed(2));
                    $("#tablecost_fwd").text("£"+response.cost_fwd.toFixed(2));
                    $("#tabledepreciation_bfwd").text("£"+response.depreciation_bfwd.toFixed(2));
                    $("#tabledepreciation").text("£"+response.depreciation.toFixed(2));
                    $("#tablecharge").text("£"+response.charge.toFixed(2));
                    $("#tabledepreciation_cfwd").text("£"+response.depreciation_cfwd.toFixed(2));
                    $("#tablenbv_bfwd").text("£"+response.nbv_bfwd.toFixed(2));
                    $("#tablenbv_cfwd").text("£"+response.nbv_cfwd.toFixed(2));
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
function clearBtn(form_id) {
    $("#" + form_id)[0].reset();
    location.reload();
    // $('#configform')[0].reset();
}
$("#edd_endDate").change(function () {
    var startDateStr = document.getElementById("edd_startDate").value;
    var endDateStr = document.getElementById("edd_endDate").value;
    var startDate = parseDateDMY(startDateStr);
    var endDate = parseDateDMY(endDateStr);
    if ((Date.parse(startDate) >= Date.parse(endDate))) {
        alert("End date should be greater than Start date");
        document.getElementById("edd_endDate").value = "";
    }
});
$("#edd_startDate").change(function () {
    var startDateStr = document.getElementById("edd_startDate").value;
    var endDateStr = document.getElementById("edd_endDate").value;
    var startDate = parseDateDMY(startDateStr);
    var endDate = parseDateDMY(endDateStr);
    if ((Date.parse(endDate) <= Date.parse(startDate))) {
        alert("Start date should be less than End date");
        document.getElementById("edd_startDate").value = "";
    }else{
        $("#edd_endDate").removeAttr('disabled','disabled');
    }
});
function parseDateDMY(dateStr) {
    var parts = dateStr.split("-");
    return new Date(parts[2], parts[1] - 1, parts[0]);
}
$(document).on('click', '.register_delete', function () {
    var id = $(this).data('id');
    if (confirm("Are you sure you want to delete?")) {
        var token = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            type: "POST",
            url: deleteAssetRegisterUrl,
            data: { id: id, _token: token },
            success: function (response) {
                console.log(response);
                // return false;
                if (isAuthenticated(response) == false) {
                    return false;
                }
                if (response.success === true) {
                    location.reload();
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
});

$(document).on('click', '.close-msg-btn', function() {
    $('.popup_alrt_msg').hide();
});
$(document).on('click','.delete_assetCat',function(){
    var id = $(this).data('id');
    if (confirm("Are you sure you want to delete?")) {
        var token = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            type: "POST",
            url: assetCatDeleteUrl,
            data: { id: id, _token: token },
            success: function (response) {
                console.log(response);
                // return false;
                if (isAuthenticated(response) == false) {
                    return false;
                }
                if (response.success === true) {
                    location.reload();
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
});
$('#assetCatTable').DataTable({
    dom: 'Blfrtip',
    buttons: [
        {
            extend: 'csv',
            text: 'Export',
            bom: true,
            exportOptions: {
                columns: [ 1, 2,]
            }
        }
    ],
});
$('#Depreciation_typeTable').DataTable({
    dom: 'Blfrtip',
    buttons: [
        {
            extend: 'csv',
            text: 'Export',
            bom: true,
            exportOptions: {
                columns: [ 1, 2,3]
            }
        }
    ],
});
// $('#assetRegisterTable').DataTable({
//     dom: 'Blfrtip',
//     buttons: [
//         {
//             extend: 'csv',
//             text: 'Export',
//             bom: true,
//             exportOptions: {
//                 columns: [ 0,1, 2,3,4,5]
//             }
//         }
//     ],
// });
