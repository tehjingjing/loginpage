# PHP Login Assignment — Sessions & Cookies in Docker

A small multi-page PHP app that demonstrates session-based login state,
a cookie-based feature, basic output-escaping security, and containerization with Docker.

# Project structure

```
login-assignment/
├── Dockerfile
├── docker-compose.yml
├── README.md
└── src/
    ├── index.php
    ├── login.php
    ├── dashboard.php
    └── logout.php
```
## 1. Build and Run the containers

```
docker compose up --build
```

Then open **http://localhost:8000** in your browser.

## 3. Test credentials

| Username | Password |
|----------|----------|
| alice    | pass123  |
| bob      | hunter2  |

## 4. Cookie feature (Part B) and why it belongs in a cookie

On successful login, the app sets a plain cookie called `last_login` containing the timestamp of that login, with a 30-day expiry — independent of the PHP session. The dashboard reads this cookie back and shows the *previous* login time ("Last visited"). A second cookie, `remembered_username`, optionally pre-fills the username field on the login form (never the password).

This data belongs in a cookie rather than the session because cookies are stored locally on the client’s browser with an explicit expiration date, they can keep working even after the browser is completely closed and reopened. This kind of data belongs in a cookie instead of a session because sessions are short-lived and automatically destroy their data as soon as the browser tab is closed. By using cookie here, the applications allow visitors to keep their customized preferences without adding unnecessary overhead or persistent storage state requirements onto the server side. 

## 5. Stretch goals attempted

- **Failed-attempt lockout**: after 3 failed login attempts, the app tracks the count in `$_SESSION['failed_attempts']` and locks the login form for 30 seconds (`$_SESSION['lockout_until']`), showing a countdown-style error message and disabling the form fields until the lockout expires.
- **Session timeout**: `$_SESSION['last_activity']` is refreshed on every authenticated request. If more than 2 minutes pass with no request, the next request to `dashboard.php` destroys the session (including the session cookie) and redirects to `login.php?timeout=1`. Multi-container Redis sessions and Render deployment were not attempted.

## 7. Implementation notes

- `session_start()` / `setcookie()` calls happen before any HTML output on every page.
- `dashboard.php` checks `$_SESSION['username']` at the very top of the script and redirects unauthenticated users to `login.php`.
- `logout.php` clears `$_SESSION`, calls `session_destroy()`, and explicitly expires the `PHPSESSID` cookie itself.
- All user-supplied and cookie-derived data is escaped with `htmlspecialchars()` before being echoed into HTML.
- Passwords are hardcoded in `login.php` (hashing is out of scope per the assignment) and are never written into a cookie, session value, or URL.
- The Docker image sets an explicit, writable `session.save_path` (`/var/lib/php/sessions`) so PHP sessions work correctly inside the container regardless of the host machine.

## 8. Stop it

```
docker compose down
```

Add `-v` (`docker compose down -v`) if you also want to wipe stored session data.



