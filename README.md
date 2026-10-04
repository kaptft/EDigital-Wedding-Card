# Kad Jemputan Digital — Hafiz & Ruqayyah
Full stack: HTML/CSS/JS (frontend) + PHP + MySQL (backend RSVP & Buku Tetamu)

## Hosting note
GitHub Pages can host the static HTML/CSS/JavaScript frontend, but it cannot run the PHP/MySQL RSVP, guestbook, or music API. Keep the PHP API on a PHP-enabled host such as InfinityFree and configure the frontend API URL for that host before enabling GitHub Pages.

Never commit database passwords or private contact numbers to this public repository. Configure database credentials on the PHP host, outside this repository.

## Struktur fail
```
index.html
.htaccess
api/
├── config.php          <- Safe template; configure credentials on PHP host
├── schema.sql
├── rsvp.php
├── guestbook.php
├── music.php
└── music.json
assets/music/
database/
```

## Cara deploy ke hosting awam (cPanel / Hostinger / dsb.)

### 1. Sediakan Database MySQL
1. Masuk cPanel → **MySQL Databases** → cipta database (contoh: `namahos_kadkahwin`) dan satu user dengan kata laluan, pastikan user tu diberi akses penuh ("All Privileges") ke database tersebut.
2. Buka **phpMyAdmin**, pilih database yang baru dicipta, klik tab **Import**, dan upload fail `api/schema.sql`. Ini akan cipta jadual `rsvp`, `guestbook` dan `rsvp_settings` dalam database terpilih.

### 2. Tetapkan sambungan database
Edit `api/config.php`, tukar baris ini kepada maklumat sebenar hosting anda:
```php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'your_database');
define('DB_USER', getenv('DB_USER') ?: 'your_database_user');
define('DB_PASS', getenv('DB_PASS') ?: '');
```
Do not commit real database credentials. Configure them only on the PHP host. The committed config is a safe template and will not connect until configured.

### 3. Upload semua fail
Upload **kandungan repository ini** (termasuk subfolder `api/`) ke `public_html/` (atau subfolder domain anda) melalui:
- **File Manager** cPanel, atau
- **FTP** (FileZilla) — host, username, password FTP dari panel hosting anda.

Upload kandungan folder ini, bukan folder repository induk. Pastikan `index.html` dan folder `api/` berada sebaris (sibling), supaya panggilan `fetch('api/rsvp.php')` dalam `index.html` berfungsi.

### Langkah khusus InfinityFree
1. Log masuk ke InfinityFree dan buka **Control Panel** bagi website anda.
2. Dalam **Online File Manager**, buka folder `htdocs`. Upload kandungan repository ini ke situ (bukan folder induk repository). Pastikan `index.html` berada terus dalam `htdocs/` dan folder `api/`, `assets/` serta `database/` juga berada di situ.
3. Dari Control Panel, buka **MySQL Databases** dan cipta database. Catat nama database, username, password dan hostname MySQL yang dipaparkan.
4. Buka phpMyAdmin melalui Control Panel, pilih database itu, kemudian import `api/schema.sql`. Fail SQL ini mencipta jadual dalam database yang dipilih; ia tidak cuba mencipta database dengan nama tetap.
5. Edit `htdocs/api/config.php` melalui File Manager. Isikan `DB_HOST`, `DB_NAME`, `DB_USER` dan `DB_PASS` menggunakan butiran MySQL InfinityFree, bukan nilai lalai `localhost`, `kad_kahwin`, `root` dan kata laluan kosong.
6. Untuk mengehadkan panggilan API dari laman lain, tukar `$ALLOWED_ORIGINS = ['*'];` dalam `api/rsvp.php` dan `api/guestbook.php` kepada domain penuh website anda, contohnya `['https://namaanda.rf.gd']`.
7. Buka domain website dan uji RSVP serta Buku Tetamu. Jika sambungan database gagal, semak semula hostname dan butiran database dalam `config.php`.

### 4. Uji
Buka `https://domainanda.com/` — kad patut terbuka, butang RSVP dan Buku Tetamu patut berfungsi dan menyimpan ke database.

Jika RSVP/Buku Tetamu tak berfungsi:
- Semak `api/config.php` — maklumat DB tepat?
- Buka terus `https://domainanda.com/api/rsvp.php` di browser — patut keluar JSON, bukan error PHP putih kosong.
- Semak PHP versi hosting ≥ 7.4 (disyorkan 8.0+). Tetapkan versi PHP melalui panel hosting jika pilihan itu tersedia.

### 5. (Pilihan) Tutup RSVP secara automatik
Dalam jadual `rsvp_settings`, lajur `rsvp_tutup_pada` sudah ditetapkan contoh `2026-12-10 23:59:00`. Tukar tarikh ini di phpMyAdmin mengikut keperluan anda — selepas tarikh tersebut, borang RSVP akan dipaparkan sebagai "ditutup" secara automatik (seperti dalam video rujukan).

## Domain percuma / murah untuk testing
Jika belum ada hosting, boleh cuba:
- **InfinityFree** / **000webhost** — hosting percuma yang sokong PHP + MySQL.
- **Hostinger** / **Exabytes** — hosting murah tempatan (Malaysia) yang sokong cPanel.

## Nota keselamatan sebelum go-live
- Tukar `$ALLOWED_ORIGINS = ['*']` dalam `rsvp.php` / `guestbook.php` kepada domain sebenar anda (contoh `https://hafizruqayyah.com`) untuk elak orang lain guna API ini dari laman lain.
- Jangan commit `api/config.php` dengan kata laluan sebenar ke GitHub awam — guna `.gitignore` atau environment variables jika anda guna Git.


## Music Playlist API

The wedding website now loads music from `api/music.php`.

### Language toggle
Visitors can switch the website interface between Bahasa Melayu and English using the `EN` / `BM` button near the music control. Their selected language is saved in the browser.

### Playlist
Edit `api/music.json` to add, remove, reorder, or disable songs.

Each song uses:
- `title`
- `artist`
- `file`
- `enabled`

Put MP3 files inside `assets/music/`.

Example:
`assets/music/wedding-prelude.mp3`

### API
`GET /api/music.php`

The frontend automatically:
1. Loads the playlist.
2. Plays the first song after the cover is opened.
3. Moves to the next song when a track ends.
4. Shows the current song.
5. Lets the visitor pause/resume using the floating music button.
