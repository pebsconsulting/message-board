<?php
/**
 * Handles query-related functionality.  In particular, this file's main purpose is to make sure each
 * page is loading the posts that it is supposed to load.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Filter the arguments for grabbing posts. */
add_action( 'pre_get_posts', 'mb_pre_get_posts' );

// Filter the post SQL clauses.
add_filter( 'posts_clauses', 'mb_posts_clauses', 10, 2 );

/* Filter parse query. */
add_action( 'parse_query', 'mb_parse_query' );

/* Make sure we don't get a 404 on some custom pages. */
add_filter( 'template_redirect', 'mb_404_override', 0 );

/**
 * Checks if viewing the forum front page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_front() {

	$is_front = false;
	$on_front = mb_get_show_on_front();

	if ( 'forums' === $on_front && is_post_type_archive( mb_get_forum_post_type() ) )
		$is_front = true;

	elseif ( 'topics' === $on_front && is_post_type_archive( mb_get_topic_post_type() ) )
		$is_front = true;

	return apply_filters( 'mb_is_forum_front', $is_front );
}

/**
 * Checks if viewing the forum login page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_login() {
	return get_query_var( 'mb_custom' ) && 'login' === get_query_var( 'mb_custom' ) ? true : false;
}

/**
 * Checks if viewing the edit page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_edit() {
	return 'edit' === mb_get_board_action() ? true : false;
}

/**
 * Checks if viewing the edit page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_forum_edit() {
	return mb_is_edit() && is_numeric( get_query_var( 'forum_id' ) ) ? true : false;
}

/**
 * Checks if viewing the edit page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_topic_edit() {
	return mb_is_edit() && is_numeric( get_query_var( 'topic_id' ) ) ? true : false;
}

/**
 * Checks if viewing the edit page.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_reply_edit() {
	return mb_is_edit() && is_numeric( get_query_var( 'reply_id' ) ) ? true : false;
}

/**
 * Checks if viewing one of the Message Board plugin pages.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function mb_is_message_board() {

	$is_message_board = false;

	if (
		   mb_is_search()
		|| mb_is_search_results()
		|| mb_is_forum_login()
		|| mb_is_edit()
		|| mb_is_forum_archive()
		|| mb_is_topic_archive()
		|| mb_is_reply_archive()
		|| mb_is_user_archive()
		|| mb_is_single_forum()
		|| mb_is_single_topic()
		|| mb_is_single_reply()
		|| mb_is_single_user()
		|| mb_is_role_archive()
		|| mb_is_single_role()
	) {
		$is_message_board = true;
	}

	return apply_filters( 'mb_is_message_board', $is_message_board );
}

/**
 * Overwrites the main query depending on the situation.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $query
 * @return void
 */
