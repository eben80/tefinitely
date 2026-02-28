# TEFinitely.ca - Internal Documentation

## 1. Project Overview
TEFinitely.ca is a web application designed to help users prepare for the TEF Canada (Test d'Évaluation de Français) exam. It provides interactive tools, flashcards, and structured training modules to improve French language proficiency, specifically targeting the Oral Expression component of the exam.

## 2. Core Functionalities & Content

### 2.1 Oral Expression
The site focuses heavily on the Oral Expression section of the TEF Canada exam, which is split into two main sections:
- **Section A (Asking Questions)**: Users practice gathering information from an advertisement by asking relevant questions. The tool provides a 5-minute countdown and a question counter (targeting 10 questions).
- **Section B (Persuasion/Argumentation)**: Users engage in realistic dialogues to practice persuading a friend or acquaintance about a specific topic.
- **Interactive Practice**: Both sections utilize a chat-like interface with AI-powered feedback, suggestions, and hints.

### 2.2 Flashcards
Located under `oral_expression_section_a.php`, this tool helps users master essential phrases. Features include:
- Audio pronunciation using Web Speech API.
- Flip functionality for English-French translations.
- Categorization by topics (e.g., jobs, housing, leisure).

### 2.3 Phased Training
Accessible via `training.php`, this provides a structured path for learners through five distinct phases:
1.  **Phase 1: Shadowing**: Listening to and repeating dialogues to master pronunciation and intonation.
2.  **Phase 2: Question Drills**: Controlled practice forming questions within a time limit.
3.  **Phase 3: Roleplays**: Semi-guided interactive scenarios with fill-in-the-blank (cloze) activities.
4.  **Phase 4: Spontaneity**: Dice-game style drills to improve real-time reaction speed.
5.  **Phase 5: Script Writing**: Tools for users to write, analyze, and perform their own scripts.

## 3. Authentication Setup

### 3.1 Session Management
The application uses standard PHP sessions to manage user authentication.
- `api/login.php`: Handles user login, sets `$_SESSION['user_id']`, and manages the initial login state.
- `api/logout.php`: Destroys the session and logs the user out.
- `api/check_session.php`: A frontend-facing endpoint used by client-side scripts to verify the current session status.

### 3.2 Access Control
Server-side access control is centralized in `api/auth_check.php`. The `checkAccess($requireSubscription, $requireAdmin)` function:
1. Verifies if `user_id` is set in the session.
2. Checks the user's role and subscription status in the database.
3. **Admins**: Users with the `admin` role are automatically granted `active` subscription status.
4. Redirects to `login.html` if not logged in, or `index.html` if a subscription is required but the user is inactive.

### 3.3 Frontend Integration
`js/auth.js` is included in protected pages to handle client-side session validation and UI updates (e.g., displaying the user's name and the logout button).

## 4. Component & Frontend Setup

### 4.1 Navigation
The navigation bar is largely duplicated across PHP and HTML files for simplicity in a multi-page architecture, but it is enhanced by `js/nav.js` which handles:
- Mobile hamburger menu toggle.
- Responsive dropdown behaviors.

### 4.2 UI Utilities
- `js/toast.js`: Provides a standard way to show non-blocking notifications to the user.
- `css/main.css`: Contains global styles, including the consistent `#f5f0ea` background color.

### 4.3 Interactive Tools (Practise)
The practice tools in `practise/section_a/` and `practise/section_b/` are standalone modules with their own local `api/` subdirectories for state management (`start_session.php`, `continue_session.php`).

5. Subscription & Payment Logic

### 5.1 PayPal Configuration
The application supports two types of PayPal transactions: **Recurring Subscriptions** and **One-Time Payments**.

#### Recurring Subscriptions
Recurring billing is managed via PayPal **Billing Plans**.
1.  **PayPal Setup**: You must create a Subscription Plan on your PayPal Developer Dashboard. Note the **Plan ID**.
2.  **App Config**: Define the Plan ID in `db/paypal_config.php` as `PAYPAL_PLAN_ID`.
3.  **Flow**:
    - The frontend (e.g., `js/paypal-util.js`) uses the PayPal SDK to render a subscription button.
    - Upon approval, `api/paypal/capture_subscription.php` verifies the status via PayPal's API and extracts the `next_billing_time`.
    - Access is granted until the `next_billing_time`, which is stored as `subscription_end_date` in our database.

#### One-Time Payments
One-time payments (e.g., a fixed 30-day access pass) are handled via PayPal **Orders**.
1.  **Logic**: The cost and duration are currently defined in the application logic.
2.  **Price Definition**: In `api/paypal/create_payment.php`, the `'value'` field (e.g., `'5.00'`) defines the cost.
3.  **Capture**: After the user pays, `api/paypal/capture_payment.php` is called. It hardcodes the access duration (default: `+30 days`) and updates the database accordingly.

### 5.2 Database Schema Breakdown

The application relies on several core tables to manage users, security, and payments:

#### `users`
- `id`: Primary key.
- `email`: Unique identifier and login credential.
- `subscription_status`: Current status (`active`/`inactive`).
- `role`: (`user` or `admin`).

#### `subscriptions`
- Tracks the relationship between a user and their PayPal subscription.
- `paypal_subscription_id`: The ID provided by PayPal.
- `subscription_start_date` / `subscription_end_date`: Defines the validity window for the user's access.
- `status`: Internal status of the subscription (e.g., `active`, `cancelled`, `suspended`).

#### `subscription_payments`
- Logs individual transaction events.
- `paypal_transaction_id`: Unique ID for each payment event (different from the subscription ID).
- `amount` / `currency`: The value and currency of the payment.
- `payment_date`: When the transaction occurred.

#### `login_history` & `admin_audit_logs`
- **Security**: `login_history` tracks IP addresses, timestamps, and status for all login attempts (used for brute-force protection).
- **Admin**: `admin_audit_logs` records every data-modifying action taken by administrators for accountability.

#### `support_tickets`
- Stores user-submitted inquiries. Tracks `status` (`open`, `in-progress`, `resolved`) and links to `user_id` when applicable.

## 6. Email Configuration & Services

### 6.1 Email Service Implementation
Email sending is centralized in `api/services/EmailService.php`. The application uses the **Amazon Simple Email Service (AWS SES)** PHP SDK to send transactional emails.

### 6.2 Required Configuration
The following global variables must be defined (typically in a secure configuration file not committed to the repository) for the email service to function:
- `$aws_key`: Your AWS Access Key ID.
- `$aws_secret`: Your AWS Secret Access Key.
- `$aws_region`: The AWS region where SES is configured (e.g., `'us-east-1'`).
- `$sender_email`: The verified email address used as the "From" address (defaults to `support@tefinitely.com`).
- **Reply-To Address**: Fixed to `tefinitely@gmail.com` in `EmailService.php`.

### 6.3 Integrated Use Cases
- **User Registration**: A welcome email is automatically sent to new users via `api/register.php`.
- **Password Resets**: The `api/auth/forgot_password.php` endpoint uses the service to send reset links.
- **Administrative Actions**: When an administrator manually adds a user via `api/admin/manage_users.php`, a welcome email is sent to the new user.

## 7. Development & Maintenance

### 7.1 Database Configuration
Database credentials should be stored in `db/db_config.php` (generated from `db/db_config.php.example`).

### 7.2 External Dependencies
- **Backend**: PHP 7.4+ with MySQL. Uses OpenAI API for interactive chat features.
- **Frontend**: Bootstrap Icons, PayPal SDK, and custom JS/CSS.
- **System Tools**: Certain features (like image conversion) may require `imagemagick`, `potrace`, and `openscad`.

## 8. PayPal Environment Transition (Sandbox to Production)

To switch the application from the PayPal Sandbox environment to the Live production environment, follow these steps:

1.  **PayPal Developer Dashboard**:
    - Log in to the [PayPal Developer Portal](https://developer.paypal.com/).
    - Go to "Apps & Credentials" and toggle to "Live".
    - Create a new App (if you haven't) to obtain your **Live Client ID** and **Secret**.
    - Under the Live app, create a **Subscription Plan** and note the **Plan ID**.
    - (Optional) Configure a **Webhook** to point to `https://yourdomain.com/api/paypal/webhook.php` and obtain the **Webhook ID**.

2.  **Update Configuration (`db/paypal_config.php`)**:
    - Set `PAYPAL_ENVIRONMENT` to `'live'`.
    - Provide the Live `PAYPAL_CLIENT_ID` and `PAYPAL_CLIENT_SECRET` in the `else` block of the environment check.
    - Update `PAYPAL_PLAN_ID` with your production plan ID.
    - If using webhooks, update your environment variables or the `PAYPAL_WEBHOOK_ID` constant.

3.  **Frontend Verification**:
    - The frontend automatically fetches the correct `client_id` and `plan_id` via `api/paypal/get_config.php`. Ensure no hardcoded values exist in your HTML/JS files.

4.  **Testing**:
    - Perform a real transaction (at a low price point if possible) to verify the end-to-end flow in production.
