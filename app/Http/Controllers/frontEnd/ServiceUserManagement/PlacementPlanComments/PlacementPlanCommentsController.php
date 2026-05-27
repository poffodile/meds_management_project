<?php

namespace App\Http\Controllers\frontend\ServiceUserManagement\PlacementPlanComments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceUserManagement\PlacementPlanComment;
use App\User;
use Auth;

class PlacementPlanCommentsController extends Controller
{
    public function add_comments(Request $request)
    {
        $service_user_id = $request->input('service_user_id');
        $comment = $request->input('comment');

        $commentText = trim($request->input('comment'));

        if (empty($commentText)) {
            return response()->json(['status' => 'error', 'message' => 'Empty comment']);
        }

        $comment = new PlacementPlanComment();
        $comment->su_placement_plan_id = $request->plan_id;
        $comment->user_id = Auth::user()->id;
        $comment->comments = $commentText;

        if($comment->save()){

            return response()->json([
                'status' => 'success',
                'message' => 'Comment added successfully.',
            ]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error saving comment.']);
        }
    }

    public function get_comments($plan_id){

        $commentData = PlacementPlanComment::select('user.name','placement_plan_comments.created_at','placement_plan_comments.comments', 'placement_plan_comments.id')
                        ->join('user', 'user.id', 'placement_plan_comments.user_id')
                        ->where('su_placement_plan_id', $plan_id)
                        ->where('placement_plan_comments.is_deleted', 0)
                        ->orderBy('placement_plan_comments.created_at', 'desc')
                        ->get();

        if(empty($commentData)){
            return response()->json(['status' => 'success', 'message' => 'No Record Found.']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Comment retrieved successfully.', 'comments' => $commentData]);
        }
    }
}
