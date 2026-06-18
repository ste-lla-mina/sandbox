<?php
include 'connection.php';
enforce_admin_gate(); 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create') {
    $names = $_POST['names'];
    $national_id = $_POST['national_id'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO clients (names, national_id, phone, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $names, $national_id, $phone, $address);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_clients.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id = $_POST['id'];
    $names = $_POST['names'];
    $national_id = $_POST['national_id'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("UPDATE clients SET names = ?, national_id = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $names, $national_id, $phone, $address, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_clients.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_clients.php");
    exit();
}

$edit_client = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_client = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_param = "%" . $search . "%";

$stmt = $conn->prepare("SELECT * FROM clients WHERE names LIKE ? OR national_id LIKE ? OR phone LIKE ? ORDER BY id DESC");
$stmt->bind_param("sss", $search_param, $search_param, $search_param);
$stmt->execute();
$clients = $stmt->get_result();
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
                <h1>Clients Registry.</h1>
            </div>

            <div class="action-bar">
                <form method="GET" action="manage_clients.php" class="search-form">
                    <input type="text" name="search" placeholder="Search by name, ID or phone..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Search</button>
                </form>
                <?php if (!$edit_client): ?>
                    <button class="btn btn-accent" onclick="document.getElementById('clientFormCard').classList.toggle('active')">+ Register New Client</button>
                <?php endif; ?>
            </div>
            
            <div class="card form-overlay <?php echo $edit_client ? 'active' : ''; ?>" id="clientFormCard">
                <h3 style="margin-bottom:20px;"><?php echo $edit_client ? 'Modify Client' : 'Register New Client'; ?></h3>
                <form action="manage_clients.php" method="POST">
                    <input type="hidden" name="action" value="<?php echo $edit_client ? 'update' : 'create'; ?>">
                    <?php if ($edit_client): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_client['id']; ?>">
                    <?php endif; ?>
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px;">
                        <div class="form-group">
                            <label>Client Name</label>
                            <input type="text" name="names" value="<?php echo $edit_client ? htmlspecialchars($edit_client['names']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>National ID</label>
                            <input type="text" name="national_id" value="<?php echo $edit_client ? htmlspecialchars($edit_client['national_id']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" value="<?php echo $edit_client ? htmlspecialchars($edit_client['phone']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" value="<?php echo $edit_client ? htmlspecialchars($edit_client['address']) : ''; ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-accent" style="margin-top:10px;"><?php echo $edit_client ? 'Update' : 'Save Client'; ?></button>
                    <?php if ($edit_client): ?>
                        <a href="manage_clients.php" class="btn btn-primary" style="margin-top:10px; margin-left:10px; background:#95a5a6;">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card">
                <h3>Registered Clients</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Names</th>
                            <th>National ID</th>
                            <th>Phone Contact</th>
                            <th>Address Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $clients->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['names']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['national_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="manage_clients.php?edit=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="manage_clients.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Confirm delete client!');">Delete</a>
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