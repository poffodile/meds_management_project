@extends('frontEnd.layouts.master')
@section('title', 'Child Profile')
@section('content')
    <style>
        .todaysMood {
            display: flex;
            justify-content: end;
        }

        a.canvasjs-chart-credit {
            display: none;
        }

        .mood-img.selected {
            border: 3px solid #f0ad4e !important;
            border-radius: 10px;
        }
    </style>

    <section id="main-content">
        <section class="wrapper">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <section class="panel">
                    <header class="panel-heading">
                        Child's Mood
                    </header>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="todaysMood">
                               
                                    <div class="jobsection">
                                        <a href="#!" class="profileDrop openMoodModel" data-action="add">+ Add mood</a>
                                    </div>
                                </div>

                                    <div class="tabl">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Date</th>
                                                    <th>Mood</th>
                                                    <th>image</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($su_moods as $index => $su_mood)
                                                    <?php
                                                    $image = url(MoodImgPath . '/dummy.jpg');
                                                    if (!empty($su_mood->image)) {
                                                        $image = url(MoodImgPath . '/' . $su_mood->image);
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ date('d-m-Y', strtotime($su_mood->created_at)) }}</td>
                                                        <td>{{ $su_mood->name }}</td>
                                                        <td><img src="{{ $image }}" alt="{{ $su_mood->name }}"
                                                                height="80" width="80" class="mood-img"
                                                                data-id="{{ $su_mood->id }}"
                                                                style="border:2px solid transparent; border-radius:10px;">
                                                        </td>
                                                        <td>
                                                            {{-- <a href="{{ url('service/mood/edit/' . $su_mood->id) }}"
                                                                class="editMoodBtn"><i class="fa fa-edit"></i></a> | <a
                                                                href="{{ url('service/mood/delete' . $su_mood->id) }}"
                                                                class="deleteMoodBtn"><i class="fa fa-trash"></i></a> --}}
                                                            <a href="javascript:void(0)" class="openMoodModel"
                                                                data-action="edit" data-id="{{ $su_mood->id }}"
                                                                data-mood_id="{{ $su_mood->mood_id }}"><i
                                                                    class="fa fa-edit"></i></a> | <a
                                                                href="javascript:void(0)" class="deleteMoodBtn" data-id="{{ $su_mood->id }}"><i
                                                                    class="fa fa-trash"></i></a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                              
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </section>
    </section>

    <!-- Child Emotional Health/Mood Modal -->
    <div class="modal fade" id="moodModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class ="row">
                        <form id="moodForm" class="form-horizontal">
                            @csrf
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <input type="hidden" id="edit_mood_id" name="edit_mood_id" value="">
                                <input type="hidden" id="mood_id">
                                <div class="formDtail">
                                    @foreach ($moods as $mood)
                                        <?php
                                        $image = url(MoodImgPath . '/dummy.jpg');
                                        if (!empty($mood->image)) {
                                            $image = url(MoodImgPath . '/' . $mood->image);
                                        }
                                        ?>

                                        <div class="mood-option"
                                            style="display:inline-block; margin:10px; text-align:center;">
                                            <input type="hidden" value="{{ $service_user_id }}" name="service_user_id">
                                            <input type="radio" name="mood_id" id="mood_{{ $mood->id }}"
                                                value="{{ $mood->id }}" style="display:none;">
                                            <label for="mood_{{ $mood->id }}" style="cursor:pointer;">
                                                <img src="{{ $image }}" alt="{{ $mood->name }}" height="80"
                                                    width="80" class="mood-img" data-id="{{ $mood->id }}"
                                                    style="border:2px solid transparent; border-radius:10px;">
                                                <div>{{ $mood->name }}</div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="modal-footer m-t-0 m-b-15 modal-bttm">
                                <button class="btn btn-default" type="button" data-dismiss="modal"
                                    aria-hidden="true">Cancel</button>
                                <button class="btn btn-warning" id="saveChildMood" type="button">Confirm</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        //showing model
        // $(document).ready(function() {

        //     $('.mood-chart').click(function() {

        //         $('.loader').show();
        //         $('body').addClass('body-overflow');

        //         var service_user_id = '{{ $service_user_id }}';

        //         if (service_user_id == undefined) {

        //             service_user_id = "{{ $service_user_id }}";
        //         }

        //         $.ajax({
        //             type: 'get',
        //             url: "{{ url('/service/moods') }}" + '/' + service_user_id,
        //             success: function(resp) {

        //                 if (isAuthenticated(resp) == false) {
        //                     return false;
        //                 }
        //                 if (resp == '') {
        //                     $('.su_moods_list').html(
        //                         '<div class="text-center p-b-20" style="width:100%">No Records found.</div>'
        //                     );
        //                 } else {
        //                     $('.su_moods_list').html(resp);
        //                 }
        //                 // $('.su_moods_list').html(resp);
        //                 // $('.health_record_input').val('');
        //                 $('#moodModal').modal('show');
        //                 $('.loader').hide();
        //                 $('body').removeClass('body-overflow');
        //             }
        //         });
        //         return false;
        //     })
        // });

        // save mood suggestion
        // $(document).on('click', '.submit-suggestion', function() {

        //     var su_mood_id = $(this).attr('su_mood_id');
        //     var service_user_id = "{{ $service_user_id }}";
        //     var suggestion = $(this).closest('.mood_record_row').find('textarea[name=\'mood_suggestion\']').val()
        //         .trim();
        //     var token = $('input[name=\'_token\']').val()

        //     if (suggestion == '') {
        //         $('textarea[name=\'mood_suggestion\']').addClass('red_border');
        //         return false;
        //     }

        //     $('.loader').show();
        //     $('body').addClass('body-overflow');

        //     $.ajax({

        //         type: 'post',
        //         url: "{{ url('/service/mood/suggestion/add') }}",
        //         data: {
        //             'suggestion': suggestion,
        //             'su_mood_id': su_mood_id,
        //             'service_user_id': service_user_id,
        //             'token': token,
        //         },

        //         success: function(resp) {

        //             if (isAuthenticated(resp) == false) {

        //                 return false;
        //             }
        //             if (resp == '' || resp == 0) {

        //                 $('span.popup_error_txt').text('Some error occured, try later.');
        //                 $('.popup_error').show();
        //                 setTimeout(function() {
        //                     $(".popup_error").fadeOut()
        //                 }, 5000);
        //             } else {
        //                 $('.su_moods_list').html(resp);
        //                 $('span.popup_success_txt').text('Suggestion Added Successfully');
        //                 $('.popup_success').show();
        //                 setTimeout(function() {
        //                     $(".popup_success").fadeOut()
        //                 }, 5000);
        //             }
        //             $('.loader').hide();
        //             $('body').removeClass('body-overflow');
        //         }
        //     });
        // });
    </script>

    <script>
        //pagination of Moods
        // $(document).on('click', '#moodModal .pagination li', function() {

        //     var page_no = $(this).children('a').text();
        //     if (page_no == '') {
        //         return false;
        //     }
        //     if (isNaN(page_no)) {
        //         var new_url = $(this).children('a').attr('href');
        //         page_no = new_url[new_url.length - 1];
        //     }

        //     $('.loader').show();
        //     $('body').addClass('body-overflow');

        //     $.ajax({
        //         type: 'get',
        //         url: "{{ url('/service/moods') }}" + '/' + "{{ $service_user_id }}" + "?page=" + page_no,
        //         success: function(resp) {
        //             if (isAuthenticated(resp) == false) {
        //                 return false;
        //             }

        //             $('.su_moods_list').html(resp);
        //             $('.loader').hide();
        //             $('body').removeClass('body-overflow');
        //         }
        //     });
        //     return false;
        // });
    </script>

    <script>
        $(document).ready(function() {
            $('.mood-img').click(function() {
                // remove highlight from all
                $('.mood-img').css('border', '2px solid transparent');

                // add highlight to the selected one
                $(this).css('border', '2px solid #f39c12');

                // select the corresponding radio button
                $(this).closest('.mood-option').find('input[type=radio]').prop('checked', true);
            });
        });

        $(document).on("click", ".mood-img", function() {
            var id = $(this).data("id");

            // select radio button
            $("#mood_" + id).prop("checked", true);

            // highlight selected image
            $(".mood-img").removeClass("selected");
            $(this).addClass("selected");
        });
    </script>

    <script>
        $(document).ready(function() {

            // Highlight selected mood
            $('.mood-img').click(function() {
                $('.mood-img').css('border', '2px solid transparent');
                $(this).css('border', '2px solid #f39c12');
                $(this).closest('.mood-option').find('input[type=radio]').prop('checked', true);
            });

            // Handle AJAX save
            $('#saveChildMood').click(function(e) {
                e.preventDefault();
                var formData = $('#moodForm').serialize();
                console.log("formData", formData);
                $.ajax({
                    url: "{{ url('service/mood/add') }}", // your backend route
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        console.log('✅ Success:', response.message);
                        alert(response.message);
                        location.reload();
                        $('#moodModal').modal('hide');
                    },
                    error: function(xhr) {
                        console.log('❌ Error:', xhr.responseText);
                        alert("Error saving mood");
                    }
                });
            });
        });

        $(document).on('click', '.deleteMoodBtn', function() {
            var id = $(this).data('id');

            if (!confirm("Are you sure you want to delete this mood?")) {
                return;
            }

            $.ajax({
                url: "{{ url('/service/mood/delete/') }}" + "/" + id,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },

                success: function(response) {
                    alert(response.message);
                    location.reload(); // reload list
                },

                error: function() {
                    alert("Error deleting mood");
                }
            });
        });
       
        $(document).on('click', '.openMoodModel', function() {

            var action = $(this).data('action');
            var mood_id = $(this).data("mood_id"); // mood selected for edit

            if (action == "add") {
                $(".modal-title").text("Add Mood");
                $("#edit_mood_id").val("");

                // Clear radio + image highlight
                $("input[name='mood_id']").prop("checked", false);
                $(".mood-img").removeClass("selected");
            } else if (action == "edit") {

                $(".modal-title").text("Edit Mood");
                $('#edit_mood_id').val($(this).data('id'));

                // Select the correct radio button
                $("input[name='mood_id']").prop("checked", false);
                $("#mood_" + mood_id).prop("checked", true);

                // Highlight selected image
                $(".mood-img").removeClass("selected");
                $(".mood-img[data-id='" + mood_id + "']").addClass("selected");
            }

            $('#moodModal').modal('show');
        });
    </script>


@endsection
