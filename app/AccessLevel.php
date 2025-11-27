<?php 
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class AccessLevel extends Model
{
	protected $table = 'access_level';


	public static function getAccessLevelList()
	{
		$access_levels = AccessLevel::where('is_deleted',0)->where('home_id', Session::get('scitsAdminSession')->home_id)->get();
		// dd($access_levels);
		$access_level_list = array();
		if(!empty($access_levels))
		{
			foreach($access_levels as $level)
			{
				$access_level_list[$level->id] = $level->name;
			}
		}
		return $access_level_list;
	}

}