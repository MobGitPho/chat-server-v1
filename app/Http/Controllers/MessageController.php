<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // Envoyer un message à un utilisateur
    public function sendPrivateMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:2048'
        ]);

        $message = new Message();
        $message->sender_id = auth()->id();
        $message->receiver_id = $request->receiver_id;
        $message->content = $request->content;

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('attachments');
            $message->attachment = $path;
        }

        $message->save();

        return response()->json(['message' => 'Message envoyé'], 201);
    }

    // Envoyer un message à un groupe
    public function sendGroupMessage(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'content' => 'required|string'
        ]);

        $user = auth()->user();

        // Vérifier si l'utilisateur appartient au groupe
        if (!$user->groups->contains($request->group_id)) {
            return response()->json(['error' => 'Vous ne pouvez envoyer un message qu’à un groupe auquel vous appartenez'], 403);
        }

        Message::create([
            'sender_id' => auth()->id(),
            'group_id' => $request->group_id,
            'content' => $request->content,
            'status' => 'sent'
        ]);

        return response()->json(['message' => 'Message envoyé au groupe'], 201);
    }

    public function getUserMessages($userId)
    {
        $messages = Message::where('receiver_id', $userId)
            ->orWhere('sender_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($messages);
    }

    public function markAsRead($messageId)
    {
        $message = Message::findOrFail($messageId);
        $message->status = 'read';
        $message->read_at = now();
        $message->save();

        return response()->json(['message' => 'Message marqué comme lu']);
    }
}
