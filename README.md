# Migration Helper for Hosts

This plugin can be used along side with temporary demos offered by Hosts. This plugin allows end users to request a migration from within the wp-admin panel. 

Setup Video - https://www.youtube.com/watch?v=P52XQOCV3B8

## Tips

* If you have marked the plugin menu has "hidden" then you can retrive it via this relative URL `/wp-admin/admin.php?page=iwp_migration`
* If you enable `Redirection URL` in the settings page then it will redirect immediately after clicking on the Begin Migration button. Thank you screen will not come.
* Enabling `Show Domain Field` will add a new input field that will concat with the redirection url with this query parameter `?domain=<value of the input field>`


## Changelog

#### 1.0.3 - 22 September 2024
- FIX - Fixed api domain support from constant.
- FIX - Disabled migrate button while it's working in the background.

#### 1.0.2 - 16 July 2024
- NEW - Domain field placeholder added.
- FIX - Fixed redirection immediately instead of showing thank you screen.

#### 1.0.1 - 28 June 2024
- NEW - Email disabling feature added.
- NEW - Domain field added.

#### 1.0.0 - 30 October 2023
- NEW - Initial Release.