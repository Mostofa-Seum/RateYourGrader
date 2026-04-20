# 🎓 RateYourGrader

<div align="center">
  <img src="https://img.shields.io/badge/Status-Live-success?style=for-the-badge&logo=vercel" alt="Status" />
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5" />
  <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3" />
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript" />

  <h3>🌟 <a href="http://rateyourgrader.page.gd/">Live Demo: RateYourGrader is LIVE!</a> 🌟</h3>
</div>

<br/>

**RateYourGrader** is a comprehensive, multi-role web platform designed to facilitate, manage, and moderate academic feedback between students and graders. Built from the ground up using a custom MVC architecture, it ensures a seamless and transparent environment for students to rate their professors, while institutions and moderators maintain platform integrity.

---

## 🚀 Exclusive Features

- **Custom Multi-Module MVC Architecture:** Code is securely compartmentalized into isolated modules (`Admin`, `Student`, `Reviewer`, `UniversityRepresentative`, and `Common`).
- **Dynamic Role-Based Access Control (RBAC):** Users are securely isolated based on their roles, meaning each role gets its own tailored dashboard, UI assets, and backend logic.
- **AJAX-Powered Dynamic UIs:** Smooth page transitions and dynamic table loads without page refreshes, providing a modern Single Page Application (SPA) feel.
- **Role Request System:** Users can seamlessly request role upgrades (e.g., Student to Reviewer via the Admin panel).
- **Interactive Modals & Toast Notifications:** Beautiful custom toast notifications and fully responsive confirmation modals for critical actions.
- **Robust Data Integrity:** Deeply integrated MySQL backend handling relationships between universities, professors, students, and reviews.

---

## 🎭 4-Role Implementation Architecture

The core of **RateYourGrader** revolves around its 4 distinct user roles, each with its own exclusive dashboard and feature set:

### 1️⃣ 🎓 Student (The Contributors)
Students are the primary drivers of content on the platform.
- **Interactive Dashboard:** Manage personal profiles and keep track of user activities.
- **Search Faculty:** Quickly look up professors by name, department, or university.
- **Professor Profiles:** View detailed aggregated ratings, past feedback, and overall scores of a professor.
- **Submit Feedback:** Write detailed reviews and submit numerical ratings for their professors securely.

### 2️⃣ ⚖️ Reviewer (The Moderators)
Reviewers ensure the platform stays professional, fair, and free of spam.
- **Dedicated Moderation Queue:** Their dashboard acts as a pending evaluation task list.
- **Content Decision Making:** Ability to explicitly **Approve** or **Reject** student-submitted reviews based on community guidelines.
- **Quality Assurance:** Browse professor profiles and monitor active public discussions to maintain institutional standards.

### 3️⃣ 🏛️ University Representative (The Institution)
A dedicated portal for official university delegates to monitor and manage their own specific faculty.
- **Faculty Management:** Add new professors to the platform or modify existing faculty details.
- **Data Analytics:** Access to **UniversityAnalyticsView**, providing data visualizations and aggregated reports on how their faculty is performing.
- **Institutional Oversight:** Search through their own faculty and track positive/negative trends within their specific institution.

### 4️⃣ 🛡️ Admin (The System Overlords)
Admins have complete control over the platform's ecosystem.
- **System Overview Dashboard:** A bird's-eye view of all platform statistics (total users, reviews, faculty, etc.).
- **User & Role Management:** Manage students, reviewers, and university representatives.
- **Handle Role Requests:** Approve or deny users requesting elevated permissions.
- **Global Moderation:** Admins can oversee all reviews, manage faculty globally, and ensure platform health through a centralized control panel.

---

## 🌐 Live Project

Check out the live deployment of the platform here: 
👉 **[http://rateyourgrader.page.gd/](http://rateyourgrader.page.gd/)**

---

<div align="center">
  <i>Empowering students with transparent grading information. 🎓</i>
</div>
