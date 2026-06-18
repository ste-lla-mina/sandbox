<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <h2>MAGERWA_MOVE</h2>
    <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>"> Operational Logs</a>
    <a href="manage_clients.php" class="<?php echo ($current_page == 'manage_clients.php') ? 'active' : ''; ?>">Clients</a>
    <a href="manage_vehicles.php" class="<?php echo ($current_page == 'manage_vehicles.php') ? 'active' : ''; ?>"> Vehicles</a>
    <a href="link_records.php" class="<?php echo ($current_page == 'link_records.php') ? 'active' : ''; ?>">Allocations</a>
    <a href="logout.php" class="logout">Logout.</a>
</div>