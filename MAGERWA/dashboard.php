<?php
include 'connection.php';
enforce_admin_gate();
$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;
$total_stmt = $conn->query("SELECT COUNT(*) AS total FROM assignments");
$total_rows = $total_stmt->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

$query = "SELECT a.plate_number, a.assigned_at, c.names AS client_name, c.phone, v.company, v.model_name 
          FROM assignments a
          JOIN clients c ON a.client_id = c.id
          JOIN vehicles v ON a.vehicle_id = v.id
          ORDER BY a.id DESC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$assignments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MAGERWA </title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <div class="page-header">
                <h1>Overview.</h1>
            </div>

            <div class="card">
                <h3 style="margin-bottom:15px;">Active Operations Log.</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Plate Number</th>
                            <th>Client</th>
                            <th>Phone Number</th>
                            <th>Vehicle</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($assignments->num_rows == 0): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; color:var(--text-muted);">No current linked logs .</td>
                        </tr>
                        <?php endif; ?>
                        <?php while($row = $assignments->fetch_assoc()): ?>
                        <tr>
                            <td><span style="background:#dfe6e9; padding:5px 10px; border-radius:4px; font-weight:bold; border:1px solid #b2bec3;"><?php echo htmlspecialchars($row['plate_number']); ?></span></td>
                            <td><strong><?php echo htmlspecialchars($row['client_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['company'] . " " . $row['model_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['assigned_at']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="dashboard.php?page=<?php echo $i; ?>" class="<?php if($page == $i) echo 'active'; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>