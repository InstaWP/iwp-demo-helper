# InstaWP Demo Helper Plugin

## Overview
A WordPress plugin that enables multiple migration actions from demo sites. Designed for hosting providers to offer temporary demos with flexible migration capabilities through a fully customizable interface. Features multiple action types, placeholder support, and remote management capabilities.

## Project Structure
```
iwp-demo-helper/
├── iwp-demo-helper.php          # Main plugin file
├── composer.json                # Composer dependencies
├── README.md                    # Plugin documentation
├── changelog.md                 # Version history
├── css/
│   └── style.css               # Frontend styles
├── js/
│   └── scripts.js              # JavaScript functionality
├── templates/
│   ├── migration.php           # Migration page template
│   └── settings.php            # Settings page template
└── vendor/                     # Composer dependencies
    └── instawp/connect-helpers/
```

## Key Features
- **Multiple Migration Actions**: Four different action types with priority-based execution
- **Admin Bar Button**: Customizable migration button in WordPress admin bar
- **Migration Interface**: Full-page migration form with branding options
- **API Integration**: Connects to InstaWP API for migration requests with comprehensive error handling
- **Email Notifications**: Configurable support team notifications
- **Webhook Support**: Custom webhook integration with full logging
- **Domain Input**: Optional domain field for migration
- **Placeholder Support**: Dynamic placeholders in URLs and email templates
- **Remote Disable**: Unauthenticated API to disable plugin functionality
- **Auto-Updates**: GitHub-based plugin updates
- **Customization**: Extensive styling and content options
- **Debug Logging**: Comprehensive API request/response logging with WP_DEBUG integration
- **Error Handling**: User-friendly error messages with specific HTTP status code handling
- **API Key Security**: Enhanced security with masked API key display (last 4 characters only)
- **Reset System**: Complete settings reset with centralized default values

## Core Components

### Main Plugin Class (`IWP_Migration`)
- **File**: `iwp-demo-helper.php:11`
- **Pattern**: Singleton design pattern
- **Key Methods**:
  - `add_migrate_button()` - Adds admin bar button
  - `iwp_migration_initiate()` - Handles AJAX migration requests
  - `render_migrate_page()` - Displays migration interface
  - `check_update()` - Manages auto-updates

### Migration Actions System
The plugin supports four different migration actions with priority-based execution:

#### 1. Open Link on Button Click (HIGHEST PRIORITY)
- **Setting**: `iwp_open_link_action` + `iwp_open_link_url` + `iwp_open_link_new_tab`
- **Behavior**: Overrides all other actions when enabled
- **Features**: 
  - Placeholder support: `{{site_url}}`, `{{customer_email}}`, `{{site_id}}`
  - New tab option
  - No API calls made

#### 2. Convert Sandbox to Regular Site
- **Setting**: `iwp_convert_sandbox`
- **Behavior**: Makes API call to InstaWP migration endpoint
- **Integration**: Works with support ticket creation

#### 3. Create Support Ticket
- **Setting**: `iwp_create_ticket` + `iwp_support_email`
- **Behavior**: Includes support email in API call
- **Templates**: Uses configurable email subject/body

#### 4. Show Domain Choice & Redirect
- **Setting**: `iwp_show_domain_field` + `iwp_domain_field_label` + `iwp_redirection_url`
- **Behavior**: Shows domain input, redirects after submission
- **Integration**: Supports webhook calls

### Configuration Settings
Extensive settings system with 25+ configurable options organized in 5 tabs:

#### General Settings Tab
- API key for InstaWP integration
- All four migration action configurations
- Webhook URL integration

#### Content & Branding Tab
- Logo URL, title text, main content (WYSIWYG)
- Brand colors, footer text, thank you message
- Source demo URL parameter appending

#### Button Configuration Tab
- Admin bar button text and positioning
- CTA button styling and colors
- Close button customization
- Button visibility controls

#### Email & Notifications Tab
- Email templates with placeholder support
- Support email configuration
- Subject and body customization

#### Advanced Tab
- Custom CSS editor
- Plugin visibility controls
- **Plugin disable functionality**
- **Reset settings functionality** ✅ WORKING
- **Debug logging capability** for API requests and responses ✅ NEW
- **Export/Import Settings** functionality via web interface and WP-CLI ✅ NEW

### Frontend Interface
**Migration Page** (`templates/migration.php:17`):
- Branded header with logo
- Customizable content area
- Optional domain input field
- CTA button with custom styling
- Thank you screen with close button

