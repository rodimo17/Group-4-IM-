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

// for handling 0 and string matching org_name and org_id (similar with update_record's search bar)
if ($search_name !== '') {
    $safe_search = mysqli_real_escape_string($conn, $search_name);
    $sql .= " WHERE o.org_ID LIKE '%$safe_search%' OR o.org_name LIKE '%$safe_search%'";
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
         .btn-clear {
          padding: 12px 20px;
          background: #6c757d; 
          color: #ffffff;
         text-decoration: none;
         border-radius: 12px;
         font-size: 14px;
         font-weight: 600;
         display: inline-block;
         transition: all 0.2s;
         box-sizing: border-box;
        }
     .btn-clear:hover { 
       background: #5a6268; 
      }
     .btn-clear:active { 
    transform: scale(0.98); 
     }
        
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
        .btn-delete:active { transform: scale(0.97); 
    }
    </style>
</head>
<body> 

  <div class="form-container">
        <a href="Main_Page.html" class="nav-link">&larr; Back to Main Page</a>
        <h1>Delete Organization Records</h1>

    <?php if (!empty($message)): ?>
        <div class="msg <?php echo ($message_type === 'error') ? 'msg-error' : 'msg-success'; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['message'])): ?>
        <?php $messageClass = (($_GET['message_type'] ?? 'success') === 'error') ? 'msg-error' : 'msg-success'; ?>
        <div class="msg <?php echo $messageClass; ?>"><?php echo htmlspecialchars($_GET['message']); ?></div>
    <?php endif; ?>

    <form action="delete_record.php" method="GET" class="search-box">
        <input type="text" name="search_name" class="search-input" placeholder="Search by Organization's ID or Name" value="<?php echo htmlspecialchars($search_name); ?>">
        <button type="submit" class="btn-search">Search</button>
        
        <?php if ($search_name !== ''): ?>
            <a href="delete_record.php" class="btn-clear">Clear</a>
        <?php endif; ?>
    </form>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-wrapper">
        <table>
            <tr>
                <th>Org ID</th>
                <th>Organization Name</th>
                <th>Contact</th>
                <th>Sector</th>
                <th>Accreditation Year</th>
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
                           class="btn-delete" 
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
