 // Show the description based on selected role
 document.getElementById('role').addEventListener('change', function() {
    // Hide all descriptions first
    document.querySelectorAll('.role-description').forEach(function(desc) {
        desc.classList.remove('active');
    });
    
    // Show the selected role description
    const selectedRole = this.value;
    if (selectedRole) {
        const descElement = document.getElementById(selectedRole + '-desc');
        if (descElement) {
            descElement.classList.add('active');
        }
    }
});