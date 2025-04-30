
# 🎯 Mass Reader Scheduling App

Welcome to the **Mass Reader Scheduling System** — a web-based platform designed to **manage readers, events, and system settings** efficiently and securely.
Built with **PHP**, **MySQL**, **Bootstrap 5**, and **FontAwesome**, it ensures a smooth admin experience with role-based access control.

---

## 📂 Project Structure

```
/project-root
|-- /includes/
|    |-- db.php            # Database connection
|-- /templates/
|    |-- header.php        # Header (navigation, Bootstrap setup)
|    |-- footer.php        # Footer
|-- /admin/
|    |-- dashboard.php     # Admin dashboard (overview)
|    |-- manage_users.php  # Manage users (CRUD)
|    |-- events.php        # Manage events
|    |-- reports.php       # View reports
|    |-- settings.php      # System settings
|-- /reader/
|    |-- dashboard.php     # Reader dashboard
|-- /coordinator/
|    |-- dashboard.php     # Coordinator dashboard
|-- login.php              # Login page with role-based redirection
|-- logout.php             # Secure logout
|-- forgot-password.php    # Forgot password (email link)
|-- update-password.php    # Password reset
```

---

## ✨ Features

- 🔒 **Secure Login System** (session-based, OTP validation after login)
- 🛡 **Role-Based Access**:
  - Admin
  - Coordinator
  - Reader
- 👤 **User Management** (Create, Edit, Delete users with Bootstrap modals)
- 📅 **Event Management** (Assign readers to events/masses)
- 📊 **Reports Section** (Attendance, assignments, activities)
- ⚙️ **System Settings** (Edit general settings)
- 📧 **Email Notifications** (Registration credentials, OTP login codes)
- 🔥 **Fully Responsive Design** (Mobile-ready)
- 🌍 **Timezone:** `Africa/Kigali`

---

## 🚀 Quick Setup

1. **Clone the Repository:**

   ```bash
   git clone https://github.com/SilasHakuzwimana/mass-reader-scheduler.git
   cd mass-reader-scheduler
   ```
2. **Database Setup:**

   - Create a database (e.g., `mass_reader_db`).
   - Import the provided SQL file (`database.sql`) into your database.
3. **Configure Database:**

   - Edit `/includes/db.php` with your database credentials:
     ```php
     $conn = new mysqli('localhost', 'your_username', 'your_password', 'mass_reader_db');
     ```
4. **SMTP Setup for Emails:**

   - Configure your SMTP credentials inside the email functions (e.g., in `includes/functions.php`).
   - You can use Gmail SMTP, SendGrid, Mailgun, PHPMailer, etc.
5. **Run Locally or Deploy:**

   - Place files in your web server's public directory (e.g., `htdocs/` for XAMPP).
   - Access via `http://localhost/mass-reader-scheduler/login.php`.

---

## 🛠 Built With

- **PHP 8.x**
- **MySQL 8.x**
- **Bootstrap 5**
- **FontAwesome**
- **SweetAlert2** (optional - for alerts/toasts)
- **DataTables** (optional - if you want table searching/sorting)

## 🙌 Contributors

- ✍️ Designed and Developed by **Silas HAKUZWIMANA [hakuzwisilas@hmail.com]**
- Special thanks to contributors and testers.

---

## 📝 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

# 🚀 Future Improvements

- Reader Self-Registration
- Event Reminders via Email/SMS
- Attendance Tracking with QR Code
- Password Change Module
- Notifications Panel

---

# 🔥 Live Demo (Optional)

> Coming soon... Stay tuned! 😉
>
