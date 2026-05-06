<?php

namespace App\Http\Controllers\frontEnd\Roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;
use thiagoalessio\TesseractOCR\TesseractOCR;
use DB,Auth;
use App\Services\Client\ClientCarePlanService;
use App\Models\ClientRiskAssessment;
use App\Models\ClientBehaviorSupportPlan;

class ImportDocumentController extends Controller
{
    protected $clientCarePlanService;
    
    public function __construct(ClientCarePlanService $clientCarePlanService)
    {
        $this->clientCarePlanService = $clientCarePlanService;
    }
    public function index(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $file = $request->file('import_document');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['jpg','jpeg','png','pdf'])) {
            return response()->json([
                'status' => false,
                'msg' => 'Unsupported file type'
            ]);
        }
        $path = $file->store('uploads', 'public');
        $url = asset('storage/'.$path);
        // echo $url;die;
        $token = config('services.ai.bearer_token');
        // echo $token;die;
        if (in_array($ext, ['jpg','jpeg','png'])) {
            $imagePath = storage_path('app/public/'.$path);

            $imageData = base64_encode(file_get_contents($imagePath));
            $mime = mime_content_type($imagePath);

            $base64Image = "data:".$mime.";base64,".$imageData;

            $response = Http::withToken($token)->post('https://api.openai.com/v1/responses', [
                "model" => "gpt-4.1-mini",
                "input" => [
                    [
                        "role" => "user",
                        "content" => [
                            [
                                "type" => "input_text",
                                "text" => "
                                            Extract the following data and return STRICT JSON.

                                            Rules:
                                            - care_plan → always array []
                                            - medication → always array []
                                            - risk_assessment → always array []
                                            - behaviour_support → always array []
                                            - mental_capacity_assessment → always array [],
                                            - peep → always array []

                                            If no data found, return empty array [].

                                            Return ONLY JSON. No explanation.
                                            "
                            ],
                            [
                                "type" => "input_image",
                                "image_url" => $base64Image
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['output'][0]['content'][0]['text'] ?? '';
                $content = str_replace(['```json', '```'], '', $content);
                $content = trim($content);
                $structured = json_decode($content, true);
                return response()->json([
                    'status' => true,
                    'data' => $structured
                ]);
            }
        }
        if ($ext == 'pdf') {
            $parser = new Parser();
            $pdf = $parser->parseFile(storage_path('app/public/'.$path));
            $text = $pdf->getText();

            $response = Http::withToken($token)->post('https://api.openai.com/v1/chat/completions', [
                "model" => "gpt-4o-mini",
                "messages" => [
                    [
                        "role" => "system",
                        "content" => "Extract structured JSON data"
                    ],
                    [
                        "role" => "user",
                        "content" => "
                            Extract:

                            {
                                care_plan: [],
                                medication: [],
                                risk_assessment: [],
                                behaviour_support: [],
                                mental_capacity_assessment: [],
                                peep: []
                            }

                            TEXT:".$text
                    ]
                ]
            ]);
            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                $content = str_replace(['```json', '```'], '', $content);
                $content = trim($content);
                // echo "<pre>";print_r($content);die;
                $structured = json_decode($content, true);
                return response()->json([
                    'status' => true,
                    'data' => $structured
                ]);
            }
        }
        

        return response()->json([
            'status' => false,
            'error' => $response->body()
        ]);
    }
    public function save_document_ai_response(Request $request){
        // echo "<pre>";print_r($request->all());die;
        try{
            $clientId = $request->client_id;
            $structured = $request->ai_document_response;
            // echo "<pre>";print_r($structured);die;
            $this->saveCarePlan($structured['care_plan'] ?? [], $clientId);
            // $this->saveMedication($structured['medication'] ?? [], $clientId);
            $risk = $this->saveRisk($structured['risk_assessment'] ?? [], $clientId);
            $behavior = $this->saveBehaviour($structured['behaviour_support'] ?? [], $clientId);
            // echo "<pre>";print_r($behavior);die;
            return response()->json(['success'=>true,'message'=>"Saved Successfully done",'data'=>array()]);
        }catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>"Something went wrong",'data'=>$e->getMessage()]);
        }
        
    }
    public function cleanData($data){
        if (is_array($data)) {
            $filtered = [];

            foreach ($data as $key => $value) {
                $cleaned = $this->cleanData($value);

                // remove empty values
                if ($cleaned !== null && $cleaned !== '' && $cleaned !== [] && $cleaned !== false) {
                    $filtered[$key] = $cleaned;
                }
            }

            return !empty($filtered) ? $filtered : null;
        }

        return $data;
    }
    public function saveCarePlan($data, $clientId){
        $clean = $this->cleanData($data);

        if (!empty($clean)) {
            DB::beginTransaction();
            try {
                $home_id = explode(',', Auth::user()->home_id)[0];
                    $requestData = array();
                    $requestData['home_id'] = $home_id;
                    $requestData['user_id'] = Auth::user()->id;
                    $requestData['care_setting'] = 'Domiciliary Care';
                    $requestData['plan_type'] = 'Initial Assessment';
                    $requestData['assessment_date'] = date('Y-m-d');
                    $requestData['assessed_by'] = Auth::user()->email;
                    $requestData['client_id'] = $clientId;
                    $requestData['json_data'] = json_encode($clean) ?? null;
                    $requestData['type'] = 1;
                    // echo "<pre>";print_r($requestData);die;
                    $overview = $this->clientCarePlanService->store_overview($requestData);
                    DB::commit();
                
                return response()->json(['success'=>true,'message'=>"Client Care Plan saved successfully",'data'=>$overview]);

            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['success'=>false,'message'=>"Something went wrong",'data'=>$e->getMessage()]);
            }
        }
    }
    public function saveMedication($data, $clientId){
        $clean = $this->cleanData($data);

        if (!empty($clean)) {
            DB::table('medications')->insert([
                'client_id' => $clientId,
                'json_data' => json_encode($clean)
            ]);
        }
    }
    public function saveRisk($data, $clientId){
        $clean = $this->cleanData($data);

        if (!empty($clean)) {
            $home_id = explode(',', Auth::user()->home_id)[0];
                $clientRiskAssessment = new ClientRiskAssessment();
                $clientRiskAssessment->home_id=$home_id;
                $clientRiskAssessment->user_id=Auth::user()->id;
                $clientRiskAssessment->client_id=$clientId;
                $clientRiskAssessment->assessed_date=date('Y-m-d');
                $clientRiskAssessment->json_data=json_encode($clean) ?? null;
                $clientRiskAssessment->type=1;
                $clientRiskAssessment->status=0;
                $clientRiskAssessment->save();
        }
    }
    public function saveBehaviour($data, $clientId)    {
        $clean = $this->cleanData($data);

        if (!empty($clean)) {
            $home_id = explode(',', Auth::user()->home_id)[0];
                $clientBehaviorSupportPlan = new ClientBehaviorSupportPlan();
                $clientBehaviorSupportPlan->home_id=$home_id;
                $clientBehaviorSupportPlan->user_id=Auth::user()->id;
                $clientBehaviorSupportPlan->client_id=$clientId;
                $clientBehaviorSupportPlan->date=date('Y-m-d');
                $clientBehaviorSupportPlan->json_data=json_encode($clean) ?? null;
                $clientBehaviorSupportPlan->type=1;
                $clientBehaviorSupportPlan->status=0;
                $clientBehaviorSupportPlan->save();
        }
    }
}
