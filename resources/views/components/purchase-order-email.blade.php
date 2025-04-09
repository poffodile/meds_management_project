<style>

#toList li:hover{
    cursor: pointer;
}
ul#toList {
    padding: 0 5px;
    height: 156px;
    overflow: auto;
}
span.optext {
    background-color: lightgray;
    padding: 1px 0.75em;
    margin-right: 0.5em;
    margin-bottom: 2px;
    border-radius: 4px;
    display: inline-block;
}
span#emailCount {
    background-color: lightgray;
    padding: 0px 4px;
    border-radius: 4px;
    display: inline-block;
}
span#emailCount1 {
    background-color: lightgray;
    padding: 0px 4px;
    border-radius: 4px;
    display: inline-block;
}
.emailSpan {
    /* margin-top:-30px; */
    display: inline-block;
    width: 100%;
}
.emailSpan1 {
    display: inline-block;
    width: 100%;
}
.dropdownMaltiSelect {
    position: relative;
}
.dropdownMaltiSelect #dropdownButton {
    text-align: left;
    position: relative;
    padding-right: 30px; 
    width: 100%;
}
.dropdownMaltiSelect #dropdownButton1 {
    text-align: left;
    position: relative;
    padding-right: 30px; 
    width: 100%;
}
.dropdownMaltiSelect .btn.active.focus, .dropdownMaltiSelect .btn.active:focus, .dropdownMaltiSelect .btn.focus, .dropdownMaltiSelect .btn:active.focus, .dropdownMaltiSelect .btn:active:focus, .dropdownMaltiSelect .btn:focus, .dropdownMaltiSelect label input.form-control {
    outline: none;
}
.dropdownMaltiSelect #dropdownButton::after {
    content: '';
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M7 10L12 15L17 10" stroke="%23ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>');
    background-size: 18px 18px;
    background-repeat: no-repeat;
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    transition: transform 0.2s ease;
}
.dropdownMaltiSelect #dropdownButton1::after {
    content: '';
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M7 10L12 15L17 10" stroke="%23ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>');
    background-size: 18px 18px;
    background-repeat: no-repeat;
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    transition: transform 0.2s ease;
}
.dropdownMaltiSelect #dropdownButton.active::after {
    transform: translateY(-50%) rotate(180deg);
}
.dropdownMaltiSelect #dropdownButton1.active::after {
    transform: translateY(-50%) rotate(180deg);
}
.dropdownMaltiSelect .dropdown-menu {
    display: none;
    width:100%;
    position: absolute;
    background-color: #f9f9f9;
    border: 1px solid #ccc;
    box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
    padding: 0;
    max-height: 200px;
    overflow: auto;
    z-index: 1;
}
.dropdownMaltiSelect .dropdown-menu::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}
.dropdownMaltiSelect .dropdown-menu::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.dropdownMaltiSelect .dropdown-menu::-webkit-scrollbar-thumb {
    background: #888;
}
.dropdownMaltiSelect .dropdown-menu::-webkit-scrollbar-thumb:hover {
    background: #555;
}
.dropdownMaltiSelect .dropdown-menu::-webkit-scrollbar-button {
    display: none;
}
.dropdownMaltiSelect .dropdown-menu label {
    display: block;
    padding: 7px 8px;
    cursor: pointer;
    margin: 0;
    user-select: none;
    border-bottom: 1px solid #e5e4e4;
    font-size: 12px;
}
.dropdownMaltiSelect .dropdown-menu label:hover {
    background-color: #f1f0f0;
}
.dropdownMaltiSelect .dropdown-menu label.checked {
    background-color: #e0e0e0; /* Background color for checked items */
}
.dropdownMaltiSelect .dropdown-menu label input {
    margin-right: 10px;
}
.dropdownMaltiSelect .dropdown-menu label input.form-control{
display: block;
    width: 100%;
    padding: .375rem .75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #000;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #eee;
    border-radius: 4px;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
}
</style>
<div class="modal fade" id="{{ $emailModalId }}" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalTitle }}"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="text-center mt-3" id="message_emailModal" style="display:none"></div>
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="formDtail">
                            <form id="{{ $emailformId }}" class="customerForm">
                                @csrf
                                <input type="hidden" name="id" id="{{ $emailId }}">
                                <input type="hidden" name="{{ $foreignId }}" id="email_{{ $foreignId }}">

                                <div class="mb-2 row">
                                    <label for="inputProject" class="col-sm-3 col-form-label">To <span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                        <div class="dropdownMaltiSelect">
                                            <button type="button" id="dropdownButton" class="form-control editInput"></button>
                                            <div id="dropdownMenu" class="dropdown-menu">
                                                <label><input type="search" id="{{ $toField }}" class="form-control"></label>
                                                <div class="emailSpan">
                                                
                                                </div>
                                                <!-- <div class="parent-container to-container"></div> -->
                                            </div>
                                        </div>
                                        <input type="hidden" id="selectedToEmail" name="selectedToEmail">
                                        
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputProject" class="col-sm-3 col-form-label">Cc</label>
                                    <div class="col-sm-9">
                                        <div class="dropdownMaltiSelect">
                                            <button type="button" id="dropdownButton1" class="form-control editInput"></button>
                                            <div id="dropdownMenu1" class="dropdown-menu">
                                                <label><input type="search" id="{{ $ccField }}" class="form-control"></label>
                                                <div class="emailSpan1">
                                                
                                                </div>
                                                <!-- <div class="parent-container to-container"></div> -->
                                            </div>
                                        </div>
                                        <input type="hidden" id="selectedToEmail1" name="selectedToEmail1">
                                        <!-- <input type="text" class="form-control editInput" id="{{ $ccField }}" name="vat_amount"> -->
                                    </div>
                                </div>
                                
                                <div class="mb-2 row">
                                    <label for="inputProject" class="col-sm-3 col-form-label">Subject <span class="radStar ">*</span></label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control editInput" id="{{ $subject }}" name="subject">
                                    </div>
                                    <div class="col-sm-2">
                                        
                                        <select class="form-control editInput selectOptions" id="{{ $selectBoxsubject }}" name="defaultSelect">
                                            <option value="1" id="defaultOption">Default Purchase Order</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputAddress" class="col-sm-3 col-form-label">Body</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control textareaInput CustomercheckError" id="{{ $body }}" name="body" rows="10" maxlength="500"></textarea>
                                    </div>
                                </div>
                                
                            </form>
                        </div>
                    </div>
                </div> <!-- End row -->
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="profileDrop" id="{{ $saveButtonId }}">Save</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.3.2/ckeditor.js"></script>
<script>


        //Text Editer

