# FolScan Premium

FolScan is a Chrome extension designed for Instagram account analysis, allowing users to track follower growth, identify lost followers (unfollows), and monitor following activity with tiered access levels.

## Features

- **New Followers**: Displays users who have followed the target account since the last scan.
- **Lost Followers**: Identifies users who have unfollowed the target account (Premium feature).
- **New Followings**: Shows new accounts the target account has started following.
- **Unfollowed (By You)**: Lists accounts the target has stopped following (Premium feature).
- **Not Following Back**: Identifies accounts that the target follows but who do not follow the target back.
- **Mutual**: Lists mutual followers.
- **Report History**: Quick access to previous scans via a dropdown menu.
- **Data Export**: Download scan results in JSON format.

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
- **Username Input**: Enter the Instagram handle you wish to scan. **Note**: Changing this field or the dropdown selection clears the current report to avoid confusion.
- **Run Report**: Connects to Instagram to fetch follower/following data and generate a report.
- **Download JSON**: Exports the raw follower and following lists for the current target.
- **Reset All**: Clears the extension's local storage of all scan history.
- **Close**: Minimizes the analysis popup back to the launcher button.

## Data Storage

All FolScan data is stored locally within your browser using `chrome.storage.local`.

- **`folscan_usernames`**: A list of recently scanned handles for the history dropdown.
- **`folscan_[username]_followers`**: An array of objects containing the `username` and `full_name` of the account's followers from the most recent scan.
- **`folscan_[username]_followings`**: An array of objects containing the `username` and `full_name` of the account's following list from the most recent scan.
- **`isPremium` / `isPro`**: Flags indicating the current license status.
- **`licenseKey`**: The last successfully validated license key.

No personal Instagram credentials or login data are stored by the extension. It relies on your active browser session on Instagram.com.
