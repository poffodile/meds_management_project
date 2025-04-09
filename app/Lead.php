<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\LeadNote;
use App\Models\Quote;
use App\Services\Quotes\QuoteService;

class Lead extends Model
{

    // protected $quoteService;
    use HasFactory;

    // public function __construct(QuoteService $quoteService)
    // {
    //     $this->quoteService = $quoteService;
    // }

    protected $table = 'leads';

    protected $fillable = [
        'home_id',
        'customer_id',
        'lead_ref',
        'assign_to',
        'source',
        'status',
        'prefer_date',
        'prefer_time',
        'converted_to',
        'notity',
        'notifiy_user_id',
        'notifocation',
        'sms',
        'email',
    ];


    public static function getAllLeadCount($home_id){
        return Lead::whereNotIn('assign_to', [0])->whereNotIn('leads.status', ['6','7'])->where('home_id', $home_id)->whereNull('deleted_at')->whereNull('converted_to')->count();
    }

    public static function getUnassignedCount($home_id){
        return Lead::where('assign_to', 0)->where('home_id', $home_id)->whereNull('deleted_at')->whereNull('converted_to')->count();
    }
    
    public static function getRejectedCount($home_id){
        return Lead::where('status', '6')->where('home_id', $home_id)->whereNull('deleted_at')->whereNull('converted_to')->count();
    }

    public function notes(){
        return $this->hasMany(LeadNote::class);
    }

    public static function getConvertedCustomersCount($home_id){
        return Lead::where('home_id', $home_id)->whereIn('converted_to', ['quote','customer'])->whereNull('deleted_at')->count();
    }

    public static function getLeadByUser($user_id, $home_id){
        return Lead::where('assign_to', $user_id)->where('home_id', $home_id)->whereNotIn('status', [7])->whereNull('deleted_at')->whereNull('converted_to')->count();
    } 

    public static function getAuthorizationCount($home_id){
        return Lead::where('status', 7)->where('home_id', $home_id)->count();
    }

    public static function leadForAdminAuthorization($id){
        return Lead::where('id', $id)->update(['authorization_status' => 1, 'status' => 7]);
    }

    public static function LeadAuthorizedAdmin($id){
        return Lead::where('id', $id)->update(['authorization_status' => 2]);
    } 

    public static function getActionedLead($home_id){
        return DB::table('leads')
            ->where('leads.home_id', $home_id)
            ->whereNull('leads.converted_to')
            ->where(function ($query) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('lead_tasks')
                        ->whereColumn('lead_tasks.lead_ref', 'leads.lead_ref');
                })
                ->orWhereExists(function ($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('lead_notes')
                        ->whereColumn('lead_notes.lead_id', 'leads.id');
                });
            })
            ->distinct()
            ->count();
    }


    public static function saveLeadConvertQuote($data, $home_id){
        // dd($data);
        $result = self::where('id', $data['lead_id'])
            ->update([ 
                "converted_to" => 'quote',
                'notify' => $data['notify'], 
                'notifiy_user_id' => $data['notifiy_user_id'], 
                'notifocation' => $data['notifocation'] ?? null, 
                'sms' => $data['sms'] ?? null,
                'email' => $data['email'] ?? null
            ]);

        if($result == true){
            $lead = Lead::where('id', $data['lead_id'])->first();
        }

        // dd($lead);
        $service = new  QuoteService;
        $quote = $service->generateQuoteRef();
            return Quote::create([
                'home_id' => $home_id,
                'user_id' => $lead->assign_to,
                'quote_ref' => $quote,
                'customer_id' => $lead->customer_id,
                'quota_date' => date('Y-m-d'),
                'status' => "Draft"
            ]);
    }
}
