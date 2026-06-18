<?php
include 'connection.php';
enforce_admin_gate();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create') {
    $client_id = $_POST['client_id'];
    $vehicle_id = $_POST['vehicle_id'];
    $plate_number = $_POST['plate_number'];

    $stmt = $conn->prepare("INSERT INTO assignments (client_id, vehicle_id, plate_number) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $client_id, $vehicle_id, $plate_number);
    $stmt->execute();
    $stmt->close();
    header("Location: link_records.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id = $_POST['id'];
    $client_id = $_POST['client_id'];
    $vehicle_id = $_POST['vehicle_id'];
    $plate_number = $_POST['plate_number'];

    $stmt = $conn->prepare("UPDATE assignments SET client_id = ?, vehicle_id = ?, plate_number = ? WHERE id = ?");
    $stmt->bind_param("iisi", $client_id, $vehicle_id, $plate_number, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: link_records.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM assignments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: link_records.php");
    exit();
}

$edit_link = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM assignments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_link = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_param = "%" . $search . "%";

$query = "SELECT a.id, a.plate_number, a.client_id, a.vehicle_id, c.names AS client_name, v.company, v.model_name 
          FROM assignments a
          JOIN clients c ON a.client_id = c.id
          JOIN vehicles v ON a.vehicle_id = v.id
          WHERE a.plate_number LIKE ? OR c.names LIKE ? OR v.company LIKE ? OR v.model_name LIKE ?
          ORDER BY a.id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
$stmt->execute();
$assignments = $stmt->get_result();
$stmt->close();

$clients = $conn->query("SELECT id, names FROM clients");
if ($edit_link) {
    $vehicles = $conn->query("SELECT id, company, model_name FROM vehicles WHERE id NOT IN (SELECT vehicle_id FROM assignments) OR id = " . $edit_link['vehicle_id']);
} else {
    $vehicles = $conn->query("SELECT id, company, model_name FROM vehicles WHERE id NOT IN (SELECT vehicle_id FROM assignments)");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MAGERWA - Link Asset Ownership</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; background: white; padding: 15px 20px; border-radius: 8px; border: 1px solid var(--border); }
        .search-form { display: flex; gap: 10px; }
        .search-form input { width: 280px; padding: 10px 15px; }
        .search-form button { padding: 10px 20px; background: var(--accent); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .form-overlay { display: none; margin-bottom: 30px; }
        .form-overlay.active { display: block; }
        .actions-cell { display: flex; gap: 10px; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <div class="page-header">
                <h1>Allocations</h1>
            </div>

            <div class="action-bar">
                <form method="GET" action="link_records.php" class="search-form">
                    <input type="text" name="search" placeholder="Search allocations..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" >Search</button>
                </form>
                <?php if (!$edit_link): ?>
                    <button class="btn btn-accent" onclick="document.getElementById('linkFormCard').classList.toggle('active')">+ Link New Allocation</button>
                <?php endif; ?>
            </div>

            <div class="card form-overlay <?php echo $edit_link ? 'active' : ''; ?>" id="linkFormCard">
                <h3 style="margin-bottom:20px;"><?php echo $edit_link ? 'Modify Ownership Linkage Configuration' : 'Execute Ownership Linkage'; ?></h3>
                <form action="link_records.php" method="POST">
                    <input type="hidden" name="action" value="<?php echo $edit_link ? 'update' : 'create'; ?>">
                    <?php if ($edit_link): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_link['id']; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Client</label>
                        <select name="client_id" required>
                            <option value="">Choose the client</option>
                            <?php while($c = $clients->fetch_assoc()): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($edit_link && $edit_link['client_id'] == $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['names']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Vehicle</label>
                        <select name="vehicle_id" required>
                            <option value="">Choose vehicle</option>
                            <?php while($v = $vehicles->fetch_assoc()): ?>
                                <option value="<?php echo $v['id']; ?>" <?php echo ($edit_link && $edit_link['vehicle_id'] == $v['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($v['company'] . " - " . $v['model_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Assign Unique Plate Number</label>
                        <input type="text" name="plate_number" value="<?php echo $edit_link ? htmlspecialchars($edit_link['plate_number']) : ''; ?>" placeholder="e.g. RAE 123 A" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%; margin-top:10px;"><?php echo $edit_link ? 'Save Link Alteration' : 'Execute'; ?></button>
                    <?php if ($edit_link): ?>
                        <a href="link_records.php" class="btn btn-primary" style="width:100%; margin-top:10px; text-align:center; background:#95a5a6;">Cancel Modification</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card">
                <h3>Active Allocations Directory</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Registration Plate</th>
                            <th>Assigned Client</th>
                            <th>Vehicle Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $assignments->fetch_assoc()): ?>
                        <tr>
                            <td><span style="background:#dfe6e9; padding:5px 10px; border-radius:4px; font-weight:bold; border:1px solid #b2bec3;"><?php echo htmlspecialchars($row['plate_number']); ?></span></td>
                            <td><strong><?php echo htmlspecialchars($row['client_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['company'] . " " . $row['model_name']); ?></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="link_records.php?edit=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="link_records.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Sever relationship allocation link context parameters?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>