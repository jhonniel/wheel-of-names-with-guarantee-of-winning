<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\WinnerConfig;
use App\Models\WheelBackground;
use App\Services\SpinService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WheelController extends Controller
{
    public function index()
    {
        $participants = Participant::where('active', true)
            ->with('config')
            ->get(['id','name','color','active']);

        return response()->json([
            'segments' => $participants->map(fn($p) => [
                'id' => $p->id,
                'label' => $p->name,
                'color' => $p->color,
                'weight' => optional($p->config)->weight ?? 1,
                'guarantee_quota' => optional($p->config)->guarantee_quota ?? 0,
            ]),
        ]);
    }

    public function spin(SpinService $service, Request $request)
    {
        $result = $service->spinOnce();
        return response()->json($result);
    }


    public function upsertConfig(Request $request)
    {
        $data = $request->validate([
            'participant_id' => ['required','exists:participants,id'],
            'weight' => ['nullable','numeric','min:0'],
            'guarantee_quota' => ['nullable','integer','min:0'],
            'valid_from' => ['nullable','date'],
            'valid_to' => ['nullable','date','after_or_equal:valid_from'],
        ]);

        $config = WinnerConfig::updateOrCreate(
            ['participant_id' => $data['participant_id']],
            Arr::only($data, ['weight','guarantee_quota','valid_from','valid_to'])
        );

        return response()->json($config);
    }
}


