
$(document).ready(function() {

    $.ajax({
        url: getUserListNotify,
        method: 'GET',
        // processData: false,  
        // contentType: false,  
        success: function(response) {
            // alert(response.message);
            if (isAuthenticated(response) == false) {
                alert(28)
                return false;
            }
            const selectElement = document.getElementById('notifiy_user');
    
            selectElement.innerHTML = '';

            response.data.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.text = user.name;
                selectElement.appendChild(option);
            });
        },
        error: function(xhr, status, error) {
            console.error(error);
        }
    });
    
    document.getElementById("toggleDiv").style.display =  "none";

    $('#save_lead_convert_quote').on('click', function() {

        let formData = $('#notify_lead_quote_form').serialize();
    
        $.ajax({
            url: saveLeadConvertQuote,
            method: 'POST',
            data: formData,
            // processData: false,  
            // contentType: false,  
            success: function(response) {
                if (isAuthenticated(response) == false) {
                    alert(29)
                    return false;
                }
                alert(response.data);
                $('#sentToQuoteModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    
    });
});

function toggleDiv(show) {
    document.getElementById("toggleDiv").style.display = show ? "block" : "none";
}
