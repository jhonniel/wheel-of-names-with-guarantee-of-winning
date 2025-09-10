<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Spin;

class SpinService
{
    public function spinOnce(): array
    {
        $now = now();
        $participants = Participant::where('active', true)
            ->with(['config' => function ($q) use ($now) {
                $q->where(function ($q) use ($now) {
                    $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                })->where(function ($q) use ($now) {
                    $q->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
                });
            }])->get();

        if ($participants->isEmpty()) {
            abort(422, 'No participants to spin');
        }

        $eligibleGuaranteed = $participants->filter(fn($p) =>
            optional($p->config)->guarantee_quota > 0
        );

        if ($eligibleGuaranteed->isNotEmpty()) {
            $winner = $eligibleGuaranteed->random();
            $winner->config->decrement('guarantee_quota');
        } else {
            $pool = $participants->map(function ($p) {
                $w = max(0.0, (float) (optional($p->config)->weight ?? 1));
                return ['p' => $p, 'w' => $w];
            })->filter(fn($x) => $x['w'] > 0)->values();

            if ($pool->isEmpty()) {
                abort(422, 'All weights are zero');
            }

            // Check if all participants have the same weight (default random selection)
            $weights = $pool->pluck('w')->unique();
            if ($weights->count() === 1 && $weights->first() == 1.0) {
                // All participants have equal weight (1.0), use simple random selection
                $winner = $pool->random()['p'];
            } else {
                // Use weighted selection
                $total = $pool->sum('w');
                $r = mt_rand() / mt_getrandmax() * $total;
                $acc = 0.0; $winner = $pool->last()['p'];
                foreach ($pool as $item) {
                    $acc += $item['w'];
                    if ($r <= $acc) { $winner = $item['p']; break; }
                }
            }
        }

        $segments = $participants->map(fn($p) => [
            'id' => $p->id,
            'label' => $p->name,
            'color' => $p->color,
            'weight' => (float) (optional($p->config)->weight ?? 1),
            'guarantee_quota' => (int) (optional($p->config)->guarantee_quota ?? 0),
        ])->values();

        $angle = rand(0, 359) + (rand(0, 99) / 100);
        Spin::create([
            'participant_id' => $winner->id,
            'pool_snapshot' => $segments,
            'angle' => $angle,
        ]);

        return [
            'winner' => [
                'id' => $winner->id,
                'label' => $winner->name,
                'color' => $winner->color,
            ],
            'angle' => $angle,
            'segments' => $segments,
        ];
    }
}


