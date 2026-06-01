<?php
// WIP
require 'connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $accreditation_year = $_POST['accreditation_year'];
    $org_ID           = $_POST['org_ID']; 
    $status           = $_POST['application_status'];
    $org_name         = $_POST['org_name'];
    $org_address      = $_POST['org_address'];
    $date_organized   = $_POST['date_organized'];
    $contact_number   = $_POST['contact_number'];
    $linkages         = $_POST['linkages'];
    //$purpose          = $_POST['purpose']; //check if we can remove
    //$services         = $_POST['services']; //check if we can remove

    //Unique ID's for multivalued tables will not be included here
    //They will be instantiated in the XAMPP instead 

    //Handles "other" string options (TENTATIVE if we still want the user to have their own answer)
    $agency = ($_POST['registering_agency'] == 'Other') ? $_POST['registering_agency_other'] : $_POST['registering_agency'];
    $level  = ($_POST['org_level'] == 'Other') ? $_POST['org_level_other'] : $_POST['org_level'];
    $sector = ($_POST['sector'] == 'Other') ? $_POST['sector_other'] : $_POST['sector'];
  
    $male          = (int)$_POST['members_male'];
    $female        = (int)$_POST['members_female'];

    $total_members = $male + $female;

    $voters_reg    = (int)$_POST['voters_registered'];
    $voters_unreg  = (int)$_POST['voters_nonregistered'];


    //checks for duplicate org_ID
    $check = $conn->query("SELECT org_ID FROM org_details WHERE org_ID = '$org_ID'");
    if ($check->num_rows > 0) {
    die("Organization ID already exists.");
    }

    // SINGLE VALUED TABLES

    $sql_org = "INSERT INTO org_details (org_ID, org_name, office_address, date_organized, contact_number, org_level, linkages_membership, sector_served) 
                VALUES ('$org_ID', '$org_name', '$org_address', '$date_organized', '$contact_number', '$level', '$linkages', '$sector')";
    $conn->query($sql_org);

    $sql_acc = "INSERT INTO accreditation (org_ID, accreditation_year, renewal_status, male_members, female_members, total_members, unregistered_voter_count, registered_voter_count) 
                VALUES ('$org_ID', $accreditation_year, '$status', $male, $female, $total_members, $voters_unreg, $voters_reg)";
    $conn->query($sql_acc);

    //MULTI-VALUED TABLES

    if (!empty($_POST['registering_agency'])) {
        foreach ($_POST['registering_agency'] as $agency_item) {
            $actual_agency = ($agency_item == 'Other') ? $_POST['registering_agency_other'] : $agency_item;

            $sql_agency = "INSERT INTO Registering_Agency (org_ID, accreditation_year, registering_agency) 
                           VALUES ('$org_ID', $accreditation_year, '$actual_agency')";
            $conn->query($sql_agency);
        }
    }

    if (!empty($_POST['purpose'])) {
        foreach ($_POST['purpose'] as $purpose_item) {
            $actual_purpose = ($purpose_item == 'Other') ? $_POST['purpose_other'] : $purpose_item;

            $sql_purpose = "INSERT INTO Purpose (org_ID, accreditation_year, purpose) 
                            VALUES ('$org_ID', $accreditation_year, '$actual_purpose')";
            $conn->query($sql_purpose);
        }
    }

    if (!empty($_POST['services'])) {
        foreach ($_POST['services'] as $service_item) {
            $actual_service = ($service_item == 'Other') ? $_POST['services_other'] : $service_item;

            $sql_services = "INSERT INTO services_facilities (org_ID, accreditation_year, services_facilities) 
                             VALUES ('$org_ID', $accreditation_year, '$actual_service')";
            $conn->query($sql_services);
        }
    }

    if (!empty($_POST['funds'])) {
        foreach ($_POST['funds'] as $fund) {
            $actual_fund = ($fund == 'Other') ? $_POST['funds_other'] : $fund;
            
            $sql_finance = "INSERT INTO finance (org_ID, accreditation_year, financing_source) 
                            VALUES ('$org_ID', $accreditation_year, '$actual_fund')";
            $conn->query($sql_finance);
        }
    }

    //local body priority
    if (!empty($_POST['priority'])) {
        foreach ($_POST['priority'] as $prio) {
            $actual_prio = ($prio == 'Other') ? $_POST['priority_other'] : $prio;
            
            $sql_priority = "INSERT INTO local_body_priority (org_ID, accreditation_year, local_body_priority) 
                             VALUES ('$org_ID', $accreditation_year, '$actual_prio')";
            $conn->query($sql_priority);
        }
    }

