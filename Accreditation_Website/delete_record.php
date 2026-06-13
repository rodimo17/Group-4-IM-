<?php
include 'connect_db.php';

$message = "";
$message_type = "";

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $org_id = $_GET['org_id'] ?? null;
    $accreditation_year = $_GET['accreditation_year'] ?? null;

    if ($org_id && $accreditation_year) {
        $org_id = mysqli_real_escape_string($conn, $org_id);
        $accreditation_year = mysqli_real_escape_string($conn, $accreditation_year);

        // Run deletion queries in correct dependency order
        $delete_acc_details = "DELETE FROM accreditation WHERE org_id = '$org_id' AND accreditation_year = '$accreditation_year'";
        $result_acc = mysqli_query($conn, $delete_acc_details);

        $delete_org_details = "DELETE FROM org_details WHERE org_id = '$org_id'";
        $result_org = mysqli_query($conn, $delete_org_details);

        if ($result_acc && $result_org) {
            $message = "Record deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting record: " . mysqli_error($conn);
            $message_type = "error";
        }
    }
}

// SEARCH CONDITION

$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';

// MAIN TABLE DISPLAY SQL 

$sql = "SELECT 
            o.org_ID AS org_id,
            o.org_name,
            o.contact_number,
            o.sector_served,
            a.accreditation_year,
            a.renewal_status,
            a.total_members,
            GROUP_CONCAT(DISTINCT r.registering_agency SEPARATOR ', ') AS all_agencies,
            GROUP_CONCAT(DISTINCT f.financing_source SEPARATOR ', ') AS all_funds,
            GROUP_CONCAT(DISTINCT p.purpose SEPARATOR ', ') AS all_purposes,
            GROUP_CONCAT(DISTINCT s.services_facilities SEPARATOR ', ') AS all_services,
            GROUP_CONCAT(DISTINCT l.local_body_priority SEPARATOR ', ') AS all_priorities
        FROM org_details o
        LEFT JOIN accreditation a ON o.org_ID = a.org_ID
        LEFT JOIN Registering_Agency r ON o.org_ID = r.org_ID AND a.accreditation_year = r.accreditation_year
        LEFT JOIN finance f ON o.org_ID = f.org_ID AND a.accreditation_year = f.accreditation_year
        LEFT JOIN Purpose p ON o.org_ID = p.org_ID AND a.accreditation_year = p.accreditation_year
        LEFT JOIN services_facilities s ON o.org_ID = s.org_ID AND a.accreditation_year = s.accreditation_year
        LEFT JOIN local_body_priority l ON o.org_ID = l.org_ID AND a.accreditation_year = l.accreditation_year";

// for handling 0 and string matching org_name (similar with update_record's search bar)
if ($search_name !== '') {
    $safe_search = mysqli_real_escape_string($conn, $search_name);
    $sql .= " WHERE o.org_name LIKE '%$safe_search%'";
}

$sql .= " GROUP BY 
            o.org_ID,
            o.org_name,
            o.contact_number,
            o.sector_served,
            a.accreditation_year,
            a.renewal_status,
            a.total_members";

$result = $conn->query($sql);
?>

<!-- delete_record.php UI -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Records</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13px; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: left; }
        th { background-color: #007BFF; color: white; }
        .btn { padding: 8px 12px; background: #d8301a; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
        .msg { padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        .msg-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .msg-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .search-box { margin: 20px 0; display: flex; gap: 10px; }
        .search-input { padding: 8px 12px; width: 320px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .btn-search { padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-clear { padding: 8px 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; }
    </style>
</head>
<body> 

    <h2>Delete Records</h2>
    <a href="Main_Page.html" class="btn" style="background: #d8301a; margin-right: 5px;">Back</a>

    <?php if (!empty($message)): ?>
        <div class="msg <?php echo ($message_type === 'error') ? 'msg-error' : 'msg-success'; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['message'])): ?>
        <?php $messageClass = (($_GET['message_type'] ?? 'success') === 'error') ? 'msg-error' : 'msg-success'; ?>
        <div class="msg <?php echo $messageClass; ?>"><?php echo htmlspecialchars($_GET['message']); ?></div>
    <?php endif; ?>

    <form action="delete_record.php" method="GET" class="search-box">
        <input type="text" name="search_name" class="search-input" placeholder="Type Organization Name to filter..." value="<?php echo htmlspecialchars($search_name); ?>">
        <button type="submit" class="btn-search">Search</button>
        
        <?php if ($search_name !== ''): ?>
            <a href="delete_record.php" class="btn-clear">Clear</a>
        <?php endif; ?>
    </form>

    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Org ID</th>
                <th>Organization Name</th>
                <th>Contact</th>
                <th>Sector</th>
                <th>Year</th>
                <th>Status</th>
                <th>Total Members</th>
                <th>Registering Agencies</th>
                <th>Funding Sources</th>
                <th>Purposes</th>
                <th>Services/Facilities</th>
                <th>Local Priorities</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['org_id']); ?></td>
                    <td><strong><?php echo htmlspecialchars($row['org_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['sector_served']); ?></td>
                    <td><?php echo $row['accreditation_year'] ?? 'N/A'; ?></td>
                    <td><?php echo $row['renewal_status'] ?? 'N/A'; ?></td>
                    <td><?php echo $row['total_members'] ?? 'N/A'; ?></td>
                    <td><?php echo $row['all_agencies'] ?? 'None'; ?></td>
                    <td><?php echo $row['all_funds'] ?? 'None'; ?></td>
                    <td><?php echo $row['all_purposes'] ?? 'None'; ?></td>
                    <td><?php echo $row['all_services'] ?? 'None'; ?></td>
                    <td><?php echo $row['all_priorities'] ?? 'None'; ?></td>
                    <td style="white-space: nowrap;">
                        <a href="delete_record.php?action=delete&org_id=<?php echo urlencode($row['org_id']); ?>&accreditation_year=<?php echo urlencode($row['accreditation_year']); ?>"
                           class="btn" 
                           style="background:#dc3545;" 
                           onclick="return confirm('Delete this record permanently?');">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p style="color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 4px;">
            No records found! <!-- empty record or no records match-->
        </p>
    <?php endif; ?>
</body>
</html>