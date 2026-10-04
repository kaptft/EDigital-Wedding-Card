<?php
/**
 * config.php
 * Tetapan sambungan database + CORS untuk Kad Jemputan Digital Hafiz & Ruqayyah
 *
 * PENTING: Tukar nilai di bawah mengikut hosting anda (cPanel / Hostinger / etc).
 * Jangan letak maklumat sebenar ini dalam repo Git awam — guna environment variables
 * jika boleh (getenv()), atau edit terus fail ini di server selepas upload.
 */

// ---- Tetapan Database (tukar ikut hosting anda) ----
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'your_database');
define('DB_USER', getenv('DB_USER') ?: 'your_database_user');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// ---- CORS: senaraikan domain yang dibenarkan panggil API ini ----
// Letakkan domain awam kad kahwin anda, contoh: 'https://kadkahwin-hafizruqayyah.com'
// Guna '*' semasa testing sahaja; tukar ke domain sebenar sebelum go-live.
$ALLOWED_ORIGINS = [
    '*',
];

function apply_cors(array $allowedOrigins): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array('*', $allowedOrigins, true)) {
        header('Access-Control-Allow-Origin: *');
    } elseif ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
    }
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

/**
 * Dapatkan sambungan PDO. Mati dengan JSON error jika gagal (supaya frontend
 * boleh paparkan mesej yang sopan berbanding error PHP mentah).
 */
function get_pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'ok'    => false,
            'error' => 'Sambungan pangkalan data gagal. Sila semak tetapan DB di config.php.',
        ]);
        exit;
    }
}

/** Helper ringkas untuk balas JSON dan hentikan skrip. */
function json_out(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Bersihkan input teks ringkas (trim + strip tag). */
function clean(string $s): string
{
    return trim(strip_tags($s));
}
