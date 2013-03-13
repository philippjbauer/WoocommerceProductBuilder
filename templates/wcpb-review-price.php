<?php
/**
 * WooCommerce Product Builder Review Price Template
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.6
 */
global $wcpb;
$arr_session_data = $wcpb->get_session_data();
?>
<div class="wcpb-config-review-price">
	<div class="total"><?php echo number_format( $wcpb->product_price(), 2, '.', ',' ); ?> €</div>
	<div class="tax"><?php _e( 'incl. VAT, excl. shipping', 'wcpb' ); ?></div>
	<form action="<?php echo get_permalink( get_the_ID() ); ?>" method="post">
		<button name="action" value="add_to_cart"><?php _e( 'add to cart', 'wcpb' ) ?></button>
	</form>
</div>
<?php
?>