echo "<!DOCTYPE html>\n";
echo "<html lang='en'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>Submission Success</title>\n";
echo "    <style>\n";
echo "        :root { --text-main: #1d1d1f; --primary-color: #34c759; }\n";
echo "        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%); color: var(--text-main); line-height: 1.5; margin: 0; display: flex; flex-direction: column; min-height: 100vh; overflow-x: hidden; position: relative; }\n";
echo "        .glow-orb { position: absolute; width: 450px; height: 450px; border-radius: 50%; filter: blur(90px); z-index: 0; opacity: 0.5; pointer-events: none; }\n";
echo "        .orb-1 { top: -10%; left: 10%; background: #6366f1; }\n";
echo "        .orb-2 { bottom: -5%; right: 10%; background: red; }\n";
echo "        .main-wrapper { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px 20px; z-index: 1; }\n";
echo "        .container { background: rgba(255, 255, 255, 0.45); backdrop-filter: blur(25px) saturate(190%); -webkit-backdrop-filter: blur(25px) saturate(190%); border: 1px solid rgba(255, 255, 255, 0.4); width: 100%; max-width: 440px; padding: 45px; border-radius: 22px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08); transition: transform 0.3s ease, box-shadow 0.3s ease; text-align: center; box-sizing: border-box; }\n";
echo "        .container:hover { transform: translateY(-2px); box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15); }\n";
echo "        .success-icon { display: inline-flex; justify-content: center; align-items: center; width: 64px; height: 64px; background: rgba(52, 199, 89, 0.12); border-radius: 50%; margin-bottom: 24px; position: relative; }\n";
echo "        .checkmark { width: 12px; height: 22px; border: solid var(--primary-color); border-width: 0 3px 3px 0; transform: rotate(45deg); margin-top: -4px; }\n";
echo "        h1 { margin: 0 0 12px 0; font-size: 28px; font-weight: 700; letter-spacing: -0.5px; }\n";
echo "        .subtitle { color: #434345; margin: 0 0 35px 0; font-size: 15px; }\n";
echo "        .subtitle strong { color: var(--text-main); font-weight: 600; }\n";
echo "        .btn { display: block; padding: 14px 24px; font-size: 15px; text-decoration: none; border-radius: 12px; font-weight: 600; transition: all 0.25s ease; background: var(--primary-color); color: #ffffff; box-shadow: 0 4px 12px rgba(52, 199, 89, 0.25); }\n";
echo "        .btn:hover { background: #28a745; transform: scale(1.015); box-shadow: 0 6px 16px rgba(52, 199, 89, 0.35); }\n";
echo "        .btn:active { transform: scale(0.985); }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";
echo "    <div class='glow-orb orb-1'></div>\n";
echo "    <div class='glow-orb orb-2'></div>\n";
echo "    <div class='main-wrapper'>\n";
echo "        <div class='container'>\n";
echo "            <div class='success-icon'><div class='checkmark'></div></div>\n";
echo "            <h1>Success!</h1>\n";
echo "            <p class='subtitle'>Application for <strong>$org_name</strong> has been successfully transferred to MySQL.</p>\n";
echo "            <div class='action-area'>\n";
echo "                <a href='Main_Page.html' class='btn btn-primary'>Go to Main Page</a>\n";
echo "            </div>\n";
echo "        </div>\n";
echo "    </div>\n";
echo "</body>\n";
echo "</html>\n";


} else {
    echo "You must submit the form first!";
}

?>