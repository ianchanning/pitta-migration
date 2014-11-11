=== Plugin Name ===
Contributors: icc97
Tags: backup, database, migrate, mysql, developer, db migration, website deploy, wordpress migration, migration
Requires at least: 2.2
Tested up to: 4.0
Stable tag: 0.3.0
License: GPL v3
License URI: http://www.gnu.org/licenses/gpl.txt

Migrate WordPress databases using WP_HOME and WP_SITEURL constants.

== Description ==

This plugin is aimed at developers that need to migrate their databases from 
production to test or local domains.

This is the simplest way I've found to migrate databases quickly. 

You only have to set the `WP_HOME` 
([WordPress address URL](http://codex.wordpress.org/Editing_wp-config.php#WordPress_address_.28URL.29)) 
and `WP_SITEURL` 
([Blog address URL](http://codex.wordpress.org/Editing_wp-config.php#Blog_address_.28URL.29))  
constants once for each environment and then the database is automatically 
upgraded when you import a database and login to the admin area.

This stands on the shoulders of the constants and fills the hole for when 
plugins don't follow the rules and use the database directly.

This plugin is designed to be the most lightweight way to migrate your database 
and stay out the way of your own processes.

It works with WordPress to use WordPress' own constants to update the database 
using the 
[WordPress Database Object](http://codex.wordpress.org/Class_Reference/wpdb). 
It avoids search and replaces in text files. It uses database queries to update 
the database as should be done.

Most developers have their own methods for exporting/importing the database - 
if you can use `mysqldump` then you probably don't want a WordPress plugin to 
do it for you.

It makes no assumptions about your database and its cross platform.

P.S. Pitta is taken from the start of an [answer from WordPress SE](http://wordpress.stackexchange.com/a/182/5433):

> Deployment of a WordPress site from one box to another has been a PITA since 
> day one I started working with WordPress. (Truth-be-told it was a PITA with 
> Drupal for 2 years before I started with WordPress so the problem is 
> certainly not exclusively with WordPress.)

So this plugin aims to make things less PITA and more yummy Pitta (pedants will
mention that Pitta can also be spelled pita).

Its inspiration actually comes from the [second answer](http://wordpress.stackexchange.com/q/119/5433) 
from the same [WordPress SE question](http://wordpress.stackexchange.com/q/119/5433).

Coincidentally there is a bird called a [Pitta](https://en.wikipedia.org/wiki/Pitta):

> The fairy pitta **migrates** from Korea, Japan, Taiwan and coastal China to Borneo.

P.P.S. This plugin is based off the excellent [WordPress Plugin Boilerplate](https://github.com/theantichris/WordPress-Plugin-Boilerplate) from antichris on Github

== Installation ==

1. Upload `pitta-migration` directory to the `/wp-content/plugins/` directory
1. Insert the `WP_HOME` and `WP_SITEURL` constants into your `wp-config.php` file
1. Activate the plugin through the 'Plugins' menu in WordPress
1. As soon as you activate the plugin it will try to migrate the database to match the constants

== Frequently Asked Questions ==

= I've imported my database to migrate and set WP_HOME and WP_SITEURL but nothing happens =

Check the the plugin is active. If you're just restored a production database the plugin might have been deactivated there. 

= It's active too... =

Check if it has run, by looking in the `wp_options` table for home and siteurl, as sometimes the admin notice gets lost.

== Changelog ==

= 0.3.0 =
* Bug fix: Switched to using `admin_notices` as the default hook instead of `admin_init` as the notice would sometimes not appear

= 0.2.1 =
* Removed excess logging

= 0.2.0 =
* Put the plugin into an object 
* Used the [WordPress Plugin Boilerplate](https://github.com/theantichris/WordPress-Plugin-Boilerplate) from antichris on Github

= 0.1 =
* The initial plugin that worked but was very basic

== Upgrade Notice ==