var editor_config = {
  toolbar: [
      {name: 'basicstyles', items: ['Bold','Italic','Underline','Strike','-','RemoveFormat']},
      {name: 'format', items: ['Format']},
      {name: 'paragraph', items: ['Indent','Outdent','-','BulletedList','NumberedList']},
      {name: 'link', items: ['Link','Unlink']},
{name: 'undo', items: ['Undo','Redo']}
  ],
};

CKEDITOR.replace('{{ $body }}', editor_config );
// CKEDITOR.replace('customer_notes', editor_config );
// CKEDITOR.replace('internal_notes', editor_config );
//Text Editer
</script>
<script>
    
    $("#{{ $saveButtonId }}").on('click',function(){
        for (var instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }
        $.ajax({
            type: "POST",
            url: "{{ $saveUrl }}",
            data: new FormData($("#{{ $emailformId }}")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
                success: function(response) {
                    // console.log(response);return false;
                    if(response.vali_error){
                        alert(response.vali_error);
                        $("#email").css('border','1px solid red');
                        $(window).scrollTop(0);
                        return false;
                    }else if(response.success === true){
                        $(window).scrollTop(0);
                        $('#message_emailModal').addClass('success-message').text(response.message).show();
                        setTimeout(function() {
                            $('#message_emailModal').removeClass('success-message').text('').hide();
                            getAllEmails(response.data);
                        }, 3000);
                    }else{
                        // alert("Something went wrong");
                        $('#message_emailModal').addClass('error-message').text(response.message).show();
                        setTimeout(function() {
                            // $('#error-message').text('').fadeOut();
                            $('#message_emailModal').removeClass('error-message').text('').hide();
                        }, 3000);
                        return false;
                    }
                },
                error: function(xhr, status, error) {
                   var errorMessage = xhr.status + ': ' + xhr.statusText;
                    console.log('Error - ' + errorMessage + "\nMessage: " + error);
                }
            });
    });
</script>
<script>
    $('#{{ $toField }}').on('keyup', function() {
        let to = $(this).val();
        searchTo(to);
    });
    $('#{{ $ccField }}').on('keyup', function() {
        let cc = $(this).val();
        searchCC(cc);
    });
    function searchTo(to=null){
        // alert(to);return false;
        $.ajax({
        url: "{{ url('search_email_list') }}",
        method: 'post',
        data: {
            email: to,_token: '{{ csrf_token() }}'
        },
            success: function(response) {
                console.log(response);
                // return false;
                const deptdivList = document.querySelector('.emailSpan');
                // deptdivList.innerHTML = "";
                if(response.all_emails.length >0){
                    var data_response='';
                    response.all_emails.forEach(item => {
                        const userType=item.userType;
                        
                        var userTypeText='';
                        if(userType == 1){
                            userTypeText='Customer';
                        }else if(userType == 2){
                            userTypeText='Supplier';
                        }else{
                            userTypeText='User';
                        }
                        data_response += `<label class="editInput email_lists" data-email="${item.email}" data-name="${item.name}" data-user-type="${userType}">${item.email} - ${item.name} (${userTypeText})</label>`;
                    });
                    deptdivList.innerHTML=data_response;
                    var EmailExist=$("#selectedToEmail").val();
                    const selectedEmails = [];
                    if(EmailExist !=''){
                        selectedEmails.push(EmailExist);
                    }
                    document.querySelectorAll('.email_lists').forEach(label => {
                        label.addEventListener('click', function () {
                            const email = this.getAttribute('data-email');
                            const name = this.getAttribute('data-name');
                            const userType = this.getAttribute('data-user-type');

                            // console.log(`Email: ${email}`);
                            // console.log(`Name: ${name}`);
                            // console.log(`User Type: ${userType}`);
                            selectedEmails.push(email);
                            $("#selectedToEmail").val(selectedEmails);
                            const countEmailExist=$("#selectedToEmail").val();
                            var exp = countEmailExist ? countEmailExist.split(",") : [];
                            console.log(exp);
                            if(exp.length >2){
                                const countList=exp.length-2;
                                $("#emailCount").remove();
                                $("#dropdownButton").append('<span id="emailCount">+'+countList+'</span>');
                            }else{
                                $("#dropdownButton").append('<span class="optext">'+email+'&emsp;<b class="removeSpan" onclick="removeSpan(this)">X</b></span>');
                            }
                            // alert(`Selected: ${email} - ${name} (${userType})`);
                        });
                    });
                }else{
                    deptdivList.innerHTML = '<li>No data found</li>';
                }

            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }
    function searchCC(cc=null){
        // alert(to);return false;
        $.ajax({
        url: "{{ url('search_email_list') }}",
        method: 'post',
        data: {
            email: cc,_token: '{{ csrf_token() }}'
        },
            success: function(response) {
                console.log(response);
                // return false;
                const deptdivList1 = document.querySelector('.emailSpan1');
                deptdivList1.innerHTML = "";
                if(response.all_emails.length >0){
                    var data_response1='';
                    response.all_emails.forEach(item => {
                        const userType1=item.userType;
                        
                        var userTypeText1='';
                        if(userType1 == 1){
                            userTypeText1='Customer';
                        }else if(userType1 == 2){
                            userTypeText1='Supplier';
                        }else{
                            userTypeText1='User';
                        }
                        data_response1 += `<label class="editInput email_lists1" data-email="${item.email}" data-name="${item.name}" data-user-type="${userType1}">${item.email} - ${item.name} (${userTypeText1})</label>`;
                    });
                    deptdivList1.innerHTML=data_response1;
                    var EmailExist1=$("#selectedToEmail1").val();
                    const selectedEmails1 = [];
                    if(EmailExist1 != ''){
                        selectedEmails1.push(EmailExist1);
                    }
                    // console.log("hee"+selectedEmails1);return false;
                    document.querySelectorAll('.email_lists1').forEach(label => {
                        label.addEventListener('click', function () {
                            const email = this.getAttribute('data-email');
                            const name = this.getAttribute('data-name');
                            const userType = this.getAttribute('data-user-type');

                            // console.log(`Email: ${email}`);
                            // console.log(`Name: ${name}`);
                            // console.log(`User Type: ${userType}`);
                            selectedEmails1.push(email);
                            $("#selectedToEmail1").val(selectedEmails1);
                            const countEmailExist1=$("#selectedToEmail1").val();
                            var exp1 = countEmailExist1 ? countEmailExist1.split(",") : [];
                            console.log(exp1);
                            if(exp1.length >2){
                                const countList=exp1.length-2;
                                $("#emailCount1").remove();
                                $("#dropdownButton1").append('<span id="emailCount1">+'+countList+'</span>');
                            }else{
                                $("#dropdownButton1").append('<span class="optext">'+email+'&emsp;<b class="removeSpan1" onclick="removeSpan1(this)">X</b></span>');
                            }
                            // alert(`Selected: ${email} - ${name} (${userType})`);
                        });
                    });
                }else{
                    deptdivList1.innerHTML = '<li>No data found</li>';
                }

            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }
    // function removeSpan(button){
    //     var selectedToEmail=$("#selectedToEmail").val();
    //     var row = button.parentNode;
    //     console.log(row);
    //     row.parentNode.removeChild(row);
    // };
function removeSpan(button) {
    var selectedToEmail = $("#selectedToEmail").val();
    var emailArray = selectedToEmail ? selectedToEmail.split(",") : [];
    var emailToRemove = button.parentNode.textContent.trim().split("X")[0];
    // console.log("email "+emailToRemove.trim());return false;
    var row = button.parentNode;
    row.parentNode.removeChild(row);
    var reemailArray = emailArray.filter(email => email !== emailToRemove.trim());
    $("#selectedToEmail").val(reemailArray.join(","));

    // console.log(reemailArray);
    const countExistEmail=reemailArray.length-2;
    var dropdownButton= $("#dropdownButton");
    dropdownButton.html('');
    if(reemailArray.length <3){
        for(var i=0;i<2;i++){
            if(reemailArray[i] !=undefined){
                dropdownButton.append('<span class="optext">'+reemailArray[i]+'&emsp;<b class="removeSpan" onclick="removeSpan(this)">X</b></span>');
            }
        }
    }else{
        $("#emailCount").remove();
        for(var i=0;i<reemailArray.length-1;i++){
           dropdownButton.append('<span class="optext">'+reemailArray[i]+'&emsp;<b class="removeSpan" onclick="removeSpan(this)">X</b></span>');
        }
        $("#dropdownButton").append('<span id="emailCount">+'+countExistEmail+'</span>');
    }
}
function removeSpan1(button1) {
    var selectedToEmail1 = $("#selectedToEmail1").val();
    var emailArray1 = selectedToEmail1 ? selectedToEmail1.split(",") : [];
    var emailToRemove1 = button1.parentNode.textContent.trim().split("X")[0];
    // console.log("email "+emailToRemove1.trim());return false;
    var row1 = button1.parentNode;
    row1.parentNode.removeChild(row1);
    var reemailArray1 = emailArray1.filter(email1 => email1 !== emailToRemove1.trim());
    $("#selectedToEmail1").val(reemailArray1.join(","));

    // console.log(reemailArray1);
    const countExistEmail1=reemailArray1.length-2;
    var dropdownButton1= $("#dropdownButton1");
    dropdownButton1.html('');
    if(reemailArray1.length <3){
        for(var i=0;i<2;i++){
            if(reemailArray1[i] !=undefined){
                dropdownButton1.append('<span class="optext">'+reemailArray1[i]+'&emsp;<b class="removeSpan1" onclick="removeSpan1(this)">X</b></span>');
            }
        }
    }else{
        $("#emailCount1").remove();
        for(var i=0;i<reemailArray1.length-1;i++){
           dropdownButton1.append('<span class="optext">'+reemailArray1[i]+'&emsp;<b class="removeSpan1" onclick="removeSpan1(this)">X</b></span>');
        }
        $("#dropdownButton1").append('<span id="emailCount1">+'+countExistEmail1+'</span>');
    }
}
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
    const dropdownButton = document.getElementById("dropdownButton");
    const dropdownButton1 = document.getElementById("dropdownButton1");
    const dropdownMenu = document.getElementById("dropdownMenu");
    const dropdownMenu1 = document.getElementById("dropdownMenu1");
    // const selectAllCheckbox = document.getElementById("selectAll");
    const optionCheckboxes = document.querySelectorAll(".option");

    dropdownButton.addEventListener("click", function() {
        dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
        dropdownButton.classList.toggle("active");
        // $("#toList").show();
        searchTo(null);
    });
    dropdownButton1.addEventListener("click", function() {
        dropdownMenu1.style.display = dropdownMenu1.style.display === "block" ? "none" : "block";
        dropdownButton1.classList.toggle("active");
        // $("#toList").show();
        searchCC(null);
    });

    document.addEventListener("click", function(event) {
        if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.style.display = "none";
            dropdownButton.classList.remove("active");
        }
    });
    document.addEventListener("click", function(event) {
        if (!dropdownButton1.contains(event.target) && !dropdownMenu1.contains(event.target)) {
            dropdownMenu1.style.display = "none";
            dropdownButton1.classList.remove("active");
        }
    });

    // selectAllCheckbox.addEventListener("change", function() {
    //     optionCheckboxes.forEach(function(checkbox) {
    //         checkbox.checked = selectAllCheckbox.checked;
    //         updateLabelClass(checkbox);
    //     });
    //     updateDropdownText();
    // });

    // optionCheckboxes.forEach(function(checkbox) {
    //     checkbox.addEventListener("change", function() {
    //         updateDropdownText();
    //         updateLabelClass(checkbox);
    //         if (!checkbox.checked) {
    //             selectAllCheckbox.checked = false;
    //         }
    //     });
    // });

    // function updateLabelClass(checkbox) {
    //     const label = checkbox.parentElement;
    //     if (checkbox.checked) {
    //         label.classList.add("checked");
    //     } else {
    //         label.classList.remove("checked");
    //     }
    // }

    // function updateDropdownText() {
    //     const selectedOptions = [];
    //     optionCheckboxes.forEach(function(checkbox) {
    //         if (checkbox.checked) {
    //             selectedOptions.push(checkbox.value);
    //         }
    //     });

    //     if (selectedOptions.length > 3) {
    //         dropdownButton.textContent = `${selectedOptions.length} selected`;
    //     } else if (selectedOptions.length > 0) {
    //         dropdownButton.textContent = selectedOptions.join(", ");
    //     } else {
    //         dropdownButton.textContent = "Select options";
    //     }

    //     // alert(`Selected options: ${selectedOptions.join(", ")}`);
    // }
});
</script>