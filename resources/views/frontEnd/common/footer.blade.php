@include('frontEnd.common.modify_request')
@include('frontEnd.common.system_guide')
@include('frontEnd.common.sticky_notification')

<!--footer start-->
<footer class="footer-section">
  <div class="text-center">
    {{ date('Y')}} &copy; Care One OS
    <a href="#" class="go-top">
      <!-- <i class="fa fa-angle-up"></i> -->
      <img src="{{  asset('public/images/scits_hand.png')}}" alt="system_guide" class="system_guide" height="25" width="auto" />
    </a>
  </div>

  <!-- <div class="text-left">
    <a href="#" style="color:white;" class="system_guide"> System Guide </a>
  </div> -->
</footer>
<!--footer end-->
<script src="https://www.dukelearntoprogram.com/course1/common/js/image/SimpleImage.js"></script>

<script>
  $(document).ready(function() {
    $('#myTable').DataTable();
    // $('#expend_cash_table').DataTable();
    // $("#expend_cash_table").append(
    //   $('<tfoot/>').append( $("#expend_cash_table thead tr").clone() )
    // );
  });


  function upload() {
    var fileinput = document.getElementById("finput");
    var image = new SimpleImage(fileinput);
    console.log("Image name:", image);
    const fileName = fileinput.files[0].name;

    const formData = new FormData();
    formData.append('image', fileinput.files[0]);

    const reader = new FileReader();

    // When the file is loaded, set the image source to the file's data
    reader.onload = function(e) {
      const preview = document.getElementById('formImagePreview');
      const formImageHide = document.getElementById('previewContainer');

      console.log(preview);
      preview.src = e.target.result;

      formImageHide.style.display = 'block'; // Show the image
      preview.style.display = 'block'; // Show the image
    };

    reader.readAsDataURL(fileinput.files[0]);

    fetch('{{ route("saveFormDotIoImage") }}', {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Include CSRF token
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          console.log('Image saved:', data.file_path);
          const elements = document.querySelectorAll('.uploded_image');
          elements.forEach(element => {
            element.value = data.file_path;
          });
          // alert('Image uploaded successfully!');
        } else {
          alert('Failed to upload image: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while uploading the image.');
      });
  }

  function uploadImageFun() {

    var fileinput = document.getElementById("finput2");
    console.log(fileinput);
    var image = new SimpleImage(fileinput);

    console.log("Image name:", image);
    const fileName2 = fileinput.files[0].name;

    const formDataEdit = new FormData();
    formDataEdit.append('image', fileinput.files[0]);

    const reader = new FileReader();

    // When the file is loaded, set the image source to the file's data
    reader.onload = function(e) {
      const preview2 = document.getElementById('previousImage');
      const previewContainer2 = document.getElementById('previewContainer2');

      console.log(preview2);
      preview2.src = e.target.result;
      preview2.style.display = 'block'; // Show the image
      previewContainer2.style.display = 'block'; // Show the image
    };

    reader.readAsDataURL(fileinput.files[0]);

    fetch('{{ route("saveFormDotIoImage") }}', {
        method: 'POST',
        body: formDataEdit,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Include CSRF token
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          console.log('Image saved:', data.file_path);
          const elements = document.querySelectorAll('.uploded_image');
          elements.forEach(element => {
            elements.value = "";
            element.value = data.file_path;
          });
          // alert('Image uploaded successfully!');
        } else {
          alert('Failed to upload image: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while uploading the image.');
      });
    console.log("File Name:", fileName2);

  }

  $(document).ready(function() {
    $('.system_guide').on('click', function() {
      $('#System_guide').modal('show');
    });
  });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/choices.js/1.1.6/choices.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<!-- <script src="{{ url('/public/js/datepicker.js') }}"></script> -->
<script>
  // Date Pikker INCLUDE JQUERY & JQUERY UI 1.12.1
  // $( function() {
  //     $( "#datepicker" ).datepicker({
  //         dateFormat: "dd-mm-yy"
  //         ,	duration: "fast"
  //     });
  // } );
