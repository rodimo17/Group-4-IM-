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

$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';

$sql = "SELECT 
            o.org_ID AS org_id, 
            o.org_name, 
            o.office_address AS org_address, 
            o.contact_number, 
            o.date_organized,
            o.sector_served,
            a.accreditation_year,
            a.renewal_status,
            a.total_members,
            GROUP_CONCAT(DISTINCT ra.agency_name SEPARATOR ', ') AS all_agencies,
            GROUP_CONCAT(DISTINCT fs.source_name SEPARATOR ', ') AS all_funds,
            GROUP_CONCAT(DISTINCT po.purpose_name SEPARATOR ', ') AS all_purposes,
            GROUP_CONCAT(DISTINCT sf.service_name SEPARATOR ', ') AS all_services,
            GROUP_CONCAT(DISTINCT lbp.priority_name SEPARATOR ', ') AS all_priorities
        FROM org_details o
        LEFT JOIN accreditation a ON o.org_ID = a.org_id
        LEFT JOIN registering_agency ra ON o.org_ID = ra.org_id
        LEFT JOIN financing_source fs ON o.org_ID = fs.org_id
        LEFT JOIN purpose_objectives po ON o.org_ID = po.org_id
        LEFT JOIN services_facilities sf ON o.org_ID = sf.org_id
        LEFT JOIN local_body_priority lbp ON o.org_ID = lbp.org_id";

if (!empty($search_name)) {
    $search_escaped = mysqli_real_escape_string($conn, $search_name);
    $sql .= " WHERE o.org_name LIKE '%$search_escaped%' OR o.org_ID LIKE '%$search_escaped%'";
}

$sql .= " GROUP BY o.org_ID, a.accreditation_year";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Records Portal</title>
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            --panel-bg: rgba(255, 255, 255, 1);
            --panel-border: rgba(255, 255, 255, 0.4);
            --text-main: #1d1d1f;
            --text-sub: #55555a;
            --shadow-color: rgba(0, 0, 0, 0.06);
            --primary-color: #0071e3;
            --danger-color: #ff3b30;
            --danger-hover: #d7261e;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg-gradient);
            margin: 0;
            padding: 40px 20px;
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            box-sizing: border-box;
            scrollbar-width: none;  
        }
        body::-webkit-scrollbar {
            display: none;
        }
        .form-container {
            max-width: 1250px;
            background: var(--panel-bg);
            border: 1px solid var(--panel-border);
            margin: 0 auto;
            padding: 40px;
            border-radius: 22px;
            box-shadow: 0 20px 40px var(--shadow-color);
        }
        h1 {
            margin: 0 0 25px 0;
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.5px;
            text-align: center;
        }
        .nav-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: opacity 0.2s;
        }
        .nav-link:hover { opacity: 0.8; }
        .msg {
            padding: 14px 18px;
            margin-bottom: 25px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
        }
        .msg-success {
            background: rgba(52, 199, 89, 0.12);
            color: #248a3d;
            border: 1px solid rgba(52, 199, 89, 0.25);
        }
        .msg-error {
            background: rgba(255, 59, 48, 0.1);
            color: #cc2e24;
            border: 1px solid rgba(255, 59, 48, 0.2);
        }
        .search-box {
            margin: 20px 0 30px 0;
            display: flex;
            gap: 12px;
        }
        .search-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid rgba(0, 0, 0, 0.12);
            background: rgba(255, 255, 255, 0.55);
            border-radius: 12px;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
        }
        .search-input:focus {
            border-color: var(--primary-color);
            background: #ffffff;
        }
        .btn-search {
            padding: 12px 24px;
            background: var(--primary-color);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-search:hover { background: #0077ed; }
        .btn-search:active { transform: scale(0.98); }
        
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            border-radius: 14px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            background: rgba(255, 255, 255, 0.3);
            margin-bottom: 15px;
        }
        
        .table-wrapper::-webkit-scrollbar {
            height: 8px;
            background-color: rgba(0, 0, 0, 0.02);
        }
        .table-wrapper::-webkit-scrollbar-thumb {
            background-color: rgba(0, 113, 227, 0.2);
            border-radius: 10px;
        }
        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background-color: rgba(0, 113, 227, 0.4);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
            text-align: left;
        }
        th {
            background-color: rgba(255, 255, 255, 0.5);
            color: var(--text-main);
            font-weight: 700;
            padding: 14px 16px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.08);
            border-right: 1px solid rgba(0, 0, 0, 0.06);
            white-space: nowrap;
        }
        td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            border-right: 1px solid rgba(0, 0, 0, 0.06);
            color: #3a3a3c;
            white-space: nowrap;
        }
        th:last-child, td:last-child {
            border-right: none;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: rgba(255, 255, 255, 0.25); }
        
        .btn-delete {
            padding: 8px 14px;
            background: var(--danger-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            font-weight: 600;
            font-size: 13px;
            box-shadow: 0 4px 10px rgba(255, 59, 48, 0.15);
            transition: all 0.2s;
        }
        .btn-delete:hover {
            background: var(--danger-hover);
            box-shadow: 0 6px 14px rgba(255, 59, 48, 0.25);
        }
        .btn-delete:active { transform: scale(0.97); }
    </style>
</head>
<body>

    <div class="form-container">
        <a href="Main_Page.html" class="nav-link">&larr; Back to Main Page</a>
        <h1>Delete Organization Records</h1>

        <?php if (!empty($message)): ?>
            <div class="msg msg-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="GET" action="delete_record.php" class="search-box">
            <input type="text" name="search_name" class="search-input" placeholder="Search by Organization Name or ID..." value="<?php echo htmlspecialchars($search_name); ?>">
            <button type="submit" class="btn-search">Search Registry</button>
        </form>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Org ID</th>
                            <th>Organization Name</th>
                            <th>Office Address</th>
                            <th>Contact Number</th>
                            <th>Date Organized</th>
                            <th>Sector Served</th>
                            <th>Accreditation Year</th>
                            <th>Renewal Status</th>
                            <th>Total Members</th>
                            <th>Registering Agencies</th>
                            <th>Sources of Funds</th>
                            <th>Purposes / Objectives</th>
                            <th>Services / Facilities</th>
                            <th>Local Body Priorities</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['org_id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['org_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['org_address']); ?></td>
                                <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['date_organized']); ?></td>
                                <td><?php echo htmlspecialchars($row['sector_served']); ?></td>
                                <td><?php echo htmlspecialchars($row['accreditation_year'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['renewal_status'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['total_members'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['all_agencies'] ?? 'None'); ?></td>
                                <td><?php echo htmlspecialchars($row['all_funds'] ?? 'None'); ?></td>
                                <td><?php echo htmlspecialchars($row['all_purposes'] ?? 'None'); ?></td>
                                <td><?php echo htmlspecialchars($row['all_services'] ?? 'None'); ?></td>
                                <td><?php echo htmlspecialchars($row['all_priorities'] ?? 'None'); ?></td>
                                <td>
                                    <a href="delete_record.php?action=delete&org_id=<?php echo urlencode($row['org_id']); ?>&accreditation_year=<?php echo urlencode($row['accreditation_year']); ?>" 
                                       class="btn-delete"
                                       onclick="return confirm('Are you sure you want to permanently delete this record?');">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="msg msg-error" style="text-align: center; margin-bottom: 0;">No organization records match your lookup parameters.</div>
        <?php endif; ?>
    </div>

</body>
</html>