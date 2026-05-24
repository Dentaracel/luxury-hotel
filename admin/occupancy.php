<?php
require_once 'config.php';
requireLogin();

$query = "
    SELECT r.id, r.room_number, r.floor, r.room_type,
           b.id as booking_id, b.nama, b.check_in, b.check_out, b.status, b.telepon, b.email
    FROM rooms r
    LEFT JOIN bookings b ON r.id = b.room_id AND b.status IN ('confirmed', 'checked_in')
    ORDER BY r.floor, r.room_number
";

$rooms = $conn->query($query);
if (!$rooms) die("Database Error: " . $conn->error);

$floors_data = array();
while ($row = $rooms->fetch_assoc()) {
    $floor = $row['floor'];
    if (!isset($floors_data[$floor])) $floors_data[$floor] = array();
    $floors_data[$floor][] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Occupancy - Admin Panel Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body>

<?php include '_sidebar.php'; ?>

<div class="main-content">
    <div class="header">
        <h1>🏢 Status Occupancy Kamar</h1>
        <p>Status dan informasi tamu per kamar</p>
    </div>

    <div class="legend">
        <span><i class="ri-circle-fill" style="color:#4CAF50;"></i> Terpakai</span>
        <span><i class="ri-circle-fill" style="color:#9E9E9E;"></i> Kosong</span>
    </div>

    <?php foreach ($floors_data as $floor => $rooms_list): ?>
        <div class="section">
            <h2>🏢 Lantai <?php echo $floor; ?></h2>
            <div class="rooms-grid">
                <?php foreach ($rooms_list as $room): ?>
                    <div class="room-card <?php echo $room['booking_id'] ? 'occupied' : 'empty'; ?>">
                        <div class="room-header">
                            <h3>Kamar <?php echo $room['room_number']; ?></h3>
                            <span class="room-type"><?php echo ucfirst($room['room_type']); ?></span>
                        </div>
                        <?php if ($room['booking_id']): ?>
                            <div class="room-info">
                                <p><strong>Nama:</strong> <?php echo htmlspecialchars($room['nama']); ?></p>
                                <p><strong>Telepon:</strong> <?php echo htmlspecialchars($room['telepon']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($room['email']); ?></p>
                                <p><strong>Check-in:</strong> <?php echo date('d/m/Y', strtotime($room['check_in'])); ?></p>
                                <p><strong>Check-out:</strong> <?php echo date('d/m/Y', strtotime($room['check_out'])); ?></p>
                                <p><strong>Status:</strong>
                                    <span class="badge status-<?php echo $room['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $room['status'])); ?>
                                    </span>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="room-info">
                                <p style="color:#999; font-style:italic;">Kamar kosong — siap dipesan</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    setTimeout(() => location.reload(), 60000);
</script>
</body>
</html>
