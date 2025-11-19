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
                         <div class="col-md-12 col-sm-12 col-xs-12">
                             <label for="rate_description">Description</label>
                             <textarea class="form-control" name="description" id="rate_description" rows="5" placeholder="Type comments..."></textarea>
                         </div>
                         <div class="col-md-12 col-sm-12 col-xs-12 m-t-10 m-b-10">
                            <label for="suggestions">Suggestions</label>
                             <textarea class="form-control" name="suggestions" id="suggestions" rows="5" placeholder="Type comments..."></textarea>
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
                     if(response.success == true){
                         location.reload();
                     }
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

             $('#rate_description').val($(this).data('description'));
             $('#suggestions').val($(this).data('suggestions'));

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
