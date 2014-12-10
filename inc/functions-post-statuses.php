<?php
/**
 * Sets up custom post status functions.  Registers post statuses with WordPress.  Handles callbacks for 
 * when a post status changes.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Register post statuses. */
add_action( 'init', 'mb_register_post_statuses' );

/* Transition post status. */
add_action( 'transition_post_status', 'mb_transition_post_status', 10, 3 );

/**
 * Returns the slug for the "publish" post status.  Used by replies by default.  Note that this status 
 * is not registered by default because it's a default WordPress post status.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_publish_post_status() {
	return apply_filters( 'mb_get_publish_post_status', 'publish' );
}

/**
 * Returns the slug for the "trash" post status.  Used by forums, topics, and replies by default.  Note 
 * that this status is not registered by default because it's a default WordPress post status.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_trash_post_status() {
	return apply_filters( 'mb_get_trash_post_status', 'trash' );
}

/**
 * Returns the slug for the "open" post status.  Used by forums and topics by default.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_open_post_status() {
	return apply_filters( 'mb_get_open_post_status', 'open' );
}

/**
 * Returns the slug for the "close" post status.  Used by forums and topics by default.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_close_post_status() {
	return apply_filters( 'mb_get_close_post_status', 'close' );
}

/**
 * Returns the slug for the "spam" post status.  Used by topics and replies by default.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_spam_post_status() {
	return apply_filters( 'mb_get_spam_post_status', 'spam' );
}

/**
 * Returns the slug for the "orphan" post status.  Used by topics and replies by default.
 *
 * @note Not currently in use.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function mb_get_orphan_post_status() {
	return apply_filters( 'mb_get_orphan_post_status', 'orphan' );
}

/**
 * Returns an array of allowed post statuses for forums.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_forum_post_statuses() {
	$statuses = array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_trash_post_status() );
	return apply_filters( 'mb_get_forum_post_statuses', $statuses );
}

/**
 * Returns an array of allowed post statuses for topics.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_topic_post_statuses() {
	$statuses = array( mb_get_open_post_status(), mb_get_close_post_status(), mb_get_spam_post_status(), mb_get_trash_post_status() );
	return apply_filters( 'mb_get_topic_post_statuses', $statuses );
}

/**
 * Returns an array of allowed post statuses for replies.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_get_reply_post_statuses() {
	$statuses = array( mb_get_publish_post_status(), mb_get_spam_post_status(), mb_get_trash_post_status() );
	return apply_filters( 'mb_get_topic_post_statuses', $statuses );
}

/**
 * Registers post statuses used by the plugin that WordPress doesn't offer out of the box.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_register_post_statuses() {

	/* Open status args. */
	$open_args = array(
		'label'                     => __( 'Open', 'message-board' ),
		'label_verb'                => __( 'Open', 'message-board' ), // custom
		'label_count'               => _n_noop( 'Open <span class="count">(%s)</span>', 'Open <span class="count">(%s)</span>', 'message-board' ),
		'public'                    => true,
		'show_in_admin_status_list' => true,
		'show_in_admin_all_list'    => true,
	);

	/* Close status args. */
	$close_args = array(
		'label'                     => __( 'Closed', 'message-board' ),
		'label_verb'                => __( 'Close',  'message-board' ), // custom
		'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>', 'message-board' ),
		'public'                    => true,
		'show_in_admin_status_list' => true,
		'show_in_admin_all_list'    => true,
	);

	/* Spam status args. */
	$spam_args = array(
		'label'                     => __( 'Spam', 'message-board' ),
		'label_verb'                => __( 'Spam', 'message-board' ), // custom
		'label_count'               => _n_noop( 'Spam <span class="count">(%s)</span>', 'Spam <span class="count">(%s)</span>', 'message-board' ),
		'public'                    => current_user_can( 'manage_forums' ) && !is_admin() ? true : false,
		'exclude_from_search'       => true,
		'show_in_admin_status_list' => true,
		'show_in_admin_all_list'    => false,
	);

	/* Orphan status args. */
	$orphan_args = array(
		'label'                     => __( 'Orphan', 'message-board' ),
		'label_verb'                => __( 'Orphan', 'message-board' ), // custom
		'label_count'               => _n_noop( 'Orphan <span class="count">(%s)</span>', 'Orphan <span class="count">(%s)</span>', 'message-board' ),
		'public'                    => true,
		'exclude_from_search'       => true,
		'show_in_admin_status_list' => true,
		'show_in_admin_all_list'    => false,
	);

	/* Register post statuses. */
	register_post_status( mb_get_open_post_status(),   apply_filters( 'mb_open_post_status_args',   $open_args   ) );
	register_post_status( mb_get_close_post_status(),  apply_filters( 'mb_close_post_status_args',  $close_args  ) );
	register_post_status( mb_get_spam_post_status(),   apply_filters( 'mb_spam_post_status_args',   $spam_args   ) );
	register_post_status( mb_get_orphan_post_status(), apply_filters( 'mb_orphan_post_status_args', $orphan_args ) );
}