function mb_pre_get_posts( $query ) {

	/* If viewing an admin page or this isn't the main query, bail. */
	if ( is_admin() || !$query->is_main_query() )
		return;

	/* If viewing the forum archive page. */
	if ( mb_is_forum_archive() ) {

		$statuses = array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_publish_post_status(), mb_get_private_post_status(), mb_get_archive_post_status() );

		if ( current_user_can( 'read_hidden_forums' ) )
			$statuses[] = mb_get_hidden_post_status();

		$query->set( 'post_type',      mb_get_forum_post_type()    );
		$query->set( 'post_status',    $statuses );
		$query->set( 'posts_per_page', mb_get_forums_per_page()    );
		$query->set( 'orderby',        array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		$query->set( 'post_parent',    0                           );

		add_filter( 'the_posts', 'mb_posts_hierarchy_filter', 10, 2 );
	}

	/* Is topic archive page. */
	elseif ( mb_is_topic_archive() ) {

		$statuses = array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_publish_post_status(), mb_get_private_post_status() );

		if ( current_user_can( 'read_hidden_topics' ) )
			$statuses[] = mb_get_hidden_post_status();

		$query->set( 'post_type',      mb_get_topic_post_type()    );
		$query->set( 'post_status',    $statuses                   );
		$query->set( 'posts_per_page', mb_get_topics_per_page()    );
		$query->set( 'order',          'DESC'                      );
		$query->set( 'orderby',        'menu_order'                );

		add_filter( 'the_posts', 'mb_posts_super_filter', 10, 2 );
	}

	/* If viewing a user view. */
	elseif ( mb_is_user_page() ) {

		/* Single user forums created page. */
		if ( mb_is_user_page( 'forums' ) ) {

			$query->set( 'post_type',      mb_get_forum_post_type() );
			$query->set( 'posts_per_page', mb_get_forums_per_page() );
			$query->set( 'order',          'ASC'                    );
			$query->set( 'orderby',        'title'                  );

		/* Single user topics created page. */
		} elseif ( mb_is_user_page( 'topics' ) ) {

			$query->set( 'post_type',      mb_get_topic_post_type() );
			$query->set( 'posts_per_page', mb_get_topics_per_page() );
			$query->set( 'order',          'DESC'                   );
			$query->set( 'orderby',        'menu_order'             );

		/* Single user replies created page. */
		} elseif ( mb_is_user_page( 'replies' ) ) {

			$query->set( 'post_type',      mb_get_reply_post_type()  );
			$query->set( 'posts_per_page', mb_get_replies_per_page() );
			$query->set( 'order',          'DESC'                    );
			$query->set( 'orderby',        'date'                    );

		/* Single user bookmarks saved page. */
		} elseif ( mb_is_user_page( 'bookmarks' ) ) {

			$user = get_user_by( 'slug', get_query_var( 'author_name' ) );
			$favs = get_user_meta( $user->ID, mb_get_user_topic_bookmarks_meta_key(), true );
			$favs = wp_parse_id_list( $favs );

			/* Empty array with `post_in` hack. @link https://core.trac.wordpress.org/ticket/28099 */
			if ( empty( $favs ) )
				$favs = array( 0 );

			$query->set( 'post__in',       $favs                    );
			$query->set( 'post_type',      mb_get_topic_post_type() );
			$query->set( 'posts_per_page', mb_get_topics_per_page() );
			$query->set( 'order',          'DESC'                   );
			$query->set( 'orderby',        'menu_order'             );

			add_filter( 'posts_where', 'mb_auth_posts_where', 10, 2 );

		/* Single user topic subscriptions page. */
		} elseif ( mb_is_user_page( 'topic-subscriptions' ) ) {

			$user = get_user_by( 'slug', get_query_var( 'author_name' ) );
			$subs = mb_get_user_topic_subscriptions( $user->ID );

			/* Empty array with `post_in` hack. @link https://core.trac.wordpress.org/ticket/28099 */
			if ( empty( $subs ) )
				$subs = array( 0 );

			$query->set( 'post__in',       $subs                    );
			$query->set( 'post_type',      mb_get_topic_post_type() );
			$query->set( 'posts_per_page', mb_get_topics_per_page() );
			$query->set( 'order',          'DESC'                   );
			$query->set( 'orderby',        'menu_order'             );

			add_filter( 'posts_where', 'mb_auth_posts_where', 10, 2 );

		/* Single user forum subscriptions page. */
		} elseif ( mb_is_user_page( 'forum-subscriptions' ) ) {

			$user = get_user_by( 'slug', get_query_var( 'author_name' ) );
			$subs = mb_get_user_forum_subscriptions( $user->ID );

			/* Empty array with `post_in` hack. @link https://core.trac.wordpress.org/ticket/28099 */
			if ( empty( $subs ) )
				$subs = array( 0 );

			$query->set( 'post__in',       $subs                    );
			$query->set( 'post_type',      mb_get_forum_post_type() );
			$query->set( 'posts_per_page', mb_get_forums_per_page() );
			$query->set( 'order',          'DESC'                   );
			$query->set( 'orderby',        'menu_order'             );

			add_filter( 'posts_where', 'mb_auth_posts_where', 10, 2 );
		}
	}

	/* If viewing the search results page. */
	elseif ( mb_is_search_results() ) {

		$post_type = $query->get( 'post_type' );

		/* If not searching a specific post type, make sure to search all forum-related post types. */
		if ( empty( $post_type ) || 'any' === $post_type || !isset( $_GET['mb_search_mode'] ) )
			$query->set( 'post_type', array( mb_get_forum_post_type(), mb_get_topic_post_type(), mb_get_reply_post_type() ) );

		$query->set( 'post_status',    array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_publish_post_status() ) );
		$query->set( 'posts_per_page', mb_get_topics_per_page()    );
	}

	/*
	 * If viewing a user role archive. We're running this early in the page load to change the query
	 * var to match the actual role name. It's kind of hacky, but it gets the job done.
	 */
	elseif ( mb_is_user_archive() && get_query_var( 'mb_role' ) ) {

		$role = get_query_var( 'mb_role' );

		if ( $role && in_array( "mb_{$role}", array_keys( mb_get_dynamic_roles() ) ) )
			$query->set( 'mb_role', "mb_{$role}" );
	}

	/* Check if viewing a single forum, topic, or reply. */
	elseif ( $query->is_single && isset( $query->query_vars['post_type'] ) && in_array( $query->query_vars['post_type'], array( mb_get_forum_post_type(), mb_get_topic_post_type(), mb_get_reply_post_type() ) ) ) {

		add_filter( 'the_posts', 'mb_posts_can_read_parent' );
	}
}

