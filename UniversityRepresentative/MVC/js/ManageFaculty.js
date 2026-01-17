let fetchTimeout;

// 1. AUTO-FETCH COURSE NAME (For Modal)
function fetchCourseName() {
    const cIdInput = document.getElementById('new_course_id');
    const nameInput = document.getElementById('new_course_name');
    const cId = cIdInput.value.trim();

    clearTimeout(fetchTimeout);

    if (!cId) {
        nameInput.value = '';
        nameInput.placeholder = "Waiting for ID...";
        nameInput.classList.remove('error-text');
        return;
    }

    // Debounce 300ms
    fetchTimeout = setTimeout(() => {
        const formData = new FormData();
        formData.append('action', 'get_course_name');
        formData.append('c_id', cId);

        fetch('ManageFaculty.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                nameInput.value = data.course_name;
                nameInput.classList.remove('error-text');
                nameInput.style.backgroundColor = "#e9ecef";
            } else {
                nameInput.value = "No course found";
                nameInput.classList.add('error-text');
            }
        })
        .catch(e => console.error("Fetch error:", e));
    }, 300);
}

// 2. OPEN MODAL
function openEditModal(pId) {
    const modal = document.getElementById('editModal');
    
    // Reset inputs
    document.getElementById('new_course_id').value = '';
    document.getElementById('new_course_name').value = '';
    document.getElementById('new_course_name').classList.remove('error-text');
    document.getElementById('course-list-container').innerHTML = '<div style="color:#888;">Loading courses...</div>';
    
    document.getElementById('edit_p_id').value = pId;

    fetchDetails(pId);
    modal.classList.add('active');
}

function fetchDetails(pId) {
    const fd = new FormData();
    fd.append('action', 'get_details');
    fd.append('p_id', pId);

    fetch('ManageFaculty.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            const data = d.data;
            document.getElementById('edit_name').value = data.Name;
            document.getElementById('edit_dept').value = data.Department;
            document.getElementById('edit_uni').value = data.University;
            
            renderCourseList(data.CourseData);
        } else {
            showToast('Error: ' + d.message, 'error');
        }
    })
    .catch(e => console.error(e));
}

function renderCourseList(courseString) {
    const container = document.getElementById('course-list-container');
    container.innerHTML = '';

    if (!courseString) {
        container.innerHTML = '<div class="no-courses">No courses assigned yet.</div>';
        return;
    }

    const courses = courseString.split('||');

    courses.forEach(c => {
        const parts = c.split('::');
        if (parts.length === 2) {
            const cId = parts[0];
            const cName = parts[1];

            const item = document.createElement('div');
            item.className = 'course-item';
            item.innerHTML = `
                <div class="course-info">
                    <span class="c-id">#${cId}</span>
                    <span class="c-name">${cName}</span>
                </div>
                <button class="btn-sm btn-danger" onclick="removeCourse('${cId}')">Remove</button>
            `;
            container.appendChild(item);
        }
    });
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
    setTimeout(() => location.reload(), 200); 
}

// 3. ADD COURSE (Assign existing course to this prof)
function addCourse() {
    const pId = document.getElementById('edit_p_id').value;
    const cId = document.getElementById('new_course_id').value;
    const cName = document.getElementById('new_course_name').value;

    if (!cId || !cName) {
        showToast('Please enter a valid Course ID.', 'error');
        return;
    }

    if (cName === "No course found") {
        showToast('Cannot add invalid course.', 'error');
        return;
    }

    const fd = new FormData();
    fd.append('action', 'add_course');
    fd.append('p_id', pId);
    fd.append('course_id', cId);
    fd.append('course_name', cName);

    fetch('ManageFaculty.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
        showToast(d.message, d.status);
        if(d.status === 'success') {
            document.getElementById('new_course_id').value = '';
            document.getElementById('new_course_name').value = '';
            fetchDetails(pId); // Refresh list inside modal
        }
    });
}

// 4. REMOVE COURSE
function removeCourse(cId) {
    if (!confirm("Unassign this course?")) return;

    const pId = document.getElementById('edit_p_id').value;
    const fd = new FormData();
    fd.append('action', 'remove_course');
    fd.append('c_id', cId);
    fd.append('p_id', pId); // Send P_id to delete specific relation

    fetch('ManageFaculty.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
        showToast(d.message, d.status);
        if(d.status === 'success') fetchDetails(pId);
    });
}

// 5. UPDATE PROF DETAILS
function updateProfessor() {
    const pId = document.getElementById('edit_p_id').value;
    const name = document.getElementById('edit_name').value;
    const dept = document.getElementById('edit_dept').value;
    const uni = document.getElementById('edit_uni').value;

    const fd = new FormData();
    fd.append('action', 'update_professor');
    fd.append('p_id', pId);
    fd.append('name', name);
    fd.append('department', dept);
    fd.append('university', uni);

    fetch('ManageFaculty.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => showToast(d.message, d.status));
}

// 6. DELETE PROFESSOR
function deleteProfessor() {
    if (!confirm("Delete this professor completely?")) return;

    const pId = document.getElementById('edit_p_id').value;
    const fd = new FormData();
    fd.append('action', 'delete_professor');
    fd.append('p_id', pId);

    fetch('ManageFaculty.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            showToast(d.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(d.message, 'error');
        }
    });
}

function showToast(message, type) {
    const toast = document.getElementById('toast-box');
    toast.textContent = message;
    toast.className = `toast-box show ${type}`;
    setTimeout(() => { toast.className = 'toast-box'; }, 3500);
}

window.onclick = function(e) {
    if (e.target.classList.contains('modal')) closeEditModal();
}