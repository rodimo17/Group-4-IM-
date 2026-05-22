function calculateTotalMembers() {
    const male = parseInt(document.getElementById('members_male').value) || 0;
    const female = parseInt(document.getElementById('members_female').value) || 0;
    document.getElementById('total_members_display').textContent = male + female;
}