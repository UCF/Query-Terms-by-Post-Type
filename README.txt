=== Query Terms by Post Type ===
Contributors: ucfwebcom
Requires at least: 5.3
Tested up to: 6.1
Stable tag: 1.0.1
Requires PHP: 7.4
License: GPLv3 or later
License URI: http://www.gnu.org/copyleft/gpl-3.0.html

Adds options for filtering taxonomy term retrieval by post types with assigned terms.


== Description ==

By default, when querying taxonomy terms throughout WordPress (e.g. via `get_terms()`), terms assigned to _all_ post types the taxonomy is registered on are returned.  For instance, if your site has the taxonomy "genre" available on the post types "book" and "magazine", and you want to retrieve a list of genres assigned to _just_ magazines, you'd have to first retrieve all magazine IDs, and then perform a `wp_get_object_terms()` call, passing in the retrieved magazine IDs.  [This WordPress Stack Exchange question](https://wordpress.stackexchange.com/questions/57444/get-terms-by-custom-post-type) explains the issue well; also see [this WordPress core trac ticket](https://core.trac.wordpress.org/ticket/18106).

This plugin adds support for a `post_types` argument for `WP_Term_Query->get_terms()`/`get_terms()` and a GET param on REST endpoints for taxonomies registered with [`show_in_rest` enabled](https://developer.wordpress.org/reference/functions/register_taxonomy/#parameters).


== Installation/setup ==
Simply install and activate this plugin, and follow [usage instructions](#usage) below.  No further plugin configuration is required, but please [review the "Things to keep in mind" section](#things-to-keep-in-mind) below before utilizing this plugin in a production environment.


== Usage ==

= Usage with `get_terms()` =
When retrieving terms via `get_terms()`, pass in the `post_types` argument with either a string or array of strings for each post type name to filter against.  Terms assigned to _any_ of the provided post types will be returned (not _all_).

Examples, with a custom taxonomy "genre", and post types "book" and "magazine":

```
<?php
$magazine_genres = get_terms( array(
    'taxonomy'   => 'genre',
    'post_types' => 'magazine'
) );

$magazine_or_book_genres = get_terms( array(
    'taxonomy'   => 'genre',
    'post_types' => array( 'magazine', 'book' )
) );
```

= Usage with `get_categories()` and `get_tags()` =
`get_categories()` and `get_tags()` utilize `get_terms()` under the hood, so you can simply pass these functions the `post_types` argument in the same way:

```
<?php
$just_post_categories = get_categories( array(
    'post_types' => 'post'
) );

$just_post_tags = get_tags( array(
    'post_types' => 'post'
) )
```

= Usage with REST endpoints =
When querying vanilla REST API endpoints for taxonomies, you can pass in the `post_types` param with a list of post type names to filter against.  Terms assigned to _any_ of the provided post types will be returned (not _all_).  Comma-separated lists of names or query parameter array syntax are both supported.

Examples, with a custom taxonomy "genre", and post types "book" and "magazine":

**Magazine genres:**

`wp/v2/genre?post_types=magazine`

**Magazine or book genres:**

`wp/v2/genre?post_types=magazine,book`

`wp/v2/genre?post_types[]=magazine&post_types[]=book`

= Things to keep in mind =
- One other place within WordPress where term counts are not filtered by post type as expected is within the WordPress admin, when viewing an `edit-tags.php` screen.  This plugin _does not_ attempt to rectify inaccurate values in the "Count" column of these admin screens at this time for performance reasons.
- You may run into performance issues with large numbers of terms or posts when filtering term results by `post_types`.
- This plugin works by hooking into the [`terms_clauses`](https://developer.wordpress.org/reference/hooks/terms_clauses/) hook, which modifies the generated SQL statement when retrieving terms.  This plugin does its best to modify SQL statements late during execution, but may still conflict with other plugins that attempt to override this hook.  **It is your responsibility to test and ensure this plugin works as expected with existing code.**


== Changelog ==

= 1.0.1 =
Enhancements:
* Added composer file.

= 1.0.0 =
* Initial release


== Upgrade Notice ==

n/a


== Development ==

[Enabling debug mode](https://codex.wordpress.org/Debugging_in_WordPress) in your `wp-config.php` file is recommended during development to help catch warnings and bugs.

= Requirements =
* node v16+
* gulp-cli

= Instructions =
1. Clone the Query-Terms-by-Post-Type repo into your local development environment, within your WordPress installation's `plugins/` directory: `git clone https://github.com/UCF/Query-Terms-by-Post-Type.git`
2. `cd` into the new Query-Terms-by-Post-Type directory, and run `npm install` to install required packages for development into `node_modules/` within the repo
3. Optional: If you'd like to enable [BrowserSync](https://browsersync.io) for local development, or make other changes to this project's default gulp configuration, copy `gulp-config.template.json`, make any desired changes, and save as `gulp-config.json`.

    To enable BrowserSync, set `sync` to `true` and assign `syncTarget` the base URL of a site on your local WordPress instance that will use this plugin, such as `http://localhost/wordpress/my-site/`.  Your `syncTarget` value will vary depending on your local host setup.

    The full list of modifiable config values can be viewed in `gulpfile.js` (see `config` variable).
3. Run `gulp default` to run all main static asset commands (currently, this just generates the plugin's README.md).
4. If you haven't already done so, create a new WordPress site on your development environment to test this plugin against.
5. Activate this plugin on your development WordPress site.

= Other Notes =
* This plugin's README.md file is automatically generated. Please only make modifications to the README.txt file, and make sure the `gulp readme` command has been run before committing README changes.  See the [contributing guidelines](https://github.com/UCF/Query-Terms-by-Post-Type/blob/master/CONTRIBUTING.md) for more information.


== Contributing ==

Want to submit a bug report or feature request?  Check out our [contributing guidelines](https://github.com/UCF/Query-Terms-by-Post-Type/blob/master/CONTRIBUTING.md) for more information.  We'd love to hear from you!
