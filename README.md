# InstaWP Demo Helper

A comprehensive WordPress plugin designed for hosting providers to offer seamless migration capabilities from temporary demo sites. Features multiple migration actions, complete customization, robust error handling, and advanced security measures.

## 🚀 Setup Video
https://www.youtube.com/watch?v=P52XQOCV3B8

## ✨ Key Features

- **Multiple Migration Actions**: Four different action types with priority-based execution
- **Complete Customization**: Full branding control - colors, content, buttons, and email templates
- **API Integration**: Robust InstaWP API connection with comprehensive error handling
- **Debug Logging**: Advanced request/response logging for troubleshooting
- **API Key Security**: Enhanced security with masked display and data protection
- **Error Handling**: User-friendly messages for all API scenarios with specific HTTP status codes
- **Placeholder Support**: Dynamic placeholders for URLs and email templates
- **Reset System**: Complete settings reset with centralized default values
- **Export/Import Settings**: Backup and restore configurations via JSON with WP-CLI support
- **Remote Disable**: Unauthenticated API to remotely disable plugin functionality
- **Auto-Updates**: GitHub-based plugin updates

## Use Cases

- **InstaDemos (for Product Companies)**: You can install this plugin on your demo site and use it to send people to Go to Live URL for InstaWP. 
- **InstaDemos (for Hosts)**: You can install this plugin on your demo site and use it to convert a sandbox site to regular site, create a support request and you can also let them those a domain name + redirect to your hosting website. This can be later combined with `iwp-migrate-helper` to migrate the site to your hosting platform.
- **WaaS (with WooCommerce etc)**: You can simple have user click on Go Live to go to your WooCommerce shop with an attached site_id or site_url. This can be later combined with `iwp-wp-integration` plugin for you convert the sandbox site to a site with higher plan.

## Actions

The plugin supports four different actions when the migration button is clicked:

### 1. Open Link on Button Click (Override Action)
- **Priority**: Highest - overrides all other actions
- **Features**: 
  - Direct redirection to custom URL
  - Support for placeholders: `{{site_url}}`, `{{customer_email}}`, `{{site_id}}`
  - Option to open in new tab or current window
- **Use Case**: Direct redirection to external systems

### 2. Convert Sandbox to Regular Site
- Makes API call to InstaWP to convert sandbox
- Integrated with support ticket creation

### 3. Create Support Ticket
- Sends email notification to support team
- Uses configurable email templates
- Can work independently or with sandbox conversion

### 4. Show Domain Choice & Redirect
- Shows domain input field to user
- Redirects to specified URL with domain parameter
- Supports webhook integration

## 🔌 API Integration

### Migration Request
- **Endpoint**: `{INSTAWP_API_DOMAIN}/api/v2/migrate-request`
- **Method**: POST
- **Authentication**: Bearer token
- **Triggers**: When "Convert Sandbox" or "Create Ticket" is enabled

### Enhanced Error Handling 🆕
Comprehensive error handling with specific messages for different scenarios:

- **401 Unauthorized**: "Invalid API key. Please check your InstaWP API key in plugin settings."
- **404 Not Found**: Shows FYI message "Site may not exist or a migrate request already exists" (allows continuation)
- **403 Forbidden**: "Access denied. Please verify your API permissions and try again."
- **429 Rate Limited**: "Too many requests. Please wait a moment and try again."
- **500+ Server Errors**: "InstaWP API is temporarily unavailable. Please try again later."
- **Network Errors**: "Network error: [specific error message]"

### Remote Disable
- **Endpoint**: `POST /wp-json/iwp-migration/v1/disable`
- **Authentication**: None required
- **Purpose**: Remotely disable plugin functionality
- **Effect**: Hides admin bar button and shows disabled message

## 🔧 Debug Logging 🆕

Advanced logging system for API requests and responses:

### Configuration
- **Setting**: Enable "Debug Logging" in Advanced tab
- **Requirements**: Both WP_DEBUG and plugin setting must be enabled
- **Location**: WordPress debug.log file
- **Security**: API keys automatically masked in logs

### What Gets Logged
- **API Requests**: URL, method, headers (masked), request body
- **API Responses**: Status codes, response body, timestamps
- **Webhook Calls**: Full request/response logging
- **Network Errors**: Connection issues and failures

### Security Features
- API keys show only last 4 characters (e.g., `***********************************cdef`)
- Sensitive data automatically masked
- Conditional logging (only when enabled)

## 🔐 API Key Security 🆕

Enhanced security measures for API key handling:

### Display Protection
- **Masked Display**: Shows only last 4 characters in admin interface
- **No Show Button**: Removed ability to view full API key
- **Read-only Field**: Prevents accidental editing with visual styling
- **Form Protection**: Custom sanitization prevents saving masked values

