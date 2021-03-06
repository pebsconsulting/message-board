<?php
/**
 * Callback functions for the various meta boxes used on the post screen in the admin for all 
 * the plugin's post types.
 *
 * @package    MessageBoard
 * @subpackage Admin
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Custom `submitdiv` meta box.  This replaces the WordPress default because it has too many things 
 * hardcoded that we cannot overwrite, particularly dealing with post statuses.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $post
 * @param  array   $args
 * @return void
 */
function mb_submit_meta_box( $post, $args = array() ) {

	$forum_type = mb_get_forum_post_type();
	$topic_type = mb_get_topic_post_type();
	$reply_type = mb_get_reply_post_type();

	$post_type     = $post->post_type;
	$post_status   = $post->post_status;
	$hidden_status = 'auto-draft' === $post_status ? 'draft' : $post_status;

	/* If the post is a forum. */
	if ( $forum_type === $post_type ) {

		$allowed_stati = mb_get_forum_post_statuses();
		$status_obj    = in_array( $post_status, $allowed_stati ) ? get_post_status_object( $post_status ) : get_post_status_object( mb_get_open_post_status() );

	/* If the post is a topic. */
	} elseif ( $topic_type === $post_type ) {

		$allowed_stati = mb_get_topic_post_statuses();
		$status_obj    = in_array( $post_status, $allowed_stati ) ? get_post_status_object( $post_status ) : get_post_status_object( mb_get_open_post_status() );

	/* If the post is a reply. */
	} elseif ( $reply_type === $post_type ) {

		$allowed_stati = mb_get_reply_post_statuses();
		$status_obj    = in_array( $post_status, $allowed_stati ) ? get_post_status_object( $post_status ) : get_post_status_object( mb_get_publish_post_status() );
	} ?>

	<div class="submitbox" id="submitpost">

		<div id="minor-publishing">

			<div id="misc-publishing-actions">

				<div class="misc-pub-section misc-pub-post-status">

					<label for="post_status">
						<?php printf( __( 'Status: %s', 'message-board' ), "<strong class='mb-current-status'>{$status_obj->label}</strong>" ); ?>
					</label>

					<a href="#post-status-select" class="edit-post-status hide-if-no-js">
						<span aria-hidden="true"><?php _e( 'Edit', 'message-board' ); ?></span> 
						<span class="screen-reader-text"><?php _e( 'Edit status', 'message-board' ); ?></span>
					</a>

					<div id="post-status-select" class="hide-if-js">

						<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( $hidden_status ); ?>" />

						<div id="post_status">

							<?php mb_dropdown_forum_status(
								array(
									'selected' => $post_status,
									'id'       => 'post_status',
									'name'     => 'post_status'
								)
							); ?>

							<a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e( 'OK', 'message-board' ); ?></a>
							<a href="#post_status" class="cancel-post-status hide-if-no-js button-cancel"><?php _e( 'Cancel', 'message-board' ); ?></a>

						</div><!-- #post_status -->

					</div><!-- #post-status-select -->

				</div><!-- .misc-pub-section -->

				<?php
				/* Get the post date. */
				$date_mysql = 0 != $post->ID ? $post->post_date : current_time( 'mysql' );

				/* Translators: Publish box date format. */
				$date_i18n = date_i18n( __( 'M j, Y @ G:i', 'message-board' ), strtotime( $date_mysql ) );
				?>

				<div class="misc-pub-section curtime misc-pub-curtime">
					<span id="timestamp"><?php printf( __( 'Date: %s', 'message-board' ), "<strong>{$date_i18n}</strong>" ); ?></span>
				</div><!-- .misc-pub-curtime -->

				<div class="misc-pub-section">
					<i class="dashicons dashicons-admin-users"></i> 
					<?php printf( __( 'Author: %s', 'message-board' ), '<strong>' . get_the_author_meta( 'display_name', $post->post_author ) . '</strong>' ); ?>
				</div><!-- .misc-pub-section -->

				<?php do_action( 'post_submitbox_misc_actions' ); ?>

			</div><!-- #misc-publishing-actions -->

			<div class="clear"></div>

		</div><!-- #minor-publishing -->

		<div id="major-publishing-actions">

			<?php do_action( 'post_submitbox_start' ); ?>

			<div id="delete-action">

				<?php if ( current_user_can( 'delete_post', $post->ID ) ) : ?>
					<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>">
						<?php !EMPTY_TRASH_DAYS ? _e( 'Delete Permanently', 'message-board' ) : _e( 'Move to Trash', 'message-board' ) ?>
					</a>
				<?php endif; ?>

			</div><!-- #delete-action -->

			<div id="publishing-action">

				<span class="spinner"></span>

				<?php if ( 0 == $post->ID || !in_array( $post_status, $allowed_stati ) ) : ?>

					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish', 'message-board' ) ?>" />
					<?php submit_button( __( 'Publish', 'message-board' ), 'primary button-large', 'mb-publish', false, array( 'accesskey' => 'p' ) ); ?>

				<?php else : ?>

					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update', 'message-board' ) ?>" />
					<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Update', 'message-board' ) ?>" />

				<?php endif; ?>

			</div><!-- #publishing-action -->

			<div class="clear"></div>

		</div><!-- #major-publishing-actions -->

	</div><!-- #submitpost -->
<?php }

