# Invoice App

A full-stack invoicing web app built with PHP and MySQL. It manages clients, invoices, estimates and payments, and turns the data into a dashboard, charts and downloadable PDFs.

I built this while learning to code, adding one feature at a time and documenting the journey in Notion.

## Features

**Clients**
- Add, edit and delete clients
- Deleting is blocked (with a friendly message) while a client still has invoices

**Invoices**
- Create invoices with any number of line items (add and remove rows with JavaScript)
- Edit and delete invoices
- Terms & conditions on each invoice
- Download any invoice as a PDF
- Share an invoice through a private link that can be turned off at any time

**Payments**
- Record partial payments against an invoice
- Status updates automatically: unpaid, partial or paid
- Running total paid and balance remaining

**Estimates**
- Create estimates with line items and an expiry date
- Mark as sent or rejected
- Convert an estimate into an invoice in one click (items and terms are copied over)

**Dashboard and reports**
- Total invoiced, collected and outstanding
- Invoice counts by status
- Overdue invoice alerts
- Charts for invoiced vs collected per month and invoices by status
- Top clients by amount invoiced

## Tech Stack

- **Backend:** PHP 8 with PDO (prepared statements throughout)
- **Database:** MySQL
- **Frontend:** HTML, Bootstrap 5, vanilla JavaScript
- **Charts:** Chart.js
- **PDF generation:** Dompdf (installed with Composer)
- **Local server:** XAMPP

## What I Learned

- Designing a relational schema with foreign keys (clients, invoices, items, payments)
- Writing JOINs, GROUP BY queries and subqueries for totals and reports
- Using transactions so related inserts and deletes succeed or fail together
- Protecting against SQL injection (prepared statements) and XSS (`htmlspecialchars`)
- Deriving values (totals, balances, status) from data instead of storing them
- Generating unguessable share links with `random_bytes()`
- Managing PHP dependencies with Composer

## Getting Started

**Requirements:** XAMPP (Apache and MySQL), PHP 8+, and Composer.

1. Clone this repo into your XAMPP `htdocs` folder:
   ```
   git clone https://github.com/YOUR-USERNAME/invoice-app.git
   ```
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Create the database by running `schema.sql` in MySQL Workbench or phpMyAdmin.
4. Install dependencies from inside the project folder:
   ```
   composer install
   ```
5. Check the connection details in `db.php` (XAMPP defaults are user `root` with an empty password).
6. Open `http://localhost/invoice-app/dashboard.php`.

## Screenshots

Add your screenshots to a `screenshots` folder and link them here:

| Dashboard | Reports |
|---|---|
| ![Dashboard](screenshots/dashboard.png) | ![Reports](screenshots/reports.png) |

| Invoice | Estimates |
|---|---|
| ![Invoice](screenshots/invoice.png) | ![Estimates](screenshots/estimates.png) |

## Roadmap

- Login system for admin access
- Automated email reminders for overdue invoices
- Hosting the app online

## Author

Built by OJO in Nairobi, Kenya.
