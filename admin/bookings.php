<?php
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $booking_id = intval($_POST['booking_id']);
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE bookings SET status = '$status' WHERE id = $booking_id");
}

$search_term = '';
$where_clause = '1=1';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $conn->real_escape_string($_GET['search']);
    $where_clause = "(nama LIKE '%$search_term%' OR email LIKE '%$search_term%' OR telepon LIKE '%$search_term%')";
}

$bookings = $conn->query("
    SELECT b.*, COALESCE(r.room_number, '-') as room_number
    FROM bookings b
    LEFT JOIN rooms r ON b.room_id = r.id
    WHERE $where_clause
    ORDER BY b.tanggal_booking DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking - Admin Panel Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body>

<?php include '_sidebar.php'; ?>

<div class="main-content">
    <div class="header">
        <h1>📋 Manajemen Booking</h1>
        <p>Kelola semua booking dan update status</p>
    </div>

    <div class="section">
        <div class="search-box">
            <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap;">
                <input type="text" name="search" placeholder="Cari nama, email, atau telepon..."
                    value="<?php echo htmlspecialchars($search_term); ?>"
                    style="flex:1; min-width:200px; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:15px; min-height:48px;">
                <button type="submit" style="padding:12px 20px; background:#667eea; color:white; border:none; border-radius:8px; cursor:pointer; min-height:48px; font-size:15px;">
                    <i class="ri-search-line"></i> Cari
                </button>
                <?php if ($search_term): ?>
                    <a href="bookings.php" style="padding:12px 20px; background:#f5f5f5; color:#333; border:1px solid #ddd; border-radius:8px; text-decoration:none; display:flex; align-items:center; min-height:48px;">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="section">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Lantai</th>
                        <th>Kamar</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bookings && $bookings->num_rows > 0):
                        while ($row = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['telepon']); ?></td>
                            <td>Lantai <?php echo $row['floor'] ?? '-'; ?></td>
                            <td><?php echo $row['room_number'] ?? '-'; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['check_in'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['check_out'])); ?></td>
                            <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                            <td><span class="badge status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                    <select name="status" onchange="this.form.submit();"
                                        style="padding:8px; border:1px solid #ddd; border-radius:6px; font-size:13px; min-height:38px; cursor:pointer;">
                                        <option value="confirmed" <?php echo $row['status']=='confirmed'?'selected':''; ?>>Confirmed</option>
                                        <option value="checked_in" <?php echo $row['status']=='checked_in'?'selected':''; ?>>Check-in</option>
                                        <option value="checked_out" <?php echo $row['status']=='checked_out'?'selected':''; ?>>Check-out</option>
                                        <option value="cancelled" <?php echo $row['status']=='cancelled'?'selected':''; ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="11" style="text-align:center; padding:40px; color:#999;">Tidak ada booking ditemukan</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    setTimeout(() => location.reload(), 60000);
</script>
</body>
</html>
