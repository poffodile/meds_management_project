var grandTotalBalanceOnCard=0;
var grandTotalBalanceOnCardCheck=0;
$(document).ready(function() {
    $.ajax({
        type: "get",
        url: getAllExpendCash,
        data: {_token:token},
        success: function(response) {
            console.log(response);
            // return false;
            if (typeof isAuthenticated === "function") {
                if (isAuthenticated(response) == false) {
                    return false;
                }
            }
            if (response.success === true) {
                const data = response.data;
                const expendCard = data.expendCard;
                const previousData = data.previous_month_data;
                const cash = parseFloat(data.cash ?? 0);
                let tbody = $('#expend_result');
                tbody.empty();
    
                let index = 0;
                let enterInLoop = 0;
                let totalBalancebfwd = 0;
                let totalBalanceFund = 0;
                let sumPurchaseCashIn = 0;
                let grandTotal='';
                let date = null;
                 if (previousData && parseFloat(previousData.previousbalanceOnCard) !== 0) {
                    enterInLoop = 1;
                    // if(expendCard.length >0){
                       grandTotal= '£'+previousData.previousbalanceOnCard;
                    // }
                    tbody.append(`
                        <tr>
                            <td>${++index}</td>
                            <td>${previousData.prvious_date}</td>
                            <td>${grandTotal}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    `);
                }
    
                expendCard.forEach((val) => {
                    var loginuserid='00';
                    if(response.is_admin == 1){
                        loginuserid=val.loginUserId;
                    }
                    let purchaseAmount = parseFloat(val.purchase_amount ?? 0);
                    let fundAdded = parseFloat(val.fund_added ?? 0);
                    let balanceBfwd = parseFloat(val.balance_bfwd ?? 0);
                    let expendDate = new Date(val.expend_date);
                    let dbMonth = expendDate.getMonth();
    
                    sumPurchaseCashIn += purchaseAmount;
                    totalBalanceFund += fundAdded;
    
                    if (enterInLoop === 0) {
                        totalBalancebfwd = balanceBfwd;
                        enterInLoop = 1;
                    }
    
                    let showBalanceBfwd = '';
                    if (enterInLoop !=1 && date === null) {
                        date = dbMonth;
                        showBalanceBfwd = `£${balanceBfwd}`;
                    }
                    let receipt='';
                    if(val.receipt){
                        receipt='<a href="'+receipt_imag_src +"/"+val.receipt+'" target="_blank"><i class="fa fa-eye"></i></a>';
                    }
                    tbody.append(`
                        <tr>
                            <td>${++index}</td>
                            <td class="white_space_nowrap">${val.expend_date}</td>
                            <td>${showBalanceBfwd}</td>
                            <td>${val.fund_added ? '£' + val.fund_added : ''}</td>
                            <td>£${purchaseAmount}</td>
                            <td>${val.card_details ?? ''}</td>
                            <td>${receipt}</td>
                            <td>${val.dext == 1 ? 'Yes' : 'No'}</td>
                            <td>${val.invoice_la == 1 ? 'Yes' : 'No'}</td>
                            <td>${val.initial ?? ''}</td>
                            <td><a href="javascript:void(0)" class="openModalBtn" data-toggle="modal" data-target="#expend_card" data-action="edit" data-id="${val.id}" data-loginuserid="${loginuserid}" data-expend_date="${val.expend_date}" data-balance_bfwd="${val.balance_bfwd}" data-fund_added="${val.fund_added}" data-purchase_amount="${val.purchase_amount}" data-card_details="${val.card_details}" data-receipt="${val.receipt}" data-dext="${val.dext}" data-invoice_la="${val.invoice_la}" data-initial="${val.initial}" id=""><i class="fa fa-pencil" aria-hidden="true"></i></a> | <a href="javascript:void(0)" class="deleteBtn" data-id="${val.id}"><i class="fa fa-trash radStar" aria-hidden="true"></i></a></td>
                        </tr>
                    `);
                });
    
                // Final calculation
                let previousBalance = parseFloat(previousData.previousbalanceOnCard ?? 0);
                let sum = (totalBalancebfwd === 0) ? (totalBalanceFund + previousBalance) : (totalBalanceFund + totalBalancebfwd);
                let calculation = sum - sumPurchaseCashIn;
                let balanceOnCard = calculation - cash;
    
                $('#totalBalanceOnCard').val(parseFloat(balanceOnCard.toFixed(2)));
                $('#totalBalancebfwd').text(`£${totalBalancebfwd ? totalBalancebfwd : previousData.previousbalanceOnCard}`);
                // if(grandTotalBalanceOnCardCheck == 0){
                //     grandTotalBalanceOnCardCheck=balanceOnCard;
                // }
                grandTotalBalanceOnCard=balanceOnCard;
                $('#totalBalanceFund').text(`£${totalBalanceFund}`);
                $('#sumPurchaseCashIn').text(`£${sumPurchaseCashIn.toFixed(2)}`);
                $("#balanceOnCard").text("£" + parseFloat(grandTotalBalanceOnCard.toFixed(2)));
                datatbleCall();
            }
        },
        error: function(xhr, status, error) {
            var errorMessage = xhr.status + ': ' + xhr.statusText;
            alert('Error - ' + errorMessage + "\nMessage: " + error);
        }
    });
});
function datatbleCall(){
        const table = $('#expend_cash_table').DataTable({
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'csv',
                text: 'Export',
                bom: true,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 7, 8]
                }
            }
        ],
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();
                var intVal = function (i) {
                    return typeof i === 'string'
                        ? parseFloat(i.replace(/[£,]/g, '')) || 0
                        : typeof i === 'number'
                        ? i
                        : 0;
                };
        
                
                var columnsToTotal = [2,3,4];
        
                columnsToTotal.forEach(function (colIdx) {
                    var total = api
                        .column(colIdx, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
        
                    $(api.column(colIdx).footer()).html('£' + total.toFixed(2));
                });
            }
        });
    }
