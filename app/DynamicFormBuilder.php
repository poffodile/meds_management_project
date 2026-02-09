<?php

namespace App;
use DB, Session, Auth;
use Illuminate\Database\Eloquent\Model;


class DynamicFormBuilder extends Model
{
    protected $table = 'dynamic_form_builder';  

    public static function getFormBuilderId($form_default_id = null){

    	$home_id = Session::get('scitsAdminSession')->home_id;
    	
    	$form_builder_id = DynamicFormBuilder::where('form_default_id',$form_default_id)
		    				->where('home_id',$home_id)
		    				->value('id');

		return $form_builder_id;
    }

    public static function getFormList(){

        $home_id = Auth::user()->home_id; 
        $dynamic_forms = DynamicFormBuilder::select('id','title','location_ids', 'detail')
                                ->where('home_id',$home_id)
                                ->get()
                                ->toArray();
        return $dynamic_forms;
    }

    public static function getReminderDayFormList($home_id) {

        $dynamic_forms = DynamicFormBuilder::select('id','title','location_ids','reminder_day')
                            ->where('home_id',$home_id)
                            ->get()
                            ->toArray();

        return $dynamic_forms;

    }

    /*public function form_builder(){
    	$home_id = Session::get('scitsAdminSession')->home_id;
    	return $this->hasOne('App\FormBuilder','form_default_id','id')->where('home_id',$home_id)->select(array('id'));
    }*/                

}