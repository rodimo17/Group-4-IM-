<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Records Dashboard</title>
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
            margin-bottom: 25px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: opacity 0.2s;
        }
        .nav-link:hover { opacity: 0.8; }
        
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            border-radius: 14px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            background: rgba(255, 255, 255, 0.3);
            margin-top: 10px;
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

        .msg-info, .msg-error {
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
            text-align: center;
        }
        .msg-info {
            background: rgba(0, 113, 227, 0.08);
            color: var(--primary-color);
            border: 1px solid rgba(0, 113, 227, 0.15);
        }
        .msg-error {
            background: rgba(255, 59, 48, 0.1);
            color: #cc2e24;
            border: 1px solid rgba(255, 59, 48, 0.2);
        }
    </style>
</head>
<body>

    <div class="form-container">
        <a href="Main_Page.html" class="nav-link">&larr; Back to Main Page</a>
        <h2>Master Organization Records</h2>

        <?php if (isset($_GET['message'])): ?>
            <div class="msg-info">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <?php
        include 'connect_db.php';

        $sql = "SELECT 
                    o.org_ID AS org_id, 
                    o.org_name, 
                    o.contact_number, 
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
                LEFT JOIN local_body_priority lbp ON o.org_ID = lbp.org_id
                GROUP BY o.org_ID, a.accreditation_year";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            echo '<div class="table-wrapper">';
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Org ID</th>';
            echo '<th>Organization Name</th>';
            echo '<th>Contact Number</th>';
            echo '<th>Sector</th>';
            echo '<th>Year</th>';
            echo '<th>Status</th>';
            echo '<th>Total Members</th>';
            echo '<th>Registering Agencies</th>';
            echo '<th>Funding Sources</th>';
            echo '<th>Purposes</th>';
            echo '<th>Services/Facilities</th>';
            echo '<th>Local Priorities</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['org_id']) . '</td>';
                echo '<td><strong>' . htmlspecialchars($row['org_name']) . '</strong></td>';
                echo '<td>' . htmlspecialchars($row['contact_number']) . '</td>';
                echo '<td>' . htmlspecialchars($row['sector_served']) . '</td>';
                echo '<td>' . htmlspecialchars($row['accreditation_year'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($row['renewal_status'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($row['total_members'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($row['all_agencies'] ?? 'None') . '</td>';
                echo '<td>' . htmlspecialchars($row['all_funds'] ?? 'None') . '</td>';
                echo '<td>' . htmlspecialchars($row['all_purposes'] ?? 'None') . '</td>';
                echo '<td>' . htmlspecialchars($row['all_services'] ?? 'None') . '</td>';
                echo '<td>' . htmlspecialchars($row['all_priorities'] ?? 'None') . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        } else {
            echo '<div class="msg-error">No records found in the database registry.</div>';
        }

        $conn->close();
        ?>
    </div>

</body>
</html>