### JavaScript Functionality (`js/scripts.js`)
- Color picker integration for admin settings
- AJAX migration request handling with enhanced error handling
- URL parameter appending for tracking
- Form state management
- Error display and button states with specific error codes
- **404 FYI message display** with 3-second timeout before thank you screen
- **Enhanced error messaging** with detailed HTTP status code information

### Styling (`css/style.css`)
- Inter font family integration
- Responsive design with flexbox
- Card-based layout with shadows
- Customizable button states
- Admin bar styling overrides
- **Enhanced error and warning message styling** with distinct visual appearance
- **API key field styling** with read-only masked display

## API Integration

### Migration Endpoint
**URL**: `{INSTAWP_API_DOMAIN}/api/v2/migrate-request`
**Method**: POST
**Authentication**: Bearer token
**Triggers**: When `iwp_convert_sandbox` OR `iwp_create_ticket` is enabled

**Request Body**:
```json
{
  "url": "site_url",
  "email": "support_email", 
  "customer_email": "admin_email",
  "subject": "email_subject",
  "body": "email_body"
}
```

### Webhook Integration
Optional POST request to custom webhook URL with same payload as API request.
**Triggers**: When `iwp_convert_sandbox` OR `iwp_show_domain_field` is enabled

### API Error Handling ✅ NEW
Comprehensive error handling system with specific messages for different scenarios:

#### HTTP Status Code Handling
- **401 Unauthorized**: "Invalid API key. Please check your InstaWP API key in plugin settings."
- **404 Not Found**: Shows FYI message "Site may not exist or a migrate request already exists" (allows continuation)
- **403 Forbidden**: "Access denied. Please verify your API permissions and try again."
- **429 Rate Limited**: "Too many requests. Please wait a moment and try again."
- **500+ Server Errors**: "InstaWP API is temporarily unavailable. Please try again later."
- **Network Errors**: "Network error: [specific error message]"

#### Error Response Features
- **Error Codes**: All error messages include HTTP status codes for troubleshooting
- **User-Friendly Messages**: Clear, actionable error descriptions
- **Logging Integration**: All errors logged when debug logging is enabled
- **Non-blocking 404s**: 404 errors show warning but allow migration to continue

### Remote Disable API
**URL**: `/wp-json/iwp-migration/v1/disable`
**Method**: POST
**Authentication**: None (unauthenticated)
**Purpose**: Remotely disable plugin functionality
**Effect**: Sets `iwp_disable_plugin` to 'yes'

**Response**:
```json
{
  "success": true,
  "message": "Migration plugin has been disabled successfully.",
  "timestamp": "2024-01-01 12:00:00"
}
```

## Placeholder System

### Available Placeholders
- `{{site_url}}` → `site_url()` - Current WordPress site URL
- `{{customer_email}}` → `get_option('admin_email')` - Site admin email
- `{{site_id}}` → `get_option('iwp_site_id', '')` - Custom site ID (set by external systems)

### Usage Locations
- **Open Link URLs**: `iwp_open_link_url` field
- **Email Templates**: Subject and body fields (`iwp_email_subject`, `iwp_email_body`)

### Processing Function
**Function**: `replace_placeholders()` at `iwp-demo-helper.php:68-76`
**Implementation**: Simple `str_replace()` with associative array

## Debug Logging System ✅ NEW

### Overview
Comprehensive logging system for API requests and responses to aid in troubleshooting and monitoring.

### Configuration
- **Setting**: `iwp_debug_logging` checkbox in Advanced tab
- **Requirements**: Both WP_DEBUG and plugin setting must be enabled
- **Location**: WordPress debug.log file
- **Security**: API keys are automatically masked in logs

### Logged Information
#### Request Logging
- **URL**: Target API endpoint
- **Method**: HTTP method (POST, GET, etc.)
- **Headers**: Request headers (Authorization header masked)
- **Body**: Request payload with sensitive data masked
- **Timestamp**: When request was made

#### Response Logging
- **Status Code**: HTTP response status
- **Body**: Complete API response
- **Timestamp**: When response was received
- **Errors**: Network errors and connection issues

### Log Format
```
[IWP Demo Helper] REQUEST - https://app.instawp.io/api/v2/migrate-request: 
{"timestamp":"2025-08-08 09:17:23","type":"request","url":"https://app.instawp.io/api/v2/migrate-request","request_headers":{"Authorization":"Bearer ***********************************cdef"},...}

[IWP Demo Helper] RESPONSE - https://app.instawp.io/api/v2/migrate-request:
{"timestamp":"2025-08-08 09:17:24","type":"response","response_code":404,"response_body":"{\"status\":false,\"message\":\"The requested resource not found.\"}"}
```

