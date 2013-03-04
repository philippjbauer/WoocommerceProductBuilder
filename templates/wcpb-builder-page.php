<?php
/**
 * WooCommerce Product Builder Page Template
 */
$mix_request = false;
if ( isset( $_REQUEST ) )
	$mix_request = $_REQUEST;
do_action( 'wcpb_before_product_builder', $mix_request );
var_dump( $mix_request );
?>

<div id="wcpb-config-wrapper">
	
	<div id="wcpb-config-selection">
		<nav class="wcpb-category-tablist">
			<?php do_action( 'wcpb_category_tablist' ); ?>
		</nav>
		<section class="wcpb-category-options">
			<?php do_action( 'wcpb_category_options' ); ?>
		</section>
	</div>
	
	<div id="wcpb-config-review">
		<ul class="wcpb-config-review-attr">
			<?php do_action( 'wcpb_product_attribute_selection' ); ?>
		</ul>
		<div class="clearfix"></div>
		<ul class="wcpb-config-review-thumbs">
			<?php do_action( 'wcpb_review_thumblist' ); ?>
		</ul>
		<div class="wcpb-config-review-list">
			<!-- <a href="?action=show-details"><?php _e( 'show details', 'wcpb' ); ?></a> | -->
			<a href="<?php echo get_permalink( get_the_id() ); ?>&action=restart"><?php _e( 'restart', 'wcpb' ); ?></a>
		</div>
		<div class="clearfix"></div>
		<div class="wcpb-config-review-price">
			<div class="total"><?php do_action( 'wcpb_show_product_total' ) ?> €</div>
			<div class="tax"><?php _e( 'incl. VAT, excl. shipping', 'wcpb' ); ?></div>
			<!--div class="breakdown">12,00 € / 1,0 l = 12,00 € / Liter</div-->
			<?php do_action( 'wcpb_show_add_to_cart_button' ); ?>
		</div>
	</div>
	
	<div class="clearfix"></div>
	
</div>