function save_expend_card(){
    var error=0;
    $(".checkInput").each(function(){
        if($(this).val() == '' || $(this).val() == null){
            $(this).css('border','1px solid red');
            error=1;
            return false;
        }else{
            $(this).css('border','');
        }
    });
    $('.modal-body').scrollTop(0);
    var fund_added=$("#fund_added").val();
    var purchase_amount=$("#purchase_amount").val();
    var id=$("#id").val();
    var url=saveUrl;
    if(id != ''){
        url=editUrl;
    }
    if(error == 1){
        return false;
    }else if(fund_added == '' && purchase_amount == ''){
        alert("Please fill Fund added to card or Purchases");
        return false;
    }else{
        $.ajax({
            type: "POST",
            url: url,
            data: new FormData($("#expend_cardForm")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                console.log(response);
                // return false;
                if (typeof isAuthenticated === "function") {
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                }
                if (response.vali_error) {
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                } else if (response.success === true) {
                    $(window).scrollTop(0);
                    $('#message_save').addClass('success-message').text(response.message).show();
                    setTimeout(function() {
                        $('#message_save').removeClass('success-message').text('').hide();
                        location.href = redirectUrl
                    }, 3000);
                } else if (response.success === false) {
                    $('#message_save').addClass('error-message').text(response.message).show();
                    setTimeout(function() {
                        $('#error-message').text('').fadeOut();
                    }, 3000);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + error);
            }
        });
    }
}
// $("#fromDate").change(function() {
//     var startDate = document.getElementById("fromDate").value;
//     var endDate = document.getElementById("ToDate").value;

//     if ((Date.parse(endDate) <= Date.parse(startDate))) {
//         alert("Start date should be less than End date");
//         document.getElementById("fromDate").value = "";
//         return false;
//     }
// });
// $("#ToDate").change(function() {
//     var startDateStr = document.getElementById("fromDate").value;
//     var endDateStr = document.getElementById("ToDate").value;
//     var startDate = parseDateDMY(startDateStr);
//     var endDate = parseDateDMY(endDateStr);

