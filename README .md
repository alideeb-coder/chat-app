# 💬 Chat App – Real-Time Chat Application (MVP)

A full-featured real‑time chat application built with **vanilla PHP** (no frameworks) and **vanilla JavaScript** (AJAX polling), designed as a portfolio project to demonstrate backend architecture, security best practices, and frontend interactivity.

---

## 🛠️ Tech Stack & Techniques

### 🔙 Backend (PHP 8+)
| Technology / Technique | Description |
|------------------------|-------------|
| **Pure PHP (no framework)** | All backend logic is written in vanilla PHP using a custom file structure. |
| **PDO (PHP Data Objects)** | Secure database access using prepared statements and parameterized queries. |
| **Singleton Database Pattern** | A single `Database` class ensures only one PDO connection exists per request. |
| **CSRF Protection** | All POST forms and AJAX endpoints are protected by unique, session‑based tokens. |
| **Password Hashing** | Uses `password_hash()` (bcrypt) and `password_verify()` for secure storage. |
| **Session Management** | Custom `session.php` handles session start and regeneration (fixation protection). |
| **"Remember Me" – Split Token** | Implements a `selector:validator` pattern stored in a dedicated `remember_me` table. |
| **Google OAuth 2.0** | Full login flow using `google/apiclient`, with automatic account creation and local avatar download. |
| **Password Reset** | Secure token‑based reset system with expiry (UTC timing, dynamic links). |
| **Security Headers** | `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, HSTS. |
| **Error Logging** | All errors are logged to `logs/errors.log` via a custom `bootstrap.php` configuration. |
| **AJAX JSON API** | All dynamic actions (send, edit, delete, mark read, search, upload) return structured JSON. |
| **Try/Catch Exception Handling** | PDO operations are wrapped in `try/catch` blocks with proper error logging. |

### 🎨 Frontend (Vanilla JavaScript, Tailwind CSS)
| Technology / Technique | Description |
|------------------------|-------------|
| **Tailwind CSS** | Utility‑first CSS framework for rapid UI development (CDN for development). |
| **Vanilla JavaScript** | No frameworks – pure JS for all dynamic behavior. |
| **AJAX Polling** | Real‑time message updates via `fetch()` every 1 second. |
| **Event Delegation** | Click handlers for edit/delete buttons use event delegation for dynamically created elements. |
| **Dynamic DOM Manipulation** | Messages, user lists, and badges are rendered fully client‑side. |
| **Search with Debounce** | Live user search with 300ms debounce, fetching results from `search_users.php`. |
| **Online Status Indicators** | Green/grey dots updated every 5 seconds based on `last_seen` timestamps. |
| **Unread Message Badges** | Red badges displaying unread counts, updated every 5 seconds. |
| **Read Receipts (✓/✓✓)** | Sent messages show a grey check (unread) that turns blue double‑check when read. |
| **Edit & Delete Messages** | In‑place editing with save/cancel, and delete with confirmation dialog. |
| **Infinite Scroll / Auto‑Scroll** | Smooth auto‑scroll to latest messages with a "new messages" notification button. |
| **Responsive Design** | Mobile‑friendly layout with a hamburger menu and sliding sidebar overlay. |
| **Toast Notifications** | Elegant logout success toast with progress bar and auto‑dismiss. |
| **Profile Picture Upload** | AJAX upload with client‑side preview update. |

### 🗄️ Database (MySQL / MariaDB)
| Technique | Description |
|-----------|-------------|
| **Normalized Schema** | Separate tables for `users`, `messages`, and `remember_me`. |
| **Full‑Text Indexes (attempted)** | Experimented with `FULLTEXT` and `ngram` parsers for high‑performance search. |
| **Composite Indexes** | Optimized `messages` table with indexes on `(receiver_id, sender_id, is_read)`. |
| **Foreign Key Constraints** | `remember_me.user_id` references `users.id` with `ON DELETE CASCADE`. |
| **UTC Timestamps** | All time‑sensitive data (`last_seen`, `expires`) uses `UTC_TIMESTAMP()`. |

### 📦 External Libraries & APIs
| Library / API | Description |
|---------------|-------------|
| **Google API Client (`google/apiclient`)** | OAuth 2.0 authentication with Google. |
| **cURL** | Used internally by Google Client and for downloading Google avatars. |
| **Composer** | Dependency management for PHP libraries. |
| **npm / Tailwind CLI** | Local Tailwind compilation (optional, CDN used for development). |

---



## 🔐 Security Features

- **CSRF Protection** on all forms and AJAX POST endpoints.
- **Password Hashing** with bcrypt (never stored in plain text).
- **Split Token "Remember Me"** with periodic token rotation.
- **HTTP Security Headers** (CSP, X-Frame-Options, HSTS).
- **Prepared Statements (PDO)** to prevent SQL injection.
- **Output Escaping** (`htmlspecialchars()`) to prevent XSS.
- **Session Regeneration** after login and logout.
- **Cookie Security** (httponly, secure flag, SameSite implied).

---

## 🔮 Roadmap to v2.0 (Production‑Ready)

- [ ] **WebSockets (Ratchet)** – Real‑time bidirectional communication.
- [ ] **Redis** – Cache frequently accessed data (online status, unread counts).
- [ ] **ElasticSearch / Typesense** – High‑performance search engine.
- [ ] **Docker** – Containerize the full stack.
- [ ] **Cloud Storage (S3 / R2)** – Offload avatar uploads.
- [ ] **Email Service (Mailgun / SendGrid)** – Real password reset emails.
- [ ] **Job Queues (Redis Queue / php‑resque)** – Handle email sending asynchronously.
- [ ] **RESTful API** – Decouple frontend/backend for mobile apps.

---

## 👨‍💻 About This Project

This project was built from scratch as a **learning journey** to master:
- PHP backend architecture without frameworks
- Secure authentication patterns (OAuth, "remember me", password reset)
- Real‑time frontend techniques (polling, DOM manipulation)
- Performance considerations (database indexes, debouncing, event delegation)
- Professional code organization and security hardening

**It is not a framework, but a demonstration of fundamentals.**

---

## 📄 License & Credits

This project is created by **Ali Deeb** (= 👤 You) for portfolio purposes.  
All Google OAuth icons and Tailwind CSS are used under their respective licenses.
