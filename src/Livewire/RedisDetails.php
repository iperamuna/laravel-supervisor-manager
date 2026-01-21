<?php

namespace Iperamuna\LaravelSupervisorManager\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Redis;

class RedisDetails extends Component
{
    public function render()
    {
        $info = [];
        $projectMemory = 'N/A';
        $config = [];
        $error = null;

        try {
            // Check if Redis is configured
            if (Redis::connection()) {
                $info = Redis::info();
                $config = config('database.redis.default', []);
                $projectMemory = $info['used_memory_human'] ?? 'N/A';
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return view('supervisor-manager::livewire.redis-details', [
            'info' => $info,
            'config' => $config,
            'projectMemory' => $projectMemory,
            'error' => $error,
        ]);
    }
}
