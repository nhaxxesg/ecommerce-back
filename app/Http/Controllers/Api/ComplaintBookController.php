<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Complaint;
use Illuminate\Support\Str;

class ComplaintBookController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:reclamo,queja',
            'product_description' => 'required|string|min:10',
            'complaint_detail' => 'required|string|min:20',
        ]);

        $user = $request->user();

        $complaint = new Complaint([
            'code' => 'REC-' . strtoupper(Str::random(8)),
            'type' => $request->type,
            'consumer_name' => $user->name,
            'consumer_email' => $user->email,
            'consumer_phone' => $request->consumer_phone ?? '',
            'product_description' => $request->product_description,
            'complaint_detail' => $request->complaint_detail,
            'status' => 'pending',
            'response_deadline' => now()->addDays(30),
        ]);

        $complaint->save();

        return response()->json([
            'message' => 'Reclamación registrada exitosamente',
            'complaint' => $complaint
        ], 201);
    }
} 