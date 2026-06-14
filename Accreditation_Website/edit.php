<?php
include 'connect_db.php';

//initiating global variables
$row = null;
$org_id = null;
$accreditation_year = null;

// Initialize all your child table arrays here so they are NEVER undefined
$agencies = [];
$purposes = [];
$services = [];
$funds = [];
$priorities = [];

// Helper variables to hold custom "Other" inputs if found
$agency_other_val = "";
$purpose_other_val = "";
$services_other_val = "";
$funds_other_val = "";
$priority_other_val = "";

// Define standard arrays to detect custom "Other" database entries
$standard_agencies   = ['SEC', 'CDA', 'HLURB', 'DOLE', 'DSWD'];
$standard_purposes   = ['Social Justice', 'Livelihood', 'Youth & Sports', 'Environmental', 'Senior Citizens'];
$standard_services   = ['Educational', 'Advocacy', 'Health', 'Disaster', 'Livelihood'];
$standard_funds      = ['Membership Dues', 'Fund Raising', 'Local Domain', 'Foreign Donation', 'Local Grant', 'Foreign Grant'];
$standard_priorities = ['BDC', 'GAD', 'VAWC', 'BCPC', 'BADAAC', 'BHC', 'BPOC', 'BDRRMC', 'BTFP', 'BTFK', 'BDRRMC_Comm', 'BESWMC'];

