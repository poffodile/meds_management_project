@extends('frontEnd.layouts.master')
@section('title', 'Child Behavior')
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
           .star-rating {
            display: flex;
            flex-direction: row-reverse;
            font-size: 4em;
            justify-content: space-around;
            padding: 0 .2em;
            text-align: center;
            width: 6em;
            margin-bottom: 20px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            color: #ccc;
            cursor: pointer;
        }

        .star-rating :checked~label {
            color: #f90;
        }

        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #fc0;
        }
    </style>

    <section id="main-content">
        <section class="wrapper">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <section class="panel">
                    <header class="panel-heading">
                        Child's Behavior
                    </header>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="todaysMood">

                                    <div class="jobsection">
                                        <a href="javascript:void(0)" class="profileDrop openBehaviorModel" data-action="add"> + Add Behavior</a>
                                    </div>
                                </div>

                                <div class="tabl">
                                    <table class="table table-striped">
                                        <thead style="border-top: 1px solid #dddddd;">
                                            <tr>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th>Staff Name</th>
                                                <th>Rate</th>
                                                <th>Description</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($su_behaviors as $index => $su_behavior)
                                             
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ date('d-m-Y', strtotime($su_behavior->created_at)) }}</td>
                                                    <td>{{ $su_behavior->name }}</td>
                                                    <td> 
                                                        @php
                                                            $ratingStats = $su_behavior->rate;
                                                            $avg_rating = $ratingStats && $ratingStats ? round($ratingStats, 1) : 0;
                                                            $rating_count = $ratingStats ? intval($ratingStats) : 0;
                                                            $displayAvg = isset($avg_rating) ? (float) $avg_rating : 0;
                                                            $fullStars = floor($displayAvg);
                                                            $hasHalf = $displayAvg - $fullStars >= 0.5 ? true : false;
                                                        @endphp
                                                        <span class="profile-rating" title="{{ $rating_count ?? 0 }} rating(s)"
                                                        style="vertical-align:middle;">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            @if ($i <= $fullStars)
                                                                <i class="fa fa-star" style="color:#f90"></i>
                                                            @elseif ($i == $fullStars + 1 && $hasHalf)
                                                                <i class="fa fa-star-half-o" style="color:#f90"></i>
                                                            @else
                                                                <i class="fa fa-star-o" style="color:#ccc"></i>
                                                            @endif
                                                        @endfor
                                                        <small
                                                            style="margin-left:6px; color:#666;">({{ $displayAvg }})</small>
                                                    </span></td>
                                                    <td>{{ $su_behavior->description }}</td>
                                                     <td>
                                                        <div class="childMoodAction">
                                                            <a href="javascript:void(0)" class="openBehaviorModel" data-action="edit" data-id="{{ $su_behavior->id }}" data-rate="{{ $su_behavior->rate }}" data-description="{{ $su_behavior->description }}"><i class="fa fa-edit"></i></a> 
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


    <!-- Modal -->
    <div class="modal fade" id="BehaviorModal" tabindex="-1" role="dialog" aria-labelledby="BehaviorModalLabel">
        <div class="modal-dialog widthmodelRatting" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title"> Child Behavior </h4>
                </div>
                <div class="modal-body">
                    <div class="starmodelRatting">
                        <form id="ratingForm" method="POST">
                            @csrf
                            <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                            <input type="hidden" name="edit_behavior_id" id="edit_behavior_id" value="">
                            <div class="star-rating">
                                @for ($i = 5; $i >= 1; $i--)
                                    <input type="radio" id="{{ $i }}-stars" name="rating"
                                        value="{{ $i }}" >
                                    <label for="{{ $i }}-stars" class="star">&#9733;</label>
                                @endfor

                            </div>
                            <div class="form-group">
                                <label for="rate_comments">Description</label>
                                 <textarea class="form-control" name="description" id="rate_comments" rows="5" placeholder="Type comments..."></textarea>
                             </div>
                        </form>
                    </div>
                    <div class="form-group modal-footer m-t-0 ">
                        <button class="btn btn-warning" id="saveChildRating" type="submit">
                            Submit </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).on('click', '.openBehaviorModel', function() {

            var action = $(this).data('action');
            var behavior_id = $(this).data("behavior_id"); // mood selected for edit

            if (action == "add") {
                $(".modal-title").text("Add Behavior");
                $("#edit_behavior_id").val("");
            } else if (action == "edit") {
                $(".modal-title").text("Edit Behavior");
                $('#edit_behavior_id').val($(this).data('id'));
                var rate = $(this).data('rate');
                var description = $(this).data('description');
                // Set rating
                $('input[name="rating"][value="' + rate + '"]').prop('checked', true);
                // Set description
                $('#rate_comments').val(description);
            }

            $('#BehaviorModal').modal('show');
        });

          // Submit child rating via AJAX
        $(document).on('click', '#saveChildRating', function(e) {
            e.preventDefault();
            var $form = $('#ratingForm');
            var rating = $form.find('input[name="rating"]:checked').val();

            var serviceUserId = "{{ $service_user_id }}";
            $.ajax({
                url: "{{ url('/service/behavior/') }}" + "/" + serviceUserId,
                method: 'POST',
                data: $form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res && res.status) {
                        alert(res.message || 'Rating saved');
                        $('#BehaviorModal').modal('hide');
                        location.reload();
                    } else {
                        alert(res.message || 'Failed to save rating');
                         if (res.message && res.message.includes("already")) {
                            $('#BehaviorModal').modal('hide');
                        }
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        var first = Object.keys(xhr.responseJSON.errors)[0];
                        alert(xhr.responseJSON.errors[first][0]);
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        alert(xhr.responseJSON.message);
                    } else {
                        alert('An unexpected error occurred');
                    }
                }
            });
        });
    </script>
@endsection
