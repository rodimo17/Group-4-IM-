<?php
include 'connect_db.php';

// Check if a search keyword was submitted via the URL parameter
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// SQL Query structure similar with edit.php
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

// Apply filter conditions if a search keyword was provided
if (!empty($search_keyword)) {
    $search_escaped = mysqli_real_escape_string($conn, $search_keyword);
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
    <title>Update Organization Records</title>
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            --panel-bg: rgba(255, 255, 255, 1);
            --panel-border: rgba(255, 255, 255, 0.4);
            --text-main: #1d1d1f;
            --text-sub: #55555a;
            --shadow-color: rgba(0, 0, 0, 0.06);
            --primary-color: #0071e3;
            --primary-hover: #0077ed;
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
        h2 {
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
        .btn-search:hover { background: var(--primary-hover); }
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
        
        .btn-edit {
            padding: 8px 16px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            font-weight: 600;
            font-size: 13px;
            box-shadow: 0 4px 10px rgba(0, 113, 227, 0.15);
            transition: all 0.2s;
        }
        .btn-edit:hover {
            background: var(--primary-hover);
            box-shadow: 0 6px 14px rgba(0, 113, 227, 0.25);
        }
        .btn-edit:active { transform: scale(0.97); }

        .msg-error {
            background: rgba(255, 59, 48, 0.1);
            color: #cc2e24;
            border: 1px solid rgba(255, 59, 48, 0.2);
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <a href="Main_Page.html" class="nav-link">&larr; Back to Main Page</a>
        <h2>Update Organization Records</h2>

        <form method="GET" action="update_record.php" class="search-box">
            <input type="text" name="search" class="search-input" placeholder="Search by Organization Name or ID..." value="<?php echo htmlspecialchars($search_keyword); ?>">
            <button type="submit" class="btn-search">Search</button>
        </form>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Org ID</th>
                            <th>Organization Name</th>
                            <th>Contact Number</th>
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
                                <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
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
                                    <a href="edit.php?org_id=<?php echo urlencode($row['org_id']); ?>&accreditation_year=<?php echo urlencode($row['accreditation_year']); ?>" class="btn-edit">Edit</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="msg-error" style="text-align: center;">No records found matching your query details.</div>
        <?php endif; ?>
    </div>

</body>
</html>