<?php

namespace App\Http\Controllers\frontend\roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ClientController extends Controller
{
    public function index()
    {
        return view('frontEnd.roster.client.client');
    }

    public function initiate()
    {
        $paygateId = '10011072130';
        $paygateKey = 'test'; // from PayGate dashboard

        $data = [
            'PAYGATE_ID'       => $paygateId,
            'REFERENCE'        => 'pgtest_1234567',
            'AMOUNT'           => '3299',
            'CURRENCY'         => 'ZAR',
            'RETURN_URL'       => 'https://www.geeksforgeeks.org/',
            'TRANSACTION_DATE' => now()->format('Y-m-d H:i:s'),
            'LOCALE'           => 'en-za',
            'COUNTRY'          => 'ZAF',
            'EMAIL'            => 'customer@paygate.co.za',
            'NOTIFY_URL'       => 'https://www.w3schools.com/',
        ];

        /**
         * Step 1: Create checksum string (ORDER IS IMPORTANT)
         */
        $checksumString = '';
        foreach ($data as $value) {
            $checksumString .= $value;
        }
        $checksumString .= $paygateKey;

        /**
         * Step 2: Generate MD5 checksum
         */
        $checksum = md5($checksumString);

        $data['CHECKSUM'] = $checksum;

        /**
         * Step 3: Call PayGate API
         */
        $response = Http::asForm()
            ->post(
                'https://secure.paygate.co.za/payweb3/initiate.trans/payweb3/initiate.trans',
                $data
            );

        /**
         * Step 4: Handle response
         */
        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'error'   => $response->body(),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'response' => $response->body(),
        ]);
    }
}