</script>
<script>
//   function childCourseData(){
//     $.ajax({
//         url: "{{ url('/proxy/courses') }}",
//         type: "GET",
//         success: function(response) {
//           console.log(response);
//           var courseHtml='<option selected disabled >Select Course</option>';
//           if(response.status === true){
//               var courseList=response.all_course_list;
//               courseList.forEach((val) =>{
//                   courseHtml+=`
//                       <option value="${val.title}" data-level="${val.level}" data-image="${val.image}" data-description="${val.description}" data-coursenumber="${val.coursenumber}">${val.title}</option>
//                   `;
//               });
//           }
//           $("#childCourse").html(courseHtml);
//         },
//         error: function(xhr) {
//             console.log("Error", xhr);
//         }
//     });
//   }
function childCourseData(id = null, callback = null){
    $("#ClientModalTitle").text("Add Client");
    $("#clientFormSaveBtn").text("Create Client");
    if (callback){
      $("#ClientModalTitle").text("Edit Client");
      $("#clientFormSaveBtn").text("Update Client");
    }else{
      $("#add_service_user")[0].reset();
      var $fileupload = $('.fileupload');
      var $preview = $fileupload.find('.fileupload-preview');
      $preview.html(
          '<img src="" ' +
          'style="max-height:150px; max-width:200px;" />'
      );
      $fileupload.removeClass('fileupload-exists').addClass('fileupload-new');
    }
    $.ajax({
        url: "{{ url('/proxy/courses') }}",
        type: "GET",
        success: function(response) {
          console.log("proxcy courses response:::");
          console.log(response);
          var courseHtml='';
          if(response.status === true){
              var courseList=response.all_course_list;
              var courese_index = 0;
              courseList.forEach((val, key) => {
                    courseHtml += `
                        <div class="course-box" data-index="${key}">
                            <label>
                                <input type="checkbox" class="course_qualifications" data-coursenumber="${val.coursenumber}" data-title="${val.title}">${val.title}
                            </label>
                            <input type="hidden" data-name="courses[${key}][coursenumber]" value="${val.coursenumber}">
                            <input type="hidden" data-name="courses[${key}][title]" value="${val.title}">
                            <input type="hidden" data-name="courses[${key}][level]" value="${val.level}">
                            <input type="hidden" data-name="courses[${key}][course_image]" value="${val.image}">
                            <input type="hidden" data-name="courses[${key}][description]" value="${val.description}">
                            <input type="hidden" data-name="courses[${key}][course_id]" value="${val.course_id}">
                            <input type="hidden" data-name="courses[${key}][table_id]" value="" class="table_id_input">
                            
                        </div>
                    `;
                    courese_index = key;
                });
                courseHtml += `
                        <div class="course-box" data-index="${courese_index+1}">
                            <label>
                                <input type="checkbox" class="course_qualificationsOther">Other
                            </label>
                        </div>
                    `;
                // <input type="file" class="qual_upload" data-name="courses[${key}][certificate]" disabled>
          }
          $(".su_usercheckbox-grid").html(courseHtml);
          getCarerList(function(){
              if (callback) callback();
          });
          // if (callback) callback();
        },
        error: function(xhr) {
            console.log("Error", xhr);
        }
    });
  }
  function getCarerList(callback = null){
    
    $.ajax({
        url: "{{ url('/roster/carer-list') }}",
        type: "post",
        success: function(response) {
          // console.log(response);
          // return false;
          var carersHtml='';
          if(response.status === true){
              var carerList=response.data;
              carerList.forEach((val, key) => {
                    carersHtml += `
                        <div class="carer-box" data-index="${key}">
                            <label>
                                <input type="checkbox" class="carer_checkbox" data-id="${val.id}">${val.name}
                            </label>
                            <input type="hidden" data-name="carers[${key}][id]" value="${val.id}">
                            <input type="hidden" data-name="carers[${key}][table_id]" value="" class="table_carer_id">
                            
                        </div>
                    `;
                });
                
          }
          $(".carer_checkbox-html").html(carersHtml);
          if (callback) callback();
        },
        error: function(xhr) {
            console.log("Error", xhr);
        }
    });
  }
  let deletedCarerIds = [];
  $(document).on('change','.carer_checkbox',function(){
    var carer_box = $(this).closest('.carer-box');
    var tableId = carer_box.find('.table_carer_id').val();
    if($(this).is(':checked')){
      carer_box.find('[data-name]').each(function () {
          $(this).attr('name', $(this).data('name'));
      });
      if (tableId) {
          deletedCarerIds = deletedCarerIds.filter(id => id != tableId);
      }
    }else{
      carer_box.find('[data-name]').each(function () {
            $(this).removeAttr('name');
        });
        if (tableId) {
            deletedCarerIds.push(tableId);
        }
    }
    $('#deleted_carerIds').val(JSON.stringify(deletedCarerIds));
  });
  function autoCheckCarers(selectedCarers){
    // console.log("carers::: ",selectedCarers);
    $('.carer_checkbox').each(function () {
        let carerCheckbox = $(this);
        let id = carerCheckbox.data('id');
        let carerMatched = selectedCarers.find(carer => carer.carer_id == id);
        if (carerMatched) {
            carerCheckbox.prop('checked', true);
            let carerbox = carerCheckbox.closest('.carer-box');
            carerbox.find('[data-name]').each(function () {
                $(this).attr('name', $(this).data('name'));
            });

            carerbox.find('.table_carer_id').val(carerMatched.id);
        }
    });
  }
</script>