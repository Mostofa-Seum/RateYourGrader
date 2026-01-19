//HELPER FUNCTIONS

function toggleEditProfileSection() {
  const section = document.getElementById("edit-profile-section");
  if (!section) return;

  if (section.style.display === "none" || section.style.display === "") {
    section.style.display = "block";
    section.scrollIntoView({ behavior: "smooth" });
  } else {
    section.style.display = "none";
  }
}

function showMessage(elementId, message, type) {
  const messageBox = document.getElementById(elementId);
  if (!messageBox) return;

  messageBox.className = "message-box";
  void messageBox.offsetWidth; //reset animation
  messageBox.className = `message-box active ${type}`;
  messageBox.textContent = message;

  setTimeout(() => {
    messageBox.className = "message-box";
    messageBox.textContent = "";
  }, 4000);
}

//PROFILE UPDATES

function updateUsername() {
  const username = document.getElementById("username").value;
  const msgId = "username-message";

  if (!username) {
    showMessage(msgId, "⚠ Please enter a username.", "error");
    return;
  }

  const formData = new FormData();
  formData.append("action", "update_username");
  formData.append("username", username);

  fetch("UniversityRepDashboard.php", { method: "POST", body: formData })
    .then((r) => r.json())
    .then((d) => {
      if (d.status === "success") {
        document.querySelector(".profile-name").textContent = username;
        showMessage(msgId, "✔ " + d.message, "success");
      } else {
        showMessage(msgId, "⚠ " + d.message, "error");
      }
    })
    .catch(() => showMessage(msgId, "⚠ Server error.", "error"));
}

function updatePassword() {
  const current = document.getElementById("current-password").value;
  const newPass = document.getElementById("new-password").value;
  const confirm = document.getElementById("confirm-password").value;
  const msgId = "password-message";

  if (!current || !newPass || !confirm) {
    showMessage(msgId, "⚠ Fill all fields.", "error");
    return;
  }
  if (newPass !== confirm) {
    showMessage(msgId, "⚠ Passwords mismatch.", "error");
    return;
  }
  if (newPass.length < 6) {
    showMessage(msgId, "⚠ Min 6 chars.", "error");
    return;
  }

  const fd = new FormData();
  fd.append("action", "update_password");
  fd.append("current_password", current);
  fd.append("new_password", newPass);
  fd.append("confirm_password", confirm);

  fetch("UniversityRepDashboard.php", { method: "POST", body: fd })
    .then((r) => r.json())
    .then((d) => {
      if (d.status === "success") {
        showMessage(msgId, "✔ " + d.message, "success");
        resetPasswordForm();
      } else {
        showMessage(msgId, "⚠ " + d.message, "error");
      }
    })
    .catch(() => showMessage(msgId, "⚠ Server error.", "error"));
}

function resetPasswordForm() {
  document.getElementById("current-password").value = "";
  document.getElementById("new-password").value = "";
  document.getElementById("confirm-password").value = "";
}

//COURSE MANAGEMENT

function openCourseModal() {
  const modal = document.getElementById("courseModal");
  modal.style.display = "flex";
  fetchCourses();
}

function closeCourseModal() {
  document.getElementById("courseModal").style.display = "none";
  resetCourseForm();
}

function fetchCourses() {
  const fd = new FormData();
  fd.append("action", "get_courses");

  fetch("UniversityRepDashboard.php", { method: "POST", body: fd })
    .then((r) => r.json())
    .then((d) => {
      if (d.status === "success") renderCourses(d.data);
    });
}

function renderCourses(data) {
  const tbody = document.getElementById("courseTableBody");
  tbody.innerHTML = "";

  if (data.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="3" style="text-align:center; padding:1rem;">No courses found.</td></tr>';
    return;
  }

  data.forEach((c) => {
    const row = document.createElement("tr");
    row.innerHTML = `
            <td><span class="c-id-badge">${c.c_id}</span></td>
            <td>${c.course_name}</td>
            <td>
                <button class="btn-sm btn-secondary" onclick="editCourse(${c.serial}, '${c.c_id}', '${c.course_name}')">Edit</button>
                <button class="btn-sm btn-danger" onclick="deleteCourse(${c.serial})">Delete</button>
            </td>
        `;
    tbody.appendChild(row);
  });
}

function saveCourse() {
  const serial = document.getElementById("course_serial").value;
  const cId = document.getElementById("course_id_input").value.trim();
  const cName = document.getElementById("course_name_input").value.trim();
  const msgId = "course-message";

  if (!cId || !cName) {
    showMessage(msgId, "⚠ ID and Name required.", "error");
    return;
  }

  const fd = new FormData();
  // Determine action: Add or Update
  fd.append("action", serial ? "update_course" : "add_course");
  if (serial) fd.append("serial", serial);
  fd.append("c_id", cId);
  fd.append("c_name", cName);

  fetch("UniversityRepDashboard.php", { method: "POST", body: fd })
    .then((r) => r.json())
    .then((d) => {
      if (d.status === "success") {
        showMessage(msgId, "✔ " + d.message, "success");
        resetCourseForm();
        fetchCourses();
      } else {
        showMessage(msgId, "⚠ " + d.message, "error");
      }
    })
    .catch(() => showMessage(msgId, "⚠ Server error.", "error"));
}

function editCourse(serial, id, name) {
  document.getElementById("course_serial").value = serial;
  document.getElementById("course_id_input").value = id;
  document.getElementById("course_name_input").value = name;

  const btn = document.getElementById("saveCourseBtn");
  btn.textContent = "Update";
  btn.className = "btn btn-primary"; //Ensure standard style

  document.getElementById("cancelEditBtn").style.display = "inline-block";
}

function resetCourseForm() {
  document.getElementById("course_serial").value = "";
  document.getElementById("course_id_input").value = "";
  document.getElementById("course_name_input").value = "";
  document.getElementById("saveCourseBtn").textContent = "Add";
  document.getElementById("cancelEditBtn").style.display = "none";
}

function deleteCourse(serial) {
  if (!confirm("Are you sure? This cannot be undone.")) return;

  const fd = new FormData();
  fd.append("action", "delete_course");
  fd.append("serial", serial);

  fetch("UniversityRepDashboard.php", { method: "POST", body: fd })
    .then((r) => r.json())
    .then((d) => {
      if (d.status === "success") {
        fetchCourses();
      } else {
        alert(d.message);
      }
    });
}

// Window click to close modals
window.onclick = function (event) {
  const modal = document.getElementById("courseModal");
  if (event.target === modal) closeCourseModal();
};


