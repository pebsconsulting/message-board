<?php
/**
 * This template part outputs the edit page content.
 */
?>

<header class="mb-page-header">
	<h1 class="mb-page-title"><?php mb_edit_page_title(); ?></h1>
</header><!-- .mb-page-header -->

<?php if ( mb_is_forum_edit() ) : // If viewing the forum edit page. ?>

	<?php mb_get_template_part( 'form-forum', 'edit' ); ?>

<?php elseif ( mb_is_topic_edit() ) : // If viewing the topic edit page. ?>

	<?php mb_get_template_part( 'form-topic', 'edit' ); ?>

<?php elseif ( mb_is_reply_edit() ) : // If viewing the reply edit page. ?>

	<?php mb_get_template_part( 'form-reply', 'edit' ); ?>

<?php elseif ( mb_is_user_edit() ) : // If viewing the user edit page. ?>

	<?php mb_get_template_part( 'form-user', 'edit' ); ?>

<?php endif; ?>