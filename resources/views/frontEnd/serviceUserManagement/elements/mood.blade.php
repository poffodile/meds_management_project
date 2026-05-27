@extends('frontEnd.layouts.master')
@section('title', 'Child Mood')
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


.moodDescription {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;        /* 3 lines only */
    overflow: hidden;
    text-overflow: ellipsis;
    width: 232px;
    line-height: 1.4;
    max-height: calc(1.4em * 3);
    white-space: normal;  
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
                                            <thead style="border-top: 1px solid #dddddd;">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Date</th>
                                                    <th>Mood</th>
                                                    <th>image</th>
                                                    <th>Description</th>
                                                    <th>Suggestions</th>
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
                                                        <td>
                                                            <img src="{{ $image }}" alt="{{ $su_mood->name }}" height="50" width="50" class="mood-img" data-id="{{ $su_mood->id }}" style="border-radius:10px;">
                                                        </td>
                                                        <td style="width: 240px;"><div class="moodDescription"> {{ $su_mood->description }} </div> </td>
                                                        <td style="width: 240px;"><div class="moodDescription"> {{ $su_mood->suggestions }} </div> </td>
                                                        <td>
                                                            <div class="childMoodAction">
                                                                <a href="javascript:void(0)" class="openMoodModel" data-action="edit" data-id="{{ $su_mood->id }}" data-mood_id="{{ $su_mood->mood_id }}" data-description="{{ $su_mood->description }}" data-suggestions="{{ $su_mood->suggestions }}"><i class="fa fa-edit"></i></a> | 
                                                                <a href="javascript:void(0)" class="deleteMoodBtn" data-id="{{ $su_mood->id }}"><i class="fa fa-trash"></i></a>
                                                            </div>
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
    @include('frontEnd.serviceUserManagement.elements.add_mood')
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
@endsection
