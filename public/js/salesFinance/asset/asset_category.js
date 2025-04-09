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
                if (response.vali_error) {
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                } else if (response.success === true) {
                    $(window).scrollTop(0);
                    $('#message_save').addClass('success-message').text(response.message).show();
                    setTimeout(function () {
                        $('#message_save').removeClass('success-message').text('').hide();
                        location.href = redirectUrl;
                    }, 3000);
                } else if (response.success === false) {
                    $('#message_save').addClass('error-message').text(response.message).show();
                    setTimeout(function () {
                        $('#error-message').text('').fadeOut();
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
    $("#assetCategoryModalLabel").text("Add Asset Category");
    $("#assetCategoryModal").modal('show');
}
function saveassetCategoryModal() {
    var name = $("#name").val();
    if (name == '') {
        $("#name").css('border', '1px solid red');
        return false;
    } else {
        $("#name").css('border', '');
        $.ajax({
            type: "POST",
            url: assetCatSaveUrl,
            data: new FormData($("#assetCategoryForm")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function (response) {
                console.log(response);
                // return false;
                if (response.vali_error) {
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                } else if (response.success === true) {
                    $(window).scrollTop(0);
                    $('#messageAssetCategory').addClass('success-message').text(response.message).show();
                    setTimeout(function () {
                        $('#messageAssetCategory').removeClass('success-message').text('').hide();
                        location.reload();
                    }, 3000);
                } else if (response.success === false) {
                    $('#messageAssetCategory').addClass('error-message').text(response.message).show();
                    setTimeout(function () {
                        $('#error-message').text('').fadeOut();
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
    $("#assetCategoryModalLabel").text("Edit Asset Category");
    var id = $(this).data('id');
    var name = $(this).data('name');
    var status = $(this).data('status');

    $("#id").val(id);
    $("#name").val(name);
    $("#statusAssetModal").val(status);
});
function opendepreciation_typesModal() {
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
            url: assetDepreciationTypeSaveUrl,
            data: new FormData($("#depreciation_typesForm")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function (response) {
                console.log(response);
                // return false;
                if (response.vali_error) {
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                } else if (response.success === true) {
                    $(window).scrollTop(0);
                    $('#messagedepreciation_types').addClass('success-message').text(response.message).show();
                    setTimeout(function () {
                        $('#messagedepreciation_types').removeClass('success-message').text('').hide();
                        location.reload();
                    }, 3000);
                } else if (response.success === false) {
                    $('#messagedepreciation_types').addClass('error-message').text(response.message).show();
                    setTimeout(function () {
                        $('#error-message').text('').fadeOut();
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

            if (response.success === true) {
                const tableData = `<div class="productDetailTable asset_layer" style="">
                <table class="table" id="containerA">
                  <thead class="table-light">
                    <tr class="text-center">
                      <th class="col-1"></th>
                      <th class="col-1"></th>
                      <th class="col-1">Total</th>
                      <th class="col-1"></th>
                      <th class="col-1">Property</th>
                      <th class="col-1">Household</th>
                      <th class="col-1">F&F</th>
                      <th class="col-1">Motor Vehicle</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <th colspan="8">Cost</th>
                    </tr>
                    <tr>
                      <td></td>
                      <td class="text-start">Bfwd</td>
                      <td>1,334,736</td>
                      <td></td>
                      <td>901,506</td>
                      <td>163,814</td>
                      <td>102,164</td>
                      <td>167,252</td>
                    </tr>
                    <tr>
                      <td></td>
                      <td class="text-start">Additions</td>
                      <td>276,688</td>
                      <td></td>
                      <td>-</td>
                      <td>40,065</td>
                      <td>100,083</td>
                      <td>136,540</td>
                    </tr>
                    <tr>
                      <td></td>
                      <td class="text-start">Disposals</td>
                      <td>70,149</td>
                      <td></td>
                      <td>-</td>
                      <td>- 159</td>
                      <td>-</td>
                      <td>- 69,990</td>
                    </tr>
                    <tr class="total">
                      <td></td>
                      <td class="text-start">Cfwd</td>
                      <td class="line">1,541,275</td>
                      <td></td>
                      <td class="line">901,506</td>
                      <td class="line">203,720</td>
                      <td class="line">202,247</td>
                      <td class="line">233,802</td>
                    </tr>
                    <tr>
                      <td colspan="8"></td>
                    </tr>
                    <tr>
                      <th colspan="8">Depreciation</th>
                    </tr>
                    <tr>
                      <td></td>
                      <td class="text-start">Bfwd</td>
                      <td>255,441</td>
                      <td></td>
                      <td>26,105</td>
                      <td>107,375</td>
                      <td>74,817</td>
                      <td>47,144</td>
                    </tr>
                    <tr>
                      <td></td>
                      <td class="text-start">Charge For Year</td>
                      <td>140,732</td>
                      <td></td>
                      <td>18,030</td>
                      <td>31,831</td>
                      <td>36,306</td>
                      <td>54,565</td>
                    </tr>
                    <tr>
                      <td></td>
                      <td class="text-start">Eliminated on disposals</td>
                      <td>17,538</td>
                      <td></td>
                      <td>-</td>
                      <td>- 40</td>
                      <td>-</td>
                      <td>- 17,498</td>
                    </tr>
                    <tr class="total">
                      <td></td>
                      <td class="text-start">Cfwd</td>
                      <td class="line">378,635</td>
                      <td></td>
                      <td class="line">44,136</td>
                      <td class="line">139,166</td>
                      <td class="line">111,123</td>
                      <td class="line">84,211</td>
                    </tr>
                    <tr>
                      <td colspan="8"></td>
                    </tr>
                    <tr class="total2">
                      <td></td>
                      <th class="text-start">NBV Cfwd</th>
                      <td class="line">1,162,640</td>
                      <td></td>
                      <td class="line">857,371</td>
                      <td class="line">64,554</td>
                      <td class="line">91,124</td>
                      <td class="line">149,591</td>
                    </tr>
                    <tr>
                      <td colspan="8"></td>
                    </tr>
                    <tr class="total2">
                      <td></td>
                      <th class="text-start">NBV Bfwd</th>
                      <td class="line">1,079,295</td>
                      <td></td>
                      <td class="line">875,401</td>
                      <td class="line">56,440</td>
                      <td class="line">27,347</td>
                      <td class="line">120,108</td>
                    </tr>
                  </tbody>
                </table>
              </div>`;
                $("#search_data").append(tableData);

                // $("#search_data").show();

                // alert(typeof(response.cost_bfwd))
                // $("#assetRegisterList").html(response.html_data);
                // $("#cost_bfwd").text("£"+response.cost_bfwd.toFixed(2));
                // $("#cost_disposal").text("£"+response.cost_disposal.toFixed(2));
                // $("#cost_addition").text("£"+response.cost_addition.toFixed(2));
                // $("#cost_fwd").text("£"+response.cost_fwd.toFixed(2));
                // $("#depreciation_bfwd").text("£"+response.depreciation_bfwd.toFixed(2));
                // $("#depreciation").text("£"+response.depreciation.toFixed(2));
                // $("#charge").text("£"+response.charge.toFixed(2));
                // $("#depreciation_cfwd").text("£"+response.depreciation_cfwd.toFixed(2));
                // $("#nbv_bfwd").text("£"+response.nbv_bfwd.toFixed(2));
                // $("#nbv_cfwd").text("£"+response.nbv_cfwd.toFixed(2));
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
    // $('#configform')[0].reset();
}
$("#edd_endDate").change(function () {
    var startDate = document.getElementById("edd_startDate").value;
    var endDate = document.getElementById("edd_endDate").value;

    if ((Date.parse(startDate) >= Date.parse(endDate))) {
        alert("End date should be greater than Start date");
        document.getElementById("edd_endDate").value = "";
    }
});
$("#edd_startDate").change(function () {
    var startDate = document.getElementById("edd_startDate").value;
    var endDate = document.getElementById("edd_endDate").value;

    if ((Date.parse(endDate) <= Date.parse(startDate))) {
        alert("Start date should be less than End date");
        document.getElementById("edd_startDate").value = "";
    }
});
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