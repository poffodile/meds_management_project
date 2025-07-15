
//checking ajax response. if it is unauthorized, show message 
function isAuthenticated(resp){ //alert(2);
	if(resp == 'unauthorize' || resp == '"unauthorize"'){

        //alert("Sorry, You are not authorized to access this."); 

        if(confirm("Sorry, You are not authorized to access this. Do you want to contact Administrator for permissions")){
            $('#ModifyRequestModal').modal('show');
        }

        $('.loader').hide();
        $('body').removeClass('body-overflow');

        //$('.unauth-err').show();
        return false; 

    } else if(resp == 'locked' || resp == '"locked"' || resp == 'logged_out') {

        //window.location = "http://localhost/scits/lockscreen";

        alert("Sorry, Your Session has been expired. Please reload the page."); 
        $('.loader').hide();
        $('body').removeClass('body-overflow');
        return false; 
    }else{
        return true;     	
    }
}

//
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

//show tick when textbox is filled
$(document).on('keyup','.tick_text', function(){ //alert(1);
        var textarea = $(this);
        var val = textarea.val();
        val = val.trim();
    
    if(val == ''){
        textarea.closest('.input-group').find('.tick_show').html('');
    } else{
        textarea.closest('.input-group').find('.tick_show').html('<i class="fa fa-check"></i>');
    }
});

//----- click on cog(setting) icon to view options of a record
$(document).ready(function(){
    $(document).on('click','.settings',function(){
        $(this).find('.pop-notifbox').toggleClass('active');
        $(this).closest('.cog-panel').siblings('.cog-panel').find('.pop-notifbox').removeClass('active');
    });
    $(window).on('click',function(e){
        e.stopPropagation();
        var $trigger = $(".settings");
        if($trigger !== e.target && !$trigger.has(e.target).length){
            $('.pop-notifbox').removeClass('active');
        }
    });
});

//----- 3 tabs script for models
$('.logged-box').hide();
$('.search-box').hide();
$('.logged-btn').removeClass('active');
$('.search-btn').removeClass('active');

$('.add-new-btn').on('click',function(){ 
    $(this).addClass('active');
    $(this).siblings().removeClass('active');
    $(this).closest('.modal-body').find('.add-new-box').show();
    $(this).closest('.modal-body').find('.add-new-box').siblings('.risk-tabs').hide();
});
$('.logged-btn').on('click',function(){ //alert(1);
    $(this).addClass('active');
    $(this).siblings().removeClass('active');
    $(this).closest('.modal-body').find('.logged-box').show();
    $(this).closest('.modal-body').find('.logged-box').siblings('.risk-tabs').hide();
});
$('.search-btn').on('click',function(){
    $(this).addClass('active');
    $(this).siblings().removeClass('active');
    $(this).closest('.modal-body').find('.search-box').show();
    $(this).closest('.modal-body').find('.search-box').siblings('.risk-tabs').hide();
});

//----- when click on plus button then details of the record will be shown below the record
$('.input-plusbox').hide();
$(document).on('click','.input-plus',function(){
    $(this).closest('.cog-panel').find('.input-plusbox').toggle();
    setTimeout(function () {
        autosize($("textarea"));
    },200);
});


//----- script used in appointments.blade.php and risk.blade.php
/*---------Three tabs click option----------*/
$('.risk-logged-box').hide();
$('.risk-search-box').hide();
$('.risk-logged-btn').removeClass('active');
$('.risk-search-btn').removeClass('active');

$('.risk-add-btn').on('click',function(){ 
    $(this).addClass('active');
    $(this).siblings().removeClass('active');
    $(this).closest('.modal-body').find('.risk-add-box').show();
    $(this).closest('.modal-body').find('.risk-add-box').siblings('.risk-tabs').hide();
});
$('.risk-logged-btn').on('click',function(){
    $(this).addClass('active');
    $(this).siblings().removeClass('active');
    $(this).closest('.modal-body').find('.risk-logged-box').show();
    $(this).closest('.modal-body').find('.risk-logged-box').siblings('.risk-tabs').hide();
});
$('.risk-search-btn').on('click',function(){
    $(this).addClass('active');
    $(this).siblings().removeClass('active');
    $(this).closest('.modal-body').find('.risk-search-box').show();
    $(this).closest('.modal-body').find('.risk-search-box').siblings('.risk-tabs').hide();
});

$(document).ready(function(){

    $(document).on('click','.rec-head', function(){
        $(this).next('.rec-content').slideToggle();
        $(this).find('i').toggleClass('fa-angle-down');
        //$('.input-plusbox').hide();
    });
});

/*    $(document).ready(function(){

    $(document).on('click','.daily-rcd-head', function(){
        $(this).next('.daily-rcd-content').slideToggle();
        $(this).find('i').toggleClass('fa-angle-down');
        $('.input-plusbox').hide();
    });
});
*/

$(document).on('click','.conf-del', function(){
    if(confirm("Are sure you to delete this ?")){
        return true;
    } else{
        return false;
    }
}); 







  const inputs = document.querySelectorAll('.dd-input');

  // Handle dropdown behavior like radio buttons
  inputs.forEach(input => {
    input.addEventListener('change', () => {
      if (input.checked) {
        inputs.forEach(otherInput => {
          if (otherInput !== input) {
            otherInput.checked = false;
          }
        });
      }
    });
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', function (e) {
    inputs.forEach(input => {
      // Check if the click target is outside the dropdown wrapper
      const dropdown = input.closest('.dropdown'); // Adjust selector to match your wrapper
      if (dropdown && !dropdown.contains(e.target)) {
        input.checked = false;
      }
    });
  });
