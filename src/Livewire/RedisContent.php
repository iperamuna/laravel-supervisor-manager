<?php

namespace Iperamuna\LaravelSupervisorManager\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Redis;

class RedisContent extends Component
{
    public string $pattern = '*';
    public array $keys = [];
    public ?string $selectedKey = null;
    public mixed $keyContent = null;
    public ?string $keyType = null;
    public int $keyTtl = -1;
    public ?string $error = null;
    public string $connection = 'default';
    public array $connections = [];

    // Store the raw content separately if needed, but we mostly display parsed
    public mixed $keyContentRaw = null;

    public function mount()
    {
        $this->connections = array_keys(array_filter(
            config('database.redis', []),
            fn($value, $key) => is_array($value) && !in_array($key, ['options']),
            ARRAY_FILTER_USE_BOTH
        ));

        // Ensure default is selected if available, or first one
        if (!in_array($this->connection, $this->connections) && count($this->connections) > 0) {
            $this->connection = $this->connections[0] ?? 'default';
        }

        $this->loadKeys();
    }

    public function updatedConnection()
    {
        $this->loadKeys();
    }

    public function loadKeys()
    {
        $this->reset(['selectedKey', 'keyContent', 'keyType', 'keyTtl', 'error']);

        try {
            $connection = Redis::connection($this->connection);
            $retrievedKeys = $connection->keys($this->pattern ?: '*');

            // Sort and limit
            sort($retrievedKeys);
            $this->keys = array_slice($retrievedKeys, 0, 1000);

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->keys = [];
        }
    }

    public function selectKey($key)
    {
        $this->selectedKey = $key;
        $this->error = null;

        try {
            $this->keyType = $this->determineType($key);
            $this->keyTtl = Redis::connection($this->connection)->ttl($key);

            switch ($this->keyType) {
                case 'string':
                    $content = Redis::connection($this->connection)->get($key);
                    $this->keyContent = $this->tryParse($content);
                    break;
                case 'hash':
                    $this->keyContent = Redis::connection($this->connection)->hgetall($key);
                    break;
                case 'list':
                    $this->keyContent = Redis::connection($this->connection)->lrange($key, 0, 100);
                    break;
                case 'set':
                    $this->keyContent = Redis::connection($this->connection)->smembers($key);
                    break;
                case 'zset':
                    $this->keyContent = Redis::connection($this->connection)->zrange($key, 0, 100, ['WITHSCORES' => true]);
                    break;
                default:
                    $this->keyContent = 'Unsupported type or empty';
            }
        } catch (\Exception $e) {
            $this->error = "Could not load key: " . $e->getMessage();
        }
    }

    public function deleteKey()
    {
        if ($this->selectedKey) {
            Redis::connection($this->connection)->del($this->selectedKey);
            $this->selectedKey = null;
            $this->keyContent = null;
            $this->loadKeys();
        }
    }

    protected function determineType($key)
    {
        $type = Redis::connection($this->connection)->type($key);

        if (is_numeric($type)) {
            return match ((int) $type) {
                1 => 'string',
                2 => 'set',
                3 => 'list',
                4 => 'zset',
                5 => 'hash',
                default => 'unknown',
            };
        }

        return (string) $type;
    }

    protected function tryParse($content)
    {
        // Recursively try to unserialize or json_decode
        if (!is_string($content)) {
            return $content;
        }

        // Try PHP Unserialize
        if ($this->isSerialized($content, $unserialized)) {
            return $this->tryParse($unserialized);
        }

        // Try JSON Decode
        $json = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            // If JSON acts as a wrapper for serialized string (rare but possible)
            if (is_string($json)) {
                return $this->tryParse($json);
            }
            return $json;
        }

        return $content;
    }

    protected function isSerialized($value, &$result = null)
    {
        // Bit of a hack to check if string is serialized without generating E_NOTICE
        // and safely handling classes we don't have.
        // We use @ to suppress errors for partial or broken serializations
        if (!is_string($value)) {
            return false;
        }
        if (trim($value) === 'b:0;') {
            $result = false;
            return true;
        }
        $length = strlen($value);
        if ($length < 4) {
            return false;
        }

        // Basic check for common serialized formats: s:..., a:..., O:..., N;, b:..., i:...
        // : at index 1 for others, index 2 or more for s, O, a
        $semicolon = strpos($value, ';');
        $brace = strpos($value, '{');

        if ($semicolon === false && $brace === false) {
            return false;
        }

        // Safe Unserialize call
        // We allow all classes? Or just basic types to avoid instantiation side effects?
        // 'allowed_classes' => false returns __PHP_Incomplete_Class for objects, which is safer for viewing.
        $parsed = @unserialize($value, ['allowed_classes' => false]);

        if ($parsed !== false) {
            $result = $parsed;
            return true;
        }

        return false;
    }

    public function render()
    {
        return view('supervisor-manager::livewire.redis-content');
    }
}
