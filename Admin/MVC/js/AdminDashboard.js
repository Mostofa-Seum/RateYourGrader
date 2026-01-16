// --- GLOBAL VARS ---
let pendingAction = null; 
let searchTimeout = null;

// --- VIEW SWITCHING ---
function openSection(type) {
    const container = document.getElementById('admin-content-area');
    const title = document.getElementById('section-title');
    const tableDiv = document.getElementById('dynamic-table-container');
    
    // Clear previous search bars if any
    const existingSearch = document.getElementById('active-search-bar');
    if(existingSearch) existingSearch.remove();

    container.style.display = 'block';
    container.scrollIntoView({ behavior: 'smooth' });
    tableDiv.innerHTML = '<div class="loader">Loading data...</div>';

    if (type === 'users') {
        title.textContent = 'Manage Standard Users';
        injectSearchBar('Start typing username to search...', 'performUserSearch');
        fetchData('get_users', renderUserTable);
        
    } else if (type === 'reviewers') {
        title.textContent = 'Manage Reviewers';
        fetchData('get_reviewers', renderReviewerTable);
        
    } else if (type === 'unireps') {
        title.textContent = 'Manage University Representatives';
        fetchData('get_unireps', renderUniRepTable);
        
    } else if (type === 'professors') {
        title.textContent = 'Manage Faculty Data';
        injectSearchBar('Start typing faculty name to search...', 'performFacultySearch');
        fetchData('get_professors', renderProfTable);
        
    } else if (type === 'requests') {
        title.textContent = 'Pending Role Requests';
        fetchData('get_requests', renderReqTable);
        
    } else if (type === 'reviews') {
        title.textContent = 'Content Moderation (Reviews)';
        fetchData('get_reviews', renderReviewTable);
    }
}

function closeSection() {
    document.getElementById('admin-content-area').style.display = 'none';
    const existingSearch = document.getElementById('active-search-bar');
    if(existingSearch) existingSearch.remove();
}

// --- SEARCH FUNCTIONS ---

function injectSearchBar(placeholder, functionName) {
    const searchDiv = document.createElement('div');
    searchDiv.id = 'active-search-bar';
    searchDiv.style.marginBottom = '1.5rem';
    searchDiv.innerHTML = `
        <div style="position: relative;">
            <input type="text" id="search-input" placeholder="${placeholder}" 
                   oninput="${functionName}()"
                   style="padding:0.75rem; width:100%; border:1px solid #ddd; border-radius:8px; font-size:0.95rem; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        </div>
    `;
    document.querySelector('.section-header').after(searchDiv);
}

function performUserSearch() {
    const query = document.getElementById('search-input').value.trim();
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (query.length > 0) fetchData('search_users', renderUserTable, { query: query });
        else fetchData('get_users', renderUserTable);
    }, 300); 
}

function performFacultySearch() {
    const query = document.getElementById('search-input').value.trim();
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (query.length > 0) fetchData('search_professors', renderProfTable, { query: query });
        else fetchData('get_professors', renderProfTable);
    }, 300); 
}

// --- AJAX HELPERS ---
function fetchData(action, callback, payload = {}) {
    const fd = new FormData();
    fd.append('action', action);
    for (const key in payload) fd.append(key, payload[key]);

    fetch('AdminDashboard.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
        if(d.status === 'success') callback(d.data);
        else document.getElementById('dynamic-table-container').innerHTML = `<p style="color:red; text-align:center;">Error: ${d.message}</p>`;
    })
    .catch(e => console.error(e));
}

function sendAction(action, payload) {
    const fd = new FormData();
    fd.append('action', action);
    for (const key in payload) fd.append(key, payload[key]);

    fetch('AdminDashboard.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            showToast(d.message, 'success');
            setTimeout(() => location.reload(), 1500); 
        } else {
            showToast('Error: ' + d.message, 'error');
        }
    })
    .catch(e => showToast('Network Error', 'error'));
}

// --- TOAST NOTIFICATIONS ---
function showToast(message, type) {
    const toast = document.getElementById('toast-box');
    toast.textContent = message;
    toast.className = `toast-box show ${type}`;
    setTimeout(() => { toast.className = 'toast-box'; }, 3000);
}

// --- CUSTOM CONFIRMATION ---
function openConfirm(msg, actionCallback) {
    const modal = document.getElementById('confirmModal');
    document.getElementById('confirm-msg').textContent = msg;
    modal.classList.add('active');
    
    document.getElementById('confirm-btn-action').onclick = function() {
        actionCallback();
        closeConfirmModal();
    };
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('active');
}

// --- RENDERERS ---

function renderUserTable(data) {
    if(data.length === 0) return noData('No user found');
    let html = `<table class="admin-table"><thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead><tbody>`;
    data.forEach(u => {
        const role = u.Role ? u.Role : '<em style="color:#999">None</em>';
        html += `<tr>
            <td>${u.s_id}</td>
            <td>${u.Username}</td>
            <td>${u.Email}</td>
            <td>${role}</td>
            <td>
                <button class="btn btn-sm btn-success" onclick="assignRole(${u.s_id}, 'Reviewer')">Make Reviewer</button>
                <button class="btn btn-sm btn-primary" onclick="assignRole(${u.s_id}, 'UniRep')">Make UniRep</button>
                <button class="btn btn-sm btn-danger" onclick="deleteUser(${u.s_id})">Remove</button>
            </td>
        </tr>`;
    });
    document.getElementById('dynamic-table-container').innerHTML = html + `</tbody></table>`;
}

