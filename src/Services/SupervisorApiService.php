<?php

namespace Iperamuna\SupervisorManager\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class SupervisorApiService
{
    protected Client $client;

    public function __construct()
    {
        $this->configureClient();
    }

    protected function configureClient(): void
    {
        $url = config('supervisor-manager.url');
        $port = config('supervisor-manager.port');
        $username = config('supervisor-manager.username');
        $password = config('supervisor-manager.password');

        $baseUrl = rtrim($url, '/') . ':' . $port . '/RPC2';

        $this->client = new Client([
            'base_uri' => $baseUrl,
            'auth' => [$username, $password],
            'timeout' => 10,
        ]);
    }

    public function getAllProcessInfo(): array
    {
        return $this->call('supervisor.getAllProcessInfo');
    }

    public function getProcessInfo(string $name): array
    {
        return $this->call('supervisor.getProcessInfo', [$name]);
    }

    public function startProcess(string $name, bool $wait = true): mixed
    {
        return $this->call('supervisor.startProcess', [$name, $wait]);
    }

    public function stopProcess(string $name, bool $wait = true): mixed
    {
        return $this->call('supervisor.stopProcess', [$name, $wait]);
    }

    public function restartProcess(string $name, bool $wait = true): mixed
    {
        try {
            $this->stopProcess($name, $wait);
        } catch (Exception $e) {
            // Ignore if process is not running
        }

        return $this->startProcess($name, $wait);
    }

    public function startAllProcesses(bool $wait = true): array
    {
        return $this->call('supervisor.startAllProcesses', [$wait]);
    }

    public function stopAllProcesses(bool $wait = true): array
    {
        return $this->call('supervisor.stopAllProcesses', [$wait]);
    }

    public function start(bool $wait = true): array
    {
        return $this->startAllProcesses($wait);
    }

    public function stop(bool $wait = true): array
    {
        return $this->stopAllProcesses($wait);
    }

    public function getState(): array
    {
        return $this->call('supervisor.getState');
    }

    public function readProcessStdoutLog(string $name, int $offset, int $length): string
    {
        return $this->call('supervisor.readProcessStdoutLog', [$name, $offset, $length]);
    }

    public function readProcessStderrLog(string $name, int $offset, int $length): string
    {
        return $this->call('supervisor.readProcessStderrLog', [$name, $offset, $length]);
    }

    public function clearProcessLogs(string $name): bool
    {
        return $this->call('supervisor.clearProcessLogs', [$name]);
    }

    public function tailProcessStdoutLog(string $name, int $offset, int $length): array
    {
        return $this->call('supervisor.tailProcessStdoutLog', [$name, $offset, $length]);
    }

    public function tailProcessStderrLog(string $name, int $offset, int $length): array
    {
        return $this->call('supervisor.tailProcessStderrLog', [$name, $offset, $length]);
    }

    public function restart(): bool
    {
        return $this->call('supervisor.restart');
    }

    /**
     * @throws Exception
     */
    protected function call(string $method, array $params = []): mixed
    {
        $xml = $this->encodeRequest($method, $params);

        try {
            $response = $this->client->post('', [
                'body' => $xml,
                'headers' => [
                    'Content-Type' => 'text/xml',
                ],
            ]);

            $body = (string) $response->getBody();

            return $this->decodeResponse($body);

        } catch (GuzzleException $e) {
            Log::error('Supervisor API Error: ' . $e->getMessage());
            throw new Exception("Failed to connect to Supervisor at {$this->client->getConfig('base_uri')}: " . $e->getMessage());
        }
    }

    protected function encodeRequest(string $method, array $params): string
    {
        $xml = '<?xml version="1.0"?>';
        $xml .= '<methodCall>';
        $xml .= '<methodName>' . htmlspecialchars($method) . '</methodName>';

        if (!empty($params)) {
            $xml .= '<params>';
            foreach ($params as $param) {
                $xml .= '<param><value>' . $this->encodeValue($param) . '</value></param>';
            }
            $xml .= '</params>';
        }

        $xml .= '</methodCall>';

        return $xml;
    }

    protected function encodeValue(mixed $value): string
    {
        if (is_int($value)) {
            return '<int>' . $value . '</int>';
        } elseif (is_bool($value)) {
            return '<boolean>' . ($value ? '1' : '0') . '</boolean>';
        } elseif (is_string($value)) {
            return '<string>' . htmlspecialchars($value) . '</string>';
        } elseif (is_array($value)) {
            // Check if associative array (struct) or indexed array (array)
            if (array_keys($value) !== range(0, count($value) - 1) && !empty($value)) {
                $xml = '<struct>';
                foreach ($value as $key => $val) {
                    $xml .= '<member>';
                    $xml .= '<name>' . htmlspecialchars($key) . '</name>';
                    $xml .= '<value>' . $this->encodeValue($val) . '</value>';
                    $xml .= '</member>';
                }
                $xml .= '</struct>';
            } else {
                $xml = '<array><data>';
                foreach ($value as $val) {
                    $xml .= '<value>' . $this->encodeValue($val) . '</value>';
                }
                $xml .= '</data></array>';
            }

            return $xml;
        }

        return '<string>' . htmlspecialchars((string) $value) . '</string>';
    }

    protected function decodeResponse(string $xml): mixed
    {
        $xmlElement = simplexml_load_string($xml);

        if ($xmlElement === false) {
            throw new Exception('Invalid XML response from Supervisor');
        }

        // Check for faults
        if (isset($xmlElement->fault)) {
            $fault = $this->decodeValue($xmlElement->fault->value);
            throw new Exception('Supervisor Fault: ' . ($fault['faultString'] ?? 'Unknown error') . ' (Code: ' . ($fault['faultCode'] ?? 'N/A') . ')');
        }

        if (isset($xmlElement->params->param->value)) {
            return $this->decodeValue($xmlElement->params->param->value);
        }

        return null;
    }

    protected function decodeValue(SimpleXMLElement $value): mixed
    {
        if (isset($value->array)) {
            $array = [];
            foreach ($value->array->data->value as $item) {
                $array[] = $this->decodeValue($item);
            }

            return $array;
        } elseif (isset($value->struct)) {
            $struct = [];
            foreach ($value->struct->member as $member) {
                $struct[(string) $member->name] = $this->decodeValue($member->value);
            }

            return $struct;
        } elseif (isset($value->int) || isset($value->i4)) {
            return (int) (string) ($value->int ?? $value->i4);
        } elseif (isset($value->boolean)) {
            return (string) $value->boolean === '1';
        } elseif (isset($value->string)) {
            return (string) $value->string;
        } elseif (isset($value->double)) {
            return (float) (string) $value->double;
        }

        // Fallback for simple string content if no type tag
        return (string) $value;
    }
}
