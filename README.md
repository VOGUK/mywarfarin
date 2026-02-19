# My Warfarin Tracker

My Warfarin is a secure, mobile-responsive web application designed to help patients easily track their INR blood test results and manage their daily Warfarin dosages. 

Built as a lightweight Single Page Application (SPA), it requires absolutely **zero database configuration** to install. Just upload the files to a standard PHP web host, including shared web hosting, and the app will automatically generate and manage its own SQLite database.

## ✨ Key Features

* **Dashboard & Analytics:** View your last 12 months of INR results plotted on a dynamic graph with visual target range indicators.
* **Interactive Dosage Calendar:** A mobile-friendly monthly calendar grid to record and view prescribed daily Warfarin dosages (in mg).
* **Automated PDF & CSV Reports:** Instantly generate, format, and download clinical PDF reports or CSV spreadsheets entirely in the browser.
* **Multi-User & Role Management:** Built-in Admin panel to create users and assign roles (`Admin`, `Full User`, `Viewer`).
* **Secure Data Sharing:** Generate a unique 10-character share code to allow doctors or family members to view (but not edit) your data securely.
* **Offline Backup & Restore:** Download your entire database as a secure JSON file to keep offline, with a simple 1-click restore function.
* **Native App Feel:** Fully mobile-responsive UI with swipeable menus, plus Light/Dark modes and text-scaling for accessibility. Add it to your phone's Home Screen for a native app experience.

## 🛠 Technology Stack

* **Frontend:** HTML5, CSS3, Vanilla JavaScript
* **Backend:** PHP 7.4+
* **Database:** SQLite (Auto-generated via PDO)
* **Libraries:** [Chart.js](https://www.chartjs.org/) (Data visualization), [jsPDF & AutoTable](https://github.com/simonbengtsson/jsPDF-AutoTable) (Client-side PDF generation)

## 🚀 Installation & Setup

1. **Download or Clone** this repository to your local machine.
2. **Upload** the following files to your PHP-enabled web hosting server (e.g., inside `public_html/mywarfarin/`):
   * `index.html`
   * `api.php`
   * `Warfarin.png`
   * `.htaccess` (Crucial for security!)
   * `robots.txt`
3. **Navigate** to the folder in your web browser (e.g., `https://yourdomain.com/mywarfarin/index.html`).
4. The system will automatically create the `mywarfarin.sqlite` database file on its first run.
5. **Log in** using the default administrator credentials:
   * **Username:** `admin`
   * **Password:** `admin`

*(Note: It is highly recommended to change the admin password immediately or create a new personal admin account and delete the default one.)*

## 🔒 Security Configuration

Because this app uses a flat-file SQLite database, you **must** ensure your web server blocks public access to the `.sqlite` file so nobody can download it directly. 

This repository includes an `.htaccess` file that handles this automatically on Apache servers:
```apache
<Files ~ "\.(sqlite|db)$">
    Order allow,deny
    Deny from all
</Files>
