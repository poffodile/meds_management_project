<?php
 
 namespace App\Models\staffManagement;
 
 use Illuminate\Database\Eloquent\Factories\HasFactory;
 use Illuminate\Database\Eloquent\Model;
 use App\User;
 
 class sosAlert extends Model
 {
     use HasFactory;
 
     protected $table = 'sos_alerts';
 
     protected $fillable = [
         'staff_id',
         'home_id',
         'location',
         'message',
         'status',
         'acknowledged_by',
         'acknowledged_at',
         'resolved_by',
         'resolved_at',
     ];
 
     protected $casts = [
         'status' => 'integer',
         'acknowledged_at' => 'datetime',
         'resolved_at' => 'datetime',
     ];
 
     public function scopeActive($query)
     {
         return $query->where('is_deleted', 0);
     }
 
     public function scopeForHome($query, $homeId)
     {
         return $query->where('home_id', $homeId);
     }
 
     public function staff()
     {
         return $this->belongsTo(User::class, 'staff_id');
     }
 
     public function acknowledgedByUser()
     {
         return $this->belongsTo(User::class, 'acknowledged_by');
     }
 
     public function resolvedByUser()
     {
         return $this->belongsTo(User::class, 'resolved_by');
     }
 }
