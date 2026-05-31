# LAPORAN PRAKTIKUM
## Sistem Pemesanan Kamar Hotel Berbasis Web
### Luxury Hotel Booking System

---

**Nama Proyek** : Luxury Hotel Booking System  
**Mata Kuliah** : PEMRPGRAMAN WEB
**Tahun** : 2026  

---

## BAB I — PENDAHULUAN

### 1.1 Latar Belakang

Perkembangan teknologi informasi yang pesat mendorong berbagai sektor bisnis untuk mengadopsi sistem berbasis web, termasuk industri perhotelan. Sistem pemesanan kamar hotel secara manual memiliki banyak keterbatasan seperti kesalahan pencatatan, sulitnya pengecekan ketersediaan kamar secara real-time, serta kurangnya efisiensi dalam pengelolaan data tamu.

Proyek **Luxury Hotel Booking System** hadir sebagai solusi berbasis web yang mengintegrasikan antarmuka pengguna yang modern dengan sistem backend yang handal. Sistem ini memungkinkan tamu untuk melakukan pemesanan kamar secara online, memilih tipe kamar, lantai, serta layanan tambahan, dengan perhitungan harga yang dilakukan secara otomatis dan real-time.

### 1.2 Tujuan

1. Membangun sistem pemesanan kamar hotel berbasis web yang fungsional.
2. Mengimplementasikan logika ketersediaan kamar secara real-time menggunakan query database.
3. Menerapkan konsep antarmuka pengguna (UI) yang responsif dan modern.
4. Menyediakan halaman administrasi untuk pengelolaan data pemesanan.
5. Menerapkan validasi data pada sisi server maupun klien.

### 1.3 Ruang Lingkup

Sistem ini mencakup:
- Halaman utama (landing page) dengan informasi hotel dan tipe kamar.
- Halaman pemesanan kamar dengan form interaktif.
- Backend PHP untuk pemrosesan form dan query database.
- Panel admin dengan fitur dashboard, occupancy, manajemen booking, dan kelola kamar.
- Perhitungan harga dinamis berdasarkan tipe kamar, jumlah malam, dan layanan tambahan.

---

## BAB II — ANALISIS SISTEM

### 2.1 Analisis Kebutuhan Fungsional

| No  | Kebutuhan              | Deskripsi                                                                     |
|-----|------------------------|-------------------------------------------------------------------------------|
| F1  | Tampilan Kamar         | Sistem menampilkan tipe dan harga kamar dari database secara dinamis                  |
| F2  | Ketersediaan Real-time | Sistem mengecek ketersediaan kamar berdasarkan tanggal check-in dan check-out |
| F3  | Pemilihan Kamar        | Pengguna dapat memilih lantai dan nomor kamar yang tersedia                   |
| F4  | Kalkulasi Harga        | Sistem menghitung total harga secara otomatis termasuk layanan tambahan       |
| F5  | Validasi Form          | Sistem memvalidasi input pengguna sebelum menyimpan ke database               |
| F6  | Konfirmasi Booking     | Sistem memberikan nomor booking setelah pemesanan berhasil                    |
| F7  |  Dashboard Admin       | Admin melihat ringkasan total booking, total kamar, dan kamar terpakai        |
| F8  | Manajemen Booking      | Admin dapat mencari dan mengubah status booking (confirmed/checked_in/checked_out/cancelled) |
| F9  | Status Occupancy       | Admin dapat melihat status setiap kamar per lantai beserta info tamu          |
| F10 | Kelola Kamar & Harga   | Admin dapat mengubah tipe kamar dan harga per malam                           |
| F11 | Autentikasi Admin      | Panel admin dilindungi dengan sistem login sesi                               |

### 2.2 Analisis Kebutuhan Non-Fungsional

| No  | Kebutuhan| Keterangan                                                                                      |
|-----|----------|-------------------------------------------------------------------------------------------------|
| NF1 | Responsif| Tampilan dapat diakses di desktop maupun mobile (sidebar toggle pada layar kecil)               |
| NF2 | Keamanan | Input pengguna di-escape sebelum query; sesi admin diproteksi dengan `requireLogin()`           |
| NF3 | Performa | Data kamar dan harga dimuat secara asinkron via Fetch API; dashboard auto-refresh tiap 30 detik |
| NF4 | Kegunaan | Antarmuka intuitif dengan ikon Remixicon, badge status berwarna, dan pesan error yang jelas     |

