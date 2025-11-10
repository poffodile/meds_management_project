<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use DB;
use App\ServiceUserHealthRecord, App\Notification, App\ServiceUser, App\ServiceUserMood, App\Mood;
use Illuminate\Support\Facades\Auth;

class MoodController extends ServiceUserManagementController
{
    public function index($service_user_id = null)
    {
        $home_id = Auth::user()->home_id;
        $su_moods = DB::table('su_mood')->select('su_mood.*', 'mood.id as mood_id', 'mood.name', 'mood.image')
            ->where('su_mood.is_deleted', '0')
            ->where('mood.home_id', $home_id)
            ->where('su_mood.service_user_id', $service_user_id)
            ->join('mood', 'mood.id', 'su_mood.mood_id')
            ->orderBy('su_mood.id', 'desc')
            ->paginate(10);

        foreach ($su_moods as $key => $value) {
            $image = url(MoodImgPath) . '/dummy.jpg';
            if (!empty($value->image)) {

                $image = url(MoodImgPath . '/' . $value->image);
            }

            $check = (!empty($value->suggestions)) ? '<i class="fa fa-check"></i>' : '';

            echo '  <div class="form-group col-md-12 col-sm-12 col-xs-12 cog-panel mood_record_row">
                        <div class="input-group popovr">
                            <div class="col-md-1">
                                <img src=' . $image . ' alt="No mood" height="35px" width="auto">
                            </div>
                        
                            <div class="col-md-10">
                                <input type="text" name="" class="form-control cus-control"  disabled  value="Feeling - ' . $value->name . '" maxlength="255"/>';

            if (!empty($value->description)) {
                echo '<div class="input-plus color-green"> <i class="fa fa-plus"></i> </div>';
            }
            echo            '</div>
                        </div>                        

                        
                        <div class="input-plusbox form-group col-md-11 col-xs-12 p-0 p-t-15 detail">
                            <label class="cus-label color-themecolor"> Details: </label>
                            <div class="cus-input" style="float:right">
                                <div class="input-group">
                                  <textarea rows="5" name="detail" disabled="" class="form-control tick_text txtarea " value="" maxlength="1000">' . $value->description . '</textarea>
                                  <span class="input-group-addon cus-inpt-grp-addon color-grey settings"></span>
                                </div>
                            </div>
                        </div>

                        <div class="input-plusbox form-group col-md-11 col-xs-12 p-0 p-t-15 detail">
                            <label class="cus-label color-themecolor"> Suggestions: </label>
                            <div class="cus-input" style="float:right">
                                <div class="input-group">
                                    <textarea rows="5" name="mood_suggestion" class="form-control tick_text txtarea" value="" maxlength="1000">' . $value->suggestions . '</textarea>
                                    <div class="input-group-addon cus-inpt-grp-addon sbt_tick_area"">
                                        <div class="tick_show sbt_btn_tick_div submit-suggestion" su_mood_id=' . $value->id . '>' . $check . '</div>
                                    </div>
                                  <!-- <span class="input-group-addon cus-inpt-grp-addon color-grey settings submit-suggestion sbt_tick" su_mood_id=' . $value->id . '><i class="fa fa-check"></i></span> -->
                                </div>
                            </div>
                        </div>
                    </div>';
        }
        if ($su_moods->links() != '') {
            echo '<div class="m-l-15">';
            echo $su_moods->links();
            echo '</div>';
        }
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            // echo '<pre>'; print_r($data); 
            $su_mood                 = ServiceUserMood::find($data['su_mood_id']);
            $su_mood->suggestions    = $data['suggestion'];
            $su_mood->suggestion_provider_id = Auth::user()->id;
            //$su_mood->home_id        = Auth::user()->home_id;

            if ($su_mood->save()) {

                //saving notification start
                /*$notification                  = new Notification;
                $notification->service_user_id = $data['service_user_id'];
                $notification->event_id        = $health_record->id;
                $notification->event_type      = 'SU_HR';
                $notification->event_action    = 'ADD';    
                $notification->home_id         = Auth::user()->home_id;
                $notification->user_id         = Auth::user()->id;                  
                $notification->save();*/
                //saving notification end
                $result = $this->index($data['service_user_id']);
                echo $result;
            } else {
                echo '0';
            }
        }
    }

    public function mood_view($service_user_id)
    {
        $home_id = Auth::user()->home_id;
        $moods = Mood::where('home_id', $home_id)->where('is_deleted', '0')->get();
        $su_moods = DB::table('su_mood')->select('su_mood.*', 'mood.id as mood_id', 'mood.name', 'mood.image')
            ->where('su_mood.is_deleted', '0')
            ->where('mood.home_id', $home_id)
            ->where('su_mood.service_user_id', $service_user_id)
            ->join('mood', 'mood.id', 'su_mood.mood_id')
            ->orderBy('su_mood.id', 'desc')
            ->get();

        return view('frontEnd.serviceUserManagement.elements.mood', compact('moods', 'service_user_id', 'su_moods'));
    }

    public function saveMood(Request $request)
    {
        \Log::info('MoodController@saveMood called', $request->all());

        $request->validate([
            'mood_id' => 'required|exists:mood,id',
            'service_user_id' => 'required',
        ]);

        // If ID comes → Edit, else Add
        $moodId = $request->edit_mood_id ?? null;

        DB::table('su_mood')->updateOrInsert(
            ['id' => $moodId],  // Match ID (if null → insert)
            [
                'service_user_id' => $request->service_user_id,
                'mood_id' => $request->mood_id,
                'description' => $request->description ?? "Test",
                'suggestions' => $request->suggestions ?? null,
                'suggestion_provider_id' => Auth::user()->id,
                'home_id' => Auth::user()->home_id,
                'is_deleted' => 0,
                'updated_at' => now(),
                'created_at' => $moodId ? null : now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => $moodId ? 'Mood updated successfully.' : 'Mood added successfully.'
        ]);
    }

    public function delete($id)
    {
        \Log::info('MoodController@deleteMood called: ID = ' . $id);

        DB::table('su_mood')
            ->where('id', $id)
            ->update([
                'is_deleted' => 1,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Mood deleted successfully.']);
    }
}
