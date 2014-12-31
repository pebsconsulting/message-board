<?php
/**
 * Handles the template hierarchy.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Override the template hierarchy when viewing the forums. */
add_filter( 'template_include', 'mb_template_include' );

/* Adds the theme compatibility layer. */
add_action( 'mb_theme_compat', 'mb_theme_compat' );

/**
 * Returns the theme folder that houses the templates for the plugin.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_theme_template_folder() {
	return apply_filters( 'mb_get_theme_template_folder', 'board' );
}

/**
 * Function for loading template parts.  This is similar to the WordPress `get_template_part()` function 
 * with the exception that it will fall back to templates in the plugin's `/templates` folder.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $slug
 * @param  string  $name
 * @return void
 */
function mb_get_template_part( $slug, $name = '' ) {

	/* Get theme and plugin templates paths. */
	$theme_dir  = mb_get_theme_template_folder();
	$plugin_dir = trailingslashit( message_board()->dir_path ) . 'templates';

	/* Build the templates array for the theme. */
	$templates = array();

	if ( !empty( $name ) )
		$templates[] = "{$theme_dir}/{$slug}-{$name}.php";

	$templates[] = "{$theme_dir}/{$slug}.php";

	/* Attempt to find the template in the theme. */
	$has_template = locate_template( $templates, false, false );

	/* If no theme template found, check for name + slug template in plugin. */
	if ( !$has_template && !empty( $name ) && file_exists( "{$plugin_dir}/{$slug}-{$name}.php" ) )
		$has_template = "{$plugin_dir}/{$slug}-{$name}.php";

	/* Else, if no theme template found, check for it in the plugin. */
	elseif ( !$has_template && file_exists( "{$plugin_dir}/{$slug}.php" ) )
		$has_template = "{$plugin_dir}/{$slug}.php";

	/* If we found a template, load it. */
	if ( $has_template )
		require( $has_template );
}

/**
 * Custom template hierarchy.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $template
 * @return string
 */
function mb_template_include( $template ) {

	/* If not viewing a message board page, bail. */
	if ( !mb_is_message_board() )
		return $template;

	/* Set up some default variables. */
	$dir          = mb_get_theme_template_folder();
	$has_template = false;
	$_templates   = array();

	/* If viewing a single forum page. */
	if ( mb_is_single_forum() ) {

		$_templates[] = "{$dir}/single-forum.php";
	}

	/* If viewing the forum archive (default forum front). */
	elseif ( mb_is_forum_archive() ) {

		$_templates[] = "{$dir}/archive-forum.php";
	}

	/* If viewing a single topic. */
	elseif ( mb_is_single_topic() ) {

		$_templates[] = "{$dir}/single-topic.php";
	}

	/* If viewing the topic archive (possible forum front page). */
	elseif ( mb_is_topic_archive() ) {

		$_templates[] = "{$dir}/archive-topic.php";
	}

	/* If viewing a user sub-page. */
	elseif ( mb_is_user_page() ) {

		$page = sanitize_key( get_query_var( 'mb_user_page' ) );

		$_templates[] = "{$dir}/single-user-{$page}.php";
		$_templates[] = "{$dir}/single-user.php";
	}

	/* If viewing a user profile page. */
	elseif ( mb_is_single_user() ) {

		$_templates[] = "{$dir}/single-user.php";
	}

	/* If viewing the user archive. */
	elseif ( mb_is_user_archive() ) {

		$_templates[] = "{$dir}/archive-user.php";
	}

	/* If viewing the advanced search page. */
	elseif ( mb_is_search() ) {

		$_templates[] = "{$dir}/search.php";
	}

	/* If viewing a search results page. */
	elseif ( mb_is_search_results() ) {

		$_templates[] = "{$dir}/search-results.php";
	}

	/* If viewing the forum login page. */
	elseif ( mb_is_forum_login() ) {

		$_templates[] = "{$dir}/login.php";
	}

	/* If viewing an edit page. */
	elseif ( mb_is_edit() ) {

		if ( mb_is_forum_edit() )
			$_templates[] = "{$dir}/edit-forum.php";

		elseif ( mb_is_topic_edit() )
			$_templates[] = "{$dir}/edit-topic.php";

		elseif ( mb_is_reply_edit() )
			$_templates[] = "{$dir}/edit-reply.php";

		elseif ( mb_is_user_edit() )
			$_templates[] = "{$dir}/edit-user.php";

		$_templates[] = "{$dir}/edit.php";
	}

	/* Add the fallback template. */
	$_templates[] = "{$dir}/board.php";

	/* Check to see if we can find one of our templates. */
	$has_template = locate_template( apply_filters( 'mb_template_hierarchy', $_templates, $dir ) );

	/* Allow devs to overwrite template. */
	$has_template = apply_filters( 'mb_template_include', $has_template, $dir );

	/* If we have a template return it. */
	if ( $has_template )
		return $has_template;

	/* Load our fallback if nothing is found at this point. */
	require_once( trailingslashit( message_board()->dir_path ) . 'templates/board.php' );
	return '';
}

function mb_theme_compat() {


	/* If viewing a single forum page. */
	if ( mb_is_single_forum() ) {

		mb_get_template_part( 'content', 'single-forum' );
	}

	/* If viewing the forum archive (default forum front). */
	elseif ( mb_is_forum_archive() ) {

		mb_get_template_part( 'content', 'archive-forum' );
	}

	/* If viewing a single topic. */
	elseif ( mb_is_single_topic() ) {

		mb_get_template_part( 'content', 'single-topic' );
	}

	/* If viewing the topic archive (possible forum front page). */
	elseif ( mb_is_topic_archive() ) {

		mb_get_template_part( 'content', 'archive-topic' );
	}

	/* If viewing a single reply. */
	elseif ( mb_is_single_reply() ) {

		mb_get_template_part( 'content', 'single-reply' );
	}

	/* If viewing the reply archive. */
	elseif ( mb_is_reply_archive() ) {

		mb_get_template_part( 'content', 'archive-reply' );
	}

	/* If viewing a user sub-page. */
	elseif ( mb_is_user_page() ) {

		mb_get_template_part( 'content', 'single-user' );
	}

	/* If viewing a user profile page. */
	elseif ( mb_is_single_user() ) {

		mb_get_template_part( 'content', 'single-user' );
	}

	/* If viewing the user archive. */
	elseif ( mb_is_user_archive() ) {

		mb_get_template_part( 'content', 'archive-user' );
	}

	/* If viewing the advanced search page. */
	elseif ( mb_is_search() ) {

		mb_get_template_part( 'content', 'search' );
	}

	/* If viewing a search results page. */
	elseif ( mb_is_search_results() ) {

		mb_get_template_part( 'content', 'search-results' );
	}

	/* If viewing the forum login page. */
	elseif ( mb_is_forum_login() ) {

		mb_get_template_part( 'content', 'login' );
	}

	/* If viewing an edit page. */
	elseif ( mb_is_edit() ) {

		mb_get_template_part( 'content', 'edit' );
	}
}
