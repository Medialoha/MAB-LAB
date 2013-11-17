MABL
=======

#### Installation ####

You can find a complete procedure here : [medialoha.net](http://medialoha.net/index.php/en/menu-mablab-en)

#### Update procedure ####

1. Backup your data
- Edit the configuration file */includes/config.php* from the new release 
- Upload new files on your server
- Launch script file at /install/update-to-*VERSION_CODE*/update.php
- Remove install directory

#### How to help us ? ####

Join our discussion forum here [mabl group](https://groups.google.com/d/forum/mabl) and share your ideas.

#### How to Support us ? ####

Have a look on our web site and check out our other products.

## Change Log ##

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
