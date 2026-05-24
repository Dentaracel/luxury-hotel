<?php
require_once 'config.php';
requireLogin();

$total_booking = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$total_rooms = $conn->query("SELECT COUNT(*) as count FROM rooms")->fetch_assoc()['count'];
$occupied_rooms = $conn->query("
    SELECT COUNT(DISTINCT b.room_id) as count
    FROM bookings b
    WHERE b.status IN ('confirmed', 'checked_in')
")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body>

<?php include '_sidebar.php'; ?>

<div class="main-content">
    <div class="header">
        <h1>📊 Dashboard</h1>
        <p>Ringkasan Data Hotel Booking System</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e3f2fd;">
                <i class="ri-file-list-line"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Booking</p>
                <p class="stat-value"><?php echo $total_booking; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#f3e5f5;">
                <i class="ri-door-open-line"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Kamar</p>
                <p class="stat-value"><?php echo $total_rooms; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f5e9;">
                <i class="ri-checkbox-circle-line"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Kamar Terpakai</p>
                <p class="stat-value"><?php echo $occupied_rooms; ?></p>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="section">
        <h2>📋 Booking Terbaru</h2>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Lantai</th>
                        <th>Kamar</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("
                        SELECT b.id, b.nama, b.floor, r.room_number, b.check_in, b.check_out, b.status
                        FROM bookings b
                        LEFT JOIN rooms r ON b.room_id = r.id
                        ORDER BY b.tanggal_booking DESC
                        LIMIT 5
                    ");
                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td>Lantai <?php echo $row['floor']; ?></td>
                            <td><?php echo $row['room_number'] ?? '-'; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['check_in'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['check_out'])); ?></td>
                            <td><span class="badge status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <tr><td colspan="7" style="text-align:center; padding:30px; color:#999;">Belum ada booking</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Occupancy per Lantai -->
    <div class="section">
        <h2>🏢 Occupancy per Lantai</h2>
        <div class="occupancy-grid">
            <?php
            $floors = $conn->query("
                SELECT
                    r.floor,
                    COUNT(r.id) as total,
                    SUM(CASE WHEN b.id IS NOT NULL THEN 1 ELSE 0 END) as occupied
                FROM rooms r
                LEFT JOIN bookings b ON r.id = b.room_id
                    AND b.status IN ('confirmed', 'checked_in')
                GROUP BY r.floor
                ORDER BY r.floor
            ");
            if ($floors && $floors->num_rows > 0):
                while ($floor = $floors->fetch_assoc()):
                    $pct = $floor['total'] > 0 ? ($floor['occupied'] / $floor['total']) * 100 : 0;
            ?>
                <div class="occupancy-card">
                    <h3>Lantai <?php echo $floor['floor']; ?></h3>
                    <div class="occupancy-bar">
                        <div class="occupancy-fill" style="width:<?php echo $pct; ?>%"></div>
                    </div>
                    <p class="occupancy-text">
                        <?php echo $floor['occupied']; ?> dari <?php echo $floor['total']; ?> kamar
                        (<?php echo round($pct); ?>%)
                    </p>
                </div>
            <?php
                endwhile;
            endif;
            ?>
        </div>
    </div>
</div>

<script>
    setTimeout(() => location.reload(), 30000);
</script>
</body>
</html>
