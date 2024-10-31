=== Sectors - Conditional Templates & Hooks ===
Contributors: intoxstudio, devinstitute
Donate link:
Tags: conditional templates, actions, filters, hooks
Requires at least: 4.8
Requires PHP: 5.6
Tested up to: 6.6
Stable tag: 1.2
License: GPLv3

What if you could add templates, actions, and filters depending on the context?

== Description ==

Sectors is a first of its kind plugin for WordPress. Create theme templates for any context on your site. Make sure select Actions or Filters are only added when certain conditions are met.

####Contexts

When you add a new sector to your site, you select the content it covers. This could be:

* All posts in a select category
* Pages by an author
* Custom Post Types with a Custom Taxonomy
* ...
* Any combination of above

Sectors also comes with built in support for BuddyPress, WPML, Polylang, and more.

####Templates

Sectors will look in your theme folder for the following templates:

* `/sectors/<sector-name>.php`
* `/sector-<sector-name>.php`

If found, it will be automatically loaded for the context.

####API

**Template Tags**

Check if a query is part of a given or any sector:

`is_sector(string $sector):boolean`

Get all sectors for current context:

`get_current_sectors():array`

**Hooks**

Sectors extends all WordPress Actions and Filters by adding a scope. This means you can add a callback to an action and make sure it's only executed in a given context:

`add_sector_action(string $sector, string $tag, callable $function, int $priority = 10, int $accepted_args = 1 )

add_sector_filter(string $sector, string $tag, callable $function, int $priority = 10, int $accepted_args = 1 )`

== Installation ==

1. Upload the full plugin directory to your `/wp-content/plugins/` directory or install the plugin through `Plugins` in the Admin Dashboard
1. Activate the plugin through `Plugins` in the Admin Dashboard
1. Create your first Sector under the menu *Sectors > Add New*

== Frequently Asked Questions ==

== Screenshots ==

1. Actions & Filters without Sectors
2. Actions & Filters using Sectors

== Upgrade Notice ==

== Changelog ==

= 1.2 =

* Added: wordpress 5.5 support
* Added: minimum wordpress version 4.8
* Added: minimum php version 5.6
* Updated: wp-content-aware-engine
* Updated: wp-db-updater

= 1.1 =

* Added: api to get current sectors
* Added: 'sector' to body class
* Added: ui improvements
* Updated: wp-content-aware-engine

= 1.0 =

* Welcome