# Giant Auto Upholstery (PHP + MySQL / phpMyAdmin)

## Setup (WampServer 64)
1. Copy this folder into `C:\wamp64\www\` and name it `giant` (so `index.php` is at `C:\wamp64\www\giant\index.php`).
2. Start WampServer and wait for the tray icon to turn **green**.
3. Open http://localhost/phpmyadmin, click the **Import** tab, choose
   `database/giant_upholstery.sql` and click **Import**.
   This creates the `giant_upholstery` database, all tables and the price list.
4. If your MySQL login is not `root` with an empty password, edit `config/config.php`.
5. Visit http://localhost/giant/

Requires PHP 7.3 or newer (works on WampServer 3.2 and XAMPP) with the `pdo_mysql` and `mbstring` extensions (both enabled by default).

## Where the data goes (view it in phpMyAdmin -> giant_upholstery)
| Table              | What it captures |
|--------------------|------------------|
| `users`            | name, email, phone, hashed password, marketing opt-in, consent time, IP, browser, created and last login time |
| `bookings`         | every booking (guests and members): service, contact details, address, date, time slot, damage notes, status, consent time, IP |
| `contact_messages` | messages sent from the Contact page |
| `login_log`        | every successful and failed login (also used to block brute-force attempts) |
| `services`         | the price list. Edit a row and the website updates. |

To confirm or complete a booking, open `bookings` in phpMyAdmin and change the `status` column
(`pending`, `confirmed`, `completed`, `cancelled`). The customer sees it on their dashboard.

## Security built in
- Passwords stored with `password_hash` (bcrypt), never in plain text
- All queries use prepared statements (SQL injection safe), all output is escaped (XSS safe)
- CSRF token on every form, hidden honeypot field against spam bots
- Login throttling: 5 failed attempts in 15 minutes blocks that email/IP
- `config/`, `includes/` and `database/` are blocked from direct browser access by `.htaccess`

## Before going live
- Set a password for the MySQL `root` user and create a dedicated database user for the site
- Turn on HTTPS (the session cookie becomes `Secure` automatically)
- You store personal information, so POPIA applies: add a privacy policy page and link it
  next to the consent checkboxes
- "Forgot password" currently sends the customer to the contact form. A self-service reset
  needs an email service (SMTP), which was not part of this project.