//     if ((Date.parse(startDate) > Date.parse(endDate))) {
//         alert("End date should be greater than Start date");
//         document.getElementById("ToDate").value = "";
//         return false;
//     }else if(startDateStr ==''){
//         alert("Please select From Date");
//         document.getElementById("ToDate").value = "";
//         return false;
//     }else if(endDateStr == ''){
//         alert("Please select To Date");
//         return false;
//     }else{
//          $.ajax({
//             type: "POST",
//             url: filterUrl,
//             data: {startDate:startDateStr,endDate:endDateStr,_token:token},
//             success: function(response) {
//                 console.log(response);
//                 // return false;
//                 if (isAuthenticated(response) == false) {
//                     return false;
//                 }
//                 if (response.success === true) {
//                     var table = $('#expend_cash_table').DataTable();
//                     table.destroy();
//                     $("#expend_result").html(response.html_data);
//                     $("#balanceOnCard").text('£'+response.balanceOnCard);
//                     $("#sumPurchaseCashIn").text('£'+response.sumPurchaseCashIn);
//                     $("#totalBalanceFund").text('£'+response.totalBalanceFund);
//                     $("#totalBalancebfwd").text('£'+response.totalBalancebfwd);
//                     datatbleCall();
                    
//                 }
//             },
//             error: function(xhr, status, error) {
//                 var errorMessage = xhr.status + ': ' + xhr.statusText;
//                 alert('Error - ' + errorMessage + "\nMessage: " + error);
//             }
//         });
//     }
// });
$("#year").on('change',function(){
    card_filter_function();
});
$("#month").on('change',function(){
    $("#year").val('');
});
function card_filter_function(){
    var year=$("#year").val();
    var month=$("#month").val();
    if(year == '' || year == null){
        alert("Please Select The Year");
        return false;
    }else if(month == '' || month == null){
        alert("Please Select The Month");
        return false;
    }else{
        $.ajax({
            type: "POST",
            url: filterUrl,
            data: {year:year,month:month,_token:token},
            success: function(response) {
                console.log(response);
                // return false;
                if (typeof isAuthenticated === "function") {
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                }
                if (response.success === true) {
                    var table = $('#expend_cash_table').DataTable();
                    table.destroy();
                    $("#expend_result").html(response.html_data);
                    $("#balanceOnCard").text('£'+response.balanceOnCard);
                    $("#sumPurchaseCashIn").text('£'+response.sumPurchaseCashIn);
                    $("#totalBalanceFund").text('£'+response.totalBalanceFund);
                    $("#totalBalancebfwd").text('£'+response.totalBalancebfwd);
                    datatbleCall();
                    
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + error);
            }
        });
    }
}
function parseDateDMY(dateStr) {
    var parts = dateStr.split("-");
    return new Date(parts[2], parts[1] - 1, parts[0]);
}
function check_file() {
    const fileInput = document.getElementById('receipt');
    const filePath = fileInput.value;

    const allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

    if (!allowedExtensions.exec(filePath)) {
        alert('Only image files are allowed (JPG, JPEG, PNG).');
        fileInput.value = '';
        return false;
    }

    const file = fileInput.files[0];
    const maxSizeInMB = 2;
    const fileSize = file.size / 1024 / 1024;

    if (fileSize > maxSizeInMB) {
        alert('File size must be less than 2 MB.');
        fileInput.value = '';
        return false;
    }

    return true;
}
$(document).on('input', '.numberInput', function () {
    let val = $(this).val().replace(/[^0-9.]/g, '');
    if ((val.match(/\./g) || []).length > 1) {
        val = val.slice(0, -1);
    }
    $(this).val(val);
});
$(document).on('input', '.no_input', function () {
    $(this).val('');
});
$(document).on('click','.openModalBtn', function(){
    var action=$(this).data('action');
    var loginuserid=$(this).data('loginuserid');
    var id=$(this).data('id');
    var expend_date=$(this).data('expend_date');
    var balance_bfwd=$(this).data('balance_bfwd');
    var fund_added=$(this).data('fund_added');
    var purchase_amount=$(this).data('purchase_amount');
    var card_details=$(this).data('card_details');
    var receipt=$(this).data('receipt');
    var dext=$(this).data('dext');
    var invoice_la=$(this).data('invoice_la');
    var initial=$(this).data('initial');
    var balance_bfwdEdit=$(this).data(balance_bfwd);
    var text = '<img src="'+existImage+'" alt="" class="image_delete">';
    if(action === 'add'){
        $("#expend_cardLabel").text("Add Expend Card");
        $("#id").val('');
        $("#expend_date").val('');
        if(balance_bfwd ==''){
            $("#balance_bfwd").val('');
        }
        $("#fund_added").val('');
        $("#purchase_amount").val('');
        $("#card_details").val('');
        $("#exist_image").html(text);
        if(dext == 1){
            $("#yes").attr('checked','checked');
        }else{
            $("#no").attr('checked','checked');
        }
        if(invoice_la == 1){
            $("#yes2").attr('checked','checked');
        }else{
            $("#no2").attr('checked','checked');
        }
        $("#initial").val('');
    }else{
        if(loginuserid != '00'){
            $("#loginuserid").val(loginuserid);
        }
        $("#expend_cardLabel").text("Edit Expend Card");
        $("#id").val(id);
        $("#expend_date").val(expend_date);
        if(balance_bfwd ==''){
            $("#balance_bfwd").val(balance_bfwdEdit);
        }
        $("#fund_added").val(fund_added);
        $("#purchase_amount").val(purchase_amount);
        $("#card_details").val(card_details);
        if (receipt) {
            var text = '<img src="' + receipt_imag_src + "/" + receipt + '" alt="" class="image_delete" data-delete="' + id + '">';
            $("#exist_image").html(text);
        }else {
            $("#exist_image").html(text);
        }
        if(dext == 1){
            $("#yes").attr('checked','checked');
        }else{
            $("#no").attr('checked','checked');
        }
        if(invoice_la == 1){
            $("#yes2").attr('checked','checked');
        }else{
            $("#no2").attr('checked','checked');
        }
        $("#initial").val(initial);
        $(".checkInput").css('border','');
    }
    
});
$(document).on('click','.deleteBtn', function(){
    var id=$(this).data('id');
    if(confirm("Are you sure to delete it?")){
        $.ajax({
            type: "POST",
            url: deleteUrl,
            data: {id:id,_token:token},
            success: function(response) {
                console.log(response);
                // return false;
                if (typeof isAuthenticated === "function") {
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                }
                if (response.success === true) {
                    location.reload();
                }
                if(response.success === false){
                    alert('Something went wrong');
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + error);
            }
        });
    }
});
const today = new Date().toISOString().split('T')[0];
document.getElementById("expend_date").setAttribute("max", today);

