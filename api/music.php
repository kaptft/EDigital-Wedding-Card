<?php
// =====================================================
// HAFIZ & RUQAYYAH WEDDING
// MUSIC PLAYLIST API
// GET /api/music.php
// =====================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$dataFile = __DIR__ . '/music.json';

if (!file_exists($dataFile)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Music playlist file not found.',
        'playlist' => []
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents($dataFile), true);

if (!is_array($data) || !isset($data['playlist'])) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid music playlist configuration.',
        'playlist' => []
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$playlist = array_values(array_filter($data['playlist'], function ($song) {
    return isset($song['enabled']) && $song['enabled'] === true
        && !empty($song['file']);
}));

echo json_encode([
    'success' => true,
    'count' => count($playlist),
    'playlist' => $playlist
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
