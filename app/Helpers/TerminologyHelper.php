<?php

use App\Models\CompanyHomeSetting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('getTerm')) {
    /**
     * Get the dynamically configured term for the system.
     *
     * @param string $key The default term (e.g., 'Staff', 'Service User')
     * @param int|null $companyId The company ID (optional, will try to infer if not provided)
     * @return string
     */
    function getTerm($key, $companyId = null)
    {
        $termKey = strtolower(str_replace(' ', '_', $key)) . '_term';

        if (!$companyId && \Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            
            // If the authenticated user is a frontend user (has home_id)
            if (isset($user->home_id) && !empty($user->home_id)) {
                $home = \Illuminate\Support\Facades\DB::table('home')->where('id', $user->home_id)->first();
                if ($home && isset($home->admin_id)) {
                    $companyId = $home->admin_id;
                }
            } 
            
            // If the user is an admin or we couldn't find the home
            if (!$companyId) {
                if (isset($user->company_id) && $user->company_id) {
                    $companyId = $user->company_id;
                } elseif (isset($user->admn_id) && $user->admn_id) {
                    $companyId = $user->admn_id;
                } else {
                    $companyId = $user->id;
                }
            }
        }
        
        \Illuminate\Support\Facades\Log::info("TerminologyHelper getTerm:", ['key' => $key, 'companyId' => $companyId, 'userId' => \Illuminate\Support\Facades\Auth::id()]);

        $cacheKey = 'terminology_' . ($companyId ?? 'default') . '_' . $termKey;

        // Remember forever, but we need to clear it on save. Or just cache for a few minutes.
        // Caching for 10 seconds is safer if we don't clear the cache manually everywhere.
        return Cache::remember($cacheKey, 3600, function () use ($termKey, $key, $companyId) {
            $query = CompanyHomeSetting::query();
            
            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            $settings = $query->first();

            if ($settings && isset($settings->{$termKey}) && !empty($settings->{$termKey})) {
                return $settings->{$termKey};
            }

            return $key;
        });
    }
}