/**
 * This is a filter on `posts_clauses` that allows the plugin to work around core WP expecting
 * hierarchical post types to have hierarchical permalinks.  Rather, we want our forums to be
 * flat, so we need to make sure the correct forum is queried on single forum views.  We do this 
 * by overwriting the "where" clause and querying by the post name.
 *
 * @since  1.0.0
 * @access public
 * @param  array   $clauses
 * @param  object  $query
 * @return array
 */
function mb_posts_clauses( $clauses, $query ) {
	global $wpdb;

	$type = mb_get_forum_post_type();

	if ( $query->get( $type ) && $query->get( 'post_type' ) && $type === $query->get( 'post_type' ) ) {

		$clauses['where'] = $wpdb->prepare(
			" AND {$wpdb->posts}.post_name = %s AND {$wpdb->posts}.post_type = %s",
			sanitize_title_for_query( $query->get( $type ) ),
			$type
		);
	}

	return $clauses;
}

/**
 * Filter on 'posts_where' to make sure we're not loading posts by the author.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $where
 * @param  object  $query
 * @global object  $wpdb
 * @return string
 */
function mb_auth_posts_where( $where, $query ) {
	global $wpdb;

	$author_id = absint( get_query_var( 'author' ) );

	return str_replace( " AND ({$wpdb->posts}.post_author = {$author_id})", '', $where );
}

/**
 * Filter on `the_posts` on single post views to make sure the current user can read the parent
 * post.  Otherwise, return an empty array.  This will cause the page to properly 404.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $posts
 * @return array
 */
function mb_posts_can_read_parent( $posts ) {

	if ( !empty( $posts ) && 0 < $posts[0]->post_parent && !current_user_can( 'read_post', $posts[0]->post_parent ) )
		$posts = array();

	return $posts;
}

/**
 * Puts forums in the correct, hierarchical order.  Meant to be used as a filter on `the_posts`.
 *
 * @link   http://wordpress.stackexchange.com/questions/63599/custom-post-type-wp-query-and-orderby
 * @since  1.0.0
 * @access public
 * @param  array  $posts
 * @param  object $query
 * @return array
 */
function mb_posts_hierarchy_filter( $posts, $query ) {

	$post_parent = mb_is_single_forum() ? get_queried_object_id() : 0;

	$refs = $list = array();

	foreach ( $posts as $post ) {
		$thisref = &$refs[ $post->ID ];

		$thisref['post'] = $post;

		if ( $post_parent === $post->post_parent )
			$list[ $post->ID ] = &$thisref;
		else
			$refs[ $post->post_parent ]['children'][ $post->ID ] = &$thisref;
	}

	$result = array();
	mb_recursively_flatten_list( $list, $result );

	remove_filter( 'the_posts', 'mb_posts_hierarchy_filter' );

	return $result;
}

