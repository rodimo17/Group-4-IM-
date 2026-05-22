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
    $purpose          = $_POST['purpose'];
    $services         = $_POST['services'];

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

    // SINGLE VALUED TABLES

    $sql_org = "INSERT INTO org_details (org_ID, org_name, office_address, date_organized, contact_number, org_level, linkages_membership, sector_served) 
                VALUES ('$org_ID', '$org_name', '$org_address', '$date_organized', '$contact_number', '$level', '$linkages', '$sector')";
    $conn->query($sql_org);

    $sql_acc = "INSERT INTO accreditation (org_ID, accreditation_year, renewal_status, male_members, female_members, total_members, unregistered_voter_count, registered_voter_count) 
                VALUES ('$org_ID', $accreditation_year, '$status', $male, $female, $total_members, $voters_unreg, $voters_reg)";
    $conn->query($sql_acc);

    //MULTI-VALUED TABLES

    // add brackets sa html mamaya ex. name="registering_agency[]"

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

    echo "<h2>Success!</h2>";
    echo "<p>Application for <strong>$org_name</strong> has been successfully transferred to MySQL.</p>";
    echo "<a href='dashboard.php' style='padding: 10px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;'>Go to Dashboard</a>";

} else {
    echo "You must submit the form first!";
}

?>