/**
 * Forum attribute meta box.  This handles the forum type, parent, and menu order.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $post
 * @return void
 */
function mb_forum_attributes_meta_box( $post ) {

	wp_nonce_field( '_mb_forum_attr_nonce', 'mb_forum_attr_nonce' );

	$forum_types = mb_get_forum_type_objects(); ?>

	<p>
		<label for="mb_forum_type">
			<strong><?php _e( 'Forum Type:', 'message-board' ); ?></strong>
		</label>
	</p>
	<p>
		<?php mb_dropdown_forum_type( array( 'selected' => mb_get_forum_type( $post->ID ) ) ); ?>
	</p>

	<p>
		<label for="mb_parent_forum">
			<strong><?php _e( 'Parent Forum:', 'message-board' ); ?></strong>
		</label>
	</p>
	<p>
		<?php mb_dropdown_forums(
			array(
				'name'              => 'parent_id',
				'id'                => 'mb_parent_forum',
				'show_option_none'  => __( '(no parent)', 'message-board' ),
				'option_none_value' => 0,
				'selected'          => $post->post_parent
			)
		); ?>
	</p>

	<p>
		<label for="mb_menu_order"><strong><?php _e( 'Order:', 'message-board' ); ?></strong></label>
	</p>
	<p>
		<input type="number" name="menu_order" id="mb_menu_order" value="<?php echo esc_attr( $post->menu_order ); ?>" />
	</p><?php
}

/**
 * Topic attributes meta box.  This handles whether the topic is sticky and the parent forum. It also 
 * has the hidden input to save the proper `menu_order` field for the post.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $post
 * @return void
 */
function mb_topic_attributes_meta_box( $post ) {

	wp_nonce_field( '_mb_topic_attr_nonce', 'mb_topic_attr_nonce' );

	$topic_type_object = get_post_type_object( mb_get_topic_post_type() ); ?>

	<p>
		<label for="mb_topic_type">
			<strong><?php _e( 'Topic Type:', 'message-board' ); ?></strong>
		</label>
	</p>
	<p>
		<?php mb_dropdown_topic_type( array( 'selected' => mb_get_topic_type( $post->ID ) ) ); ?>
	</p>

	<p>
		<label for="mb_parent_forum">
			<strong><?php echo $topic_type_object->labels->parent_item_colon; ?></strong>
		</label>
	</p>
	<p>
		<?php mb_dropdown_forums(
			array(
				'child_type' => mb_get_topic_post_type(),
				'name'       => 'parent_id',
				'id'         => 'mb_parent_forum',
				'selected'   => !empty( $post->post_parent ) ? $post->post_parent : mb_get_default_forum_id()
			)
		); ?>
	</p>

	<?php $order = 0 != $post->ID ? $post->post_date : current_time( 'mysql' ); ?>
	<input type="hidden" name="menu_order" value="<?php echo esc_attr( mysql2date( 'U', $order ) ); ?>" />
<?php }