// $(document).on('input', '#purchase_amount', function () {
//     var fund_added=$("#fund_added").val();
//     var purchase_amount=$("#purchase_amount").val();
//     var expend_date=$("#expend_date").val();
//     if(expend_date == ''){
//         alert("Please choose date first");
//         $("#purchase_amount").val('');
//         return false;
//     }else{
//         $.ajax({
//             type: "POST",
//             url: check_CardclosingAmount,
//             data: {expend_date:expend_date,_token:token},
//             success: function(response) {
//                 console.log(response);
//                 if (isAuthenticated(response) == false) {
//                         return false;
//                     }
//                 if (response.success === true) {
//                     var data=response.data;
//                     var check_totalAmount=(parseFloat(data.previousbalanceOnCard) || 0) + (parseFloat(fund_added) || 0);
//                     if(data.previousbalanceOnCard == 0){
//                         alert("Please fill Fund Added first");
//                         $("#purchase_amount").val('');
//                         return false;
//                     }else if(purchase_amount > check_totalAmount){
//                         alert("You can't enter above amount of closing balance or Fund added balance");
//                         $("#purchase_amount").val('');
//                     }
//                 }
//                 if(response.success === false){
//                     alert('Something went wrong');
//                 }
//             },
//             error: function(xhr, status, error) {
//                 var errorMessage = xhr.status + ': ' + xhr.statusText;
//                 alert('Error - ' + errorMessage + "\nMessage: " + error);
//             }
//         });
//     }

// });