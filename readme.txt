=== Blighty Explorer ===
Contributors: Blighty
Tags: dropbox explorer, dropbox, share dropbox, dropbox folder, file manager, explorer, file management, document management, digital store, integrate dropbox, embed dropbox, dropbox upload
Donate link: http://blighty.net/go/blighty-explorer-plugin-paypal-donation/
Requires at least: 6.4.2
Tested up to: 6.4.2
Stable tag: 2.3.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Allows the complete file structure of a folder in Dropbox to be shared and navigated within a Wordpress page or post. It also allows for file uploads.

== Description ==
This plugin allows the complete file structure of a folder in Dropbox to be shared within a Wordpress page or post. The folder can then be navigated via the Wordpress site and files selected and downloaded. Changes in the Dropbox folder, such as the addition or deletion of files, are reflected through to the Wordpress site.

= Features =
* Shows a folder/file navigator that mirrors a folder in your dropbox account.
* Optionally display file size and file date in the folder display.
* Allows for file uploads, either to a dedicated hidden folder, or to the currently displayed dropbox folder.
* Allows access rights to be set on each top-level subfolder.
* Compatible with most lightbox-type image plugins.

== Installation ==
- Connect this plugin to your Dropbox account.
- This will create a subfolder called Blighty Explorer in your Apps folder within Dropbox.
- Place your folders and files you wish to share with the WordPress installation inside the Apps/Blighty Explorer subfolder.
- Use the shortcode [bex_folder] in your post or page to display a folder structure / file navigator.
- Use the shortcode [bex_upload] in your post or page to display a file upload dialog.

== Frequently Asked Questions ==

= Are they any attributes for the shortcodes? =
For `[bex_folder]` you can specify a root folder: e.g. `[bex_folder root="\demo2"]` This would set the starting point to the demo2 subfolder under Dropbox\Apps\Blighty Explorer\.
You can also specify a default sort direction for the filenames: e.g. `[bex_folder sortdir="D"]` (Use A for Ascending, D for Descending.) 
Both of these override any equivalent settings you may have in the plugin's Admin Settings.

= Can I use share two different folders from the same Dropbox account on two different installations? =
Yes. Structure your Apps/Blighty Explorer folder in Dropbox to have two subfolders. Change the root folder in the settings page on each installation to the subfolder you want to share.

= Something seems to have gone wrong and/or the plugin is not connected to Dropbox. What can I do? =
On the admin settings page for the plugin, add the `&bex_reset=1` parameter to the URL. This will reset the plugin by disconnecting it from Dropbox. Attempt to reconnect it again using the link provided.

