<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        return $this->ok(SupportTicket::where('user_id', $request->user()->id)->with('messages')->latest()->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:191'], 'category' => ['required', 'in:general,account,billing,technical,safety'],
            'message' => ['required', 'string', 'max:10000'],
        ]);
        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id, 'email' => $request->user()->email,
            'subject' => $data['subject'], 'category' => $data['category'], 'status' => 'open',
        ]);
        $ticket->messages()->create(['sender_id' => $request->user()->id, 'message' => $data['message']]);

        return $this->ok($ticket->load('messages'), 'Support ticket created', 201);
    }

    public function reply(Request $request, string $ticket): JsonResponse
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:10000']]);
        $model = SupportTicket::where('user_id', $request->user()->id)->findOrFail($ticket);
        if ($model->status === 'closed') {
            return $this->fail('This ticket is closed.', 409);
        }
        $message = SupportMessage::create(['support_ticket_id' => $model->id, 'sender_id' => $request->user()->id, 'message' => $data['message']]);

        return $this->ok($message, 'Reply added', 201);
    }
}
