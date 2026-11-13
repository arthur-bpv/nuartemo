<?php

namespace App\Http\Controllers;

use App\Models\Key;
use Illuminate\Http\JsonResponse;

class KeyController extends Controller
{
    public function index(): JsonResponse
    {
        $keys = Key::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        return response()->json(['data' => $keys]);
    }
}