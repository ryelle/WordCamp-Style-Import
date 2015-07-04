<?php

namespace WordCamp\Theme_Cloner;

defined( 'WPINC' ) or die();

/*
Plugin Name: WordCamp Theme Cloner
Description: Allows organizers to clone the theme and custom CSS, etc from other WordCamps as a starting point for their own site.
Version:     0.1
Author:      WordCamp.org
Author URI:  http://wordcamp.org
License:     GPLv2 or later
*/


/* todo
 *
 * i18n strings
 * phpdoc everything
 * change name to site cloner, update prefix
 */


/*
 * todo commit msg
 *
 * because such a fundamental change, removed 90% of code, so went ahead and rewrote from scratch rather than trying to integrate the two
 *
 * fixes 11, { 3 others }
 *
 * ---
 *
 * leave comment on #4 saying to migrate to backbone and _ templates instead of php, so that can filter on the fly
 */


require_once( __DIR__ . '/includes/setup-customizer-components.php' );
require_once( __DIR__ . '/includes/preview-source-site.php' );
require_once( __DIR__ . '/includes/import-source-site.php' );