// 2. Open your conditional check block safely
if (isset($_GET['org_id']) && isset($_GET['accreditation_year'])) {
    $org_id = mysqli_real_escape_string($conn, $_GET['org_id']);
    $accreditation_year = mysqli_real_escape_string($conn, $_GET['accreditation_year']);

    // Fetch the existing data from both main tables
    $query = "SELECT o.org_ID AS org_id, o.*, a.renewal_status, a.male_members, a.female_members, a.total_members, 
                     a.registered_voter_count, a.unregistered_voter_count
              FROM org_details o
              LEFT JOIN accreditation a ON o.org_id = a.org_id
              WHERE o.org_ID = '$org_id' AND a.accreditation_year = '$accreditation_year'";
    
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Database Error: " . mysqli_error($conn));
    }
    $row = mysqli_fetch_assoc($result);
    
    if (!$row) {
        die("Record not found!");
    }

    // 3. Populate child arrays and separate custom "Other" inputs
    $res = mysqli_query($conn, "SELECT registering_agency FROM Registering_Agency WHERE org_id='$org_id' AND accreditation_year='$accreditation_year'");
    while($r = mysqli_fetch_assoc($res)) { 
        $val = $r['registering_agency'];
        if (in_array($val, $standard_agencies)) { $agencies[] = $val; } else { $agencies[] = 'Other'; $agency_other_val = $val; }
    }

    $res = mysqli_query($conn, "SELECT purpose FROM Purpose WHERE org_id='$org_id' AND accreditation_year='$accreditation_year'");
    while($r = mysqli_fetch_assoc($res)) { 
        $val = $r['purpose'];
        if (in_array($val, $standard_purposes)) { $purposes[] = $val; } else { $purposes[] = 'Other'; $purpose_other_val = $val; }
    }

    $res = mysqli_query($conn, "SELECT services_facilities FROM services_facilities WHERE org_id='$org_id' AND accreditation_year='$accreditation_year'");
    while($r = mysqli_fetch_assoc($res)) { 
        $val = $r['services_facilities'];
        if (in_array($val, $standard_services)) { $services[] = $val; } else { $services[] = 'Other'; $services_other_val = $val; }
    }

    $res = mysqli_query($conn, "SELECT financing_source FROM finance WHERE org_id='$org_id' AND accreditation_year='$accreditation_year'");
    while($r = mysqli_fetch_assoc($res)) { 
        $val = $r['financing_source'];
        if (in_array($val, $standard_funds)) { $funds[] = $val; } else { $funds[] = 'Other'; $funds_other_val = $val; }
    }

    $res = mysqli_query($conn, "SELECT local_body_priority FROM local_body_priority WHERE org_id='$org_id' AND accreditation_year='$accreditation_year'");
    while($r = mysqli_fetch_assoc($res)) { 
        $val = $r['local_body_priority'];
        if (in_array($val, $standard_priorities)) { $priorities[] = $val; } else { $priorities[] = 'Other'; $priority_other_val = $val; }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barangay Bagbag Accreditation Form</title>
    <link rel="stylesheet" href="css/create.css">
    <style>
        .btn-cancel {
            padding: 12px 25px; background: #6c757d; color: white; text-decoration: none; 
            border-radius: 8px; font-weight: bold; display: inline-block; transition: 0.3s;
        }
        .btn-cancel:hover { background: #5a6268; }
        .action-container { display: flex; gap: 15px; justify-content: center; margin-top: 30px; }
        .other-input { width: auto; display: inline-block; margin-left: 10px; padding: 4px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>

    <div class="form-container">
        <?php if ($row): ?>
        <header class="form-header">
            <h3>Republika ng Pilipinas</h3>
            <h3>Tanggapan ng Punong Barangay</h3>
            <h3>Barangay Bagbag, Novaliches Distrito 5</h3>
            <h3>Lungsod ng Quezon</h3>
            <hr>
            <h2>Update Application for Accreditation</h2>
            <h3 style="color: #007BFF; margin-top: 10px;">Editing Org ID: <?php echo htmlspecialchars($row['org_id']); ?> | Year: <?php echo htmlspecialchars($accreditation_year); ?></h3>
        </header>

        <div class="break"></div>

        <form action="update_action.php?org_id=<?php echo urlencode($org_id); ?>&accreditation_year=<?php echo urlencode($accreditation_year); ?>" method="POST">
            
            <div class="form-group options-list" style="flex-direction: row; gap: 30px; justify-content: center; margin-bottom: 30px;">
                <label class="status-label required">Renewal Status:</label>
                <label class="option-item">
                    <input type="radio" name="application_status" value="New" <?php echo ($row['renewal_status'] === 'New') ? 'checked' : ''; ?> required> New
                </label>
                <label class="option-item">
                    <input type="radio" name="application_status" value="Previously Accredited" <?php echo ($row['renewal_status'] === 'Previously Accredited') ? 'checked' : ''; ?> required> Previously Accredited
                </label>
            </div>

            <div class="break"></div>

            <div class="form-group">
                <div class="row-group">
                    <label class="status-label required">Accreditation Year:</label>
                    <input type="number" value="<?php echo htmlspecialchars($accreditation_year); ?>" disabled>
                </div>
                <div class="row-group">
                    <label class="status-label required">Organization ID:</label>
                    <input type="text" value="<?php echo htmlspecialchars($row['org_id']); ?>" disabled>
                </div>
                <div class="row-group">
                    <label class="status-label required">Name of Organization/Association</label> 
                    <input type="text" name="org_name" value="<?php echo htmlspecialchars($row['org_name']); ?>" maxlength="70" required>
                </div>
                <div class="row-group">
                    <label>Office Address:</label>
                    <input type="text" name="org_address" value="<?php echo htmlspecialchars($row['office_address']); ?>" maxlength="100" required>
                </div>
                <div class="row-group">
                    <label>Date Organized/Registered:</label>
                    <input type="date" name="date_organized" value="<?php echo htmlspecialchars($row['date_organized']); ?>" required>
                </div>
                <div class="row-group">
                    <label>Contact Number:</label>
                    <input type="tel" name="contact_number" value="<?php echo htmlspecialchars($row['contact_number']); ?>" pattern="\d*" maxlength="11" required>
                </div>
            </div>

            <div class="break"></div>

            <div class="form-group">
                <label class="question-title required">Registering Agency (Please check appropriate box)</label>
                <div class="options-list">
                    <label class="option-item"><input type="checkbox" name="registering_agency[]" value="SEC" <?php echo in_array('SEC', $agencies) ? 'checked' : ''; ?>> 1. Security and Exchange Commission</label>
                    <label class="option-item"><input type="checkbox" name="registering_agency[]" value="CDA" <?php echo in_array('CDA', $agencies) ? 'checked' : ''; ?>> 2. Cooperative Development Authority</label>
                    <label class="option-item"><input type="checkbox" name="registering_agency[]" value="HLURB" <?php echo in_array('HLURB', $agencies) ? 'checked' : ''; ?>> 3. Housing and Land Use Regulatory</label>
                    <label class="option-item"><input type="checkbox" name="registering_agency[]" value="DOLE" <?php echo in_array('DOLE', $agencies) ? 'checked' : ''; ?>> 4. Department of Labor and Employment</label>
                    <label class="option-item"><input type="checkbox" name="registering_agency[]" value="DSWD" <?php echo in_array('DSWD', $agencies) ? 'checked' : ''; ?>> 5. Department of Social Welfare and Development</label>
                    <label class="option-item">
                        <input type="checkbox" name="registering_agency[]" value="Other" <?php echo in_array('Other', $agencies) ? 'checked' : ''; ?>> 
                        6. Other (Please Specify): 
                        <input type="text" class="other-input" name="registering_agency_other" value="<?php echo htmlspecialchars($agency_other_val); ?>" maxlength="45">
                    </label>
                </div>
            </div>

            <div class="break"></div>

            <div class="form-group">
                <label class="question-title required">Organizational Level: (Please check applicable box)</label>
                <div class="options-list">
                    <label class="option-item"><input type="radio" name="org_level" value="Barangay-Based" <?php echo ($row['org_level'] === 'Barangay-Based' || $row['org_level'] === 'Chapter' || $row['org_level'] === 'Affiliate of Large NGO' ? '' : ($row['org_level'] ? '' : '')) || $row['org_level'] === 'Barangay-Based' ? 'checked' : ''; ?> required> 1. Barangay-Based</label>
                    <label class="option-item"><input type="radio" name="org_level" value="Chapter" <?php echo ($row['org_level'] === 'Chapter') ? 'checked' : ''; ?> required> 2. Chapter</label>
                    <label class="option-item"><input type="radio" name="org_level" value="Affiliate of Large NGO" <?php echo ($row['org_level'] === 'Affiliate of Large NGO') ? 'checked' : ''; ?> required> 3. Affiliate of Large NGO</label>
                    <label class="option-item">
                        <?php $is_level_other = !empty($row['org_level']) && !in_array($row['org_level'], ['Barangay-Based', 'Chapter', 'Affiliate of Large NGO']); ?>
                        <input type="radio" name="org_level" value="Other" <?php echo $is_level_other ? 'checked' : ''; ?> required> 
                        4. Other (Please Specify): 
                        <input type="text" class="other-input" name="org_level_other" value="<?php echo $is_level_other ? htmlspecialchars($row['org_level']) : ''; ?>" maxlength="22">
                    </label>
                </div>
            </div>

            <div class="break"></div>

            <div class="form-group">
                <label class="question-title required">Linkages/Memberships:</label>
                <div class="options-grid">
                    <?php 
                    $linkage_options = ['Barangay', 'Municipal', 'City', 'Regional', 'National', 'International'];
                    foreach ($linkage_options as $index => $opt): 
                    ?>
                        <label class="option-item">
                            <input type="radio" name="linkages" value="<?php echo $opt; ?>" <?php echo ($row['linkages_membership'] === $opt) ? 'checked' : ''; ?> required> 
                            <?php echo ($index + 1) . ". " . $opt; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="break"></div>

            <div class="form-group">
                <label class="question-title required">Purpose/Objectives of the Organization:</label>
                <div class="single-column-stack">
                    <label class="option-item"><input type="checkbox" name="purpose[]" value="Social Justice" <?php echo in_array('Social Justice', $purposes) ? 'checked' : ''; ?>> 1. Social Justice</label>
                    <label class="option-item"><input type="checkbox" name="purpose[]" value="Livelihood" <?php echo in_array('Livelihood', $purposes) ? 'checked' : ''; ?>> 2. Livelihood</label>
                    <label class="option-item"><input type="checkbox" name="purpose[]" value="Youth & Sports" <?php echo in_array('Youth & Sports', $purposes) ? 'checked' : ''; ?>> 3. Youth & Sports</label>
                    <label class="option-item"><input type="checkbox" name="purpose[]" value="Environmental" <?php echo in_array('Environmental', $purposes) ? 'checked' : ''; ?>> 4. Environmental</label>
                    <label class="option-item"><input type="checkbox" name="purpose[]" value="Senior Citizens" <?php echo in_array('Senior Citizens', $purposes) ? 'checked' : ''; ?>> 5. Senior Citizens</label>
                    <label class="option-item" style="grid-column: 1 / -1;">
                        <input type="checkbox" name="purpose[]" value="Other" <?php echo in_array('Other', $purposes) ? 'checked' : ''; ?>> 
                        6. Others (Please Specify):
                        <input type="text" class="other-input" name="purpose_other" value="<?php echo htmlspecialchars($purpose_other_val); ?>" style="max-width: 500px;" maxlength="15">
                    </label>
                </div>
            </div>

            <div class="break"></div>

            <div class="form-group">
                <label class="question-title required">Services/Facilities the Organization can provide or participate in:</label>
                <div class="single-column-stack">
                    <label class="option-item"><input type="checkbox" name="services[]" value="Educational" <?php echo in_array('Educational', $services) ? 'checked' : ''; ?>> 1. Educational Facilities</label>
                    <label class="option-item"><input type="checkbox" name="services[]" value="Advocacy" <?php echo in_array('Advocacy', $services) ? 'checked' : ''; ?>> 2. Advocacy & Clean-ups</label>
                    <label class="option-item"><input type="checkbox" name="services[]" value="Health" <?php echo in_array('Health', $services) ? 'checked' : ''; ?>> 3. Health Facilities</label>
                    <label class="option-item"><input type="checkbox" name="services[]" value="Disaster" <?php echo in_array('Disaster', $services) ? 'checked' : ''; ?>> 4. Disaster Response</label>
                    <label class="option-item"><input type="checkbox" name="services[]" value="Livelihood" <?php echo in_array('Livelihood', $services) ? 'checked' : ''; ?>> 5. Livelihood Facilities</label>
                    <label class="option-item">
                        <input type="checkbox" name="services[]" value="Other" <?php echo in_array('Other', $services) ? 'checked' : ''; ?>> 
                        6. Others (Please Specify):
                        <input type="text" class="other-input" name="services_other" value="<?php echo htmlspecialchars($services_other_val); ?>" maxlength="22">
                    </label>
                </div>
            </div>

            <div class="break"></div>

            <div class="form-group">
                <label class="question-title required">Sector/Group Represented/Served (Please check only one [1]):</label>
                <div class="options-grid">
                    <?php 
                    $sectors = ['Academe', 'Environmental', 'Transport', 'Urban Poor', 'Religious', 'Homeowners', 'Cooperatives', 'Professional', 'Charitable', 'Livelihood', 'Women', 'Social', 'PWD', 'Youth', 'Senior Citizens', 'Labor', 'Business', 'Social Justice', 'Health'];
                    foreach ($sectors as $ind => $sec):
                        $label_map = [
                            'Academe' => 'Academe Education', 'Environmental' => 'Environmental/Urban Protection/Solid Waste',
                            'Transport' => 'Transport/PUV Drivers/Operators/Toda', 'Urban Poor' => 'Urban Poor',
                            'Religious' => 'Religious', 'Homeowners' => 'Homeowners/Neighborhood', 'Cooperatives' => 'Cooperatives',
                            'Professional' => 'Professional', 'Charitable' => 'Charitable/Socio-Civic', 'Livelihood' => 'Livelihood/Vendors',
                            'Women' => 'Women', 'Social' => 'Social/Cultural Development', 'PWD' => 'Person w/Disability',
                            'Youth' => 'Youth/Children/Sports', 'Senior Citizens' => 'Senior Citizens', 'Labor' => 'Labor/Works',
                            'Business' => 'Business Sector', 'Social Justice' => 'Social Justice/Peace and Order', 'Health' => 'Health Sanitation'
                        ];
                    ?>
                        <label class="option-item">
                            <input type="radio" name="sector" value="<?php echo $sec; ?>" <?php echo ($row['sector_served'] === $sec) ? 'checked' : ''; ?> required> 
                            <?php echo ($ind + 1) . ". " . $label_map[$sec]; ?>
                        </label>
                    <?php endforeach; ?>
                    <label class="option-item" style="grid-column: 1 / -1;">
                        <?php $is_sector_other = !empty($row['sector_served']) && !in_array($row['sector_served'], $sectors); ?>
                        <input type="radio" name="sector" value="Other" <?php echo $is_sector_other ? 'checked' : ''; ?> required> 
                        20. Others (Pls Specify):
                        <input type="text" class="other-input" name="sector_other" value="<?php echo $is_sector_other ? htmlspecialchars($row['sector_served']) : ''; ?>" style="max-width: 500px;" maxlength="42">
                    </label>
                </div>
            </div>

            <div class="break"></div>

            <div class="form-group">
                <label class="question-title">No. of Members (Gender):</label>
                <div class="sub-fields">
                    <div class="input-box">
                        <label>Male:</label>
                        <input type="number" id="members_male" name="male_members" min="0" max="999999" oninput="if(this.value.length > 4) this.value = this.value.slice(0,4); calculateTotalMembers();" value="<?php echo htmlspecialchars($row['male_members'] ?? 0); ?>" required>
                    </div>
                    <div class="input-box">
                        <label>Female:</label>
                        <input type="number" id="members_female" name="female_members" min="0" max="999999" oninput="if(this.value.length > 4) this.value = this.value.slice(0,4); calculateTotalMembers();" value="<?php echo htmlspecialchars($row['female_members'] ?? 0); ?>" required>
                    </div>
                    <span class="total-text">Total: <span id="total_members_display"><?php echo htmlspecialchars($row['total_members'] ?? 0); ?></span></span>
                </div>
            </div>

            <div class="break"></div>

            <div class="form-group">
                <label class="question-title">No. of Members (Voters):</label>
                <div class="sub-fields">
                    <div class="input-box">
                        <label>Registered Voters:</label>
                        <input type="number" name="registered_voter_count" min="0" max="999999" oninput="if(this.value.length > 4) this.value = this.value.slice(0,4);" value="<?php echo htmlspecialchars($row['registered_voter_count'] ?? 0); ?>" required>
                    </div>
                    <div class="input-box">
                        <label>Non-Registered:</label>
                        <input type="number" name="unregistered_voter_count" min="0" max="999999" oninput="if(this.value.length > 4) this.value = this.value.slice(0,4);" value="<?php echo htmlspecialchars($row['unregistered_voter_count'] ?? 0); ?>" required>
                    </div>
                </div>
            </div>

            <div class="break"></div>

            <div class="form-group">
                <label class="question-title required">Source of Funds (Select all that apply):</label>
                <div class="options-grid">
                    <label class="option-item"><input type="checkbox" name="funds[]" value="Membership Dues" <?php echo in_array('Membership Dues', $funds) ? 'checked' : ''; ?>> 1. Membership Dues</label>
                    <label class="option-item"><input type="checkbox" name="funds[]" value="Fund Raising" <?php echo in_array('Fund Raising', $funds) ? 'checked' : ''; ?>> 2. Fund Raising</label>
                    <label class="option-item"><input type="checkbox" name="funds[]" value="Local Domain" <?php echo in_array('Local Domain', $funds) ? 'checked' : ''; ?>> 3. Local Domain</label>
                    <label class="option-item"><input type="checkbox" name="funds[]" value="Foreign Donation" <?php echo in_array('Foreign Donation', $funds) ? 'checked' : ''; ?>> 4. Foreign Donation</label>
                    <label class="option-item"><input type="checkbox" name="funds[]" value="Local Grant" <?php echo in_array('Local Grant', $funds) ? 'checked' : ''; ?>> 5. Local Grant</label>
                    <label class="option-item"><input type="checkbox" name="funds[]" value="Foreign Grant" <?php echo in_array('Foreign Grant', $funds) ? 'checked' : ''; ?>> 6. Foreign Grant</label>
                    <label class="option-item" style="grid-column: 1 / -1;">
                        <input type="checkbox" name="funds[]" value="Other" <?php echo in_array('Other', $funds) ? 'checked' : ''; ?>> 
                        7. Others (Pls. specify):
                        <input type="text" class="other-input" name="funds_other" value="<?php echo htmlspecialchars($funds_other_val); ?>" maxlength="16">
                    </label>
                </div>
            </div>

            <div class="break"></div>

            <div class="form-group">
                <label class="question-title required">Priority Membership in Local Species Bodies (Please check only two [2]):</label>
                <div class="options-list">
                    <label class="option-item"><input type="checkbox" name="priority[]" value="BDC" <?php echo in_array('BDC', $priorities) ? 'checked' : ''; ?>> 1. Barangay Development Council (BDC)</label>
                    <label class="option-item"><input type="checkbox" name="priority[]" value="GAD" <?php echo in_array('GAD', $priorities) ? 'checked' : ''; ?>> 2. Gender and Development (GAD)</label>
                    <label class="option-item"><input type="checkbox" name="priority[]" value="VAWC" <?php echo in_array('VAWC', $priorities) ? 'checked' : ''; ?>> 3. Violence Against Women and Children (VAWC)</label>
                    <label class="option-item"><input type="checkbox" name="priority[]" value="BCPC" <?php echo in_array('BCPC', $priorities) ? 'checked' : ''; ?>> 4. Barangay Council for the Protection of Children (BCPC)</label>
                    <label class="option-item"><input type="checkbox" name="priority[]" value="BADAAC" <?php echo in_array('BADAAC', $priorities) ? 'checked' : ''; ?>> 5. Barangay Anti-Drug Abuse Advisory Council (BADAAC)</label>
                    <label class="option-item"><input type="checkbox" name="priority[]" value="BHC" <?php echo in_array('BHC', $priorities) ? 'checked' : ''; ?>> 6. Barangay Health Council (BHC)</label>
                    <label class="option-item"><input type="checkbox" name="priority[]" value="BPOC" <?php echo in_array('BPOC', $priorities) ? 'checked' : ''; ?>> 7. Barangay Peace and Order Council (BPOC)</label>
                    <label class="option-item"><input type="checkbox" name="priority[]" value="BDRRMC" <?php echo in_array('BDRRMC', $priorities) ? 'checked' : ''; ?>> 8. Barangay Disaster Risk Reduction Management (BDRRMC)</label>
                    <label class="option-item"><input type="checkbox" name="priority[]" value="BTFP" <?php echo in_array('BTFP', $priorities) ? 'checked' : ''; ?>> 9. Barangay Task Force Palengke (BTFP)</label>
                    <label class="option-item"><input type="checkbox" name="priority[]" value="BTFK" <?php echo in_array('BTFK', $priorities) ? 'checked' : ''; ?>> 10. Barangay Task Force Kalinisan (BTFK)</label>
                    <label class="option-item"><input type="checkbox" name="priority[]" value="BDRRMC_Comm" <?php echo in_array('BDRRMC_Comm', $priorities) ? 'checked' : ''; ?>> 11. Barangay Disaster Risk Reduction Management Committee (BDRRMC)</label>
                    <label class="option-item"><input type="checkbox" name="priority[]" value="BESWMC" <?php echo in_array('BESWMC', $priorities) ? 'checked' : ''; ?>> 12. Barangay Ecological Solid Waste Management Committee (BESWMC)</label>
                    <label class="option-item">
                        <input type="checkbox" name="priority[]" value="Other" <?php echo in_array('Other', $priorities) ? 'checked' : ''; ?>> 
                        13. Others (Pls. Specify):
                        <input type="text" class="other-input" name="priority_other" value="<?php echo htmlspecialchars($priority_other_val); ?>" maxlength="62">
                    </label>
                </div>
            </div>

            <div class="break"></div>

            <div class="action-container">
                <button type="submit" name="update" class="submit-btn" style="margin: 0;">Update Changes</button>
                <a href="update_record.php" class="btn-cancel">Cancel / Exit</a>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <script>
        function calculateTotalMembers() {
            var male = parseInt(document.getElementById('members_male').value) || 0;
            var female = parseInt(document.getElementById('members_female').value) || 0;
            document.getElementById('total_members_display').innerText = male + female;
        }
    </script>
    <script src="js/create.js"></script>
</body>
</html>