= I like the icons in the file explorer. Can I use them in my software? =
With version 1.3.0, I implemented several icons from the wonderful Silk Icon set by [famfamfam](http://www.famfamfam.com/lab/icons/silk/). They are used under Creative Commons Attribution 3.0 License.

= Can I use both `[bex_folder]` and `[bex_upload]` on the same page? =
Absolutely! The plugin is smart enough to tie the two together. For best results, place the shortcodes side by side: `[bex_folder][bex_upload]`

= I want to only allow logged-in WordPress users or users with certain roles access to using the plugin. How do I do that? =
Use a plugin such as [User Specific Content](https://wordpress.org/plugins/user-specific-content/) in conjunction with this one in order to protect the page.

== Screenshots ==

1. Admin screen to connect to your Dropbox account and set plugin options (prior to v2.0.0).
2. Example folder navigation.
3. Example file upload.

== Changelog ==
= Version 2.3.0 - Jan 27th, 2024 =
* Bug fixes and compatability with PHP 8.*

= Version 2.2.3 - Apr 7th, 2019 =
* Bug fix to resolve uploads duplicating contents.

= Version 2.2.2 - Feb 20th, 2019 =
* Bug fix to resolve problem with apostrophes in path name.

= Version 2.2.1 - Oct 5th, 2018 =
* Bug fix to access control defaults.

= Version 2.2.0 - Oct 1st, 2018 =
* Improve access control.
* Change default folder access to be "Administrator" role instead of anonymous (if access control being used).

= Version 2.1.8 - May 24th, 2018 =
* Bug fix in detecting whether cURL function is enabled.

= Version 2.1.7 - May 22nd, 2018 =
* Added GDPR notices.
* Implemented a less-confusing root folder icon.
* Shows max filesize for file uploads in the plugin options.
* Added ISO 8601 as a date format for display.

= Version 2.1.6 - May 21st, 2018 =
* Added functionality to allow folders to be sorted inline with files.

= Version 2.1.5 - October 7th, 2017 =
* Bug fix: Improved error detection for firewall issues connecting to Dropbox.

= Version 2.1.4 - September 26th, 2017 =
* Bug fix: Undefined variable when file has no extension

= Version 2.1.3 - August 27th, 2017 =
* Bug fix: Folder names weren't displaying correctly in navigation

= Version 2.1.2 - August 3rd, 2017 =
* Bug fixes: Uploads to a subfolder were failing.

= Version 2.1.1 - June 25th, 2017 =
* Bug fixes: Minor improvements and enhancements.

= Version 2.1.0 - June 23rd, 2017 =
* Added options to select format for file date/time.
* Bug fix: Failed to show settings under certain conditions.

= Version 2.0.2 - June 14th, 2017 =
* Update: Improve efficiency of folder information refresh from Dropbox

= Version 2.0.1 - June 10th, 2017 =
* Bug fix: Bad settings link from Installed Plugins page to admin page.

= Version 2.0.0 - June 8th, 2017 =
* Major upgrade to ensure compatibility with Dropbox API v2 before v1 became obsolete.
* New look Admin UI

= Version 1.16.3 - January 14th, 2017 =
* Important maintenance notice

= Version 1.16.2 - December 14th, 2016 =
* Bug fix: Resolved potential PHP warning message

= Version 1.16.1 - November 3rd, 2016 =
* Bug fix: Fix to bug introduced with v1.16

= Version 1.16 - November 2nd, 2016 =
* Added full support for root= and sortdir= shortcode attributes.

= Version 1.15.3.1 - October 29th, 2016 =
* Performance and reliability improvements.

= Version 1.15.3 - October 22nd, 2016 =
* Allow plugin to be called using do_shortcode()

= Version 1.15.2 - June 17th, 2016 =
* Added language support for Dutch.

= Version 1.15.1 - June 16th, 2016 =
* Further localization support.

= Version 1.15.0 - June 16th, 2016 =
* Begin localization support.
* Fix bug in folder selection in admin.

= Version 1.14.0 - June 15th, 2016 =
* Certain security levels weren't always allowing access to folders.

= Version 1.13.0 - May 30th, 2016 =
* Add feature to include/exclude uploads of specific file types.

= Version 1.12.0 - May 20th, 2016 =
* Tidied up some code that wasn't checking if index was set under certain circumstances.
* Added option to subscribe to Blighty emails.

= Version 1.11.1 - April 9th, 2016 =

* Added an additional built-in stylesheet that provides formatting, but not colours/fonts.

= Version 1.11.0 - April 8th, 2016 =

* Allow built-in stylesheet to be suppressed for more flexibility.
* Added additional style hooks for complete style customisation.

= Version 1.10.0 - March 12th, 2016 =

* Optionally allow file extensions to be suppressed.
* Add option to cause files to be opened in new browser tab.
* Allow file sorting by date.
* Add option to replace "Home" directory with actual folder name of "Root" folder.

= Version 1.9.8.1 - February 17th, 2016 =

* Resolved potential stylesheet conflict with certain themes and/or plugins.

= Version 1.9.8 - February 1st, 2016 =

* Added functionality to display PDFs in the browser, instead of downloading them (in line with the Download Files setting).
* Improved Dropbox setup in certain cases where it would otherwise fail.

= Version 1.9.7 - December 30th, 2015 =

* Bug fix: Fixed initial set-up problem with Dropbox when using on a Wordpress installation with SSL admin.
* Bug fix: Problems sorting by filename in a subfolder with a + in it!

= Version 1.9.6 - December 26th, 2015 =

* Added ability to use natural sort order (so numbers are sorted numerically).

= Version 1.9.5 - December 18th, 2015 =

* Added ability to select root folder when subfolders present.

= Version 1.9.4 - December 8th, 2015 =

* Bug fix. Allow + character in filenames.

= Version 1.9.3 - November 15th, 2015 =

* Bug fix. "Allow Uploads" setting not reflecting status correctly in admin options.

= Version 1.9.2 - October 27th, 2015 =

* Minor fix to stylesheet for bex-wrapper selector.

= Version 1.9.1 - September 10th, 2015 =

* Added support for root folder override on both [bex_folder] and [bex_upload] shortcodes.
* Added the folder to the upload email.

= Version 1.9.0 - September 9th, 2015 =

* Added functionality to allow uploads into "current" folder.

= Version 1.8.0 - August 12th, 2015 =

* Added support for lightbox-type plugins.
* Improved handling of file downloads and presentation.
* Improved handling of failed Dropbox authentication on setup.

= Version 1.7.2 - August 8th, 2015 =

* Fixed a bug when there is an & in the Dropbox folder name.

= Version 1.7.1 - August 8th, 2015 =

* Fixed a bug with column header formatting introduced with 1.7.0.

= Version 1.7.0 - August 7th, 2015 =

* Allow for directional sorting by filename
* Added option to download files when selected instead of presenting them in the browser.

= Version 1.6.0 - August 4th, 2015 =

* Added WordPress Role support.
* Improved selection of root folder.

= Version 1.5.2 - June 25th, 2015 =

* More improvements to file downloads.

= Version 1.5.1 - June 25th, 2015 =

* Moved the options menu in the admin to under the settings link.
* Improved the way files are downloaded. Some pop-up blockers were preventing this before.

= Version 1.5.0 - June 18th, 2015 =

* Added caching to reduce hits to Dropbox API
* Added direct link to settings from WordPress' Installed Plugins page.
* Added option to allow uploads for a user that's not logged in.
* Cleaned up some code that could have caused conflicts with other plugins.

= Version 1.4.1 - June 9th, 2015 =

* Bug fix. Removed erroneous space from utilities.php.

= Version 1.4.0 - June 8th, 2015 =

* Added upload functionality.
* Optional email sent to admin when a file is uploaded.
* Allows files to be uploaded to a dedicated folder for review (not visible in the folder view).
* Show Dropbox account information in the admin options.

= Version 1.3.2 - May 20th, 2015 =

* Fixed a bug that caused problems with the folder/file navigation with the default path setting of blank or /.
* Tidied up some HTML in the admin page.

= Version 1.3.1 - May 18th, 2015 =

* Fixed a bug that caused problems with the folder/file navigation when WordPress permalinks were left to their default setting.

= Version 1.3.0 - May 9th, 2015 =

* Replaced the need for the WP SVG Icons plugin.
* Added new Silk icon set from [famfamfam](http://www.famfamfam.com/lab/icons/silk/).
* Show unique icons by file type.
* Only show notices on all plugins and Blighty Explorer pages in the admin.
* Tidied up the formatting of the Dropbox explorer / folder hierarchy.

= Version 1.2.0 - April 30th, 2015 =

* Added stylesheet support for improved formatting.
* Added support to optionally display file modification date and filesize in the folder/file list.
* Sorted folders to always display above files in the folder/file list.
* Added a link in the Admin to the WordPress Support Forums for this plugin.
* Tidied up some of the code.

= Version 1.1.1 - April 19th, 2015 =

* Improved root foldername validation.

= Version 1.1.0 - April 18th, 2015 =

* Added admin settings to specify an optional starting (or root) subfolder.
* Added Frequently Asked Questions.

= Version 1.0.0 - April 14th, 2015 =

* Initial release.

== Upgrade Notice ==
* 2.2.3 - Bug fix to resolve uploads duplicating contents.
