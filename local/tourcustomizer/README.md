# Tour Customizer - Local Plugin

This Moodle local plugin allows administrators to automatically reset user tours so that users see them every time they log in.

## Features

- Admin interface to enable/disable auto-reset for individual tours
- Scheduled task that runs every 5 minutes to reset enabled tours
- Integrates seamlessly with Moodle's User Tours tool

## Installation

1. The plugin files are already in place at `/local/tourcustomizer`
2. Visit **Site administration → Notifications** to install the plugin and create the database table
3. The plugin will be automatically installed

## Usage

### Enabling Auto-Reset for Tours

1. Navigate to `http://your-moodle-site/local/tourcustomizer/index.php`
2. You will see a list of all existing tours
3. Click the **Enable** button next to any tour you want to auto-reset
4. The tour will now be reset every 5 minutes for all users

### Disabling Auto-Reset

1. Visit the same page
2. Click the **Disable** button next to the tour
3. The auto-reset will stop for that tour

### Scheduled Task

The plugin includes a scheduled task that:
- Runs every 5 minutes (configurable via Site administration → Server → Scheduled tasks)
- Resets all tours that have auto-reset enabled
- Uses Moodle's native tour reset mechanism (`mark_major_change()`)

## Database

The plugin creates one table:
- `mdl_local_tourcustomizer_tours`: Stores which tours should be auto-reset

## Permissions

- **local/tourcustomizer:manage**: Required to access the admin interface
  - Granted to managers by default

## Files Created

- `version.php` - Plugin version information
- `db/install.xml` - Database schema
- `db/tasks.php` - Scheduled task registration
- `db/access.php` - Capability definitions
- `classes/manager.php` - Core functionality
- `classes/task/reset_tours.php` - Scheduled task implementation
- `index.php` - Admin interface
- `lang/en/local_tourcustomizer.php` - Language strings

## Technical Details

Instead of calling the URL directly, this plugin:
1. Uses `\tool_usertours\tour::instance($tourid)` to load each tour
2. Calls `mark_major_change()` method to reset the tour
3. This clears user preferences and makes the tour appear again for all users

This approach is more efficient and doesn't require session key management.
