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

## 5. Subscription Logic

### 5.1 PayPal Integration
The site uses the PayPal JavaScript SDK for subscription management.
- **Configuration**: Fetched via `api/paypal/get_config.php`.
- **Flow**: When an unauthenticated or unsubscribed user visits the site, they are prompted to subscribe.
- **Capture**: After approval on the PayPal side, `api/paypal/capture_subscription.php` is called to update the `subscriptions` table in the database.

### 5.2 Database Implementation
Subscription status is tracked in two places:
- `users.subscription_status`: A quick-check field (`active`/`inactive`).
- `subscriptions` table: Stores historical data, including `subscription_start_date` and `subscription_end_date`.

## 6. Development & Maintenance

### 6.1 Database Configuration
Database credentials should be stored in `db/db_config.php` (generated from `db/db_config.php.example`).

### 6.2 External Dependencies
- **Backend**: PHP 7.4+ with MySQL. Uses OpenAI API for interactive chat features.
- **Frontend**: Bootstrap Icons, PayPal SDK, and custom JS/CSS.
- **System Tools**: Certain features (like image conversion) may require `imagemagick`, `potrace`, and `openscad`.
