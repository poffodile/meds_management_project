<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use App\Services\Portal\PortalFeedbackService;
use Illuminate\Http\Request;

class FeedbackHubController extends Controller
{
    public function index()
    {
        $homeId = explode(',', auth()->user()->home_id)[0];
        $feedbackService = app(PortalFeedbackService::class);
        $stats = $feedbackService->getAdminStats((int) $homeId);

        return view('frontEnd.roster.feedback.feedback_hub', [
            'stats' => $stats,
            'home_id' => $homeId,
        ]);
    }

    public function list(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:new,acknowledged,in_progress,resolved,closed',
            'type' => 'nullable|in:compliment,complaint,suggestion,concern,general',
        ]);

        $homeId = explode(',', auth()->user()->home_id)[0];
        $feedbackService = app(PortalFeedbackService::class);
        $feedback = $feedbackService->getAllFeedbackForHome(
            (int) $homeId,
            $request->status,
            $request->type
        );

        return response()->json(['status' => true, 'feedback' => $feedback]);
    }

    public function acknowledge(Request $request)
    {
        $request->validate(['feedback_id' => 'required|integer']);

        $homeId = explode(',', auth()->user()->home_id)[0];
        $feedbackService = app(PortalFeedbackService::class);
        $result = $feedbackService->acknowledgeFeedback(
            (int) $request->feedback_id,
            (int) $homeId,
            auth()->user()->id
        );

        return response()->json(['status' => $result]);
    }

    public function respond(Request $request)
    {
        $request->validate([
            'feedback_id' => 'required|integer',
            'response' => 'required|string|max:5000',
        ]);

        $homeId = explode(',', auth()->user()->home_id)[0];
        $feedbackService = app(PortalFeedbackService::class);
        $result = $feedbackService->respondToFeedback(
            (int) $request->feedback_id,
            (int) $homeId,
            auth()->user()->id,
            auth()->user()->name,
            $request->response
        );

        return response()->json(['status' => $result]);
    }

    public function close(Request $request)
    {
        $request->validate(['feedback_id' => 'required|integer']);

        $homeId = explode(',', auth()->user()->home_id)[0];
        $feedbackService = app(PortalFeedbackService::class);
        $result = $feedbackService->closeFeedback(
            (int) $request->feedback_id,
            (int) $homeId
        );

        return response()->json(['status' => $result]);
    }
}
