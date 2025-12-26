<?php 
namespace App;
use Auth;
use Illuminate\Database\Eloquent\Model;

class UserQualification extends Model
{
	protected $table = 'user_qualification';

	protected $fillable = [
        'image','name','user_id','course_id'
    ];
}