/**
 * Callback function for the `transition_post_status` hook.  This function saves the previous post status 
 * as metadata.  It also adds actions for more specific status changes.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $new_status
 * @param  string  $old_status
 * @param  object  $post
 * @return void
 */
function mb_transition_post_status( $new_status, $old_status, $post ) {

	/* Get post types. */
	$forum_type = mb_get_forum_post_type();
	$topic_type = mb_get_topic_post_type();
	$reply_type = mb_get_reply_post_type();

	/* If not one of our post types, bail. */
	if ( !in_array( $post->post_type, array( $forum_type, $topic_type, $reply_type ) ) )
		return;

	/* Keep track of the old post status by saving it as post meta. */
	$type = mb_translate_post_type( $post->post_type );
	update_post_meta( $post->ID, call_user_func( "mb_get_{$type}_prev_status_meta_key" ), $old_status );

	/* Get post type statuses. */
	$forum_statuses = mb_get_forum_post_statuses();
	$topic_statuses = mb_get_topic_post_statuses();
	$reply_statuses = mb_get_reply_post_statuses();

	/* Get the post statuses we need to work with. */
	$publish_status = mb_get_publish_post_status();
	$open_status    = mb_get_open_post_status();
	$close_status   = mb_get_close_post_status();
	$spam_status    = mb_get_spam_post_status();
	$trash_status   = mb_get_trash_post_status();

	/* If old status is not one of our statuses but the new is, assume we're publishing for the first time. */
	if ( $forum_type === $post->post_type && !in_array( $old_status, $forum_statuses ) && in_array( $new_status, $forum_statuses ) )
		mb_insert_forum_data( $post );

	elseif ( $topic_type === $post->post_type && !in_array( $old_status, $topic_statuses ) && in_array( $new_status, $topic_statuses ) )
		mb_insert_topic_data( $post );

	elseif ( $reply_type === $post->post_type && !in_array( $old_status, $reply_statuses ) && in_array( $new_status, $reply_statuses ) )
		mb_insert_reply_data( $post );

	/* Publish status change. */
	add_action( "{$publish_status}_to_{$spam_status}",  'mb_publish_to_spam'  );
	add_action( "{$publish_status}_to_{$trash_status}", 'mb_publish_to_trash' );

	/* Open status change. */
	add_action( "{$open_status}_to_{$spam_status}",     'mb_publish_to_spam'  );
	add_action( "{$open_status}_to_{$trash_status}",    'mb_publish_to_trash' );

	/* Close status change. */
	add_action( "{$close_status}_to_{$spam_status}",    'mb_close_to_spam'    );
	add_action( "{$close_status}_to_{$trash_status}",   'mb_close_to_trash'   );

	/* Spam status change. */
	add_action( "{$spam_status}_to_{$publish_status}",  'mb_spam_to_publish'  );
	add_action( "{$spam_status}_to_{$open_status}",     'mb_spam_to_pubish'   );
	add_action( "{$spam_status}_to_{$close_status}",    'mb_spam_to_close'    );

	/* Trash status change. */
	add_action( "{$trash_status}_to_{$publish_status}", 'mb_trash_to_publish' );
	add_action( "{$trash_status}_to_{$open_status}",    'mb_trash_to_publish' );
	add_action( "{$trash_status}_to_{$close_status}",   'mb_trash_to_close'   );
}

