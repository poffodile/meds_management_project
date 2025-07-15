function getDatatable(){
    const table = $('#petty_cash_table').DataTable({
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'csv',
                text: 'Export',
                bom: true,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9,10]
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
function saveCash(){
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
    var id=$("#id").val();
    var petty_cashInModal=$("#petty_cashInModal").val();
    var cash_outModal=$("#cash_outModal").val();
    var url=saveUrl;
    if(id !=''){
        url=editUrl;
    }
    if(error == 1){
        return false;
    }else if(petty_cashInModal == '' && cash_outModal == ''){
        alert("Please Fill Cash In or Cash Out");
        return false;
    }else{
        $.ajax({
            type: "POST",
            url: url,
            data: new FormData($("#cashForm")[0]),
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
                        $('#message_save').removeClass('error-message').text('').fadeOut();
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
$("#ToDate").change(function() {
    var startDateStr = document.getElementById("fromDate").value;
    var endDateStr = document.getElementById("ToDate").value;
    var startDate = parseDateDMY(startDateStr);
    var endDate = parseDateDMY(endDateStr);

    if ((Date.parse(startDate) > Date.parse(endDate))) {
        alert("End date should be greater than Start date");
        document.getElementById("ToDate").value = "";
        return false;
    }else if(startDateStr ==''){
        alert("Please select From Date");
        document.getElementById("ToDate").value = "";
        return false;
    }else if(endDateStr == ''){
        alert("Please select To Date");
        return false;
    }else{
        $.ajax({
            type: "POST",
            url: filterUrl,
            data: {startDate:startDateStr,endDate:endDateStr,_token:token},
            success: function(response) {
                console.log(response);
                // return false;
                if (typeof isAuthenticated === "function") {
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                }
                if (response.success === true) {
                    var table = $('#petty_cash_table').DataTable();
                    table.destroy();
                    $("#cash_result").html(response.html_data);
                    $("#PettyCashbalance").text('£'+response.total_balanceInCash);
                    $("#total_balance").text('£'+response.total_balance);
                    $("#petty_cashIn").text('£'+response.petty_cashIn);
                    $("#cash_out").text('£'+response.cash_out);
                    $("#total_balance").text('£'+response.total_balance);
                    getDatatable();
                    
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + error);
            }
        });
    }
});
$("#year").on('change',function(){
    cash_filter_function();
});
$("#month").on('change',function(){
    $("#year").val('');
});
function cash_filter_function(){
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
                    var table = $('#petty_cash_table').DataTable();
                    table.destroy();
                    $("#cash_result").html(response.html_data);
                    $("#PettyCashbalance").text('£'+response.total_balanceInCash);
                    $("#total_balance").text('£'+response.total_balance);
                    $("#petty_cashIn").text('£'+response.petty_cashIn);
                    $("#cash_out").text('£'+response.cash_out);
                    $("#total_balance").text('£'+response.total_balance);
                    getDatatable();
                    
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
    var id=$(this).data('id');
    var cash_date=$(this).data('cash_date');
    var petty_cashIn=$(this).data('petty_cashin');
    var cash_out=$(this).data('cash_out');
    var card_details=$(this).data('card_details');
    var receipt=$(this).data('receipt');
    var dext=$(this).data('dext');
    var invoice_la=$(this).data('invoice_la');
    var initial=$(this).data('initial');
    var balance_bfwd=$("#balance_bfwd").val();
    var balance_bfwdEdit=$(this).data('balance_bfwd');
    if(action === 'add'){
        $("#petty_cashLabel").text("Add Petty Cash");
        if(balance_bfwd ==''){
            $("#balance_bfwd").val(balance_bfwdEdit);
        }
        $("#id").val('');
        $("#cash_date").val('');
        $("#petty_cashInModal").val('');
        $("#cash_outModal").val('');
        $("#card_details").val('');
        var text = '<img src="'+existImage+'" alt="" class="image_delete">';
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
        $(".checkInput").css('border','');
        $("#petty_cashLabel").text("Edit Petty Cash");
        if(balance_bfwd ==''){
            $("#balance_bfwd").val(balance_bfwdEdit);
        }
        $("#id").val(id);
        $("#cash_date").val(cash_date);
        $("#petty_cashInModal").val(petty_cashIn);
        $("#cash_outModal").val(cash_out);
        $("#card_details").val(card_details);
        if (receipt) {
            var text = '<img src="' + imgSrc + "/" + receipt + '" alt="" class="image_delete" data-delete="' + id + '">';
            $("#exist_image").html(text);
        }else {
            $("#exist_image").html('<img src="'+existImage+'" alt="" class="image_delete">');
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
document.getElementById("cash_date").setAttribute("max", today);
// $(document).on('input', '#cash_outModal', function () {
//     var petty_cashInModal=$("#petty_cashInModal").val();
//     var cash_outModal=$("#cash_outModal").val();
//     var check_totalAmount=(parseFloat(total_balanceInCashCheck) || 0) + (parseFloat(petty_cashInModal) || 0);
//     if(cash_outModal > check_totalAmount){
//         alert("You can't enter above amount of closing balance or cashin balance");
//         $("#cash_outModal").val('');
//     }

// });