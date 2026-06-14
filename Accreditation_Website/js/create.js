//Automatic Calculation of Total Members
function calculateTotalMembers() {
    const male = parseInt(document.getElementById('members_male').value) || 0;
    const female = parseInt(document.getElementById('members_female').value) || 0;
    document.getElementById('total_members_display').textContent = male + female;
}

// Validate checkbox groups (at least 1 must be selected except local body priority)
function validateCheckboxGroup(name, displayName) {
    const checkboxes = document.querySelectorAll(`input[name="${name}"]:checked`);
    if (checkboxes.length === 0) {
        alert(`Please select at least one option (exactly 2 options for priority membership) for "${displayName}"`);
        return false;
    }
    return true;
}

function validatePriorityMembership() {
    const checkedCheckboxes = document.querySelectorAll('input[name="priority[]"]:checked');
    const count = checkedCheckboxes.length;
    if (count !== 2) {
        alert('Please select exactly two (2) Priority Membership options. You have currently selected ' + count + '.');
        return false;
    }
    return true;
}

// Main form validation
function validateForm(event) {
    // Validate Registering Agency (at least 1)
    if (!validateCheckboxGroup('registering_agency[]', 'Registering Agency')) {
        event.preventDefault();
        return false;
    }

    // Validate Purpose/Objectives (at least 1)
    if (!validateCheckboxGroup('purpose[]', 'Purpose/Objectives of the Organization')) {
        event.preventDefault();
        return false;
    }

    // Validate Services/Facilities (at least 1)
    if (!validateCheckboxGroup('services[]', 'Services/Facilities')) {
        event.preventDefault();
        return false;
    }

    // Validate Source of Funds (at least 1)
    if (!validateCheckboxGroup('funds[]', 'Source of Funds')) {
        event.preventDefault();
        return false;
    }

    // Validate Priority Membership (exactly 2)
    if (!validatePriorityMembership()) {
        event.preventDefault();
        return false;
    }

    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', validateForm);
    }

    // Add real-time validation feedback for Priority Membership
    const priorityCheckboxes = document.querySelectorAll('input[name="priority[]"]');
    priorityCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('input[name="priority[]"]:checked').length;
            
            // Disable unchecked boxes if 2 are already selected
            if (checkedCount >= 2) {
                priorityCheckboxes.forEach(cb => {
                    if (!cb.checked) {
                        cb.disabled = true;
                    }
                });
            } else {
                // Re-enable all boxes if less than 2 selected
                priorityCheckboxes.forEach(cb => {
                    cb.disabled = false;
                });
            }
        });
    });
});