### Logging Security
- API keys automatically masked in all debug logs
- Authorization headers show only last 4 characters
- No sensitive data exposure in logged responses

## 📥 Export/Import Settings 🆕

Complete backup and restore functionality for all plugin configurations:

### Web Interface
- **Export**: One-click JSON download from Advanced tab
- **Import**: File upload with real-time validation and confirmation
- **Security**: Admin-only access with proper nonce verification
- **Feedback**: Live status updates during import process

### WP-CLI Commands
Available commands for automation and bulk management:

```bash
# Export settings to file
wp iwp-demo-helper export /path/to/settings.json

# Export to stdout (useful for piping)
wp iwp-demo-helper export

# Import settings from file
wp iwp-demo-helper import /path/to/settings.json

# Preview import without making changes
wp iwp-demo-helper import /path/to/settings.json --dry-run
```

### JSON Structure
Exported files include comprehensive metadata:
- **Plugin version**: For compatibility tracking
- **Export date**: Timestamp of backup creation
- **Site URL**: Source site identification  
- **Settings**: All plugin configurations including linked fields

### Safety Features
- **Validation**: JSON format and structure verification
- **Field Filtering**: Only recognized plugin settings are imported
- **Confirmation**: User confirmation required before import
- **Error Handling**: Detailed error messages for troubleshooting

## 🎯 Placeholder Support

Dynamic placeholders available in:
- Open Link URLs
- Email subject and body templates

Available placeholders:
- `{{site_url}}` - Current site URL
- `{{customer_email}}` - Admin email address  
- `{{site_id}}` - Value from `iwp_site_id` option

## 💡 Pro Tips

* **Hidden Menu Recovery**: If you've hidden the plugin menu, access it via `/wp-admin/admin.php?page=iwp_demo_helper`
* **Action Priority**: "Open Link on Button Click" overrides all other actions when enabled
* **Immediate Redirect**: Enabling `Show Domain Choice & Redirect` redirects immediately after migration button click
* **Domain Parameter**: Enabling `Show Domain Field` adds input that concatenates with redirection URL as `?domain=<value>`
* **Remote Disable**: Use `POST /wp-json/iwp-demo-helper/v1/disable` to programmatically disable the plugin
* **Debug Logging**: Enable both WP_DEBUG and plugin debug setting for comprehensive API logging
* **Error Handling**: 404 errors show FYI message for 3 seconds before proceeding to thank you screen
* **Reset Settings**: Use "Reset All Settings to Default" in Advanced tab to restore original configuration
* **Export/Import**: Backup settings before major changes using export functionality in Advanced tab
* **WP-CLI Access**: Use SSH to access WP-CLI commands for automated backup/restore operations

## 📋 Changelog

### v1.0.7 - 08 Aug 2025 🆕
**Added:**
- Reset Settings functionality with JavaScript-based form handling
- Centralized default values system using DRY principle
- Enhanced success messaging with auto-reload for settings reset
- Proper nonce verification for reset operations
- Comprehensive API error handling with specific messages for different HTTP status codes
- Debug logging capability in Advanced tab for API requests and responses
- User-visible FYI message for 404 errors with 3-second display duration
- API key security enhancements - only last 4 characters visible, no show functionality
- Request/response logging with WP_DEBUG integration and sensitive data masking
- Export/Import settings functionality with JSON format and WP-CLI commands

**Fixed:**
- Reset button form nesting issues preventing proper form submission
- 404 errors now show user-friendly message "Site may not exist or a migrate request already exists"
- API error messages now include error codes for better troubleshooting

**Enhanced:**
- Error handling for 401 (invalid API key), 403 (access denied), 429 (rate limited), and 500+ (server errors)
- JavaScript error display with detailed error codes and network error handling
- CSS styling for warning and error messages with proper visual distinction

**Updated:**
- Default values - 'Go Live' branding instead of 'Migration' terminology
- Settings initialization to use centralized defaults on first run

### v1.0.6
**Added:**
- Setting to hide the CTA button
- Setting to append the src_demo_url parameter to links in the Main Content

### v1.0.5 - 29 October 2024
**Fixed:**
- Updated composer dependencies

### v1.0.4 - 29 October 2024
**Added:**
- Automatic update checking system

### v1.0.3 - 22 September 2024
**Fixed:**
- API domain support from constant
- Disabled migrate button while working in background

### v1.0.2 - 16 July 2024
**Added:**
- Domain field placeholder support
**Fixed:**
- Redirection behavior (immediate vs thank you screen)

### v1.0.1 - 28 June 2024
**Added:**
- Email disabling feature
- Domain field functionality

### v1.0.0 - 30 October 2023
**Added:**
- Initial release with core migration functionality