document.querySelectorAll('.openModalBtn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const action = this.getAttribute('data-action');
        const council_tax_id = document.getElementById('council_tax_id');
        const modalTitle = document.getElementById('modalTitle');
        const flat_num = document.getElementById('flat_num');
        const address = document.getElementById('address');
        const postcode = document.getElementById('postcode');
        const council = document.getElementById('council');
        const additional_notes = document.getElementById('additional_notes');
        const amount_paid = document.getElementById('amount_paid');
        const bill_period_end_date = document.getElementById('bill_period_end_date');
        const bill_period_start_date = document.getElementById('bill_period_start_date');
        const last_bill_date = document.getElementById('last_bill_date');
        const account_number = document.getElementById('account_number');
        const exemptyes = document.getElementById('exemptyes');
        const exemptno = document.getElementById('exemptno');
        const occupancy = document.getElementById('occupancy');
        const ownedByOmegayes = document.getElementById('ownedByOmegayes');
        const ownedByOmegano = document.getElementById('ownedByOmegano');
        const no_of_bedrooms = document.getElementById('no_of_bedrooms');

        if (action === 'add') {
            modalTitle.textContent = 'Add Council Tax';
        } else if (action === 'edit') {
            modalTitle.textContent = 'Edit Councli Tax';
            council_tax_id.value = this.getAttribute('data-id');
            flat_num.value = this.getAttribute('data-flat-number');
            address.value = this.getAttribute('data-address');
            additional_notes.value = this.getAttribute('data-additional');
            postcode.value = this.getAttribute('data-post_code');
            council.value = this.getAttribute('data-council');
            no_of_bedrooms.value = this.getAttribute('data-no_of_bedrooms');
            if (this.getAttribute('data-owned_by_omega') == 1) {
                ownedByOmegayes.checked = true;
            } else if (this.getAttribute('data-owned_by_omega') == 0) {
                ownedByOmegano.checked = true;
            }

            occupancy.value = this.getAttribute('data-occupancy');
            if (this.getAttribute('data-exempt') == 1) {
                exemptno.checked = true;
            } else if (this.getAttribute('data-exempt') == 0) {
                exemptyes.checked = true;
            }
            account_number.value = this.getAttribute('data-account_number');
            last_bill_date.value = this.getAttribute('data-last_bill_date');
            bill_period_start_date.value = this.getAttribute('data-bill_period_start_date');
            bill_period_end_date.value = this.getAttribute('data-bill_period_end_date');
            amount_paid.value = this.getAttribute('data-amount_paid');
        }

        $('#AddCouncilTax').modal('show');
    });
});



$(document).ready(function() {
    $("#saveCouncilTax").on("click", function(e) {
        // alert("hii");
        e.preventDefault(); // Prevent default form submission
        console.log($('#addCouncilTaxForm').serialize());
        $.ajax({
            url: saveData, // Laravel route or API endpoint
            method: "POST",
            data: $('#addCouncilTaxForm').serialize(),
            dataType: "json",
            success: function(response) {
                console.log(response);
                if (response.success) {
                    alert("Success: " + response.message);
                    // alert("Data saved successfully!");
                    $("#AddCouncilTax").modal("hide"); // Hide modal
                    $("#addCouncilTaxForm")[0].reset(); // Reset form
                    location.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function(xhr) {
                alert("Something went wrong. Please try again.");
            }
        });
    });
});
$(document).on('click', '.deleteBtn', function() {
    var id = $(this).data('id');

    if (confirm('Are you sure you want to delete this record?')) {
        $.ajax({
            url: deleteURL + id,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content') // for Laravel CSRF protection
            },
            success: function(response) {
                alert('Record deleted successfully!');
                location.reload();
            },
            error: function(xhr) {
                alert('Something went wrong.');
            }
        });
    }
});