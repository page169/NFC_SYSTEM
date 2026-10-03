<?php
foreach (file('.env') as $line) {
    $line = trim($line);
    if ($line && !str_starts_with($line, '#')) {
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

function sendSMS($phone, $message)
{
    $apiKey = $_ENV['SEMAPHORE_API_KEY'];

    if (substr($phone, 0, 2) === '09') {
        $phone = '63' . substr($phone, 1);
    } elseif (substr($phone, 0, 1) === '9') {
        $phone = '63' . $phone;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://api.semaphore.co/api/v4/messages',
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'apikey'     => $apiKey,
            'number'     => $phone,
            'message'    => $message
        ])
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    // --- LOGGING ---
    $logDir  = __DIR__ . '/logs';
    $logFile = $logDir . '/sms_' . date('Y-m-d') . '.log';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $status    = !empty($curlError) ? 'CURL_ERROR' : (isset($result[0]['status']) ? strtoupper($result[0]['status']) : 'UNKNOWN');
    $timestamp = date('Y-m-d H:i:s');

    $logEntry = "[{$timestamp}] | {$status} | TO: {$phone} | MSG: {$message} | RAW: " . $response;

    if (!empty($curlError)) {
        $logEntry .= " | ERROR: {$curlError}";
    }

    $logEntry .= PHP_EOL;

    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    // ---------------

    return $result;
}
