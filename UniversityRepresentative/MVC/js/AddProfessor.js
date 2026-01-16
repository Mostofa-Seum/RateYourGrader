function submitProfessor() {
    // Get values
    const name = document.getElementById('name').value;
    const dept = document.getElementById('department').value;
    const uni = document.getElementById('university').value;
    const c_id = document.getElementById('c_id').value; // Get Course ID
    const course_name = document.getElementById('course_name').value; // Renamed ID in HTML
    
    // Basic validation
    if (!name || !dept || !uni || !course_name || !c_id) {
        showMessage('⚠ Please fill in all fields.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'add_new_professor');
    formData.append('name', name);
    formData.append('department', dept);
    formData.append('university', uni);
    formData.append('c_id', c_id); // Send Course ID
    formData.append('course_name', course_name);

    // Disable button to indicate loading
    const btn = document.querySelector('.btn-primary');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Adding...';

    fetch('AddProfessor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = originalText;

        if (data.status === 'success') {
            showMessage('✔ ' + data.message, 'success');
            // Clear form on success
            document.getElementById('addProfForm').reset();
        } else {
            showMessage('⚠ ' + data.message, 'error');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.textContent = originalText;
        showMessage('⚠ Server Error. Check console.', 'error');
        console.error('Error:', error);
    });
}

function showMessage(message, type) {
    const box = document.getElementById('response-message');
    
    // Reset classes to trigger animation if needed
    box.className = 'message-box';
    void box.offsetWidth; 
    
    box.className = `message-box active ${type}`;
    box.textContent = message;

    // Auto hide after 4 seconds
    setTimeout(() => {
        box.className = 'message-box';
        box.textContent = '';
    }, 4000);
}