### 2.3 Arsitektur Sistem

Sistem menggunakan arsitektur **3-tier** yang memisahkan lapisan presentasi, logika bisnis, dan lapisan data:

```
[Presentation Layer]        [Business Logic Layer]       [Data Layer]
  index.html                  booking.php (PHP)           MySQL DB
  booking.php (HTML)          config.php (root)           hotel_booking_db
  main.js                     admin/config.php             - rooms
  styles.css                  admin/index.php              - bookings
  admin/admin-styles.css      admin/bookings.php           - booking_services
  admin/_sidebar.php          admin/room-management.php    - room_prices
  admin/login.php             admin/occupancy.php
                              admin/login.php
                              admin/logout.php
```

**Alur data utama:**
1. Pengguna membuka `index.html` — harga kamar dimuat via Fetch API ke `booking.php?action=getPrices`.
2. Pengguna mengisi form di `booking.php` — JavaScript memuat kamar tersedia via `booking.php?action=getAvailableRooms`.
3. Data form dikirim via POST ke `booking.php` — server memvalidasi dan menyimpan ke database.
4. Server mengembalikan pesan konfirmasi dengan nomor booking, lantai, dan nomor kamar.
5. Admin login ke `admin/login.php` — sesi disimpan dan setiap halaman admin memverifikasi sesi via `requireLogin()`.

---

## BAB III — IMPLEMENTASI

### 3.1 Struktur Direktori Proyek

```
luxury-hotel/
├── index.html              # Halaman utama / landing page
├── booking.php             # Halaman dan backend pemesanan kamar
├── config.php              # Konfigurasi koneksi database (root)
├── main.js                 # Logika JavaScript: UI interaktif & kalkulasi harga
├── styles.css              # Stylesheet keseluruhan tampilan publik
├── assets/                 # Gambar kamar dan ikon media sosial
└── admin/
    ├── index.php           # Dashboard admin
    ├── bookings.php        # Manajemen data booking
    ├── occupancy.php       # Status occupancy kamar per lantai
    ├── room-management.php # Kelola tipe kamar dan harga
    ├── login.php           # Halaman login admin
    ├── logout.php          # Handler logout
    ├── config.php          # Koneksi DB + fungsi session admin
    ├── _sidebar.php        # Komponen sidebar navigasi (include)
    └── admin-styles.css    # Stylesheet panel admin
```

### 3.2 Konfigurasi Database

Terdapat dua file konfigurasi koneksi database. File root (`config.php`) digunakan oleh halaman publik:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_booking_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset("utf8");
```

File `admin/config.php` menambahkan manajemen sesi untuk autentikasi admin:

```php
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);
session_start();

function requireLogin() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}
```

**Tabel database yang digunakan:**

| Tabel              | Kolom Utama                             | Keterangan                   |
|--------------------|-----------------------------------------|------------------------------|
| `rooms`            | id, room_number, room_type, floor       | Data kamar hotel             |
| `bookings`         | id, nama, email, telepon, kota, check_in, check_out, jumlah_tamu, jumlah_kamar, tipe_kamar  room_id, floor, catatan, total_harga, tanggal_booking, status  | Data pemesanan tamu          |
| `booking_services` | booking_id, nama_service, harga         | Layanan tambahan per booking |
| `room_prices`      | room_type, price                        | Harga per tipe kamar         |

### 3.3 Logika Ketersediaan Kamar (`booking.php`)

Fungsi `getAvailableRooms()` menggunakan subquery SQL untuk mendeteksi konflik tanggal. Kamar hanya ditampilkan apabila tidak ada booking aktif yang tanggalnya tumpang-tindih:

```sql
SELECT r.*
FROM rooms r
WHERE (
    SELECT COUNT(*) FROM bookings b
    WHERE b.room_id = r.id
    AND b.status != 'cancelled'
    AND (b.check_in <= '$check_out' AND b.check_out > '$check_in')
) = 0
ORDER BY r.floor, r.room_number
```

Setelah form disubmit, server melakukan **verifikasi kedua** untuk mencegah race condition:

```php
$verify_sql = "SELECT COUNT(*) as conflict_count FROM bookings b
               WHERE b.room_id = $room_id
               AND b.status != 'cancelled'
               AND (b.check_in <= '$check_out' AND b.check_out > '$check_in')";