### Security Features
- **API Key Masking**: Shows only last 4 characters (e.g., `***********************************cdef`)
- **Sensitive Data Protection**: Automatically masks sensitive information in logs
- **Conditional Logging**: Only logs when explicitly enabled by admin

## Development Commands

### Composer
```bash
composer install              # Install dependencies
composer dump-autoload        # Regenerate autoloader
composer dump-autoload --no-dev  # Production build
```

### Dependencies
- **PHP**: >= 7.4.0
- **InstaWP Connect Helpers**: Development package
- **WP-CLI Config Transformer**: WordPress configuration management

## Testing Environment

### Site Access
- **URL**: https://iwp-demo-helper.instawp.site
- **Admin URL**: https://iwp-demo-helper.instawp.site/wp-admin
- **Username**: copohoheyi9181
- **Password**: UkqnufdYCgPSvaL5lV0K

### SSH Access for WP-CLI
- **SSH Command**: `ssh nonuxosubi3296@188.166.242.150`
- **WP-CLI Path**: `wp --path=web/iwp-demo-helper.instawp.site/public_html`
- **Examples**:
  - List posts: `wp --path=web/iwp-demo-helper.instawp.site/public_html post list`
  - Export settings: `wp --path=web/iwp-demo-helper.instawp.site/public_html iwp-demo-helper export`
  - Import settings: `wp --path=web/iwp-demo-helper.instawp.site/public_html iwp-demo-helper import /path/to/file.json`

### Settings Defaults (Updated)
The following default values have been implemented using DRY principle:
- **iwp_create_ticket**: '' (changed from 'yes' to disabled)
- **iwp_open_link_action**: '' (disabled by default)
- **iwp_show_domain_field**: '' (disabled by default)
- **title_text**: 'Demo Helper' (changed from 'Migration Demo')
- **top_bar_text**: 'Go Live' (changed from 'Migrate')
- **cta_btn_text**: 'Go Live' (changed from 'Begin Migration')
- **iwp_disable_plugin**: '' (disabled by default)
- **iwp_hide_migration_plugin**: '' (disabled by default)
- **iwp_debug_logging**: '' (disabled by default) ✅ NEW

### Default Values System
- **Method**: `get_default_values()` at iwp-demo-helper.php:407 - Centralized default values
- **Initialization**: `initialize_default_settings()` at iwp-demo-helper.php:1014 - Sets defaults on first run
- **Reset Function**: `reset_all_settings()` at iwp-demo-helper.php:977 - Uses centralized defaults

### Reset Settings Functionality ✅ WORKING
- **Location**: Advanced tab - "Reset All Settings to Default" button
- **Implementation**: JavaScript-based form creation (iwp-demo-helper.php:945-987)
- **Security**: Proper nonce verification and form handling
- **User Experience**: Confirmation dialog + success message with auto-reload
- **Verification**: Admin bar button changes from "Go Live1" to "Go Live" after reset
- **Technical Solution**: `iwpResetSettings()` JavaScript function creates dynamic form to avoid WordPress form nesting issues

### Export/Import Settings Functionality ✅ NEW
- **Location**: Advanced tab - "Export Settings" and "Import Settings" sections
- **Export Features**:
  - **Web Interface**: One-click JSON export with automatic download
  - **WP-CLI**: `wp iwp-demo-helper export [file]` command
  - **JSON Structure**: Includes plugin version, export date, site URL, and all settings
  - **Security**: Requires `manage_options` capability
- **Import Features**:
  - **Web Interface**: File upload with drag-and-drop JSON import
  - **WP-CLI**: `wp iwp-demo-helper import <file> [--dry-run]` command
  - **Validation**: JSON format and structure validation
  - **Safety**: Confirmation dialog and field validation
  - **Dry Run**: CLI supports dry-run mode to preview changes
- **Technical Implementation**:
  - **Export Method**: `export_settings()` at iwp-demo-helper.php:1386
  - **Import AJAX**: `handle_import_settings_ajax()` at iwp-demo-helper.php:1448
  - **CLI Commands**: `IWP_Migration_CLI_Command` class at iwp-demo-helper.php:1534
  - **Field Handling**: Supports all field types including linked fields