/**
 * Resets topic/reply data when the post status is changed from 'publish' to 'spam'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_publish_to_spam( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post );

	elseif ( mb_get_reply_post_type() === $post->post_type )
		mb_reset_reply_data( $post );
}

/**
 * Resets topic/reply data when the post status is changed from 'publish' to 'trash'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_publish_to_trash( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post );

	elseif ( mb_get_reply_post_type() === $post->post_type )
		mb_reset_reply_data( $post );
}

/**
 * Resets topic data when the post status is changed from 'close' to 'spam'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_close_to_spam( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post );
}

/**
 * Resets topic data when the post status is changed from 'close' to 'trash'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_close_to_trash( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post );
}

/**
 * Resets topic/reply data when the post status is changed from 'spam' to 'publish'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_spam_to_publish( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post, true );

	elseif ( mb_get_reply_post_type() === $post->post_type )
		mb_reset_reply_data( $post, true );
}

/**
 * Resets topic data when the post status is changed from 'spam' to 'close'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_spam_to_close( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post, true );
}

/**
 * Resets topic/reply data when the post status is changed from 'trash' to 'publish'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_trash_to_publish( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post, true );

	elseif ( mb_get_reply_post_type() === $post->post_type )
		mb_reset_reply_data( $post, true );
}

/**
 * Resets topic data when the post status is changed from 'trash' to 'close'.
 *
 * @since  1.0.0
 * @access public
 * @param  $post  object
 * @return void
 */
function mb_trash_to_close( $post ) {

	if ( mb_get_topic_post_type() === $post->post_type )
		mb_reset_topic_data( $post, true );
}

/**
 * Changes a forum's post status to "open" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return int|WP_Error
 */
function mb_open_forum( $forum_id ) {
	return mb_update_post_status( $forum_id, mb_get_open_post_status() );
}

/**
 * Changes a forum's post status to "close" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $forum_id
 * @return int|WP_Error
 */
function mb_close_forum( $forum_id ) {
	return mb_update_post_status( $forum_id, mb_get_close_post_status() );
}

/**
 * Changes a topic's post status to "open" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int|WP_Error
 */
function mb_open_topic( $topic_id ) {
	return mb_update_post_status( $topic_id, mb_get_open_post_status() );
}

/**
 * Changes a topic's post status to "close" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int|WP_Error
 */
function mb_close_topic( $topic_id ) {
	return mb_update_post_status( $topic_id, mb_get_close_post_status() );
}

/**
 * Changes a topic's post status to "spam" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int|WP_Error
 */
function mb_spam_topic( $topic_id ) {
	return mb_update_post_status( $topic_id, mb_get_spam_post_status() );
}

/**
 * Changes a topic's status from "spam" to its previous status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int|WP_Error
 */
function mb_unspam_topic( $topic_id ) {
	$status = get_post_meta( $topic_id, mb_get_topic_prev_status_meta_key(), true );
	$status = !empty( $status ) ? $status : mb_get_open_post_status();
	return mb_update_post_status( $topic_id, $status );
}

/**
 * Changes a topic's post status to "orphan" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int|WP_Error
 */
function mb_orphan_topic( $topic_id ) {
	return mb_update_post_status( $topic_id, mb_get_orphan_post_status() );
}

/**
 * Changes a topic's status from "orphan" to its previous status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $topic_id
 * @return int|WP_Error
 */
function mb_unorphan_topic( $topic_id ) {
	$status = get_post_meta( $topic_id, mb_get_topic_prev_status_meta_key(), true );
	$status = !empty( $status ) ? $status : mb_get_orphan_post_status();
	return mb_update_post_status( $topic_id, $status );
}

/**
 * Changes a reply's post status to "spam" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return int|WP_Error
 */
function mb_spam_reply( $reply_id ) {
	return mb_update_post_status( $reply_id, mb_get_spam_post_status() );
}

/**
 * Changes a reply's status from "spam" to its previous status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return int|WP_Error
 */
function mb_unspam_reply( $reply_id ) {
	$status = get_post_meta( $reply_id, mb_get_reply_prev_status_meta_key(), true );
	$status = !empty( $status ) ? $status : mb_get_publish_post_status();
	return mb_update_post_status( $reply_id, $status );
}

/**
 * Changes a reply's post status to "orphan" if it has a different status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return int|WP_Error
 */
function mb_orphan_reply( $reply_id ) {
	return mb_update_post_status( $reply_id, mb_get_orphan_post_status() );
}

/**
 * Changes a reply's status from "orphan" to its previous status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $reply_id
 * @return int|WP_Error
 */
function mb_unorphan_reply( $reply_id ) {
	$status = get_post_meta( $reply_id, mb_get_reply_prev_status_meta_key(), true );
	$status = !empty( $status ) ? $status : mb_get_orphan_post_status();
	return mb_update_post_status( $reply_id, $status );
}

/**
 * Helper function for quickly updating a post's status.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @param  string  $status
 * @return int|WP_Error
 */
function mb_update_post_status( $post_id, $status ) {
	return wp_update_post( array( 'ID' => $post_id, 'post_status' => $status ) );
}
