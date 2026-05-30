<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Services\SupportSocketBroadcast;
use App\Support\SupportSocketToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        SupportConversation::query()
            ->has('messages')
            ->each(fn (SupportConversation $c) => $c->syncTicketStatusFromLastMessage());

        $statusFilter = $request->query('status', 'all');
        if (! in_array($statusFilter, ['all', 'open', 'closed'], true)) {
            $statusFilter = 'all';
        }

        $query = SupportConversation::query()
            ->with(['vendor', 'user'])
            ->withCount('messages')
            ->with(['messages' => fn ($q) => $q->latest()->limit(1)])
            ->latest('updated_at');

        if ($statusFilter === 'open') {
            $query->where('status', SupportConversation::STATUS_OPEN);
        } elseif ($statusFilter === 'closed') {
            $query->where('status', SupportConversation::STATUS_CLOSED);
        }

        $conversations = $query->paginate(20)->withQueryString();

        $openTicketsCount = SupportConversation::query()
            ->where('status', SupportConversation::STATUS_OPEN)
            ->count();

        $closedTicketsCount = SupportConversation::query()
            ->where('status', SupportConversation::STATUS_CLOSED)
            ->count();

        return view('admin.support.index', compact(
            'conversations',
            'openTicketsCount',
            'closedTicketsCount',
            'statusFilter',
        ));
    }

    public function show(SupportConversation $conversation): View
    {
        $conversation->load(['vendor', 'user']);
        $conversation->syncTicketStatusFromLastMessage();
        $conversation->refresh();

        $messages = $conversation->orderedMessages()
            ->get()
            ->map(fn (SupportMessage $m) => $m->toBroadcastArray());

        return view('admin.support.show', [
            'conversation' => $conversation,
            'ticketStatus' => $conversation->resolveTicketStatus(),
            'messages' => $messages,
            'socketToken' => SupportSocketToken::generate($conversation->id, (int) Auth::guard('admin')->id(), 'admin'),
            'socketUrl' => config('services.socket.url'),
        ]);
    }

    public function storeMessage(Request $request, SupportConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => SupportMessage::SENDER_ADMIN,
            'sender_id' => Auth::guard('admin')->id(),
            'body' => trim($validated['body']),
        ]);

        $conversation->touch();

        SupportSocketBroadcast::message($message);

        $conversation->refresh();

        return response()->json([
            'success' => true,
            'message' => $message->toBroadcastArray(),
            'ticket_status' => $conversation->resolveTicketStatus(),
        ]);
    }
}