/**
 * Reply info meta box.  Displays relevant information about the reply.  This box doesn't have editable 
 * content in it.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $post
 * @return void
 */
function mb_reply_info_meta_box( $post ) {

	$reply_id = mb_get_reply_id( $post->ID );
	$topic_id = mb_get_reply_topic_id( $reply_id );
	$forum_id = mb_get_reply_forum_id( $reply_id );

	$topic_object = get_post_type_object( mb_get_topic_post_type() );
	$forum_object = get_post_type_object( mb_get_forum_post_type() ); ?>

	<p><?php printf( __( 'Topic: %s', 'message-board' ), mb_get_topic_link( $topic_id ) ); ?></p>
	<p><?php printf( __( 'Forum: %s', 'message-board' ), mb_get_forum_link( $forum_id ) ); ?></p>
<?php }

/**
 * Forum activity dashboard widget.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function mb_dashboard_activity_meta_box() {

	$statuses   = mb_get_published_post_statuses();
	$post_types = array( mb_get_forum_post_type(), mb_get_topic_post_type(), mb_get_reply_post_type() );
	?>

	<div class="count-block">

		<span class="dashicons dashicons-format-chat"></span>
		<ul>

			<?php foreach ( $post_types as $type ) {

				$count     = 0;
				$num_posts = wp_count_posts( $type );

				foreach ( (array) $num_posts as $status => $num ) {

					if ( in_array( $status, $statuses ) )
						$count = $count + absint( $num );
				}

				$post_type_object = get_post_type_object( $type );

				$text = translate_nooped_plural( $post_type_object->labels->mb_dashboard_count, $count, 'message-board' );
				$text = sprintf( $text, number_format_i18n( $count ) );
				$class = sanitize_html_class( 'mb-' . mb_translate_post_type( $type ) . '-count' );

				if ( $post_type_object && current_user_can( $post_type_object->cap->edit_posts ) )
					printf( '<li><a class="%s" href="%s">%s</a></li>', $class, add_query_arg( 'post_type', $type, admin_url( 'edit.php' ) ), $text );

				else
					printf( '<li><span class="%s">%s</span></li>', $class, $text );
			} ?>

		</ul>
		</div><!-- .count-block -->

		<div id="mb-activity-widget">

		<?php $args = array(
			'posts_per_page' => 7,
			'post_type'      => $post_types,
			'post_status'    => $statuses,
			'order'          => 'DESC',
			'orderby'        => 'date',
			'no_found_rows'  => true,
			'cache_results'  => false,
			'perm'           => 'readable',

		);

		$loop = new WP_Query( $args );

		if ( $loop->have_posts() ) : ?>

			<div class="activity-block">

				<h4><?php _e( 'Recently Published', 'message-board' ); ?></h4>
				<ul>

				<?php
				$today    = date( 'Y-m-d', current_time( 'timestamp' ) );
				$tomorrow = date( 'Y-m-d', strtotime( '+1 day', current_time( 'timestamp' ) ) );
				?>

				<?php while ( $loop->have_posts() ) : ?>

					<?php $loop->the_post();

					$time = get_the_time( 'U' );

					if ( date( 'Y-m-d', $time ) == $today ) {
						$relative = __( 'Today' );
					} else {
						/* translators: date and time format for recent posts on the dashboard, see http://php.net/date */
						$relative = date_i18n( __( 'M jS' ), $time );
					}

					$url    = current_user_can( 'edit_post', get_the_ID() ) ? get_edit_post_link() : get_permalink();
					$format = __( '<span>%1$s, %2$s</span> %3$s', 'message-board' );
					$link   = sprintf( '<a href="%s">%s</a>', $url, get_the_title() );

					printf( "<li>{$format}</li>", $relative, get_the_time(), $link );

				endwhile; ?>

				</ul>

			</div><!-- .activity-block -->

		<?php endif; ?>

		<?php wp_reset_postdata(); ?>
	</div>
<?php }
