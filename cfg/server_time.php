<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$ctx = stream_context_create(['http' => ['timeout' => 2]]);
$google_response = @file_get_contents('https://www.google.com', false, $ctx);

if ($google_response !== false) {
    foreach ($http_response_header as $h) {
        if (stripos($h, 'date:') === 0) {
            $time_str = substr($h, 5);
            $time = strtotime($time_str);
            echo json_encode([
                'time' => $time * 1000,
                'iso'  => date('c', $time)
            ]);
            exit;
        }
    }
}

echo json_encode([
    'time' => (int) round(microtime(true) * 1000),
    'iso'  => date('c')
]);