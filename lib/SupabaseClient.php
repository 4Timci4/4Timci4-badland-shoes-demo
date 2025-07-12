<?php

require_once __DIR__ . '/../config/env.php';

class SupabaseClient
{
    private $baseUrl;
    private $apiKey;
    private $requestTimeout = 120;
    private $connectTimeout = 10;
    private $useCache = true;
    private $cacheExpiry = 1;
    private $cacheDir;

    public function __construct($baseUrl, $apiKey, $options = [])
    {
        $this->baseUrl = rtrim($baseUrl, '/') . '/rest/v1';
        $this->apiKey = $apiKey;

        if (isset($options['requestTimeout'])) {
            $this->requestTimeout = $options['requestTimeout'];
        }

        if (isset($options['connectTimeout'])) {
            $this->connectTimeout = $options['connectTimeout'];
        }

        if (isset($options['useCache'])) {
            $this->useCache = $options['useCache'];
        }

        if (isset($options['cacheExpiry'])) {
            $this->cacheExpiry = $options['cacheExpiry'];
        }

        $this->cacheDir = sys_get_temp_dir() . '/supabase_cache';
        if (!file_exists($this->cacheDir) && $this->useCache) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function request($endpoint, $method = 'GET', $data = null, $headers = [], $useCache = true)
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $cacheKey = null;
        $useCache = $this->useCache && $useCache && !isset($headers['Prefer']);

        if ($method === 'GET' && $useCache) {
            $cacheKey = md5($url . json_encode($data) . json_encode($headers));
            $cachedData = $this->getCache($cacheKey);
            if ($cachedData !== null) {
                return $cachedData;
            }
        }

        $defaultHeaders = [
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];

        $finalHeaders = $defaultHeaders;
        if (isset($headers['Prefer'])) {
            $finalHeaders[] = 'Prefer: ' . $headers['Prefer'];
        }


        $ch = curl_init();
        $responseHeaders = [];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $finalHeaders);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$responseHeaders) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2)
                return $len;

            $responseHeaders[strtolower(trim($header[0]))] = trim($header[1]);
            return $len;
        });
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);

        if (defined('APP_ENV') && APP_ENV === 'development') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        if ($method !== 'GET' && $data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);

        curl_close($ch);

        if ($curl_error) {
            error_log("Supabase cURL Error: $curl_error");
            throw new Exception("Supabase cURL Error: $curl_error", 500);
        }

        if ($http_code === 204) {
            return ['body' => null, 'headers' => $responseHeaders];
        }

        if ($http_code >= 200 && $http_code < 300) {
            if (empty($response)) {
                return ['body' => null, 'headers' => $responseHeaders];
            }

            $body = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Supabase API Error: Invalid JSON response. Body: $response");
                throw new Exception("Supabase API Error: Invalid JSON response", 500);
            }

            $result = [
                'body' => $body,
                'headers' => $responseHeaders
            ];

            if ($method === 'GET' && $useCache && $cacheKey) {
                $this->setCache($cacheKey, $result);
            }
            return $result;
        }

        $error_message = "Supabase API Error: HTTP $http_code";
        $decoded_response = json_decode($response, true);

        if ($decoded_response) {
            $error_details = json_encode($decoded_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
            $error_message .= " - Details: " . $error_details;
        } elseif (!empty($response)) {
            $error_message .= " - Response: " . $response;
        }

        error_log($error_message);
        throw new Exception($error_message, $http_code);
    }

    private function getCache($key)
    {
        if (!$this->useCache) {
            return null;
        }

        $cacheFile = $this->cacheDir . '/' . $key;

        if (file_exists($cacheFile)) {
            $cachedData = file_get_contents($cacheFile);
            $data = unserialize($cachedData);

            if (time() - $data['time'] < $this->cacheExpiry) {
                return $data['data'];
            }

            unlink($cacheFile);
        }

        return null;
    }

    private function setCache($key, $data)
    {
        if (!$this->useCache) {
            return;
        }

        $cacheFile = $this->cacheDir . '/' . $key;
        $cacheData = [
            'time' => time(),
            'data' => $data
        ];

        file_put_contents($cacheFile, serialize($cacheData));
    }

    public function clearCache($key = null)
    {
        if (!$this->useCache) {
            return;
        }

        if ($key) {
            $cacheFile = $this->cacheDir . '/' . md5($key);
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
            return;
        }

        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function prepareSql($sql, $params = [])
    {
        foreach ($params as $key => $value) {
            $placeholder = is_numeric($key) ? '?' : ':' . $key;

            if (is_null($value)) {
                $value = 'NULL';
            } elseif (is_bool($value)) {
                $value = $value ? 'TRUE' : 'FALSE';
            } elseif (is_int($value) || is_float($value)) {
                $value = (string) $value;
            } elseif (is_array($value)) {
                $items = array_map(function ($item) {
                    if (is_string($item)) {
                        return "'" . str_replace("'", "''", $item) . "'";
                    }
                    return (string) $item;
                }, $value);
                $value = '(' . implode(',', $items) . ')';
            } else {
                $value = "'" . str_replace("'", "''", $value) . "'";
            }

            $pos = strpos($sql, $placeholder);
            if ($pos !== false) {
                $sql = substr_replace($sql, $value, $pos, strlen($placeholder));
            }
        }

        return $sql;
    }

    public function executeRawSql($sql, $params = [])
    {
        $preparedSql = $this->prepareSql($sql, $params);

        return $this->request('rpc/execute_sql', 'POST', [
            'query' => $preparedSql
        ], []);
    }
}
