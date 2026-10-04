<?php
/**
 * guestbook.php
 * GET  -> senarai ucapan (terkini dahulu), sokong ?page=1&limit=10
 * POST -> hantar ucapan baru { nama, mesej }
 */
require __DIR__ . '/config.php';

$ALLOWED_ORIGINS = ['*'];
apply_cors($ALLOWED_ORIGINS);

$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page  = max(1, (int) ($_GET['page'] ?? 1));
    $limit = max(1, min(50, (int) ($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    $stmt = $pdo->prepare('
        SELECT id, nama, mesej, created_at
        FROM guestbook
        WHERE disahkan = 1
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $total = (int) $pdo->query('SELECT COUNT(*) FROM guestbook WHERE disahkan = 1')->fetchColumn();

    json_out([
        'ok'    => true,
        'data'  => $rows,
        'page'  => $page,
        'limit' => $limit,
        'total' => $total,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true) ?: $_POST;

    $nama  = clean((string) ($body['nama'] ?? ''));
    $mesej = clean((string) ($body['mesej'] ?? ''));

    if ($nama === '' || $mesej === '') {
        json_out(['ok' => false, 'error' => 'Nama dan mesej diperlukan.'], 422);
    }
    if (mb_strlen($mesej) > 500) {
        json_out(['ok' => false, 'error' => 'Mesej terlalu panjang (maksimum 500 aksara).'], 422);
    }

    $stmt = $pdo->prepare('
        INSERT INTO guestbook (nama, mesej, ip_address)
        VALUES (:nama, :mesej, :ip)
    ');
    $stmt->execute([
        ':nama'  => $nama,
        ':mesej' => $mesej,
        ':ip'    => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    json_out(['ok' => true, 'message' => 'Terima kasih atas ucapan anda!']);
}

json_out(['ok' => false, 'error' => 'Method tidak disokong.'], 405);
