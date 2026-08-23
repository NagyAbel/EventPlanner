<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use Illuminate\Http\JsonResponse;

class EventTypeController extends Controller
{
    /**
     * Display a listing of all event types.
     */
    public function index(): JsonResponse
    {
        $types = EventType::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'data' => $types,
        ]);
    }
}