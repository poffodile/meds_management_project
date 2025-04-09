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
    if(error == 1){
        return false;
    }else{
        $.ajax({
            type: "POST",
            url: saveUrl,
            data: new FormData($("#cashForm")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                console.log(response);
                // return false;
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
$("#fromDate").change(function() {
    var startDate = document.getElementById("fromDate").value;
    var endDate = document.getElementById("ToDate").value;

    if ((Date.parse(endDate) <= Date.parse(startDate))) {
        alert("Start date should be less than End date");
        document.getElementById("fromDate").value = "";
        return false;
    }
});
$("#ToDate").change(function() {
    var startDate = document.getElementById("fromDate").value;
    var endDate = document.getElementById("ToDate").value;

    if ((Date.parse(startDate) >= Date.parse(endDate))) {
        alert("End date should be greater than Start date");
        document.getElementById("ToDate").value = "";
        return false;
    }
    $.ajax({
        type: "POST",
        url: filterUrl,
        data: {startDate:startDate,endDate:endDate,_token:token},
        success: function(response) {
            console.log(response);
            // return false;
            if (response.success === true) {
                if(response.data.length == 0){
                    $("#cash_result").html('<tr><td colspan="10" class="text-danger text-center">Record Not Found</td></tr>');
                }else{
                    $("#cash_result").html(response.html_data);
                }
                $("#PettyCashbalance").text(response.total_balanceInCash);
                $("#total_balance").text(response.total_balance);
                $("#petty_cashIn").text(response.petty_cashIn);
                $("#cash_out").text(response.cash_out);
                $("#total_balance").text(response.total_balance);
                
            }
        },
        error: function(xhr, status, error) {
            var errorMessage = xhr.status + ': ' + xhr.statusText;
            alert('Error - ' + errorMessage + "\nMessage: " + error);
        }
    });
});
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