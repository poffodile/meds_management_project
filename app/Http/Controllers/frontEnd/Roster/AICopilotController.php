<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use App\Services\AI\AICopilotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AICopilotController extends Controller
{
    private function homeId(): int
    {
        return (int) explode(',', Auth::user()->home_id)[0];
    }

    public function index()
    {
        return view('frontEnd.roster.ai_copilot.index');
    }

    public function sessions(AICopilotService $service)
    {
        $sessions = $service->listSessions($this->homeId(), Auth::user()->id);
        return response()->json(['status' => true, 'sessions' => $sessions]);
    }

    public function messages(Request $request, AICopilotService $service)
    {
        $request->validate([
            'session_id' => 'required|integer',
        ]);

        $messages = $service->getMessages((int) $request->session_id, $this->homeId());
        return response()->json(['status' => true, 'messages' => $messages]);
    }

    public function send(Request $request, AICopilotService $service)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'nullable|integer',
        ]);

        $result = $service->sendMessage(
            (int) $request->session_id,
            $request->message,
            $this->homeId(),
            Auth::user()->id
        );

        return response()->json($result);
    }

    public function newSession(Request $request, AICopilotService $service)
    {
        $request->validate([
            'context_type' => 'nullable|in:general,client_specific,scheduling,clinical',
            'context_id' => 'nullable|integer',
        ]);

        $session = $service->createSession(
            $this->homeId(),
            Auth::user()->id,
            $request->input('context_type', 'general'),
            $request->context_id ? (int) $request->context_id : null
        );

        return response()->json(['status' => true, 'session' => $session]);
    }

    public function deleteSession(Request $request, AICopilotService $service)
    {
        $request->validate([
            'session_id' => 'required|integer',
        ]);

        $service->deleteSession((int) $request->session_id, $this->homeId());
        return response()->json(['status' => true]);
    }

    public function usage(AICopilotService $service)
    {
        $homeId = $this->homeId();
        $tracker = app(\App\Services\AI\TokenTracker::class);

        return response()->json([
            'status' => true,
            'daily_usage' => $tracker->getDailyUsage($homeId),
            'daily_cap' => $tracker->getDailyCap($homeId),
            'remaining' => $tracker->getRemainingTokens($homeId),
        ]);
    }
}