function renderReviewerTable(data) { renderRoleTable(data, 'Reviewer'); }
function renderUniRepTable(data) { renderRoleTable(data, 'UniRep'); }

function renderRoleTable(data, roleType) {
    if(data.length === 0) return noData(`No ${roleType}s found.`);
    let html = `<table class="admin-table"><thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead><tbody>`;
    data.forEach(u => {
        html += `<tr>
            <td>${u.s_id}</td>
            <td>${u.Username}</td>
            <td>${u.Email}</td>
            <td><strong style="color:var(--primary-color)">${roleType}</strong></td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="demoteUser(${u.s_id})">Demote to User</button>
                <button class="btn btn-sm btn-danger" onclick="deleteUser(${u.s_id})">Remove</button>
            </td>
        </tr>`;
    });
    document.getElementById('dynamic-table-container').innerHTML = html + `</tbody></table>`;
}

function renderProfTable(data) {
    if(data.length === 0) return noData('No faculty found.');
    let html = `<table class="admin-table"><thead><tr><th>Name</th><th>Dept</th><th>University</th><th>Actions</th></tr></thead><tbody>`;
    data.forEach(p => {
        const json = encodeURIComponent(JSON.stringify(p));
        html += `<tr>
            <td>${p.Name}</td>
            <td>${p.Department}</td>
            <td>${p.University}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="openEditProf('${json}')">Edit Details</button>
            </td>
        </tr>`;
    });
    document.getElementById('dynamic-table-container').innerHTML = html + `</tbody></table>`;
}

function renderReqTable(data) {
    if(data.length === 0) return noData('No pending requests.');
    let html = `<table class="admin-table"><thead><tr><th>User</th><th>Requested Role</th><th>Actions</th></tr></thead><tbody>`;
    data.forEach(r => {
        html += `<tr>
            <td>${r.Username}</td>
            <td>${r.requested_role}</td>
            <td>
                <button class="btn btn-sm btn-success" onclick="handleRequest(${r.id}, 'approve')">Approve</button>
                <button class="btn btn-sm btn-danger" onclick="handleRequest(${r.id}, 'reject')">Reject</button>
            </td>
        </tr>`;
    });
    document.getElementById('dynamic-table-container').innerHTML = html + `</tbody></table>`;
}

function renderReviewTable(data) {
    if(data.length === 0) return noData('No reviews found.');
    let html = `<table class="admin-table"><thead><tr><th>ID</th><th>Rating</th><th>Status</th><th>Reviewer</th><th>Review Excerpt</th><th>Action</th></tr></thead><tbody>`;
    data.forEach(r => {
        const btnText = r.status === 'Approved' ? 'Reject' : 'Approve';
        const btnClass = r.status === 'Approved' ? 'btn-danger' : 'btn-success';
        const statusColor = r.status === 'Approved' ? 'green' : 'red';
        const reviewer = r.ReviewerName ? r.ReviewerName : '<em style="color:#999">Unknown</em>';
        
        html += `<tr>
            <td>${r.r_id}</td>
            <td>${r.Overall_Rating} / 5</td>
            <td><span style="color:${statusColor}; font-weight:bold;">${r.status}</span></td>
            <td>${reviewer}</td>
            <td>${r.Review.substring(0, 40)}...</td>
            <td>
                <button class="btn btn-sm ${btnClass}" onclick="toggleReview(${r.r_id}, '${r.status}')">${btnText}</button>
            </td>
        </tr>`;
    });
    document.getElementById('dynamic-table-container').innerHTML = html + `</tbody></table>`;
}

function noData(msg) {
    return document.getElementById('dynamic-table-container').innerHTML = `
        <div style="padding:2rem; text-align:center; color:#777;">
            <div style="font-size:2rem; margin-bottom:10px;">🔍</div>
            <p>${msg}</p>
        </div>`;
}

// --- ACTIONS ---

function assignRole(id, role) {
    openConfirm(`Promote user to ${role}?`, function() {
        sendAction('assign_role', { user_id: id, role: role });
    });
}

function deleteUser(id) {
    openConfirm('Delete this user completely?', function() {
        sendAction('delete_user', { user_id: id });
    });
}

function demoteUser(id) {
    openConfirm('Demote this user to normal status?', function() {
        sendAction('demote_user', { user_id: id });
    });
}

function handleRequest(reqId, decision) {
    sendAction('handle_request', { req_id: reqId, decision: decision });
}

function toggleReview(id, currentStatus) {
    const newStatus = currentStatus === 'Approved' ? 'Reject' : 'Approve';
    openConfirm(`${newStatus} this review?`, function() {
        sendAction('toggle_review', { r_id: id, current_status: currentStatus });
    });
}

// --- PROF EDIT MODAL ---
function openEditProf(json) {
    const p = JSON.parse(decodeURIComponent(json));
    document.getElementById('edit_p_id').value = p.P_id;
    document.getElementById('edit_name').value = p.Name;
    document.getElementById('edit_dept').value = p.Department;
    document.getElementById('edit_uni').value = p.University;
    document.getElementById('editProfModal').classList.add('active');
}
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

function submitProfUpdate() {
    const pId = document.getElementById('edit_p_id').value;
    const name = document.getElementById('edit_name').value;
    const dept = document.getElementById('edit_dept').value;
    const uni = document.getElementById('edit_uni').value;
    
    closeModal('editProfModal');
    sendAction('update_professor', { p_id: pId, name: name, department: dept, university: uni });
}

window.onclick = function(e) {
    if(e.target.classList.contains('modal')) e.target.classList.remove('active');
}