```

Jika tidak ada konflik, data disimpan ke tabel `bookings` dan layanan tambahan disimpan ke `booking_services`:

```php
$sql = "INSERT INTO bookings (nama, email, telepon, kota, check_in, check_out,
        jumlah_tamu, jumlah_kamar, tipe_kamar, room_id, floor, catatan,
        total_harga, tanggal_booking, status)
        VALUES ('$nama','$email','$telepon','$kota','$check_in','$check_out',
        $jumlah_tamu,$jumlah_kamar,'$tipe_kamar',$room_id,$floor,'$catatan',
        $total_harga,'$tanggal_booking','$status')";

// Simpan layanan tambahan
foreach ($_POST['services'] as $service) {
    $harga_service = getServicePrice($service);
    $conn->query("INSERT INTO booking_services (booking_id, nama_service, harga)
                  VALUES ($booking_id, '$service', '$harga_service')");
}
```

### 3.4 Endpoint API

`booking.php` melayani dua endpoint GET yang dipanggil secara asinkron oleh JavaScript:

| Endpoint                    | Parameter               | Respons                                              |
|-----------------------------|-------------------------|------------------------------------------------------|
| `?action=getPrices`         | —                       | JSON objek `{"standard":500000,"deluxe":750000,...}` |
| `?action=getAvailableRooms` | `check_in`, `check_out` | JSON array data kamar yang tidak konflik             |

Harga diambil dari tabel `room_prices`; jika tabel kosong, sistem menggunakan nilai default hardcoded sebagai fallback:

```php
if (empty($prices)) {
    $prices = array('standard'=>500000,'deluxe'=>750000,'suite'=>1200000,'oceanview'=>1500000);
}
```

### 3.5 Antarmuka Halaman Utama (`index.html`)

Halaman utama terdiri dari beberapa seksi:

| Seksi               | Keterangan                                          |
|---------------------|-----------------------------------------------------|
| Header / Hero       | Judul dan tagline hotel dengan animasi ScrollReveal |
| Booking Feature Bar | Tiga keunggulan: Nyaman, View Indah, Bersih         |
| About               | Deskripsi hotel dengan gambar                       |
| Room Grid           | Tiga tipe kamar dengan harga dinamis dari database  |
| Services            | Keamanan, layanan 24 jam, hiburan, penunjuk wisata  |
| Banner Stats        | 25+ properti, 350+ booking, 600+ pelanggan          |
| Footer              | Tautan cepat, layanan, kontak, media sosial         |

Harga kamar di halaman utama dimuat secara dinamis saat halaman dibuka:

```javascript
fetch('booking.php?action=getPrices')
  .then(response => response.json())
  .then(data => {
    document.querySelectorAll('.room__card').forEach(card => {
      const roomType = card.dataset.roomtype;
      const price = data[roomType];
      if (price) {
        card.querySelector('.room-price').textContent =
          'Rp ' + price.toLocaleString('id-ID');
      }
    });
  });
```

### 3.6 Logika Kalkulasi Harga (`main.js`)

Perhitungan harga dilakukan sepenuhnya di sisi klien dan diperbarui secara real-time setiap kali terjadi perubahan pada tanggal, pilihan kamar, atau layanan tambahan.

**Formula:**
```
Total = (Harga Kamar × Jumlah Malam) + Total Layanan Tambahan
```

**Harga tipe kamar:**

| Tipe Kamar | Harga per Malam |
|------------|-----------------|
| Standard   | Rp 500.000      |
| Deluxe     | Rp 750.000      |
| Suite      | Rp 1.200.000    |
| Ocean View | Rp 1.500.000    |

**Harga layanan tambahan:**

| Layanan              | Harga      | Keterangan             |
|----------------------|------------|------------------------|
| Sarapan              | Rp 100.000 | Dikalikan jumlah malam |
| Antar Jemput Bandara | Rp 300.000 | Sekali jalan           |
| Spa & Massage        | Rp 250.000 | Sekali sesi            |
| Parkir               | Gratis     | —                      |

Logika pemilihan kamar menggunakan dropdown bertahap: pengguna harus memilih tanggal terlebih dahulu sebelum bisa memilih lantai, kemudian sistem memuat kamar tersedia di lantai tersebut:

```javascript
function loadAvailableRooms() {
  // Hanya berjalan jika check_in, check_out, dan lantai sudah dipilih
  if (!checkInVal || !checkOutVal || !floor) {
    roomSelect.disabled = true;
    return;
  }
  fetch(`booking.php?action=getAvailableRooms&check_in=${checkInVal}&check_out=${checkOutVal}`)
    .then(res => res.json())
    .then(data => {
      const filtered = data.filter(r => r.floor == floor);
      // Render option untuk setiap kamar tersedia
    });
}
```

### 3.7 Validasi Data

**Sisi klien (JavaScript / `main.js`):**
- Check-out harus minimal 1 hari setelah check-in; jika tidak, field dikosongkan dan pesan error ditampilkan.
- Dropdown kamar dinonaktifkan (`disabled`) sampai tanggal dan lantai dipilih.
- Tombol submit dinonaktifkan setelah diklik untuk mencegah double-submit.

**Sisi server (PHP / `booking.php`):**

| Validasi            | Mekanisme                                                          |
|---------------------|--------------------------------------------------------------------|
| Field wajib kosong  | Cek `empty()` pada nama, email, telepon, kota, check_in, check_out |
| Format email        | `filter_var($email, FILTER_VALIDATE_EMAIL)`                        |
| Kamar tidak dipilih | `$room_id <= 0`                                                    |
| Konflik tanggal     | Subquery COUNT sebelum INSERT                                      |
| Sanitasi input      | `$conn->real_escape_string()` pada semua input string              |

### 3.8 Panel Admin

#### 3.8.1 Autentikasi (`admin/login.php` & `admin/logout.php`)

Admin login menggunakan username dan password yang diverifikasi secara langsung. Sesi disimpan di `$_SESSION`:

```php
if ($username === 'admin' && $password === 'admin123') {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $username;
    header('Location: index.php');
    exit;
}
```

Setiap halaman admin memanggil `requireLogin()` di baris pertama untuk menjaga agar halaman tidak bisa diakses tanpa login.

#### 3.8.2 Dashboard (`admin/index.php`)

Menampilkan tiga statistik utama dari database secara langsung:

```php
$total_booking = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$total_rooms   = $conn->query("SELECT COUNT(*) as count FROM rooms")->fetch_assoc()['count'];
$occupied_rooms = $conn->query("
    SELECT COUNT(DISTINCT b.room_id) as count FROM bookings b
    WHERE b.status IN ('confirmed', 'checked_in')
")->fetch_assoc()['count'];
```

Dashboard juga menampilkan 5 booking terbaru dan grafik occupancy per lantai (progress bar), serta melakukan auto-refresh setiap 30 detik.

#### 3.8.3 Manajemen Booking (`admin/bookings.php`)

Admin dapat mencari booking berdasarkan nama, email, atau telepon, serta mengubah status booking langsung dari dropdown di baris tabel (auto-submit on change):

```php
// Status yang tersedia:
// confirmed → checked_in → checked_out → cancelled
$conn->query("UPDATE bookings SET status = '$status' WHERE id = $booking_id");
```

Halaman ini auto-refresh setiap 60 detik.

#### 3.8.4 Status Occupancy (`admin/occupancy.php`)

Menampilkan semua kamar dikelompokkan per lantai. Setiap kartu kamar menunjukkan status (terpakai/kosong) beserta detail tamu aktif (nama, telepon, email, tanggal check-in/out, status booking):

```sql
SELECT r.id, r.room_number, r.floor, r.room_type,
       b.id as booking_id, b.nama, b.check_in, b.check_out, b.status, b.telepon, b.email
FROM rooms r
LEFT JOIN bookings b ON r.id = b.room_id
    AND b.status IN ('confirmed', 'checked_in')
ORDER BY r.floor, r.room_number
```

#### 3.8.5 Kelola Kamar & Harga (`admin/room-management.php`)

Admin dapat mengubah tipe kamar dan harga per malam melalui modal popup. Perubahan harga menggunakan mekanisme DELETE lalu INSERT untuk menghindari duplikasi:

```php
// Update harga kamar
$conn->query("DELETE FROM room_prices WHERE room_type = '$room_type'");
$conn->query("INSERT INTO room_prices (room_type, price) VALUES ('$room_type', $price)");
```

#### 3.8.6 Komponen Sidebar (`admin/_sidebar.php`)

Sidebar navigasi dibuat sebagai file terpisah dan di-include ke semua halaman admin. Sidebar menampilkan item aktif secara dinamis berdasarkan nama file saat ini:

```php
class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"
```

Pada tampilan mobile, sidebar menggunakan overlay dan tombol hamburger untuk membuka/menutup navigasi.

---

## BAB IV — PENGUJIAN

### 4.1 Skenario Pengujian

| No  | Skenario                | Input                              | Expected Output                  | Status   |
|-----|-------------------------|------------------------------------|----------------------------------|----------|
| T1  | Load harga kamar        | Buka index.html                    | Harga muncul dari database       | Berhasil |
| T2  | Pilih tanggal valid     | Check-in hari ini, Check-out besok | Kamar tersedia tampil            | Berhasil |
| T3  | Check-out < check-in    | Tanggal tidak valid                | Pesan error, field dikosongkan   | Berhasil |
| T4  | Booking berhasil        | Isi semua field, submit            | Pesan sukses + nomor booking     | Berhasil |
| T5  | Konflik tanggal         | Kamar yang sudah dipesan           | Pesan error konflik kamar        | Berhasil |
| T6  | Form kosong             | Tanpa mengisi field wajib          | Pesan "semua field harus diisi"  | Berhasil |
| T7  | Email tidak valid       | Format email salah                 | Pesan "format email tidak valid" | Berhasil |
| T8  | Kalkulasi harga         | Pilih kamar + layanan tambahan     | Total dihitung real-time         | Berhasil |
| T9  | Login admin             | admin / admin123                   | Redirect ke dashboard            | Berhasil |
| T10 | Akses admin tanpa login | Langsung akses admin/index.php     | Redirect ke login.php            | Berhasil |
| T11 | Update status booking   | Ganti dropdown di bookings.php     | Status tersimpan, badge berubah  | Berhasil |
| T12 | Edit harga kamar        | Ubah harga via modal               | Harga baru tersimpan ke database | Berhasil |
### 4.2 Hasil Pengujian

Seluruh skenario pengujian berjalan sesuai ekspektasi. Sistem berhasil menangani kondisi normal maupun kondisi tepi (edge case) seperti pemesanan di tanggal yang sama, tanggal yang sudah dipesan, dan input data yang tidak valid.

---

## BAB V — HASIL DAN PEMBAHASAN

### Hasil

Website Luxury Hotel merupakan suatu sistem berbasis web yang dirancang untuk memberikan informasi sekaligus membantu proses pemesanan kamar secara online. Website ini dibangun menggunakan teknologi PHP, MySQL, HTML, CSS, dan JavaScript melalui beberapa tahapan pengembangan sehingga menghasilkan suatu website yang berguna baik untuk menampilkan informasi hotel, mempermudah proses pemesanan kamar oleh tamu, maupun pengelolaan data pemesanan oleh admin hotel.

Adapun hasil dari website yang telah dibuat yaitu:

---

#### a. Tampilan Halaman Utama

Pada "Halaman Utama" menampilkan beberapa informasi mengenai Luxury Hotel, pada bagian atas terdapat navigation bar yang dapat digunakan sebagai shortcut menuju bagian atau halaman yang diinginkan. Teks "Home" berfungsi untuk memindahkan halaman menuju "Halaman Utama" bagian deskripsi dan gambar hotel. Teks "About" berfungsi untuk memindahkan halaman menuju bagian informasi singkat mengenai Luxury Hotel. Teks "Services" berfungsi untuk memindahkan halaman menuju bagian informasi layanan unggulan yang ditawarkan. Teks "Booking" berfungsi untuk memindahkan halaman menuju halaman pemesanan kamar. Teks "Contact" berfungsi untuk memindahkan halaman menuju bagian kontak yang menampilkan informasi alamat email hotel serta ikon media sosial seperti Facebook, Instagram, YouTube, dan Twitter yang jika diklik akan mengarahkan pengguna menuju akun media sosial Luxury Hotel. Terdapat pula tombol "Admin" yang berfungsi untuk memindahkan halaman menuju panel administrasi hotel.

Pada bagian "About" menampilkan informasi singkat mengenai Luxury Hotel beserta gambar suasana hotel, dengan tagline "Mulai Liburanmu di Sini" yang menggambarkan fokus hotel dalam memberikan akomodasi berkualitas dan pengalaman yang dipersonalisasi bagi setiap tamu. Selain itu terdapat bagian "Room" yang menampilkan tiga jenis kamar yang ditawarkan yaitu Deluxe Ocean View, Executive Cityscape Room, dan Family Garden Retreat beserta harga per malamnya yang dimuat secara dinamis dari database. Kekurangan dari halaman ini yaitu harga kamar yang ditampilkan masih bergantung pada koneksi ke database, sehingga apabila koneksi gagal maka harga tidak akan muncul secara dinamis.

*Gambar x.x Tampilan Halaman Utama (Home)*

*Gambar x.x Tampilan Halaman Utama (About)*

*Gambar x.x Tampilan Halaman Utama (Room)*

*Gambar x.x Tampilan Halaman Utama (Services)*

*Gambar x.x Tampilan Halaman Utama (Contact)*

---

#### b. Tampilan Halaman Booking

Halaman "Booking" merupakan formulir pemesanan yang digunakan tamu untuk melakukan pemesanan kamar. Halaman ini terbagi menjadi beberapa bagian yaitu:

Bagian pertama adalah **Informasi Tamu**, pada bagian ini tamu diharuskan mengisi data diri seperti nama lengkap, email, nomor telepon, dan kota asal pada kolom yang telah disediakan.

Bagian kedua adalah **Detail Pemesanan**, pada bagian ini tamu memilih tanggal check-in dan check-out yang diinginkan. Setelah tanggal dipilih, tamu dapat memilih lantai dan nomor kamar yang tersedia. Sistem secara otomatis hanya menampilkan kamar yang tersedia berdasarkan tanggal yang telah dipilih.

Bagian ketiga adalah **Permintaan Khusus**, pada bagian ini tamu dapat menuliskan catatan atau permintaan khusus seperti preferensi lantai maupun akomodasi khusus lainnya.

Bagian keempat adalah **Layanan Tambahan**, pada bagian ini tamu dapat memilih layanan tambahan yang diinginkan seperti sarapan, layanan antar jemput bandara, spa & massage, dan parkir gratis.

Bagian terakhir adalah **Ringkasan Harga**, yang menampilkan harga kamar per malam, jumlah malam, subtotal kamar, serta total keseluruhan yang dihitung secara otomatis oleh sistem. Setelah semua data terisi, tamu dapat menekan tombol "Konfirmasi Booking" untuk menyelesaikan pemesanan. Kekurangan dari halaman ini yaitu tidak adanya fitur notifikasi atau pengiriman email konfirmasi otomatis kepada tamu setelah pemesanan berhasil dilakukan.

*Gambar x.x Tampilan Halaman Booking (Informasi Tamu & Detail Pemesanan)*

*Gambar x.x Tampilan Halaman Booking (Pilih Lantai & Layanan Tambahan)*

*Gambar x.x Tampilan Halaman Booking (Ringkasan Harga)*

---

#### c. Tampilan Halaman Konfirmasi Booking

Halaman "Konfirmasi Booking" merupakan halaman yang berfungsi untuk menampilkan informasi hasil pemesanan yang telah dilakukan oleh tamu. Informasi yang ditampilkan berupa nomor booking, lantai, dan nomor kamar yang telah dipesan. Setelah pemesanan berhasil, sistem secara otomatis menyimpan data pemesanan ke dalam database beserta layanan tambahan yang dipilih oleh tamu.

Kekurangan dari halaman ini yaitu tidak adanya otomatisasi pengiriman konfirmasi pemesanan ke email tamu, sehingga tamu tidak mendapatkan bukti pemesanan secara digital. Selain itu, sistem belum dilengkapi fitur pembayaran online, sehingga proses pembayaran masih dilakukan secara terpisah di luar sistem.

*Gambar x.x Tampilan Halaman Konfirmasi Booking*

---

#### d. Tampilan Halaman Login Admin

Halaman "Login" merupakan halaman yang digunakan oleh admin agar dapat mengakses halaman panel administrasi Luxury Hotel Booking System. Pada halaman ini terdapat dua kolom input yaitu kolom "Username" dan kolom "Password" yang harus diisi oleh admin. Setelah data diisi dengan benar, admin dapat menekan tombol "Login" sehingga sistem akan memverifikasi data dan mengarahkan admin menuju halaman "Dashboard Admin". Apabila username atau password yang dimasukkan salah, maka sistem akan menampilkan pesan kesalahan dan admin tidak dapat mengakses halaman admin. Selain itu, terdapat teks "Kembali ke Website" yang berfungsi untuk mengarahkan admin kembali menuju halaman utama website apabila tidak jadi melakukan login.

*Gambar x.x Tampilan Halaman Login Admin*

---

#### e. Tampilan Halaman Dashboard Admin

Halaman "Dashboard Admin" merupakan halaman utama panel administrasi Luxury Hotel yang menampilkan ringkasan data hotel secara keseluruhan. Pada bagian atas halaman terdapat tiga kartu statistik yaitu "Total Booking" yang menampilkan jumlah keseluruhan pemesanan, "Total Kamar" yang menampilkan jumlah keseluruhan kamar yang tersedia, dan "Kamar Terpakai" yang menampilkan jumlah kamar yang sedang dalam status terpakai.

Di bawah kartu statistik terdapat tabel "Booking Terbaru" yang menampilkan data lima pemesanan terakhir meliputi nomor booking, nama tamu, lantai, nomor kamar, tanggal check-in, tanggal check-out, serta status pemesanan.

Pada bagian kiri halaman terdapat sidebar navigasi yang memuat menu "Dashboard", "Occupancy", "Booking", dan "Kelola Kamar" yang masing-masing berfungsi untuk mengarahkan admin menuju halaman yang sesuai. Pada bagian bawah sidebar terdapat informasi username admin yang sedang aktif serta tombol "Logout" yang berfungsi untuk mengakhiri sesi admin dan kembali ke halaman login.

*Gambar x.x Tampilan Halaman Dashboard Admin*

---

#### f. Tampilan Halaman Status Occupancy Kamar

Halaman "Status Occupancy Kamar" merupakan halaman yang digunakan untuk menampilkan status dan informasi tamu per kamar secara real-time. Pada bagian atas terdapat keterangan legenda warna yaitu warna hijau menandakan kamar sedang terpakai dan warna abu-abu menandakan kamar dalam kondisi kosong. Kamar yang sedang terpakai akan menampilkan informasi detail tamu seperti nama, nomor telepon, email, tanggal check-in, tanggal check-out, serta status pemesanan. Sedangkan kamar yang kosong akan menampilkan keterangan "Kamar kosong — siap dipesan". Data kamar ditampilkan berdasarkan lantai sehingga memudahkan admin dalam memantau kondisi setiap kamar per lantai.

*Gambar x.x Tampilan Halaman Status Occupancy Kamar*

---

#### g. Tampilan Halaman Manajemen Booking

Halaman "Booking" merupakan halaman yang digunakan admin untuk menampilkan dan mengelola seluruh data pemesanan tamu. Data yang ditampilkan meliputi nomor booking, nama tamu, email, nomor telepon, lantai, nomor kamar, tanggal check-in, tanggal check-out, total harga, serta status pemesanan. Status pemesanan yang tersedia antara lain Confirmed, Checked-in, Checked-out, dan Cancelled. Admin dapat mengubah status pemesanan secara langsung melalui halaman ini. Kekurangan dari halaman ini yaitu tidak adanya fitur pencarian data sehingga admin harus mencari data pemesanan secara manual apabila jumlah data sudah sangat banyak.

*Gambar x.x Tampilan Halaman Manajemen Booking*

---

#### h. Tampilan Halaman Kelola Kamar & Harga

Halaman "Kelola Kamar & Harga" merupakan halaman yang digunakan admin untuk mengelola tipe kamar dan harga kamar per malam. Pada bagian atas terdapat empat kartu harga yang menampilkan harga per malam untuk masing-masing tipe kamar yaitu Standard sebesar Rp 500.000, Deluxe sebesar Rp 750.000, Suite sebesar Rp 1.200.000, dan Ocean View sebesar Rp 1.500.000. Admin dapat mengubah harga setiap tipe kamar dengan menekan tombol "Edit Harga". Di bawahnya terdapat tabel "Daftar Kamar" yang menampilkan data seluruh kamar meliputi lantai, nomor kamar, dan tipe kamar. Admin dapat mengubah tipe kamar dengan menekan tombol "Edit" pada setiap baris data kamar.

*Gambar x.x Tampilan Halaman Kelola Kamar & Harga*

---

### Pembahasan

Website Luxury Hotel merupakan sistem berbasis web yang dirancang untuk memberikan layanan informasi dan pemesanan kamar hotel secara online. Dari hasil implementasi, website berhasil menampilkan halaman utama yang informatif dengan fitur navigasi yang memudahkan akses ke berbagai bagian seperti deskripsi hotel, jenis kamar, layanan, dan kontak hotel.

Sistem pemesanan dirancang dalam satu halaman yang terintegrasi mulai dari pengisian data diri tamu, pemilihan tanggal check-in dan check-out, pemilihan lantai dan nomor kamar yang tersedia secara real-time, pemilihan layanan tambahan, hingga kalkulasi harga yang dilakukan secara otomatis oleh sistem. Hal ini memberikan kemudahan bagi tamu dalam melakukan pemesanan kamar tanpa harus berpindah-pindah halaman.

Fungsionalitas panel administrasi juga telah dikembangkan dengan baik, meliputi pemantauan status occupancy kamar per lantai, manajemen data pemesanan tamu beserta pembaruan statusnya, serta pengelolaan tipe kamar dan harga per malam. Namun dari hasil uji coba, masih ditemukan beberapa kekurangan yang perlu diperhatikan seperti belum adanya fitur notifikasi atau pengiriman email konfirmasi otomatis kepada tamu setelah pemesanan berhasil, belum tersedianya fitur pembayaran online sehingga transaksi masih dilakukan secara terpisah di luar sistem, serta belum adanya fitur pencarian data pada halaman manajemen booking yang dapat menyulitkan admin dalam mencari data pemesanan apabila jumlah data sudah sangat banyak.

---

## BAB VI — PENUTUP

### 6.1 Kesimpulan

Website Luxury Hotel merupakan sistem berbasis web yang berhasil dikembangkan untuk memberikan kemudahan dalam penyampaian informasi hotel dan proses pemesanan kamar secara online. Sistem ini mampu menampilkan informasi penting seperti jenis kamar, layanan, lokasi, dan kontak hotel dalam satu platform yang terintegrasi. Selain itu, sistem juga telah mampu menjalankan proses pemesanan kamar dalam satu halaman yang mencakup pengisian data diri tamu, pemilihan tanggal, pemilihan lantai dan nomor kamar secara real-time, pemilihan layanan tambahan, serta kalkulasi harga yang dilakukan secara otomatis oleh sistem.
Fitur panel administrasi pun telah berjalan dengan baik, seperti pemantauan status occupancy kamar per lantai, manajemen data pemesanan beserta pembaruan statusnya, dan pengelolaan tipe kamar serta harga per malam yang mempermudah proses pengolahan data oleh pihak hotel. Dengan demikian, website ini telah memenuhi tujuan yang telah ditetapkan di awal, yaitu menciptakan sistem pemesanan hotel yang informatif, efisien, dan dapat diakses secara daring.

### 6.2 Saran Pengembangan

Meskipun website Luxury Hotel telah berjalan dengan baik, masih terdapat beberapa hal yang dapat ditingkatkan untuk pengembangan ke depan. Disarankan agar sistem dilengkapi dengan fitur notifikasi atau pengiriman email konfirmasi otomatis kepada tamu setelah pemesanan berhasil dilakukan, sehingga tamu mendapatkan bukti pemesanan secara digital. Selain itu, penambahan fitur pembayaran online melalui payment gateway dapat meningkatkan kemudahan transaksi bagi tamu sekaligus mengurangi ketergantungan pada proses manual. Dari segi keamanan, disarankan agar sistem diperbarui menggunakan prepared statements untuk mencegah potensi SQL Injection secara lebih robust. Terakhir, penambahan fitur pencarian data pada halaman manajemen booking akan sangat membantu admin dalam mempermudah pencarian data pemesanan apabila jumlah data sudah semakin banyak.

---

*Laporan ini dibuat berdasarkan analisis kode sumber proyek Luxury Hotel Booking System.*  
*Copyright © 2026 Luxury Hotel. All rights reserved.*
