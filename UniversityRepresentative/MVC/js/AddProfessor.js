let fetchTimeout;

// 1. AUTO-FETCH COURSE NAME
function fetchCourseName() {
    const cIdInput = document.getElementById('c_id');
    const nameInput = document.getElementById('course_name');
    const cId = cIdInput.value.trim();

    // Clear previous timer
    clearTimeout(fetchTimeout);

    // If empty, reset
    if (!cId) {
        nameInput.value = '';
        nameInput.classList.remove('error-text');
        nameInput.placeholder = "Waiting for Course ID...";
        return;
    }

    // Debounce: Wait 300ms after typing stops
    fetchTimeout = setTimeout(() => {
        const formData = new FormData();
        formData.append('action', 'get_course_name');
        formData.append('c_id', cId);

        fetch('AddProfessor.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                // Success: Fill name and ensure clean state
                nameInput.value = data.course_name;
                nameInput.classList.remove('error-text');
                nameInput.style.backgroundColor = "#e9ecef"; 
                nameInput.style.color = "#333";
            } else {
                // Not Found: Show error
                nameInput.value = "No course found";
                nameInput.classList.add('error-text');
                showToast('Course not available. Please add the course first.', 'error');
            }
        })
        .catch(e => {
            console.error("Fetch error:", e);
            showToast('Error connecting to database.', 'error');
        });
    }, 300);
}

// 2. SUBMIT FORM
function submitProfessor() {
    // Get values
    const name = document.getElementById('name').value;
    const dept = document.getElementById('department').value;
    const uni = document.getElementById('university').value;
    const c_id = document.getElementById('c_id').value; 
    
    // Get course_name directly (works even if disabled)
    const course_name = document.getElementById('course_name').value;
    
    // Validation: Empty Fields
    if (!name || !dept || !uni || !c_id) {
        showToast('Please fill in all fields.', 'error');
        return;
    }

    // Validation: Invalid Course
    if (!course_name || course_name === "No course found" || course_name === "") {
        showToast('Invalid Course ID. Cannot add professor.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'add_new_professor');
    formData.append('name', name);
    formData.append('department', dept);
    formData.append('university', uni);
    formData.append('c_id', c_id);
    formData.append('course_name', course_name);

    // Disable button to prevent double submit
    const btn = document.querySelector('.btn-primary');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Adding...';

    fetch('AddProfessor.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = originalText;

        if (data.status === 'success') {
            showToast('✔ ' + data.message, 'success');
            // Reset form
            document.getElementById('addProfForm').reset();
            document.getElementById('course_name').value = '';
        } else {
            showToast('⚠ ' + data.message, 'error');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.textContent = originalText;
        showToast('Server connection failed.', 'error');
        console.error('Error:', error);
    });
}

// 3. TOAST NOTIFICATION
function showToast(message, type) {
    const toast = document.getElementById('toast-box');
    if(!toast) return;

    toast.textContent = message;
    toast.className = `toast-box show ${type}`;

    setTimeout(() => {
        toast.className = 'toast-box';
    }, 4000);
}