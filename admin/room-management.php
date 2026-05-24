<?php
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_room') {
        $room_id = intval($_POST['room_id']);
        $room_type = $conn->real_escape_string($_POST['room_type']);
        $conn->query("UPDATE rooms SET room_type = '$room_type' WHERE id = $room_id");
    }
    if ($_POST['action'] === 'update_price') {
        $room_type = $conn->real_escape_string($_POST['room_type']);
        $price = floatval($_POST['price']);
        $conn->query("DELETE FROM room_prices WHERE room_type = '$room_type'");
        $conn->query("INSERT INTO room_prices (room_type, price) VALUES ('$room_type', $price)");
        $success = "Harga berhasil diperbarui!";
    }
}

$prices_result = $conn->query("SELECT * FROM room_prices");
$prices = array();
while ($row = $prices_result->fetch_assoc()) {
    $prices[$row['room_type']] = $row['price'];
}

$default_prices = array('standard' => 500000, 'deluxe' => 750000, 'suite' => 1200000, 'oceanview' => 1500000);
$rooms = $conn->query("SELECT * FROM rooms ORDER BY floor, room_number");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kamar - Admin Panel Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="admin-styles.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .modal.show { display: flex; }
        .modal-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .modal-box h2 { margin-bottom: 20px; font-size: 20px; }
        .modal-box label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
        .modal-box select, .modal-box input[type="number"] {
            width: 100%; padding: 12px; border: 2px solid #eee;
            border-radius: 8px; font-size: 15px; margin-bottom: 20px; min-height: 48px;
        }
        .modal-box select:focus, .modal-box input:focus {
            outline: none; border-color: #667eea;
        }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
        .btn-cancel {
            padding: 10px 20px; background: #f5f5f5; border: 1px solid #ddd;
            border-radius: 8px; cursor: pointer; font-size: 14px; min-height: 44px;
        }
        .btn-save {
            padding: 10px 20px; background: #667eea; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 14px; min-height: 44px;
        }
        .price-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
        }
        .price-card {
            padding: 20px; border: 1px solid #eee; border-radius: 10px;
            background: #f9f9f9; text-align: center;
        }
        .price-card h4 { text-transform: capitalize; color: #333; margin-bottom: 8px; font-size: 15px; }
        .price-card .price-val { font-size: 18px; font-weight: bold; color: #667eea; margin-bottom: 12px; }
        .btn-edit-price {
            padding: 8px 16px; background: #667eea; color: white; border: none;
            border-radius: 6px; cursor: pointer; font-size: 13px; min-height: 38px;
        }
    </style>
</head>
<body>

<?php include '_sidebar.php'; ?>

<div class="main-content">
    <div class="header">
        <h1>⚙️ Kelola Kamar & Harga</h1>
        <p>Manage tipe kamar dan harga per malam</p>
    </div>

    <?php if (isset($success)): ?>
        <div style="padding:14px 20px; background:#e8f5e9; border-left:4px solid #4CAF50; border-radius:8px; margin-bottom:20px; color:#2e7d32; font-weight:500;">
            <i class="ri-check-circle-line"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <!-- Harga Kamar -->
    <div class="section">
        <h2>💰 Harga Kamar per Malam</h2>
        <div class="price-grid">
            <?php foreach ($default_prices as $type => $default_price):
                $current = $prices[$type] ?? $default_price; ?>
                <div class="price-card">
                    <h4><?php echo $type === 'oceanview' ? 'Ocean View' : ucfirst($type); ?></h4>
                    <p class="price-val">Rp <?php echo number_format($current, 0, ',', '.'); ?></p>
                    <button class="btn-edit-price" onclick="editPrice('<?php echo $type; ?>', <?php echo $current; ?>)">
                        <i class="ri-edit-line"></i> Edit Harga
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Daftar Kamar -->
    <div class="section">
        <h2>📋 Daftar Kamar</h2>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Lantai</th>
                        <th>Nomor Kamar</th>
                        <th>Tipe Kamar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($room = $rooms->fetch_assoc()): ?>
                        <tr>
                            <td>Lantai <?php echo $room['floor']; ?></td>
                            <td><?php echo $room['room_number']; ?></td>
                            <td style="text-transform:capitalize;"><?php echo $room['room_type']; ?></td>
                            <td>
                                <button onclick="editRoom(<?php echo $room['id']; ?>, '<?php echo $room['room_type']; ?>')"
                                    style="padding:8px 14px; background:#667eea; color:white; border:none; border-radius:6px; cursor:pointer; font-size:13px; min-height:38px;">
                                    <i class="ri-edit-line"></i> Edit
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Room -->
<div id="roomModal" class="modal">
    <div class="modal-box">
        <h2><i class="ri-door-open-line"></i> Edit Tipe Kamar</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_room">
            <input type="hidden" name="room_id" id="roomId">
            <label>Pilih Tipe Kamar:</label>
            <select name="room_type" id="roomType">
                <option value="standard">Standard</option>
                <option value="deluxe">Deluxe</option>
                <option value="suite">Suite</option>
                <option value="oceanview">Ocean View</option>
            </select>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('roomModal')">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Price -->
<div id="priceModal" class="modal">
    <div class="modal-box">
        <h2><i class="ri-money-dollar-circle-line"></i> Edit Harga Kamar</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_price">
            <input type="hidden" name="room_type" id="priceType">
            <label>Harga per Malam (Rp):</label>
            <input type="number" name="price" id="priceValue" min="0" step="50000" required>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('priceModal')">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editRoom(id, type) {
        document.getElementById('roomId').value = id;
        document.getElementById('roomType').value = type;
        document.getElementById('roomModal').classList.add('show');
    }
    function editPrice(type, price) {
        document.getElementById('priceType').value = type;
        document.getElementById('priceValue').value = price;
        document.getElementById('priceModal').classList.add('show');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('show');
    }
    document.querySelectorAll('.modal').forEach(m => {
        m.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('show');
        });
    });
</script>
</body>
</html>
