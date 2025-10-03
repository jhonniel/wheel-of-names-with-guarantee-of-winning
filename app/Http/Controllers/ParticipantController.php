<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\WheelBackground;
use App\Models\WheelLogo;
use App\Models\WheelSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ParticipantController extends Controller
{
    public function index(): View
    {
        $participants = Participant::with('config')->orderBy('id')->get();
        $backgrounds = WheelBackground::orderBy('created_at', 'desc')->get();
        $logos = WheelLogo::orderBy('created_at', 'desc')->get();
        $spinDuration = WheelSetting::getSpinDuration();
        $displayMode = WheelSetting::getDisplayMode();
        $audioEnabled = WheelSetting::getAudioEnabled();
        return view('participants.index', compact('participants', 'backgrounds', 'logos', 'spinDuration', 'displayMode', 'audioEnabled'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'color' => ['nullable','regex:/^#?[0-9A-Fa-f]{6}$/'],
            'active' => ['sometimes','boolean'],
        ]);
        $data['color'] = $data['color'] ?? sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        if ($data['color'] && $data['color'][0] !== '#') {
            $data['color'] = '#'.$data['color'];
        }
        $data['active'] = (bool) ($data['active'] ?? true);
        Participant::create($data);
        return back()->with('status','Participant added');
    }

    public function update(Request $request, Participant $participant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'color' => ['nullable','regex:/^#?[0-9A-Fa-f]{6}$/'],
            'active' => ['sometimes','boolean'],
        ]);
        if (!empty($data['color'])) {
            if ($data['color'][0] !== '#') {
                $data['color'] = '#'.$data['color'];
            }
        }
        $participant->update([
            'name' => $data['name'],
            'color' => $data['color'] ?? $participant->color,
            'active' => (bool) ($data['active'] ?? $participant->active),
        ]);
        return back()->with('status','Participant updated');
    }

    public function deactivate(Participant $participant): RedirectResponse
    {
        $participant->update(['active' => false]);
        return back()->with('status','Participant deactivated');
    }

    public function destroy(Participant $participant): RedirectResponse
    {
        try {
            $participantName = $participant->name;
            $participant->delete();
            return redirect('/')->with('status', "Participant '{$participantName}' deleted successfully");
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Failed to delete participant: ' . $e->getMessage());
        }
    }

    public function saveConfig(Request $request, Participant $participant): RedirectResponse
    {
        $data = $request->validate([
            'weight' => ['nullable','numeric','min:0'],
            'guarantee_quota' => ['nullable','integer','min:0'],
        ]);
        $participant->config()->updateOrCreate(
            ['participant_id' => $participant->id],
            [
                'weight' => $data['weight'] ?? ($participant->config->weight ?? 1),
                'guarantee_quota' => $data['guarantee_quota'] ?? ($participant->config->guarantee_quota ?? 0),
            ]
        );
        return back()->with('status','Config saved');
    }

    public function batchImport(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'names' => ['required','string'],
        ]);
        $names = array_filter(array_map('trim', explode("\n", $data['names'])));
        $added = 0;
        foreach ($names as $name) {
            if (!empty($name) && !Participant::where('name', $name)->exists()) {
                Participant::create([
                    'name' => $name,
                    'color' => sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
                    'active' => true,
                ]);
                $added++;
            }
        }
        return back()->with('status', "Added {$added} new participants");
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:participants,id'],
        ]);

        $deleted = Participant::whereIn('id', $data['ids'])->delete();
        return back()->with('status', "Deleted {$deleted} participants");
    }

    public function bulkActivate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:participants,id'],
        ]);

        $updated = Participant::whereIn('id', $data['ids'])->update(['active' => true]);
        return back()->with('status', "Activated {$updated} participants");
    }

    public function bulkDeactivate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:participants,id'],
        ]);

        $updated = Participant::whereIn('id', $data['ids'])->update(['active' => false]);
        return back()->with('status', "Deactivated {$updated} participants");
    }

    public function bulkGuarantee(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:participants,id'],
            'guarantee_quota' => ['required', 'integer', 'min:0'],
        ]);

        $updated = 0;
        foreach ($data['ids'] as $id) {
            $participant = Participant::find($id);
            $participant->config()->updateOrCreate(
                ['participant_id' => $participant->id],
                ['guarantee_quota' => $data['guarantee_quota']]
            );
            $updated++;
        }

        $action = $data['guarantee_quota'] > 0 ? 'Set guarantee win' : 'Removed guarantee win';
        return back()->with('status', "{$action} for {$updated} participants");
    }

    public function bulkWeight(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:participants,id'],
            'weight' => ['required', 'numeric', 'min:0'],
        ]);

        $updated = 0;
        foreach ($data['ids'] as $id) {
            $participant = Participant::find($id);
            $participant->config()->updateOrCreate(
                ['participant_id' => $participant->id],
                ['weight' => $data['weight']]
            );
            $updated++;
        }

        $action = $data['weight'] == 1 ? 'Reset weight to normal' : 'Set weight to ' . $data['weight'];
        return back()->with('status', "{$action} for {$updated} participants");
    }

    public function uploadBackground(Request $request): RedirectResponse
    {
        $request->validate([
            'background_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_name' => 'nullable|string|max:255',
        ]);

        try {
            // Store the uploaded file
            $path = $request->file('background_image')->store('wheel-backgrounds', 'public');

            // Create wheel background record
            $background = WheelBackground::create([
                'name' => $request->background_name ?: 'Custom Background',
                'image_path' => $path,
                'is_active' => true,
            ]);

            // Set this background as active (deactivates others)
            $background->setActive();

            return back()->with('status', 'Background uploaded and set as active successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload background: ' . $e->getMessage());
        }
    }

    public function activateBackground(WheelBackground $background): RedirectResponse
    {
        try {
            $background->setActive();
            return back()->with('status', 'Background set as active successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to set background as active: ' . $e->getMessage());
        }
    }

    public function deleteBackground(WheelBackground $background): RedirectResponse
    {
        try {
            // Delete the file from storage
            if (Storage::disk('public')->exists($background->image_path)) {
                Storage::disk('public')->delete($background->image_path);
            }

            // Delete the database record
            $background->delete();

            return back()->with('status', 'Background deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete background: ' . $e->getMessage());
        }
    }

    public function updateSpinDuration(Request $request): RedirectResponse
    {
        $request->validate([
            'spin_duration' => 'required|numeric|min:0.3|max:20',
        ]);

        try {
            WheelSetting::setSpinDuration($request->spin_duration);
            return back()->with('status', 'Spin duration updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update spin duration: ' . $e->getMessage());
        }
    }

    public function updateDisplayMode(Request $request): RedirectResponse
    {
        $request->validate([
            'display_mode' => 'required|in:wheel,rolling,both',
        ]);

        try {
            WheelSetting::setDisplayMode($request->display_mode);
            return back()->with('status', 'Display mode updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update display mode: ' . $e->getMessage());
        }
    }

    public function updateAudioSetting(Request $request)
    {
        $request->validate([
            'audio_enabled' => 'required|boolean',
        ]);

        try {
            WheelSetting::setAudioEnabled($request->audio_enabled);
            return response()->json([
                'success' => true,
                'message' => 'Audio setting updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update audio setting: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'logo_name' => 'nullable|string|max:255',
        ]);

        try {
            // Store the uploaded file
            $path = $request->file('logo_image')->store('wheel-logos', 'public');

            // Create wheel logo record
            $logo = WheelLogo::create([
                'name' => $request->logo_name ?: 'Custom Logo',
                'image_path' => $path,
                'is_active' => true,
            ]);

            // Set this logo as active (deactivates others)
            $logo->setActive();

            return back()->with('status', 'Logo uploaded and set as active successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload logo: ' . $e->getMessage());
        }
    }

    public function activateLogo(WheelLogo $logo): RedirectResponse
    {
        try {
            $logo->setActive();
            return back()->with('status', 'Logo set as active successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to set logo as active: ' . $e->getMessage());
        }
    }

    public function deleteLogo(WheelLogo $logo): RedirectResponse
    {
        try {
            // Delete the file from storage
            if (Storage::disk('public')->exists($logo->image_path)) {
                Storage::disk('public')->delete($logo->image_path);
            }

            // Delete the database record
            $logo->delete();

            return back()->with('status', 'Logo deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete logo: ' . $e->getMessage());
        }
    }
}


