<footer class="mainFooter">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="f_text">
                    <p><?php echo date('Y'); ?> © SCITS</p>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> -->
<script src="{{ url('public/frontEnd/jobs/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{ url('public/frontEnd/jobs/js/bootstrap.bundle.js')}}"></script>
<script src="https://www.ville-pont-eveque.fr/tools/library/DataTables/media/js/jquery.dataTables.js"></script>
<script src="https://www.ville-pont-eveque.fr/tools/library/DataTables/extensions/Select/js/dataTables.select.js"></script>
<script src="{{ url('public/frontEnd/jobs/js/custom.js')}}"></script>
<script src="{{ url('public/js/salesFinance/customCRM.js')}}"></script>
<script src="https://www.dukelearntoprogram.com/course1/common/js/image/SimpleImage.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{ url('public/frontEnd/js/developer.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.js" type="text/javascript"></script>
<script src="{{ url('public/frontEnd/js/selectize/selectize.js') }}"></script>
<script src='https://cdn.form.io/formiojs/formio.full.min.js'></script>

<script>
    function getCustomerList(id) {
        $.ajax({
            url: '{{ route("customer.ajax.getCustomerList") }}',
            success: function(response) {
                console.log(response.message);
                var get_customer_type = document.getElementById(id);
                get_customer_type.innerHTML = '';

                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.name;
                    get_customer_type.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
function date_convertInFromat(){
    // alert('here')
    flatpickr(".bulkinvoice_date", {
        dateFormat: "d/m/Y",
        defaultDate: new Date()
    });
}
</script>

</body>

</html>