/**
 * Adds super sticky posts to the posts array.  Meant to be used as a filter on `the_posts`.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $posts
 * @param  object $query
 * @return array
 */
function mb_posts_super_filter( $posts, $query ) {

	remove_filter( 'the_posts', 'mb_posts_super_filter' );

	return mb_add_stickies( $posts, mb_get_super_topics() );
}

/**
 * Adds sticky posts to the posts array.  Meant to be used as a filter on `the_posts`.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $posts
 * @param  object $query
 * @return array
 */
function mb_posts_sticky_filter( $posts, $query ) {

	remove_filter( 'the_posts', 'mb_posts_sticky_filter' );

	$forum_id = mb_is_single_forum() ? get_queried_object_id() : 0;
	return mb_add_stickies( $posts, mb_get_sticky_topics(), $forum_id );
}

/**
 * Helper function for flattening a list of parent/child posts.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $list
 * @param  array  $result
 * @return void
 */
function mb_recursively_flatten_list( $list, &$result ) {

	foreach ( $list as $node ) {
		$result[] = $node['post'];

		if ( isset( $node['children'] ) ) {
			mb_recursively_flatten_list( $node['children'], $result );
		}
	}
}

/**
 * Adds sticky posts to the front of the line with any given set of posts and stickies.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $posts         Array of post objects.
 * @param  array  $sticky_posts  Array of post IDs.
 * @param  int    $forum_id      Limit to specific forum.
 * @return array
 */
function mb_add_stickies( $posts, $sticky_posts, $forum_id = 0 ) {

	/* Only do this if on the first page and we indeed have stickies. */
	if ( !is_paged() && !empty( $sticky_posts ) ) {

		$num_posts     = count( $posts );
		$sticky_offset = 0;

		/* Loop over posts and relocate stickies to the front. */
		for ( $i = 0; $i < $num_posts; $i++ ) {

			if ( in_array( $posts[ $i ]->ID, $sticky_posts ) ) {

				$sticky_post = $posts[ $i ];

				/* Remove sticky from current position. */
				array_splice( $posts, $i, 1);

				/* Move to front, after other stickies. */
				array_splice( $posts, $sticky_offset, 0, array( $sticky_post ) );

				/* Increment the sticky offset. The next sticky will be placed at this offset. */
				$sticky_offset++;

				/* Remove post from sticky posts array. */
				$offset = array_search( $sticky_post->ID, $sticky_posts );

				unset( $sticky_posts[ $offset ] );
			}
		}

		/* Fetch sticky posts that weren't in the query results. */
		if ( !empty( $sticky_posts ) ) {

			$args = array(
					'post__in'    => $sticky_posts,
					'post_type'   => mb_get_topic_post_type(),
					'post_status' => array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_publish_post_status() ),
					'nopaging'    => true
			);

			if ( 0 < $forum_id )
				$args['post_parent'] = $forum_id;

			$stickies = get_posts( $args );

			foreach ( $stickies as $sticky_post ) {
				array_splice( $posts, $sticky_offset, 0, array( $sticky_post ) );
				$sticky_offset++;
			}
		}
	}

	return $posts;
}

/**
 * Sets `$query->is_404` to `false` right after the query has been parsed when viewing the forum front
 * page, which WP sets to 404 by default.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $query
 * @return void
 */
function mb_parse_query( $query ) {

	if ( mb_is_search_results() ) {
		$query->is_404        = false;
		$query->is_front_page = false;
		$query->is_home       = false;
		$query->is_post_type_archive = false;
	} elseif ( mb_is_forum_front() ) {
		$query->is_404 = false;
		$query->is_home = false;
	}
}

/**
 * Overrides the 404 for the forum front page early on the `template_redirect` hook.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_404_override() {
	global $wp_query;

	if ( mb_is_user_archive() || mb_is_edit() || get_query_var( 'mb_custom' ) || mb_is_search_results() ) {
		status_header( 200 );
		$wp_query->is_404        = false;
		$wp_query->is_front_page = false;
		$wp_query->is_home       = false;
		$wp_query->is_archive    = false;
		$wp_query->is_post_type_archive = false;
	}
}
