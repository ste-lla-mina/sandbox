<?php
include 'connection.php';
enforce_admin_gate();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create') {
    $company = $_POST['company'];
    $year = $_POST['year'];
    $price = $_POST['price'];
    $model_name = $_POST['model_name'];

    $stmt = $conn->prepare("INSERT INTO vehicles (company, year, price, model_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sids", $company, $year, $price, $model_name);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_vehicles.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id = $_POST['id'];
    $company = $_POST['company'];
    $year = $_POST['year'];
    $price = $_POST['price'];
    $model_name = $_POST['model_name'];

    $stmt = $conn->prepare("UPDATE vehicles SET company = ?, year = ?, price = ?, model_name = ? WHERE id = ?");
    $stmt->bind_param("sidsi", $company, $year, $price, $model_name, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_vehicles.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_vehicles.php");
    exit();
}

$edit_vehicle = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_vehicle = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_param = "%" . $search . "%";

$stmt = $conn->prepare("SELECT * FROM vehicles WHERE company LIKE ? OR model_name LIKE ? ORDER BY id DESC");
$stmt->bind_param("ss", $search_param, $search_param);
$stmt->execute();
$vehicles = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MAGERWA</title>
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
                <h1>Vehicles Registry.</h1>
            </div>

            <div class="action-bar">
                <form method="GET" action="manage_vehicles.php" class="search-form">
                    <input type="text" name="search" placeholder="Search by company or model..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" >Search</button>
                </form>
                <?php if (!$edit_vehicle): ?>
                    <button class="btn btn-accent" onclick="document.getElementById('vehicleFormCard').classList.toggle('active')">+ Register New Vehicle</button>
                <?php endif; ?>
            </div>

            <div class="card form-overlay <?php echo $edit_vehicle ? 'active' : ''; ?>" id="vehicleFormCard">
                <h3 style="margin-bottom:20px;"><?php echo $edit_vehicle ? 'Modify Inventory Vehicle Specifications' : 'Register New Vehicle'; ?></h3>
                <form action="manage_vehicles.php" method="POST">
                    <input type="hidden" name="action" value="<?php echo $edit_vehicle ? 'update' : 'create'; ?>">
                    <?php if ($edit_vehicle): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_vehicle['id']; ?>">
                    <?php endif; ?>
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px;">
                        <div class="form-group">
                            <label>Manufacturer Company</label>
                            <input type="text" name="company" value="<?php echo $edit_vehicle ? htmlspecialchars($edit_vehicle['company']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Model</label>
                            <input type="text" name="model_name" value="<?php echo $edit_vehicle ? htmlspecialchars($edit_vehicle['model_name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Production Year</label>
                            <input type="number" name="year" value="<?php echo $edit_vehicle ? htmlspecialchars($edit_vehicle['year']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Price ($)</label>
                            <input type="number" step="0.01" name="price" value="<?php echo $edit_vehicle ? htmlspecialchars($edit_vehicle['price']) : ''; ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-accent" style="margin-top:10px;"><?php echo $edit_vehicle ? 'Update' : 'Save'; ?></button>
                    <?php if ($edit_vehicle): ?>
                        <a href="manage_vehicles.php" class="btn btn-primary" style="margin-top:10px; margin-left:10px; background:#95a5a6;">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card">
                <h3>Warehouse Vehicle Stock</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Model Classification</th>
                            <th>Production Year</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $vehicles->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['company']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['model_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['year']); ?></td>
                            <td>$<?php echo number_format($row['price'], 2); ?></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="manage_vehicles.php?edit=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="manage_vehicles.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete?');">Delete</a>
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