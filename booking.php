<?php
require_once 'config.php';

$success_message = '';
$error_message = '';

function getAvailableRooms($conn, $check_in, $check_out) {
    $check_in = $conn->real_escape_string($check_in);
    $check_out = $conn->real_escape_string($check_out);

    $sql = "SELECT r.*
            FROM rooms r
            WHERE (SELECT COUNT(*) FROM bookings b
                   WHERE b.room_id = r.id
                   AND b.status != 'cancelled'
                   AND ((b.check_in <= '$check_out' AND b.check_out > '$check_in'))) = 0
            ORDER BY r.floor, r.room_number";

    $result = $conn->query($sql);
    $available = array();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $available[] = $row;
        }
    }
    return $available;
}

if (isset($_GET['action']) && $_GET['action'] == 'getAvailableRooms') {
    if (isset($_GET['check_in']) && isset($_GET['check_out'])) {
        $available = getAvailableRooms($conn, $_GET['check_in'], $_GET['check_out']);
        header('Content-Type: application/json');
        echo json_encode($available);
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'getPrices') {
    $prices_result = $conn->query("SELECT room_type, price FROM room_prices");
    $prices = array();
    if ($prices_result) {
        while ($row = $prices_result->fetch_assoc()) {
            $prices[$row['room_type']] = (int)$row['price'];
        }
    }
    if (empty($prices)) {
        $prices = array('standard' => 500000, 'deluxe' => 750000, 'suite' => 1200000, 'oceanview' => 1500000);
    }
    header('Content-Type: application/json');
    echo json_encode($prices);
    exit;
}

function getRoom($conn, $room_id) {
    $sql = "SELECT * FROM rooms WHERE id = $room_id";
    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

function getServicePrice($service) {
    switch($service) {
        case 'breakfast': return 100000;
        case 'airport': return 300000;
        case 'spa': return 250000;
        default: return 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $conn->real_escape_string(trim($_POST['nama']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $telepon = $conn->real_escape_string(trim($_POST['telepon']));
    $kota = $conn->real_escape_string(trim($_POST['kota']));
    $check_in = $conn->real_escape_string($_POST['check_in']);
    $check_out = $conn->real_escape_string($_POST['check_out']);
    $jumlah_tamu = (int)$_POST['guests'];
    $jumlah_kamar = (int)$_POST['rooms'];
    $tipe_kamar = $conn->real_escape_string($_POST['roomType']);
    $room_id = (int)$_POST['selectedRoom'];
    $floor = (int)$_POST['selectedFloor'];
    $catatan = $conn->real_escape_string(trim($_POST['notes']));
    $total_harga = (float)$_POST['totalPrice'];

    if (empty($nama) || empty($email) || empty($telepon) || empty($kota) || empty($check_in) || empty($check_out)) {
        $error_message = "Semua field yang bertanda (*) harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid!";
    } elseif ($room_id <= 0) {
        $error_message = "Silakan pilih kamar terlebih dahulu!";
    } else {
        $verify_sql = "SELECT COUNT(*) as conflict_count FROM bookings b
                       WHERE b.room_id = $room_id
                       AND b.status != 'cancelled'
                       AND ((b.check_in <= '$check_out' AND b.check_out > '$check_in'))";
        $verify_result = $conn->query($verify_sql);
        $conflict = $verify_result->fetch_assoc()['conflict_count'];

        if ($conflict > 0) {
            $error_message = "Maaf, kamar ini sudah dipesan untuk tanggal yang dipilih!";
        } else {
            $tanggal_booking = date('Y-m-d H:i:s');
            $status = 'confirmed';

            $sql = "INSERT INTO bookings (nama, email, telepon, kota, check_in, check_out, jumlah_tamu, jumlah_kamar, tipe_kamar, room_id, floor, catatan, total_harga, tanggal_booking, status)
                    VALUES ('$nama', '$email', '$telepon', '$kota', '$check_in', '$check_out', $jumlah_tamu, $jumlah_kamar, '$tipe_kamar', $room_id, $floor, '$catatan', $total_harga, '$tanggal_booking', '$status')";

            if ($conn->query($sql) === TRUE) {
                $booking_id = $conn->insert_id;
                $room_data = getRoom($conn, $room_id);

                if (isset($_POST['services']) && is_array($_POST['services'])) {
                    foreach ($_POST['services'] as $service) {
                        $service = $conn->real_escape_string($service);
                        $harga_service = getServicePrice($service);
                        $conn->query("INSERT INTO booking_services (booking_id, nama_service, harga) VALUES ($booking_id, '$service', '$harga_service')");
                    }
                }

                $success_message = "Pesanan Anda berhasil! Nomor Booking: #$booking_id | Lantai: $floor | Kamar: " . ($room_data['room_number'] ?? '-');
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
    <title>Booking - Luxury Hotel</title>
  </head>
  <body>
    <!-- Nav -->
    <div style="background: var(--primary-color); padding: 1rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
      <h3 style="color: #fff; font-size: 1.3rem; font-weight: bold;">Luxury Hotel</h3>
      <a href="index.html" class="btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;"><i class="ri-arrow-left-line"></i> Kembali</a>
    </div>

    <div class="booking__section">
      <div class="booking__header">
        <h1>Pesan Kamar Impian Anda</h1>
        <p>Nikmati kenyamanan dan kemewahan di Luxury Hotel</p>
      </div>

      <div class="booking__form__section">
        <h2 style="margin-bottom: 2rem; color: var(--text-dark);">Detail Pemesanan</h2>

        <?php if (!empty($success_message)): ?>
          <div style="display: block; margin-bottom: 2rem; padding: 1.25rem; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 0.5rem; color: #155724; font-weight: 500;">
            <i class="ri-check-circle-line"></i> <?php echo $success_message; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
          <div style="display: block; margin-bottom: 2rem; padding: 1.25rem; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 0.5rem; color: #721c24; font-weight: 500;">
            <i class="ri-alert-line"></i> <?php echo $error_message; ?>
          </div>
        <?php endif; ?>

        <form id="bookingForm" method="POST" action="">

          <!-- Informasi Tamu -->
          <div class="form__group">
            <h3 style="color: var(--text-dark); margin-bottom: 1rem; font-size: 1.2rem;">
              <i class="ri-user-line"></i> Informasi Tamu
            </h3>
            <div class="form__row">
              <div>
                <label class="form__label">Nama Lengkap *</label>
                <div class="input__with__icon">
                  <i class="ri-user-fill"></i>
                  <input type="text" name="nama" class="form__input" placeholder="Nama lengkap Anda" required />
                </div>
              </div>
              <div>
                <label class="form__label">Email *</label>
                <div class="input__with__icon">
                  <i class="ri-mail-line"></i>
                  <input type="email" name="email" class="form__input" placeholder="Email Anda" required />
                </div>
              </div>
            </div>
            <div class="form__row">
              <div>
                <label class="form__label">Nomor Telepon *</label>
                <div class="input__with__icon">
                  <i class="ri-phone-line"></i>
                  <input type="tel" name="telepon" class="form__input" placeholder="08xxxxxxxxxx" required />
                </div>
              </div>
              <div>
                <label class="form__label">Kota/Asal *</label>
                <div class="input__with__icon">
                  <i class="ri-map-pin-line"></i>
                  <input type="text" name="kota" class="form__input" placeholder="Kota asal Anda" required />
                </div>
              </div>
            </div>
          </div>

          <!-- Detail Pemesanan -->
          <div class="form__group">
            <h3 style="color: var(--text-dark); margin-bottom: 1rem; font-size: 1.2rem;">
              <i class="ri-calendar-line"></i> Detail Pemesanan
            </h3>
            <div class="form__row">
              <div>
                <label class="form__label">Check-In *</label>
                <div class="input__with__icon">
                  <i class="ri-calendar-2-fill"></i>
                  <input type="date" name="check_in" class="form__input" id="checkIn" required />
                </div>
              </div>
              <div>
                <label class="form__label">Check-Out *</label>
                <div class="input__with__icon">
                  <i class="ri-calendar-2-fill"></i>
                  <input type="date" name="check_out" class="form__input" id="checkOut" required />
                  <small style="display:block; margin-top:5px; color:#e74c3c; font-size:12px;" id="checkOutError"></small>
                </div>
              </div>
            </div>
            <input type="hidden" name="guests" id="guests" value="1" />
            <input type="hidden" name="rooms" id="rooms" value="1" />
          </div>

          <!-- Pilih Lantai & Kamar -->
          <div class="form__group">
            <h3 style="color: var(--text-dark); margin-bottom: 1rem; font-size: 1.2rem;">
              <i class="ri-building-line"></i> Pilih Lantai & Nomor Kamar
            </h3>
            <div class="form__row">
              <div>
                <label class="form__label">Lantai *</label>
                <div class="input__with__icon">
                  <i class="ri-building-2-line"></i>
                  <select name="selectedFloor" id="selectedFloor" class="form__input" required>
                    <option value="">-- Pilih Lantai --</option>
                    <option value="1">Lantai 1</option>
                    <option value="2">Lantai 2</option>
                    <option value="3">Lantai 3</option>
                  </select>
                </div>
              </div>
              <div>
                <label class="form__label">Nomor Kamar *</label>
                <div class="input__with__icon">
                  <i class="ri-door-open-line"></i>
                  <select name="selectedRoom" id="selectedRoom" class="form__input" required disabled>
                    <option value="">-- Pilih Kamar Terlebih Dahulu --</option>
                  </select>
                </div>
              </div>
            </div>
            <div style="margin-top:1rem; padding:1rem; background:#e7f3ff; border-left:4px solid #2196F3; border-radius:4px;">
              <p style="margin:0; color:#1976D2; font-size:0.9rem;">
                <i class="ri-information-line"></i> <strong>Info:</strong> Lengkapi tanggal check-in & check-out terlebih dahulu, lalu pilih lantai untuk melihat kamar tersedia.
              </p>
            </div>
          </div>

          <!-- Permintaan Khusus -->
          <div class="form__group">
            <h3 style="color: var(--text-dark); margin-bottom: 1rem; font-size: 1.2rem;">
              <i class="ri-layout-grid-line"></i> Permintaan Khusus
            </h3>
            <label class="form__label">Catatan/Permintaan Khusus</label>
            <textarea name="notes" class="form__textarea" placeholder="Contoh: preferensi lantai, akomodasi khusus, dll..."></textarea>
          </div>

          <!-- Layanan Tambahan -->
          <div class="form__group">
            <h3 style="color: var(--text-dark); margin-bottom: 1rem; font-size: 1.2rem;">
              <i class="ri-star-line"></i> Layanan Tambahan
            </h3>
            <div style="display:grid; gap:1rem;">
              <label style="display:flex; align-items:center; gap:0.75rem; cursor:pointer; padding:0.75rem; border:1px solid #e0e0e0; border-radius:6px;">
                <span style="color:var(--text-dark);">🍳 Sarapan</span>
              </label>
              <label style="display:flex; align-items:center; gap:0.75rem; cursor:pointer; padding:0.75rem; border:1px solid #e0e0e0; border-radius:6px;">
                <span style="color:var(--text-dark);">✈️ Layanan Antar Jemput Bandara</span>
              </label>
              <label style="display:flex; align-items:center; gap:0.75rem; cursor:pointer; padding:0.75rem; border:1px solid #e0e0e0; border-radius:6px;">
                <span style="color:var(--text-dark);">💆 Spa & Massage</span>
              </label>
              <label style="display:flex; align-items:center; gap:0.75rem; cursor:pointer; padding:0.75rem; border:1px solid #e0e0e0; border-radius:6px;">
                <span style="color:var(--text-dark);">🚗 Parkir Gratis</span>
              </label>
            </div>
          </div>

          <input type="hidden" name="roomType" id="roomType" value="" />

          <!-- Ringkasan Harga -->
          <div class="price__summary">
            <h3 style="margin-bottom:1.5rem; color:var(--text-dark);">
              <i class="ri-calculator-line"></i> Ringkasan Harga
            </h3>
            <div class="price__item">
              <span>Kamar (per malam):</span>
              <span id="roomPrice">Rp 0</span>
            </div>
            <div class="price__item">
              <span>Jumlah Malam:</span>
              <span id="nights">0</span>
            </div>
            <div class="price__item">
              <span>Subtotal Kamar:</span>
              <span id="subtotal">Rp 0</span>
            </div>
            <div class="price__item">
              <span>Layanan Tambahan:</span>
              <span id="servicesTotal">Rp 0</span>
            </div>
            <div class="price__item total">
              <span>TOTAL:</span>
              <span id="totalPrice">Rp 0</span>
            </div>
          </div>

          <input type="hidden" name="totalPrice" id="totalPriceInput" value="0" />

          <div class="form__actions">
            <button type="reset" class="btn btn__secondary">Reset Form</button>
            <button type="submit" class="btn">Konfirmasi Booking</button>
          </div>
        </form>
      </div>
    </div>

    <div style="background:var(--text-dark); color:var(--text-light); text-align:center; padding:1rem; font-size:0.85rem;">
      Copyright © 2026 Luxury Hotel. All rights reserved.
    </div>

    <script src="main.js"></script>
  </body>
</html>
