<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BmpRmpController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'message' => 'Placeholder index']);
    }

    public function add()
    {
        return response()->json(['success' => true, 'message' => 'Placeholder add']);
    }

    public function edit()
    {
        return response()->json(['success' => true, 'message' => 'Placeholder edit']);
    }

    public function delete()
    {
        return response()->json(['success' => true, 'message' => 'Placeholder delete']);
    }

    public function view_detail()
    {
        return response()->json(['success' => true, 'message' => 'Placeholder view_detail']);
    }
}
