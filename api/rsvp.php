<?php
/**
 * rsvp.php
 * GET  -> status RSVP (dibuka/ditutup) + jumlah ringkasan (hadir/tidak hadir)
 * POST -> hantar RSVP baru { nama, telefon, kehadiran, bilangan_pax, catatan }
 */
require __DIR__ . '/config.php';

$ALLOWED_ORIGINS = ['*'];
apply_cors($ALLOWED_ORIGINS);

$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Semak tetapan (sama ada RSVP masih dibuka)
    $stmt = $pdo->query('SELECT rsvp_dibuka, rsvp_tutup_pada FROM rsvp_settings WHERE id = 1');
    $settings = $stmt->fetch() ?: ['rsvp_dibuka' => 1, 'rsvp_tutup_pada' => null];

    $dibuka = (bool) $settings['rsvp_dibuka'];
    if ($dibuka && !empty($settings['rsvp_tutup_pada'])) {
        $dibuka = (new DateTime() <= new DateTime($settings['rsvp_tutup_pada']));
    }

    $ringkasan = $pdo->query("
        SELECT
            SUM(CASE WHEN kehadiran = 'hadir' THEN bilangan_pax ELSE 0 END) AS jumlah_hadir,
            SUM(CASE WHEN kehadiran = 'tidak_hadir' THEN 1 ELSE 0 END) AS jumlah_tidak_hadir,
            COUNT(*) AS jumlah_rekod
        FROM rsvp
    ")->fetch();

    json_out([
        'ok' => true,
        'rsvp_dibuka' => $dibuka,
        'rsvp_tutup_pada' => $settings['rsvp_tutup_pada'],
        'ringkasan' => [
            'jumlah_hadir'       => (int) ($ringkasan['jumlah_hadir'] ?? 0),
            'jumlah_tidak_hadir' => (int) ($ringkasan['jumlah_tidak_hadir'] ?? 0),
            'jumlah_rekod'       => (int) ($ringkasan['jumlah_rekod'] ?? 0),
        ],
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true) ?: $_POST;

    $nama        = clean((string) ($body['nama'] ?? ''));
    $telefon     = clean((string) ($body['telefon'] ?? ''));
    $kehadiran   = (string) ($body['kehadiran'] ?? 'hadir');
    $bilanganPax = (int) ($body['bilangan_pax'] ?? 1);
    $catatan     = clean((string) ($body['catatan'] ?? ''));

    if ($nama === '') {
        json_out(['ok' => false, 'error' => 'Nama diperlukan.'], 422);
    }
    if (!in_array($kehadiran, ['hadir', 'tidak_hadir', 'belum_pasti'], true)) {
        $kehadiran = 'hadir';
    }
    $bilanganPax = max(1, min(10, $bilanganPax));

    // Semak dahulu sama ada RSVP masih dibuka
    $settings = $pdo->query('SELECT rsvp_dibuka, rsvp_tutup_pada FROM rsvp_settings WHERE id = 1')->fetch();
    $dibuka = (bool) ($settings['rsvp_dibuka'] ?? 1);
    if ($dibuka && !empty($settings['rsvp_tutup_pada'] ?? null)) {
        $dibuka = (new DateTime() <= new DateTime($settings['rsvp_tutup_pada']));
    }
    if (!$dibuka) {
        json_out(['ok' => false, 'error' => 'Maaf, RSVP telah ditutup.'], 403);
    }

    $stmt = $pdo->prepare('
        INSERT INTO rsvp (nama, telefon, kehadiran, bilangan_pax, catatan, ip_address)
        VALUES (:nama, :telefon, :kehadiran, :pax, :catatan, :ip)
    ');
    $stmt->execute([
        ':nama'      => $nama,
        ':telefon'   => $telefon,
        ':kehadiran' => $kehadiran,
        ':pax'       => $bilanganPax,
        ':catatan'   => $catatan,
        ':ip'        => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    json_out(['ok' => true, 'message' => 'Terima kasih! RSVP anda telah direkodkan.']);
}

json_out(['ok' => false, 'error' => 'Method tidak disokong.'], 405);
