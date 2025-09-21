<?php

namespace App\Http\Controllers;

use App\Models\Winner;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WinnerController extends Controller
{
    public function index()
    {
        $winners = Winner::with(['user'])
            ->orderBy('won_at', 'desc')
            ->paginate(20);

        return view('winners.index', compact('winners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'participant_id' => 'required|exists:participants,id',
            'winner_name' => 'required|string|max:255',
            'winner_color' => 'nullable|string|max:7',
            'spin_angle' => 'nullable|numeric',
            'pool_snapshot' => 'nullable|array',
        ]);

        // Record the winner
        Winner::create([
            'participant_id' => $request->participant_id,
            'user_id' => Auth::id(),
            'winner_name' => $request->winner_name,
            'winner_color' => $request->winner_color,
            'spin_angle' => $request->spin_angle,
            'pool_snapshot' => $request->pool_snapshot,
            'won_at' => now(),
        ]);

        // Completely remove the participant from the participants list
        $participant = Participant::find($request->participant_id);
        if ($participant) {
            $participant->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Winner recorded successfully and removed from participants list!'
        ]);
    }

    public function destroy(Winner $winner)
    {
        $winner->delete();

        return redirect()->route('winners.index')
            ->with('status', 'Winner record deleted successfully.');
    }

}