## Configuration

### Required Settings
1. **API Key**: InstaWP API authentication
2. **Support Email**: Migration request recipient

### Optional Enhancements
- Custom branding (logo, colors, content)
- Webhook integration for external systems
- Redirection URL for immediate redirect
- Domain field for custom domains
- Email template customization

## Security Features
- Input sanitization with `sanitize_text_field()`
- Nonce verification for AJAX requests
- Capability checks (`manage_options`)
- Content filtering with `wp_kses_post()`
- Escaped output in templates

### API Key Security ✅ NEW
Enhanced security measures for API key handling:

#### Display Security
- **Masked Display**: API key shows only last 4 characters (e.g., `***********************************cdef`)
- **No Show Button**: Removed ability to view full API key in admin interface
- **Read-only Field**: API key field is read-only with grayed-out styling
- **Hidden Field Preservation**: Actual API key preserved in hidden field during form submissions

#### Form Protection
- **Sanitization**: Custom `sanitize_api_key_field()` prevents saving masked values
- **Regex Detection**: Detects and rejects attempts to save masked API key patterns
- **Data Integrity**: Ensures real API key is never overwritten by masked display value

#### Logging Security
- **Automatic Masking**: API keys automatically masked in all debug logs
- **Request Logs**: Authorization headers show only last 4 characters
- **Response Logs**: No API key exposure in logged responses

## Updates & Maintenance
Auto-update system using `AutoUpdatePluginFromGitHub` class:
- **Repository**: https://github.com/InstaWP/iwp-migration
- **Version Check**: Automatic on plugin load
- **Update Method**: GitHub releases

## Disable Plugin System

### Manual Disable
- **Setting**: `iwp_disable_plugin` checkbox in Advanced tab
- **Effect**: Completely disables all migration functionality

### Remote Disable API
- **Endpoint**: `POST /wp-json/iwp-migration/v1/disable`
- **Authentication**: None required (unauthenticated)
- **Security**: Only allows enabling disable setting (one-way operation)

### Disable Effects
When `iwp_disable_plugin` is set to 'yes':
1. **Admin Bar Button**: Hidden (`add_migrate_button()` returns early)
2. **AJAX Handler**: Returns error message (`iwp_migration_initiate()`)
3. **Migration Page**: Shows disabled message instead of form
4. **Template**: Red alert box with admin contact message

### Re-enabling
- Must be done manually through WordPress admin interface
- API cannot re-enable the plugin (security feature)

## Usage Scenarios
1. **Hosting Demo Sites**: Multiple migration action types for different workflows
2. **Client Presentations**: Override action for direct external system integration
3. **Development Handoffs**: Streamlined site transfer with ticket creation
4. **White-label Solutions**: Fully customizable branding and placeholder support
5. **Remote Management**: API-based disable for automated systems

## File Locations
- **Main Plugin**: `iwp-demo-helper.php:789` (initialization)
- **Settings**: `iwp-demo-helper.php:367` (field definitions)
- **AJAX Handler**: `iwp-demo-helper.php:144` (migration processing with enhanced error handling)
- **Admin Interface**: `templates/migration.php:30` (disabled state check)
- **Styling**: `css/style.css:21` (button styles + enhanced error/warning styles)
- **JavaScript**: `js/scripts.js:72` (enhanced error handling with 404 FYI display)
- **REST API**: `iwp-demo-helper.php:81` (disable endpoint registration)
- **Placeholder Function**: `iwp-demo-helper.php:68` (placeholder replacement)
- **Reset Button**: `iwp-demo-helper.php:945` (JavaScript-based reset functionality)
- **Reset Handler**: `templates/settings.php:2` (POST request processing)
- **Reset Method**: `iwp-demo-helper.php:977` (reset_all_settings method)
- **API Logging**: `iwp-demo-helper.php:82` (log_api_activity method) ✅ NEW
- **API Key Security**: `iwp-demo-helper.php:451` (mask_api_key method) ✅ NEW  
- **Error Handling**: `iwp-demo-helper.php:283` (comprehensive HTTP status code handling) ✅ NEW
- **Export Settings**: `iwp-demo-helper.php:1386` (export_settings method) ✅ NEW
- **Import Settings**: `iwp-demo-helper.php:1448` (handle_import_settings_ajax method) ✅ NEW
- **CLI Commands**: `iwp-demo-helper.php:1534` (IWP_Migration_CLI_Command class) ✅ NEW