# My Warfarin

**My Warfarin** is a lightweight, self-hosted Progressive Web App (PWA) designed to help patients easily track their INR blood test results and manage their daily Warfarin dosages. 

---

## 🚀 Key Features

| Feature | Description |
| --- | --- |
| **Smart Dashboard** | Instant view of today's dose and a 12-point INR trend chart. |
| **Visual Calendar** | A monthly grid to log, edit, or delete dosages with "Today" highlighting. |
| **Family Sharing** | Share a unique code with family or carers so they can monitor your data in real-time. |
| **PDF Reporting** | Generate professional, print-ready INR history and visual monthly calendar reports. |
| **Data Sovereignty** | Secure backup and restore via JSON files. Your data stays yours. |
| **Dark Mode** | Context-aware Sun/Moon icons with a UI that's easy on the eyes (day or night). 

---

## 🛠️ Technical Stack

This project is built to be fast, private, and easy to deploy:

* **Frontend:** Vanilla JS, HTML5, CSS3 (Custom Properties/Variables).
* **Charts:** [Chart.js](https://www.chartjs.org/) for beautiful trend visualization.
* **Reports:** [jsPDF](https://github.com/parallax/jsPDF) & [AutoTable](https://github.com/simonbengtsson/jsPDF-AutoTable).
* **Backend:** PHP 7.4+ (Lightweight API).
* **Database:** SQLite (No complex SQL setup required; just a single file).
* **PWA:** Service Workers and `manifest.json` for offline capabilities and "Install to Home Screen" support.

---

## 📦 Installation & Setup

1. **Clone the Repo:**
```bash
git clone https://github.com/yourusername/my-warfarin.git

```

2. **Upload to Server:**
Upload all files to your PHP-enabled web server (e.g., `public_html/inr/`).

3. **Set Permissions:**
* Ensure the folder is writable so the app can create `mywarfarin.sqlite`.
* Ensure `manifest.json` and `Warfarin.png` are publicly readable (Permission `644`).

4. **Login:**
* Default Username: `admin`
* Default Password: `admin`
* *Note: Change these immediately in the Settings/Admin panel!*

---

## ⚠️ Medical Disclaimer & Safety

> **IMPORTANT:** This application is a data-logging tool and **must NOT** be used as a primary medical device.
> The "Daily Reminder" feature relies on browser-internal timers which can be silenced by OS-level battery saving. **Users must rely on a dedicated, fail-safe alarm or physical pill-organizer for medication adherence. Always consult with your healthcare provider before adjusting your dosage.**

---

## 📱 PWA Features

To get the full "App" experience:
1. Open the site in Chrome (Android) or Safari (iOS).
2. Select **"Add to Home Screen"**.
3. The app will now launch without the browser address bar, providing a native look and feel, even when offline.

---

## ⚖️ License

Distributed under the MIT License. See `LICENSE` for more information.
