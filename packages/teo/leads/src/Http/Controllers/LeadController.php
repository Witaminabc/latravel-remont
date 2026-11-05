<?php

namespace Teo\Leads\Http\Controllers;

use App\Http\Controllers\Controller;
use Teo\Leads\Models\Lead;
use Teo\Leads\Mail\LeadNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:50',
            'email'   => 'nullable|email|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        try {
            $lead = Lead::create($validated);

            try {
                $to = config('leads.notify_email', config('mail.from.address'));
                Mail::to($to)->send(new LeadNotification($lead));
            } catch (\Exception $e) {
                Log::warning('Lead email notification failed: ' . $e->getMessage());
            }

            Log::info('Lead created: id=' . $lead->id . ' name=' . $lead->name);

            return response()->json([
                'success' => true,
                'message' => 'Заявка отправлена. Мы с вами свяжемся.',
                'data' => $lead,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Lead store error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Не удалось отправить заявку. Попробуйте еще раз.',
            ], 500);
        }
    }

    public function index(Request $request)
    {
        if (Auth::user()?->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Lead::query();
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function updateStatus(Request $request, $id)
    {
        if (Auth::user()?->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:new,processed',
        ]);

        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['error' => 'Заявка не найдена'], 404);
        }

        $lead->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'data' => $lead,
        ]);
    }
}
