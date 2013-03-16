<?php
/**
 * WooCommerce Product Builder Review Price Template
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.9
 */
global $wcpb;
$arr_settings		= $wcpb->get_settings();
$arr_session_data	= $wcpb->get_session_data();
?>
<div class="wcpb-config-review-price">
	<div class="wcpb-total"><?php echo number_format( $wcpb->product_price(), 2, '.', ',' ) . ' ' . $arr_settings['currency_symbol']; ?></div>
	<div class="wcpb-tax-info"><?php echo nl2br( $arr_settings['tax_information'] ); ?></div>
	<form action="<?php echo get_permalink( get_the_ID() ); ?>" method="post">
		<button class="wcpb-addtocart" name="action" value="add_to_cart"><?php _e( 'add to cart', 'wcpb' ) ?></button>
	</form>
</div>
<?php
?>