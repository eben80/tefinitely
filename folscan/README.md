# FolScan Premium

FolScan is a Chrome extension designed for Instagram account analysis, allowing users to track follower growth, identify lost followers (unfollows), and monitor following activity with tiered access levels.

## Features

- **New Followers**: Displays users who have followed the target account since the last scan.
- **Lost Followers**: Identifies users who have unfollowed the target account (Premium feature).
- **New Followings**: Shows new accounts the target account has started following.
- **Unfollowed (By You)**: Lists accounts the target has stopped following (Premium feature).
- **Not Following Back**: Identifies accounts that the target follows but who do not follow the target back.
- **Summary Header**: Displays current follower and following counts, with colored arrows (▲/▼) and change amounts relative to the previous scan.
- **Mutual**: Lists mutual followers.
- **Username Change Tracking**: Explicitly identifies users who have changed their handle between scans.
- **User Metadata Indicators**: Each user in the report is tagged with status icons:
    - 🔒 **Private**: The account is set to private.
    - ✅ **Verified**: The account is verified by Instagram.
    - 📤 **Requested by You**: You have a pending follow request to this account.
    - 📥 **Requested You**: This account has a pending follow request to you.
- **Report History & Persistence**: Quick access to previous scans via a dropdown menu. FolScan automatically remembers and displays the last generated report for each username, including a timestamp of when it was run.
- **Data Export**: Download scan results as a formatted CSV report.

## Subscription Levels

| Feature | Free | Premium | Premium Pro |
| :--- | :---: | :---: | :---: |
| **Scan Own Account** | Limited (100 items) | Unlimited | Unlimited |
| **Scan Other Accounts** | No | No | Yes (Public/Followed) |
| **Lost Followers Report** | Locked | Available | Available |
| **Unfollowed Report** | Locked | Available | Available |
| **Mock License Key** | N/A | `MOCK-PREMIUM-KEY` | `MOCK-PRO-KEY` |

### Premium Indicators
- **Premium**: A white crown (👑) appears on the launcher button.
- **Premium Pro**: A cyan crown (👑) appears on the launcher button, and the badge displays "👑 PREMIUM PRO".

## UI Components

- **Launcher (Top-Right)**: A small gold button that sits out of the way of the Instagram interface. Click to expand the full analysis tool.
- **Dropdown (Select Previous...)**: Quickly load previous scan targets.
- **Username Input**: Enter the Instagram handle you wish to scan. **Note**: Changing this field or the dropdown selection automatically loads and displays the most recent saved report for that user, if available.
- **Run Report**: Connects to Instagram to fetch fresh follower/following data and generate a new report, updating the persistence for that user.
- **Download CSV**: Generates and downloads a formatted CSV report of the current scan results.
- **Reset All**: Clears the extension's local storage of all scan history.
- **× (Top-Right)**: Minimizes the analysis popup back to the launcher button.

## Data Storage

All FolScan data is stored locally within your browser using `chrome.storage.local`.

- **`folscan_usernames`**: A list of recently scanned handles for the history dropdown.
- **`folscan_[username]_report`**: An object containing the processed scan sections and a timestamp of the last successful run.
- **`folscan_[username]_followers`**: A map where keys are numeric user IDs and values are objects containing `username`, `full_name`, `is_private`, `is_verified`, `requested_by_viewer`, and `has_requested_viewer`.
- **`folscan_[username]_followings`**: A map where keys are numeric user IDs and values are objects containing `username`, `full_name`, `is_private`, `is_verified`, `requested_by_viewer`, and `has_requested_viewer`.
- **`isPremium` / `isPro`**: Flags indicating the current license status.
- **`licenseKey`**: The last successfully validated license key.

No personal Instagram credentials or login data are stored by the extension. It relies on your active browser session on Instagram.com.
