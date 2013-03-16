<?php
/**
 * WooCommerce Product Builder Show Message Template
 *
 * Shows messages in the frontend.
 *
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.9
 */
?>
<?php if ( ! empty( $args['messages'] ) ) : ?>
<?php foreach ($args['messages'] as $key => $value) : ?>
<div class="wcpb-message"><?php echo $value; ?></div>
<?php endforeach; ?>
<?php endif; ?>

<?php if ( ! empty( $args['errors'] ) ) : ?>
<?php foreach ($args['errors'] as $key => $value) : ?>
<div class="wcpb-message error"><?php echo $value; ?></div>
<?php endforeach; ?>
<?php endif; ?>