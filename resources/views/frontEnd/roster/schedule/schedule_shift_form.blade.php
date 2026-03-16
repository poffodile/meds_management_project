<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="{{ url('public/frontEnd/css/style.css') }}" rel="stylesheet">
    <link href="{{ url('public/frontEnd/css/bs3/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://cdn.form.io/formiojs/formio.form.min.css">
    <link rel='stylesheet' href='https://cdn.form.io/formiojs/formio.full.min.css'>
    <script src="{{ url('public/frontEnd/js/jquery.min.js') }}"></script>
</head>
<style>
    .webViewStaff {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    #sucsMsg {
        display: none;
        padding: 10px;
        margin-top: 5px;
    }
</style>

<body>
    <div class="container-fluid">
        <div class="row webViewStaff">
            <div class="col-lg-12">
                <div class="emergencyMain aiInciDetaReport rounded8">
                    <div class="cardHeaderp aIInsightsheader p24 rounded8" style="border-bottom:unset">
                        <div>
                            <h2 class="h2Head">
                                {{ $formTemplate->title }}
                            </h2>
                            <p class="muteText"><?php echo $formTemplate->details; ?></p>
                            <div class="mt-3">
                                <span class="careBadg darkBlackBadg healthcare">healthcare</span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="shift_document_id" id="shift_document_id" value="{{ $singleData->id }}">
                    <input type="hidden" name="is_form_filled" id="is_form_filled"
                        value="{{ $singleData->is_form_filled }}">
                    <input type="hidden" name="formid" id="formid" value="{{ $singleData->form_id }}">
                    <input type="hidden" id="form_template" value="{{ $singleData->form_template }}">
                    <input type="hidden" id="home_id" value="{{ $singleData->home_id }}">
                    <div class="p24">
                        <div class="calendarTabs tabStaffDe">
                            <form id="TopFormss">
                                {{-- LOAD DYNAMIC FORMS --}}
                                <div id="formiotest"></div>

                                <div class="d-flex justify-content-between flexWrap align-items-center mt20 pt24"
                                    style="border-top:1px solid #ddd">
                                    {{-- <div class="d-flex gap-3 flexWrap">
                                            <div>
                                                <button class="borderBtn">Previous</button>
                                            </div>
                                            <div>
                                                <button class="borderBtn">Save Draft</button>
                                            </div>
                                        </div> --}}
                                    <div>
                                        <button type="button" class="bgBtn blackBtn" id="submitForm">
                                            Submit
                                        </button>
                                    </div>
                                    <div id="sucsMsg" class="alert alert-success">

                                    </div>
                                </div>
                            </form>
                            <!-- END TAB CONTENT -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src='https://cdn.form.io/formiojs/formio.full.min.js'></script>

    <script>
        function loaddataontables() {

            let formid = $("#formid").val();
            let home_id = $("#home_id").val();
            var token = "<?= csrf_token() ?>";
            //alert(token);
            var settings = {
                "url": "{{ url('web/service/patterndataformio') }}",
                "method": "POST",
                "data": {
                    patterndata: formid,
                    home_id: home_id,
                    _token: token
                },
                //dataType: "json",
            };
            $.ajax(settings).done(function(response) {
                //
                //console.log(response);
                Formio.createForm(document.getElementById('formiotest'), {
                    components: JSON.parse(response)
                });
            });
        }

        function viewdatawithvalueFormios() {
            let shift_document_id = $("#shift_document_id").val();
            var token = "<?= csrf_token() ?>";
            var settings = {
                "url": "{{ route('web.roster.schedule_shift.form.fetch') }}",
                "method": "POST",
                "data": {
                    shift_document_id: shift_document_id,
                    _token: token
                },
                //dataType: "json",
            };
            $.ajax(settings).done(function(response) {
                Formio.createForm(document.getElementById('formiotest'), {
                    components: JSON.parse(response.pattern)
                }, {
                    readOnly: false
                }).then(function(form) {
                    form.submission = {
                        data: JSON.parse(response.pattern_value)
                    }
                });

            });
        }
        $(document).ready(function() {
            let is_form_filled = $("#is_form_filled").val() == 0 && $("#formid").val();
            if (is_form_filled) {
                loaddataontables();
            } else if ($("#is_form_filled").val() == 1 && $("#formid").val()) {
                // loaddataontables();
                viewdatawithvalueFormios();
            }

            $(document).on('click', "#submitForm", function() {
                let shift_document_id = $("#shift_document_id").val();
                var token = "<?= csrf_token() ?>";
                let forms = $("#TopFormss").serialize() + "&_token=" +
                    token + "&shift_document_id=" + shift_document_id;


                $.ajax({
                    url: "{{ route('web.roster.schedule_shift.form.save') }}", // URL to send the request to
                    type: 'POST', // or 'POST'
                    data: forms, // Data to send with the request
                    beforeSend: function() {},
                    success: function(res) {
                        if (res.status) {

                            $("#sucsMsg")
                                .attr("tabindex", -1)
                                .addClass('alert-success')
                                .removeClass('alert-danger')
                                .show()
                                .html(res.message)
                                .focus().fadeOut(5000);
                        } else {
                            $("#sucsMsg")
                                .attr("tabindex", -1)
                                .removeClass('alert-success')
                                .addClass('alert-danger')
                                .show()
                                .html('Something went wrong !!')
                                .focus().fadeOut(5000);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        $("#sucsMsg")
                            .attr("tabindex", -1)
                            .removeClass('alert-success')
                            .addClass('alert-danger')
                            .show()
                            .html('Something went wrong !!')
                            .focus().fadeOut(5000);
                    }
                });

            });
        })
    </script>
</body>

</html>