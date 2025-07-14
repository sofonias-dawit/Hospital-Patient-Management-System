# Hospital-Patient-Management-System
A full-featured Hospital Patient Management System built on a custom PHP MVC framework, designed to manage appointments, patients, and staff roles effectively.
# Hospital Patient Management System

This is a comprehensive web application designed to streamline hospital operations by providing dedicated portals for patients, doctors, and administrators. The system is built from the ground up using a custom PHP MVC (Model-View-Controller) framework, focusing on clean code, role-based access control, and a manageable administrative backend.

---
**Key Features**

**Patient Portal**
- **Secure Registration & Login:** Patients can create their own accounts.
- **Profile Management:** Ability for patients to view and update their personal information.
- **Appointment Booking:** An intuitive interface to book appointments with available hospital departments.
- **Appointment Management:** Patients can view their upcoming and past appointment history, check the status (Pending, Approved, Completed), and see their assigned doctor.

### Administrator Dashboard
- **System Overview:** A main dashboard with key statistics (total patients, doctors, pending appointments).
- **Full CRUD Management:** Complete Create, Read, Update, and Delete functionality for:
  - **Patients:** Manage all patient records.
  - **Doctors:** Manage doctor profiles and their associated user accounts.
  - **Administrators:** Add or remove other admin users.
- **Appointment Oversight:** View all appointments in the system and assign pending appointments to available doctors.

---

## Technology Stack

- **Backend:** PHP (Custom MVC Framework)
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, Modern JavaScript
- **Key Concepts Implemented:**
  - Model-View-Controller (MVC) Architecture
  - Role-Based Access Control (RBAC)
  - Secure User Authentication with password hashing
  - RESTful routing principles

---

## Setup Instructions

1.  **Database:** Create a database (e.g., `amuhospital`) and import the provided `.sql` file to set up the necessary tables and default admin accounts.
2.  **Web Server:** Point your web server's document root to the `/public` directory of this project. `mod_rewrite` must be enabled.
3.  **Default Logins:**
    -   **Admin:** `admin` / `123`
    -   **Patient:** `abebe` / `123456`
