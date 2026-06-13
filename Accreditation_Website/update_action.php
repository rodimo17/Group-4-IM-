<?php
include 'connect_db.php';

// separated update_action and update_record para hindi masyadong mahaba...

// Initializing

$org_id = $_GET['org_id'] ?? null;
$accreditation_year = $_GET['accreditation_year'] ?? null;

if (!$org_id || !$accreditation_year) {
    header("Location: update_record.php");
    exit;
}

if (isset($_POST['update'])) {
    
    $org_id = mysqli_real_escape_string($conn, $org_id);
    $accreditation_year = mysqli_real_escape_string($conn, $accreditation_year);

    // Sanitizing inputs
    $org_name       = mysqli_real_escape_string($conn, $_POST['org_name']);
    $org_address    = mysqli_real_escape_string($conn, $_POST['org_address']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $date_organized = mysqli_real_escape_string($conn, $_POST['date_organized']);
    $linkages       = mysqli_real_escape_string($conn, $_POST['linkages']);

    // Handle "Other" for org_level Radio Button
    $org_level = $_POST['org_level'];
    if ($org_level === 'Other') {
        $org_level = $_POST['org_level_other'];
    }
    $org_level = mysqli_real_escape_string($conn, trim($org_level));

    // Handle "Other" for sector_served Radio Button
    $sector_served = $_POST['sector'];
    if ($sector_served === 'Other') {
        $sector_served = $_POST['sector_other'];
    }
    $sector_served = mysqli_real_escape_string($conn, trim($sector_served));

    // Handle Accreditation data
    $renewal_status = mysqli_real_escape_string($conn, $_POST['application_status']);
    $male_members   = (int)$_POST['male_members'];
    $female_members = (int)$_POST['female_members'];
    $total_members  = $male_members + $female_members;
    $registered_voter_count   = (int)$_POST['registered_voter_count'];
    $unregistered_voter_count = (int)$_POST['unregistered_voter_count'];

     // Update org_details and accreditation infos
    
    $org_info_update = "UPDATE org_details 
                        SET org_name='$org_name', office_address='$org_address', contact_number='$contact_number', 
                            date_organized='$date_organized', org_level='$org_level', linkages_membership='$linkages', sector_served='$sector_served'
                        WHERE org_id='$org_id'";

    $acc_info_update = "UPDATE accreditation
                        SET renewal_status='$renewal_status', male_members=$male_members, female_members=$female_members, 
                            total_members=$total_members, registered_voter_count=$registered_voter_count, unregistered_voter_count=$unregistered_voter_count
                        WHERE org_id='$org_id' AND accreditation_year='$accreditation_year'";

    $result_org = mysqli_query($conn, $org_info_update);
    $result_acc = mysqli_query($conn, $acc_info_update);

    // #2 Delete and Re-Insert Checkbox/Radio tables to avoid anomalies
    if ($result_org && $result_acc) {
        
        // registering_agency
        mysqli_query($conn, "DELETE FROM Registering_Agency WHERE org_id = '$org_id' AND accreditation_year = '$accreditation_year'");
        if (!empty($_POST['registering_agency']) && is_array($_POST['registering_agency'])) {
            foreach ($_POST['registering_agency'] as $agency) {
                if ($agency === 'Other') { $agency = $_POST['registering_agency_other']; }
                $agency = mysqli_real_escape_string($conn, trim($agency));
                if (!empty($agency)) {
                    mysqli_query($conn, "INSERT INTO Registering_Agency (org_id, accreditation_year, registering_agency) VALUES ('$org_id', '$accreditation_year', '$agency')");
                }
            }
        }

        // purpose
        mysqli_query($conn, "DELETE FROM Purpose WHERE org_id = '$org_id' AND accreditation_year = '$accreditation_year'");
        if (!empty($_POST['purpose']) && is_array($_POST['purpose'])) {
            foreach ($_POST['purpose'] as $purpose) {
                if ($purpose === 'Other') { $purpose = $_POST['purpose_other']; }
                $purpose = mysqli_real_escape_string($conn, trim($purpose));
                if (!empty($purpose)) {
                    mysqli_query($conn, "INSERT INTO Purpose (org_id, accreditation_year, purpose) VALUES ('$org_id', '$accreditation_year', '$purpose')");
                }
            }
        }

        // services_facilities
        mysqli_query($conn, "DELETE FROM services_facilities WHERE org_id = '$org_id' AND accreditation_year = '$accreditation_year'");
        if (!empty($_POST['services']) && is_array($_POST['services'])) {
            foreach ($_POST['services'] as $service) {
                if ($service === 'Other') { $service = $_POST['services_other']; }
                $service = mysqli_real_escape_string($conn, trim($service));
                if (!empty($service)) {
                    mysqli_query($conn, "INSERT INTO services_facilities (org_id, accreditation_year, services_facilities) VALUES ('$org_id', '$accreditation_year', '$service')");
                }
            }
        }

        // finance
        mysqli_query($conn, "DELETE FROM finance WHERE org_id = '$org_id' AND accreditation_year = '$accreditation_year'");
        if (!empty($_POST['funds']) && is_array($_POST['funds'])) {
            foreach ($_POST['funds'] as $fund) {
                if ($fund === 'Other') { $fund = $_POST['funds_other']; }
                $fund = mysqli_real_escape_string($conn, trim($fund));
                if (!empty($fund)) {
                    mysqli_query($conn, "INSERT INTO finance (org_id, accreditation_year, financing_source) VALUES ('$org_id', '$accreditation_year', '$fund')");
                }
            }
        }

        // local_body_priority
        mysqli_query($conn, "DELETE FROM local_body_priority WHERE org_id = '$org_id' AND accreditation_year = '$accreditation_year'");
        if (!empty($_POST['priority']) && is_array($_POST['priority'])) {
            foreach ($_POST['priority'] as $priority) {
                if ($priority === 'Other') { $priority = $_POST['priority_other']; }
                $priority = mysqli_real_escape_string($conn, trim($priority));
                if (!empty($priority)) {
                    mysqli_query($conn, "INSERT INTO local_body_priority (org_id, accreditation_year, local_body_priority) VALUES ('$org_id', '$accreditation_year', '$priority')");
                }
            }
        }

        // Redirecting back to update_record.php with a success message (or yung popup message na lang)
        header("Location: update_record.php?message=" . urlencode("Record updated successfully!") . "&message_type=success");
        exit;
    } else {
        // SQL ERROR
        $err = urlencode("SQL Error: " . mysqli_error($conn));
        header("Location: edit.php?org_id=$org_id&accreditation_year=$accreditation_year&message=$err&message_type=error");
        exit;
    }
}
?>