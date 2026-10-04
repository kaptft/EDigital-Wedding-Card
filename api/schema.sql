-- =====================================================================
-- schema.sql — Pangkalan data untuk Kad Jemputan Digital Hafiz & Ruqayyah
-- Import fail ini ke dalam database yang telah dipilih dalam phpMyAdmin.
-- =====================================================================

-- -----------------------------------------------------------------
-- Jadual RSVP (Maklumat Kehadiran)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS rsvp (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama          VARCHAR(150)      NOT NULL,
  telefon       VARCHAR(30)       NULL,
  kehadiran     ENUM('hadir','tidak_hadir','belum_pasti') NOT NULL DEFAULT 'hadir',
  bilangan_pax  TINYINT UNSIGNED  NOT NULL DEFAULT 1,
  catatan       VARCHAR(255)      NULL,
  ip_address    VARCHAR(45)       NULL,
  created_at    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_kehadiran (kehadiran)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------
-- Jadual Buku Tetamu (Guestbook / Ucapan)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS guestbook (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama          VARCHAR(150)  NOT NULL,
  mesej         TEXT          NOT NULL,
  disahkan      TINYINT(1)    NOT NULL DEFAULT 1, -- 1 = terus terbit, 0 = perlu moderasi
  ip_address    VARCHAR(45)   NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------
-- Jadual tetapan RSVP (contoh: tarikh tutup RSVP, seperti dilihat
-- dalam video rujukan "RSVP telah ditutup pada ...")
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS rsvp_settings (
  id              TINYINT UNSIGNED PRIMARY KEY DEFAULT 1,
  rsvp_tutup_pada DATETIME NULL,
  rsvp_dibuka     TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO rsvp_settings (id, rsvp_tutup_pada, rsvp_dibuka)
VALUES (1, '2026-12-10 23:59:00', 1)
ON DUPLICATE KEY UPDATE id = id;
