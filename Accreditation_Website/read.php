<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Records Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13px; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: left; }
        th { background-color: #007BFF; color: white; }
        .btn { padding: 8px 12px; background: #d8301a; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
    </style>
</head>
<body>

    <h2>Master Organization Records</h2>
    <a href="Main_Page.html" class="btn">Back</a>

    <?php
    require 'connect_db.php';

    $sql = "SELECT 
                o.org_ID, 
                o.org_name, 
                o.contact_number,
                o.sector_served,
                a.renewal_status,
                a.total_members,
                GROUP_CONCAT(DISTINCT r.registering_agency SEPARATOR ', ') AS all_agencies,
                GROUP_CONCAT(DISTINCT f.financing_source SEPARATOR ', ') AS all_funds,
                GROUP_CONCAT(DISTINCT p.purpose SEPARATOR ', ') AS all_purposes,
                GROUP_CONCAT(DISTINCT s.services_facilities SEPARATOR ', ') AS all_services,
                GROUP_CONCAT(DISTINCT l.local_body_priority SEPARATOR ', ') AS all_priorities
            FROM org_details o
            LEFT JOIN accreditation a ON o.org_ID = a.org_ID
            LEFT JOIN Registering_Agency r ON o.org_ID = r.org_ID
            LEFT JOIN finance f ON o.org_ID = f.org_ID
            LEFT JOIN Purpose p ON o.org_ID = p.org_ID
            LEFT JOIN services_facilities s ON o.org_ID = s.org_ID
            LEFT JOIN local_body_priority l ON o.org_ID = l.org_ID
            GROUP BY o.org_ID"; 

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr>
                <th>Org ID</th>
                <th>Organization Name</th>
                <th>Contact</th>
                <th>Sector</th>
                <th>Status</th>
                <th>Total Members</th>
                <th>Registering Agencies</th>
                <th>Funding Sources</th>
                <th>Purposes</th>
                <th>Services/Facilities</th>
                <th>Local Priorities</th>
              </tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['org_ID'] . "</td>";
            echo "<td><strong>" . $row['org_name'] . "</strong></td>";
            echo "<td>" . $row['contact_number'] . "</td>";
            echo "<td>" . $row['sector_served'] . "</td>";
            echo "<td>" . $row['renewal_status'] . "</td>";
            echo "<td>" . $row['total_members'] . "</td>";
            echo "<td>" . ($row['all_agencies'] ?? 'None') . "</td>";
            echo "<td>" . ($row['all_funds'] ?? 'None') . "</td>";
            echo "<td>" . ($row['all_purposes'] ?? 'None') . "</td>";
            echo "<td>" . ($row['all_services'] ?? 'None') . "</td>";
            echo "<td>" . ($row['all_priorities'] ?? 'None') . "</td>";
            
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No records found in the database.</p>";
    }
    ?>

</body>
</html>
