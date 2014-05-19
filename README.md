MABL
=======

With MABL 1.3 we introduced some extra features, still in development, and not all available :

- Play store publication 
	- multi language description manager for multiple apps using templates
	- multi language screenshots manager (coming soon)
- Sales statistics (daily sales)
	- import your sales csv files from Play sotre and voila...
	- auto sync using gsutils
- Earnings statistics (full description of monthly sales)
	- import your sales csv files from Play sotre and voila... (coming coon)
	- auto sync using gsutils (coming soon)  
- Development tools
	- Android Asset Studio from android-ui-utils

## Support us ##

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QQJM9LMALTTJA)

Try our Android applications :

[![Google Play Store](http://medialoha.net/images/google_play_download_grey.png)](https://play.google.com/store/apps/developer?id=Medialoha)

Support us can be as simple as a like on Facebook :

[![Like us on Facebook](http://www.medialoha.net/images/like-us-on-facebook-small.png)](https://www.facebook.com/pages/Medialoha/1414959965409936 "Like us on Facebook") 

#### How to help us ? ####

Join our discussion forum here [mabl group](https://groups.google.com/d/forum/mabl) and share your ideas.

## Installation / Update ##

You can find a complete procedure here : [medialoha.net](http://medialoha.net/index.php/en/menu-mablab-en)


#### Test ####

Use our android app to test your configuration :

Download apk [v1.0.0](https://github.com/Medialoha/ACRA-Backend-Tester/releases/download/1.0.0/android.app.acra-tester.apk).

Check Git hub repository [here](https://github.com/Medialoha/ACRA-Backend-Tester).

## Change Log ##

**Version 1.3.3-Helen**

- *Currency support for sales report added*
- *Sales statistics improved*
- *Complete Asset Studio integration*
- *Missed add and remove application features*

**Version 1.3.2-Helen**

- *Support devices exception list from which reports will be ignored*
- *New chart: daily sales per month*
- *Sales table can now be filtered and ordered*
- *Some issues fixed (check milestone for more details*

**Version 1.3.0-Helen**

- *Milestones management*
- *Applications management*
- *New issue state : TESTING*
- *New issue details dialog*
- *Introduced comment field for issue*
- *Improved issues table presentation*
- *Publication tools (still in development)*
- *Sales tools (still in development)*

**Version 1.2.4-Lester**

- *Installation script*
- *Issue #32: Problem deleting reports*

**Version 1.2.3-Lester**

- *Issue #31: Division by zero in /pages/home.php*
- *Issue #28 #29: Serializing values*
- *Issue #30: Missing users table prefix in db install script*

**Version 1.2.2-Lester**

- *New reports evolution chart enhanced*
- *Added environments tab in report report details dialog*
- *Settings keys now formatted and ordered*
- *New issue key method which prevent wrong grouping*
- *No more use of triggers, procedures or functions*

Run *update-to-5 update* script from any preivous version of MABL. Issues table will be **dropped** and recreated to group reports correctly.

Thanks to **xeno010** for his help !

**Version 1.2.1-Lester**

- *Support of custom MySQL connection port added*

**Version 1.2.0-Lester**

- *Formatted stack trace*
- *Formatted Log cat*
- *Issues management*
- *New issues filtering options*
- *New dashboard layout*
- *Dashboard refresh interval and number of new issues are now configurable*
- *Some improvements and minor bugs fixed*

**Version 1.1.2-Abby**

- *Add two methods for HTTP basic auth : PHP or htaccess/htpasswd files*
- *Report script moved to report/report.php*

**Version 1.1.1-Abby**

- *Correct authentication problem*
- *Limit access to logs and libs dir with htaccess file*
- *Debug logs is now disabled by default*

**Version 1.1.0-Abby**

- *Support of HTTP basic authentication.*

**Version 1.0.0-Connor**

- *Initial release*
