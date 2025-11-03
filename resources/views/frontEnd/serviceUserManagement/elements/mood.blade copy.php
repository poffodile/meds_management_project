
<!-- Child Emotional Health/Mood Modal -->
<div class="modal fade" id="moodModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Emotional Thermometer</h4>
            </div>

            <div class="modal-body">
                <div class ="row">
                    <!-- <div class="col-md-12 col-sm-12 col-xs-12">
                        <h3 class="m-t-0 m-b-20 clr-blue fnt-20"> Logged Records </h3>
                    </div> -->
                        <!-- alert messages -->
                    @include('frontEnd.common.popup_alert_messages')
                    <form id="">
                        <div class="modal-space  su_moods_list"><!-- modal-pading -->
                            <!-- logged risk list be shown here using ajax -->
                        </div>
                    </form>

                    <div class="modal-footer btns m-t-0 modal-bttm m-b-10" style="visibility: hidden;"><!-- line -->
                        <!-- <a class="su-botm-calndr" href="{{ url('/service/calendar/'.$service_user_id) }}" ><div class="pull-left"><i class="fa fa-calendar"></i></div> </a> -->
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                        <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                        <button class="btn btn-warning submit_suggestion" type="button"  > Confirm </button><!-- submit_edit_health_record -->
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
 
</script>

<script>
    //showing model
    $(document).ready(function(){
        
        $('.mood-chart').click(function(){
            
            $('.loader').show();
            $('body').addClass('body-overflow');
        
            var service_user_id = '{{ $service_user_id }}';

            if(service_user_id == undefined)  {

                service_user_id = "{{ $service_user_id }}";
            } 

            $.ajax({
                type    : 'get',
                url     : "{{ url('/service/moods') }}"+'/'+service_user_id,
                success:function(resp){
                    
                    if(isAuthenticated(resp) == false){
                        return false;
                    }
                    if(resp == '') {
                        $('.su_moods_list').html('<div class="text-center p-b-20" style="width:100%">No Records found.</div>');    
                    } else {
                        $('.su_moods_list').html(resp);
                    }
                    // $('.su_moods_list').html(resp);
                    // $('.health_record_input').val('');
                    $('#moodModal').modal('show');
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        })
    }); 
    
    // save mood suggestion
    $(document).on('click', '.submit-suggestion', function(){
        
        var su_mood_id      = $(this).attr('su_mood_id');
        var service_user_id = "{{ $service_user_id }}";
        var suggestion      = $(this).closest('.mood_record_row').find('textarea[name=\'mood_suggestion\']').val().trim();
        var token           = $('input[name=\'_token\']').val()
        
        if(suggestion == '') {
            $('textarea[name=\'mood_suggestion\']').addClass('red_border');
            return false;
        }

        $('.loader').show();
        $('body').addClass('body-overflow');

        $.ajax({

            type:'post',
            url: "{{ url('/service/mood/suggestion/add') }}",
            data: { 'suggestion' : suggestion, 'su_mood_id': su_mood_id, 'service_user_id':service_user_id, 'token':token,  },

            success:function(resp) {

                if (isAuthenticated(resp) == false)  {

                    return false;
                }
                if(resp == ''|| resp == 0)  {
                    
                    $('span.popup_error_txt').text('Some error occured, try later.');
                    $('.popup_error').show();
                    setTimeout(function(){$(".popup_error").fadeOut()}, 5000);  
                }
                else { 
                    $('.su_moods_list').html(resp);
                    $('span.popup_success_txt').text('Suggestion Added Successfully');
                    $('.popup_success').show();
                    setTimeout(function(){$(".popup_success").fadeOut()}, 5000); 
                }
                $('.loader').hide();
                $('body').removeClass('body-overflow');
            }
        });
    });
    
</script>

<script>
    //pagination of Moods
    $(document).on('click','#moodModal .pagination li',function(){

        var page_no = $(this).children('a').text();
            if(page_no == '') {
                return false;
            }
            if(isNaN(page_no)) {
                var new_url = $(this).children('a').attr('href');
                page_no = new_url[new_url.length -1];
            }
        
        $('.loader').show();
        $('body').addClass('body-overflow');

        $.ajax({
            type : 'get',
            url  : "{{ url('/service/moods') }}"+'/'+"{{ $service_user_id }}"+"?page="+page_no,
            success:function(resp){
                if(isAuthenticated(resp) == false){
                    return false;
                }

                $('.su_moods_list').html(resp);
                $('.loader').hide();
                $('body').removeClass('body-overflow');
            }
        });
        return false;
    });
</script>