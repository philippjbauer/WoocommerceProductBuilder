<?php
/**
 * WooCommerce Product Builder Page Template
 *
 * @author	Philipp Bauer
 * @version	0.1
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
			<?php do_action( 'wcpb_include_template', 'templates/wcpb-category-tablist.php' ); ?>
		</nav>
		<section class="wcpb-category-options">
			<?php do_action( 'wcpb_include_template', 'templates/wcpb-category-options.php' ); ?>
		</section>
		<div class="clearfix"></div>
	</div>
	
	<div id="wcpb-config-review">
		<ul class="wcpb-config-review-attr">
			<?php // do_action( 'wcpb_include_template', 'templates/wcpb-review-attribute.php' ); ?>
		</ul>
		<div class="clearfix"></div>
		<ul class="wcpb-config-review-thumbs">
			<?php do_action( 'wcpb_include_template', 'templates/wcpb-review-thumblist.php' ); ?>
		</ul>
		<div class="wcpb-config-review-list">
			<?php do_action( 'wcpb_include_template', 'templates/wcpb-review-textlist.php' ); ?>
			<a href="<?php echo get_permalink( get_the_id() ); ?>&action=show-details"><?php _e( 'show details', 'wcpb' ); ?></a> |
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