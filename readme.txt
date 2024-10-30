=== ClickHome-MyHome ===

Contributors: ClickHome
Donate link: http://www.clickhome.com.au/
Tags: clickhome, myhome
Requires at least: 3.9
Tested up to: 4.7.2
Stable tag: 1.6.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugins enables ClickHome clients to develop websites for their clients.


== Description ==

ClickHome is a leading solution for residential construction companies. It covers the full range of residential construction building company processes, from the first point of contact with the client, to managing the work processes with trades, suppliers and subcontractors, right through to ongoing care and maintenance. The WordPress module enables existing ClickHome clients to easily develop an engaging website for their clients.

Shortcodes are used to insert dynamic content generated from API calls to a ClickHome.MyHome API server. This content is based on the information stored for each client. Clients must login before accessing any page containing a MyHome shortcode.

Available shortcodes: Calendar, Contact, Contract, Contract Header, Documents, FAQ, House Details, Login, Logoff, Maintenance Confirmation, Maintenance Issues, Maintenance Request, Maintenance Review, Notes, Photos, Progress, Stories, and Tasks.

Available widgets: Contract Header and Logoff.
	

== Installation ==

= 1. Install ClickHome-MyHome & optional plugins. =
In your wordpress admin, go to Plugins in the left menu, then:

* Add New > Upload Plugin, choose the ClickHome-MyHome plugin zip file, Install & then activate it.
* Add New > Upload Plugin, choose ~\wp-content\plugins\ClickHome-MyHome\siteorigin-panels.2.4.8.zip, Install & then activate it.
* Add New > Upload Plugin, choose ~\wp-content\plugins\ClickHome-MyHome\so-widgets-bundle.1.5.11.zip, Install & then activate it.
	
= 2. Install supported themes. =
Go to Appearance > Themes in the left menu, then:

* Add New > Upload Theme, choose ~\wp-content\plugins\ClickHome-MyHome\astrid.zip & then Install.
* Add New > Upload Theme, choose ~\wp-content\plugins\ClickHome-MyHome\astrid-myhome.zip & then activate 'MyHome Default'.
	
= 3. Add MyHome shortcodes to pages. =
Go to Tools > Import in the left menu, then:

* Select 'Wordpress' install WordPress Importer & then Activate Plugin & Run Importer.
* Choose ~\wp-content\plugins\ClickHome-MyHome\sample-pages.xml, then Upload file & Import > Submit.

= 4. Set your home page. =
In Settings > Reading, set your front page to 'static', select it & save changes.

= 5. Set your permalink format. =
In Settings > Permalinks, select 'Post name' & save changes.

= 6. Set MyHome settings. =
In MyHome > Settings, enter the API URL as provided to you by ClickHome, then select the Login page & save changes.

= 7. Your MyHome installation should now be complete. =


== Frequently Asked Questions ==

= 1. How to use this plugin? =
For usage instructions, go to the ClickHome website and find the Support page.

= 2. Theme customisation. =
MyHome Default is already a child-theme of Astrid by aThemes, to create your own, copy & rename astrid-myhome to your liking.

* Note: The Astrid theme loads with a standard header image, which has been hidden in style.css. For best results, hide & remove the standard header image in Appearance > Customize > Header area > Header Image.

= 3. CSS classes for logged-out & logged-in. =
Use '.mh-if-logged-out' & '.mh-if-logged-in' to toggle visibility of page elements.


== Screenshots ==

1. Login shortcode.
2. Calendar shortcode.
3. Notes shortcode.
4. Tasks shortcode.
5. House Details shortcode.
6. FAQ shortcode.
7. Photos.


== Changelog ==

= 1.6.3 =
* [MyHome.MaintenanceRequest] BugFixes regarding shortcode options & calculation of days.
* [MyHome.Login] New 'Forgot Password' process.
* [MyHome.Calendar] Correct sequence order after grouping.

= 1.6.2 =
* Add support for .mh-if-maintenance & .mh-if-not-maintenance CSS classes to show/hide elements if handover date has passed
* [MyHome.Photos] Lazy load full-resolution images

= 1.6.1 =
* [MyHome.TenderSelections] & [MyHome.TenderPackages] Dynamically drawn pages for quicker category switching
* [MyHome.TenderSelections] Added 'Skip Selection Overview' option
* [MyHome.HouseType] Added 'Register Interest' lead creation feature
* [MyHome.MaintenanceRequest] Added support for limiting moreIssues by days since created (eg: moreIssues=true,7)
* [MyHome.Login] Support logging in manually while an existing Facebook account is stll connected.

= 1.6.0 =
* [MyHome.MaintenanceRequest] Added support for multipleJobs & moreIssues params
* [MyHome.MaintenanceRequest] Added support for Maint. Type description (button label also now uses 'title' instead of 'name')
* [MyHome.MaintenanceReview] Fix bug where this page is displayed twice

= 1.5.9 =
* [MyHome.Notes] Added support for nested replies
* [MyHome.Notes] Added support for document uploads with showDocuments=true
* [MyHome.Notes] Added support for template subjects with predefinedSubjects=true
* [MyHome.Photos] Added support for slideshow=true
* [MyHome.Tasks] Added support for hideFields=day
* Compatibility fix for Internet Explorer re: Tender Selections

= 1.5.8 =
* Compatibility fix for Internet Explorer re: [MyHome.Calendar]

= 1.5.7 =
* Added Tender Packages, Options, & Variations
* Allow 'resource' to be hidden in MyHome.Calendar with resource=false
* Better support for sub-directory installs

= 1.5.6 =
* Fix when using MyHome with unsupported themes
* Fix Web Inquiry submission

= 1.5.5 =
* Vertical progress bar cross-browser support
* Enabled shortcodes in page-title's & widget's
* Added MyHome.ClientName shortcode

= 1.5.4 =
* Support for 'filter' option on progress & tasks shortcodes

= 1.5 =
* Support for compatible themes
* Shortcode style updates

= 1.3 =
* Support for advertising pages.
* Bug fix concerning the logging system.

= 1.2 =
* Support for maintenance pages.
* Some minor bug fixes and improvements.

= 1.1 =
* Support for customisable contact forms.
* Faster thumbnail loading.
* Bug fix concerning the login form redirection.

= 1.0 =
* First release.

== Upgrade Notice ==

= 1.2 =
This version adds support for maintenance pages and includes some more changes and bug fixes.

= 1.1 =
This version adds support for contact forms and includes some more changes and bug fixes.

= 1.0 =
This is the first release of the plugin.
