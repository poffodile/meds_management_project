<?php
namespace App\Http\Controllers\frontEnd\ServiceUserManagement\LogBookComments;
use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use App\LogBook, App\ServiceUser, App\ServiceUserLogBook, App\User, App\HandoverLogBook, App\ServiceUserReport, App\ServiceUserCareTeam, App\CareTeam;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Notification;
use App\LogBookComment;
use Exception;
use DB, Auth;

class LogBookCommentsController extends ServiceUserManagementController
{
    private function getdata($per_page, $service_user_id = null, $log_book_id = null) {
        $query =  ServiceUserLogBook::select(
            'su_log_book.id as su_log_book_id',
            'log_book.id as log_book_id',
            'log_book_comments.id',
            'log_book_comments.comment',
            'log_book_comments.created_at',
            'log_book_comments.updated_at',
            'u.name as staff_name'
        )
        ->join('log_book','log_book.id','su_log_book.log_book_id')
        ->join('log_book_comments as log_book_comments','log_book_comments.log_book_id','log_book.id')
        ->leftJoin('user as u','u.id','log_book.user_id');

        if($service_user_id) {
            $query->where('su_log_book.service_user_id', $service_user_id);
        }
        if($log_book_id) {
            $query->where('log_book.id', $log_book_id);
        }

        // dd($query);
        return $query->where('log_book.is_deleted','0')
        ->orderBy('log_book_comments.id','asc')
        ->paginate($per_page);
    }
    public function index(Request $request) {
        $per_page = $request->get('per_page') ?: 1000;
        $log_book_id = $request->get('log_book_id');
        $service_user_id = $request->get('service_user_id');
        $su_log_book_records = $this->getData($per_page, $service_user_id, $log_book_id);
        // dd($su_log_book_records);
        return response()->json($su_log_book_records);
    }
    public function store(Request $request) {
        $rules = [
            'comment'           => 'required',
            // 'service_user_id'   => 'required',
            'log_book_id'       => 'required',
        ];
        $this->validate($request, $rules);
        $log_book_id = $request->get('log_book_id');
        $service_user_id = $request->get('service_user_id');

        $serviceUserLogBook = ServiceUserLogBook::where([
            'log_book_id' => $log_book_id,
            // 'service_user_id' => $service_user_id
        ])->first();
        if (!$serviceUserLogBook) {
            throw new Exception("Not found");
        }
        $logbBook = LogBook::find($log_book_id);
        if (!$logbBook) {
            throw new Exception("Not found");
        }
        $logBookComment = new LogBookComment();
        $logBookComment->comment = $request->get('comment');
        $logBookComment->log_book_id = $log_book_id;
        $logBookComment->user_id = Auth::user()->id;
        $return = $logBookComment->save();
        $logBookComment->staff_name = User::where('id', Auth::user()->id)->value('name');
        return response()->json($logBookComment);
    }
}