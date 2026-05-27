<?php 
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use View;
use Redirect;
use DB;
use App\SupportTicket, App\User, App\ServiceUser, App\SupportTicketMessage;
use Illuminate\Support\Facades\Mail;
use App\Helpers\Helpers;
class ContactUsController extends Controller
{
    public function add_contact_us(Request $r)
    {
        $data = $r->input();
        if(!empty($data['user_id']) && !empty($data['user_type']) && !empty($data['subject']) && !empty($data['message']))
        {
            $service_user_id = $user_id = "0";
            if($data['user_type'] == "Child"){
                $service_user_id = $data['user_id'];
                $home_id = ServiceUser::whereId($service_user_id)->value('home_id');
            } elseif($data['user_type'] == "Staff") {
                $user_id = $data['user_id'];
                $home_id = User::whereId($user_id)->value('home_id');
            } else {
                return json_encode(array(
                    'result' => array(
                        'response' => false ,
                        'message' => "Invalid User Type."
                    )
                ));
            }
            
            $sp                     = new SupportTicket;
            $sp->home_id            = $home_id;
            $sp->user_id            = $user_id;
            $sp->service_user_id    = $service_user_id;
            $sp->origin             = "A";
            $sp->title              = $data['subject'];
            $sp->status             = 0;
            $sp->is_bug             = 0;
            $sp->is_deleted         = 0;
            
            if($sp->save()){
                $stm              = new SupportTicketMessage;
                $stm->ticket_id   = $sp->id;
                $stm->sender_type = 0;
                $stm->message     = $data['message'];
            }
            if($stm->save()){
                return response(array(
                    'success' => true ,
                    'message' => "Contact Query has been sent."
                ));
            }    
        }
        else
        {
            return response(array(
                'success' => false,
                'message'=>'Fill all fields.',   
            ));
        }
    }
    
}

?>