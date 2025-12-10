=== InstaWP Demo Helper ===
Contributors: instawp
Tags: migration, demo, sandbox, instawp, hosting
Requires at least: 5.6
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enables one-click migration requests from demo WordPress sites with customizable interface and multiple action types.

== Description ==

InstaWP Demo Helper is a comprehensive WordPress plugin designed for hosting providers to offer seamless migration capabilities from temporary demo sites. Features multiple migration actions, complete customization, robust error handling, and advanced security measures.

= Key Features =

* **Multiple Migration Actions**: Four different action types with priority-based execution
* **Complete Customization**: Full branding control - colors, content, buttons, and email templates
* **API Integration**: Robust InstaWP API connection with comprehensive error handling
* **Debug Logging**: Advanced request/response logging for troubleshooting
* **API Key Security**: Enhanced security with masked display and data protection
* **Error Handling**: User-friendly messages for all API scenarios with specific HTTP status codes
* **Placeholder Support**: Dynamic placeholders for URLs and email templates
* **Reset System**: Complete settings reset with centralized default values
* **Export/Import Settings**: Backup and restore configurations via JSON with WP-CLI support
* **Remote Disable**: Unauthenticated API to remotely disable plugin functionality
* **Auto-Updates**: GitHub-based plugin updates

= Use Cases =

* **InstaDemos (for Product Companies)**: Install this plugin on your demo site and use it to send people to Go to Live URL for InstaWP.
* **InstaDemos (for Hosts)**: Install this plugin on your demo site and use it to convert a sandbox site to regular site, create a support request and let them choose a domain name + redirect to your hosting website.
* **WaaS (with WooCommerce etc)**: Have users click on Go Live to go to your WooCommerce shop with an attached site_id or site_url.

= Migration Actions =

The plugin supports four different actions when the migration button is clicked:

1. **Open Link on Button Click** - Direct redirection to custom URL with placeholder support
2. **Convert Sandbox to Regular Site** - Makes API call to InstaWP to convert sandbox
3. **Create Support Ticket** - Sends email notification to support team
4. **Show Domain Choice & Redirect** - Shows domain input field and redirects to specified URL

= API Integration =

* Migration Request Endpoint: `{INSTAWP_API_DOMAIN}/api/v2/migrate-request`
* Remote Disable Endpoint: `POST /wp-json/iwp-demo-helper/v1/disable`

= Available Placeholders =

* `{{site_url}}` - Current site URL
* `{{customer_email}}` - Admin email address
* `{{site_id}}` - Value from iwp_site_id option
* `{{site_hash}}` - Value from iwp_site_hash option

= WP-CLI Commands =

Export settings:
`wp iwp-demo-helper export /path/to/settings.json`

Import settings:
`wp iwp-demo-helper import /path/to/settings.json`

Preview import:
`wp iwp-demo-helper import /path/to/settings.json --dry-run`

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/iwp-demo-helper` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the IWP Demo Helper menu to configure the plugin settings.
4. Enter your InstaWP API key in the General Settings tab.
5. Configure branding, buttons, and email settings as needed.

== Frequently Asked Questions ==

= How do I get an API key? =

You can get your InstaWP API key from your [InstaWP API Tokens](https://app.instawp.io/user/api-tokens) page.

= I hid the plugin menu, how do I access settings? =

Access the settings directly via: `/wp-admin/admin.php?page=iwp_demo_helper`

= How do I enable debug logging? =

Enable both WP_DEBUG in wp-config.php and the "Debug Logging" option in the Advanced tab. Logs will appear in the WordPress debug.log file.

= Can I remotely disable the plugin? =

Yes, use the REST API endpoint: `POST /wp-json/iwp-demo-helper/v1/disable` (no authentication required).

= How do I reset all settings? =

Go to the Advanced tab and click "Reset All Settings to Default". This will restore all settings to their original values.

= How do I backup my settings? =

Use the "Export Settings" button in the Advanced tab to download a JSON file with all your configurations, or use the WP-CLI command: `wp iwp-demo-helper export`

== Screenshots ==

1. Migration page with customizable branding
2. Admin bar button for quick access
3. General settings configuration
4. Content & branding options
5. Button configuration panel
6. Advanced settings with export/import

== Changelog ==

= 1.1.0 - 10 December 2025 =
**Fixed:**
* "Go Live" button not clickable on small screens
* Purge plugin assets cache

= 1.0.9 - 09 December 2025 =
**Fixed:**
* Resolved mobile responsiveness issue where "Go Live" button was hidden on mobile screens

**Improved:**
* Removed conditional toggle behavior - expiry hours field now displays regardless of "Convert Sandbox to Regular Site" setting

= 1.0.8 - 17 September 2025 =
**Fixed:**
* Settings page to save and show updated values after saving

= 1.0.7 - 19 August 2025 =
**Added:**
* Reset Settings functionality with JavaScript-based form handling
* Centralized default values system using DRY principle
* Enhanced success messaging with auto-reload for settings reset
* Proper nonce verification for reset operations
* Comprehensive API error handling with specific messages for different HTTP status codes
* Debug logging capability in Advanced tab for API requests and responses
* User-visible FYI message for 404 errors with 3-second display duration
* API key security enhancements - only last 4 characters visible, no show functionality
* Request/response logging with WP_DEBUG integration and sensitive data masking
* Export/Import settings functionality with JSON format and WP-CLI commands
* Extend Site Expiry settings to allow extending site expiration

**Fixed:**
* Reset button form nesting issues preventing proper form submission
* 404 errors now show user-friendly message "Site may not exist or a migrate request already exists"
* API error messages now include error codes for better troubleshooting

**Enhanced:**
* Error handling for 401 (invalid API key), 403 (access denied), 429 (rate limited), and 500+ (server errors)
* JavaScript error display with detailed error codes and network error handling
* CSS styling for warning and error messages with proper visual distinction

**Updated:**
* Default values - 'Go Live' branding instead of 'Migration' terminology
* Settings initialization to use centralized defaults on first run

= 1.0.6 =
**Added:**
* Setting to hide the CTA button
* Setting to append the src_demo_url parameter to links in the Main Content

= 1.0.5 - 29 October 2024 =
**Fixed:**
* Updated composer dependencies

= 1.0.4 - 29 October 2024 =
**Added:**
* Automatic update checking system

= 1.0.3 - 22 September 2024 =
**Fixed:**
* API domain support from constant
* Disabled migrate button while working in background

= 1.0.2 - 16 July 2024 =
**Added:**
* Domain field placeholder support

**Fixed:**
* Redirection behavior (immediate vs thank you screen)

= 1.0.1 - 28 June 2024 =
**Added:**
* Email disabling feature
* Domain field functionality

= 1.0.0 - 30 October 2023 =
**Added:**
* Initial release with core migration functionality

== Upgrade Notice ==

= 1.1.0 =
Fixed "Go Live" button not clickable on small screens.

= 1.0.9 =
Fixed mobile responsiveness issue where "Go Live" button was hidden on mobile screens.

= 1.0.7 =
Major update with export/import settings, debug logging, enhanced API error handling, and security improvements.
