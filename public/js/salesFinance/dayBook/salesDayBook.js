$(document).ready(function () {
    $(".deleteBtn").on("click", function () {
        let salesBookId = $(this).data("id"); // Get ID from button
        let row = $("#row-" + salesBookId); // Select the row

        if (confirm("Are you sure you want to delete this record?")) {
            $.ajax({
                url: salesDayBook + "/"+ salesBookId,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success: function (response) {
                    if (response.success) {
                        // row.find("td:nth-child(7)").text(response.deleted_at); // Update deleted_at column
                        alert(response.message);
                        window.location.reload();
                    }
                },
                error: function () {
                    alert("Something went wrong!");
                },
            });
        }
    });
});