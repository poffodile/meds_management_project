<?php

namespace App\Http\Controllers\frontEnd\SystemManagement;
use App\Http\Controllers\frontEnd\SystemManagementController;
use Illuminate\Http\Request;
use App\SupportTicket, App\SupportTicketMessage;
use DB, Auth;

class SupportTicketController extends SystemManagementController
{	  
    public function index(){

        $home_id = Auth::user()->home_id; 
        $tickets = SupportTicket::where('home_id',$home_id)->orderBy('id','desc')->where('is_deleted','0')->get();
            foreach($tickets as $key => $value) {
            
            if($value->status == 1){ //if ticket is closed
                $ticket_label_class = "label-success";
                $ticket_status_txt  = 'Open';
                $ticket_status_icon = 'fa fa-comment-o';  
                $span_class         = '';                      
            }
            else{
                $ticket_label_class = "label-danger";
                $ticket_status_txt  = 'Close';
                $ticket_status_icon  = 'fa fa-times';                
                $span_class         = 'color-red';                      
            }  
        
        echo   '<div class="col-md-10 col-sm-10 col-xs-12 p-0 cog-panel">
                    <div class="input-group popovr">
                        <a href="#" class="label '.$ticket_label_class.'">'.$value->title.'</a>
                        <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                <i class="fa fa-cog"></i>
                                <div class="pop-notifbox ">
                                    <ul class="pop-notification" type="none">
                                        <li> <a href="#" data-toggle="modal" data-dismiss="modal" data-target="#chatmodal" class="view-chat" view_mseg_id='.$value->id.'> <span> <i class="fa fa-eye"></i> </span> View </a> </li>
                                        <li> <a href="#" class="tkt-status" view_mseg_id='.$value->id.'> <span class="'.$span_class.'"> <i class="'.$ticket_status_icon.'"></i> </span> '.$ticket_status_txt.' </a> </li>
                                    </ul>
                                </div>
                        </span>
                    </div>
                </div>';
            }
    }

    public function add(Request $request){

    	if($request->isMethod('post'))
        {
    		$data = $request->all();
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            $ticket                            = new SupportTicket;
            $ticket->user_id                   = $data['user_id'];
            $ticket->title                     = $data['title'];
            $ticket->status                    = 0;
            $ticket->home_id                   = $home_id;
            $ticket->save();
        
            $supportTicketMessage              = new SupportTicketMessage;
            $supportTicketMessage->ticket_id   = $ticket->id;
            $supportTicketMessage->message     = $data['message'];
            $supportTicketMessage->sender_type = 0;

            if($supportTicketMessage->save()){
            echo '1'; 
            }
            else{
            echo '0';
            }
            die;
    	}
    }

    public function view_ticket($ticket_id = null){

        $sup_ticket = SupportTicket::where('id', $ticket_id)
                                ->where('home_id', Auth::user()->home_id)
                                ->where('user_id', Auth::user()->id)
                                //->where('is_deleted', 0)
                                ->first();

        if(!empty($sup_ticket)){

            $tickets_chat = SupportTicketMessage::where('ticket_id', $ticket_id)
                                ->get()
                                ->toArray();
            
            echo '<div class="chat-conversation">
                     <ul class="conversation-list">';

            foreach ($tickets_chat as $key => $value) {

                if($value['sender_type'] == 1){
                    $admin_class = 'even';
                }
                else{
                     $admin_class = 'odd';
                }

            echo '<li class="clearfix '.$admin_class.'">
                    <div class="conversation-text">
                        <div class="ctext-wrap">
                          <p>'.$value['message'].'
                            <i>'.date('g:i a d-M-Y',strtotime($value['created_at'])).'</i>
                          </p>    
                        </div>  
                    </div>
                </li>';
            }
            echo '</ul></div>';
        }
    }

    public function add_ticket_mesg(Request $request){
        
        if($request->isMethod('post')){
            $data = $request->all();
            
            $support_ticket = SupportTicket::where('id',$data['ticket_id'])->where('home_id',Auth::user()->home_id)->first();

            if(!empty($support_ticket)){
                $ticket_mseg            = new SupportTicketMessage;
                $ticket_mseg->ticket_id = $data['ticket_id'];
                $ticket_mseg->message   = $data['chat_input'];

                if($ticket_mseg->save()){
                    $resp = $this->view_ticket($ticket_mseg->ticket_id);
                    echo $resp; die;
                }
                else{
                    echo '0';
                }
            } else{
                echo '0';
            }    
            die;
        }
    }

    public function ticket_status($support_id = null){

        $ticket_status = SupportTicket::where('id', $support_id)->where('home_id',Auth::user()->home_id)->first();
        
        if(!empty($ticket_status)){
         
            if($ticket_status->status == '0'){
                $new_status = 1;
            }
            else{
                $new_status = 0;
            }

            $ticket_status->status = $new_status;
            if($ticket_status->save()){
                $result['response']     = true;
                $result['new_status']   = $new_status;
            }
            else{
                $result['response']     = false;
                $result['new_status']   = $new_status;
            }
        }else{
                $result['response']     = false;
                $result['new_status']   = $new_status;
        }
        return $result;
    }
}