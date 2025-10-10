<style>
    .container {
        background-color: #eef2f5;
        width: 400px;
    }

    .addtxt {
        padding-top: 10px;
        padding-bottom: 10px;
        text-align: center;
        font-size: 13px;
        width: 350px;
        background-color: #e5e8ed;
        font-weight: 500
    }

    .form-control: focus {
        color: #000;
    }



    .second {
        /* width: 100%; */
        background-color: whitesmoke;
        border-radius: 4px;
        border: 1px solid #ebebeb;
        padding: 5px;
        display: flex;
        justify-content: space-between;
    }

    .text1 {
        font-size: 12px;
        font-weight: 500;
        color: #56575b;
        text-align: justify;
    }

    .text2 {
        font-size: 13px;
        font-weight: 500;
        margin-left: 6px;
        color: #56575b;
    }

    .text3 {
        font-size: 13px;
        font-weight: 500;
        margin-right: 4px;
        color: #828386;
        width: 120px;
        display: block;
    }

    .text3o {
        color: #00a5f4;
    }

    .text4 {
        font-size: 13px;
        font-weight: 500;
        color: #828386;
    }

    .text4i {
        color: #00a5f4;
    }

    .text4o {
        color: white;
    }

    .thumbup {
        font-size: 13px;
        font-weight: 500;
        margin-right: 5px
    }

    .thumbupo {
        color: #17a2b8;
    }
    .comment-staff-name{
        font-weight: 600;
        white-space: nowrap;
        margin-right: 12px;
    }
</style>


<!-- Log Book Modal -->
<div class="modal fade" id="commentsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Comments Section</h4>
            </div>
            <div class="modal-body">
                @include('frontEnd.common.popup_alert_messages')
                <div class="row">

                    <form id="su-log-book-form">
                        <div class="add-new-box risk-tabs custm-tabs p-15">

                            <div class="row">
                                <label class="col-md-12 col-sm-12 col-xs-12"> Add Comment: </label>
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div class="select-bi m-t-10">
                                        <textarea name="comment" class="form-control detail-info-txt comment" rows="8"></textarea>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="">
                                <div id="daily_log_comments_list" style="width: 100%; height: 200px; overflow: auto;">
                                </div>
                            </div>


                            <div class="form-group modal-footer m-t-0 modal-bttm">
                                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                                <input type="hidden" name="id" value="">
                                <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button class="btn btn-warning submit-comment hide-field" type="submit"> Submit </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Add Log to ServiceUser LogBook -->
<script>
    $('.submit-comment').click(function() {
        var comment_val = $('.comment').val();
        var token = $('input[name=\'_token\']').val();
        var service_user_id = $('input[name=\'service_user_id\']').val();
        var log_book_id = localStorage.getItem('log_book_id');

        // var formdata = $('#su-log-book-form').serialize();

        var error = 0;

        if (comment_val == '') {
            $('textarea[name=\'comment\']').addClass('red_border');
            error = 1;
        } else {
            $('textarea[name=\'comment\']').removeClass('red_border');
        }

        if (error == 1) {
            return false;
        }
        $('.loader').show();
        $('body').addClass('body-overflow');


        $.ajax({
            type: 'post',
            url: "{{ url('/service/logbook/comments') }}",
            data: {
                comment: comment_val,
                "_token": token,
                service_user_id: service_user_id,
                log_book_id: log_book_id
            },
            dataType: 'json',

            success: function(resp) {
                if (isAuthenticated(resp) == false) {
                    return false;
                }
                if (resp == false) {
                    $('span.popup_error_txt').text('Error Occured', 'Try after sometime');
                    $('.popup_error').show();
                    setTimeout(function() {
                        $(".popup_error").fadeOut()
                    }, 5000);
                } else {

                    $('select[name=\'comment\']').val('');
                    $('input[name=\'comment\']').val('');
                    $('textarea[name=\'comment\']').val('');

                    //show success message
                    $('span.popup_success_txt').text('Comment Added Successsfully');
                    var counter = parseInt($(`#_${log_book_id}`).text());
                    counter++;
                    $(`#_${log_book_id}`).text(counter);
                    $('.popup_success').show();
                    setTimeout(function() {
                        $(".popup_success").fadeOut()
                    }, 3000);
                    console.log("comments", resp);

                    // var d = new Date(resp.created_at);
                    // var d_format = ("0" + d.getDate()).slice(-2) + "-" + ("0" + (d.getMonth() + 1)).slice(-2) + "-" +
                    //     d.getFullYear() + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
                    console.log("resp.staff_name", resp.staff_name);
                    // var d_format = moment.utc(resp.created_at).format('DD-MM-YYYY HH:mm');
                    var d_format = moment.utc(resp.created_at).local().format('DD-MM-YYYY HH:mm');

                    $("#daily_log_comments_list").append(
                        `
                        <div class="d-flex justify-content-center py-2" style="margin-top:10px;">
                                <div class="second py-2 px-2 comment-list"> 
                                    <div class="comment-staff-name">${resp.staff_name} </div>
                                    <span class="text1">${resp.comment}</span>
                                    <div class="d-flex justify-content-between py-1 pt-2" style="text-align:right;">
                                        <div><span class="text3">${d_format}</span></div>
                                    </div>
                                </div>
                            </div>
                        `
                    );
                }
                $('.loader').hide();
                $('body').removeClass('body-overflow');
                return false;
            }
        });
        return false;
    });
</script>

@include('frontEnd.serviceUserManagement.elements.handover_to_staff')