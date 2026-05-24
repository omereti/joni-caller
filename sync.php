<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

// Clean sync code - letters and numbers only
$code = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['code'] ?? '');
if (!$code || strlen($code) < 4 || strlen($code) > 32) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid code']);
    exit;
}

$dir  = __DIR__ . '/sync-data';
$file = $dir . '/' . $code . '.json';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if ($data === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }
    $data['serverTs'] = round(microtime(true) * 1000);
    file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok' => true, 'serverTs' => $data['serverTs']]);
} else {
    if (file_exists($file)) {
        echo file_get_contents($file);
    } else {
        echo 'null';
    }
}
?>
