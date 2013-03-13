<?php
/**
 * WooCommerce Product Builder Page Template
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.6
 */

/* HANDLE POST/GET DATA */
$mix_request = false;
if ( isset( $_REQUEST ) )
	$mix_request = $_REQUEST;
do_action( 'wcpb_before_product_builder', $mix_request );
?>

<div id="wcpb-config-wrapper">
	
	<div id="wcpb-config-selection">
		<?php do_action( 'wcpb_include_template', 'templates/wcpb-category-tablist.php' ); ?>
		<?php do_action( 'wcpb_include_template', 'templates/wcpb-category-options.php' ); ?>
		<div class="clearfix"></div>
	</div>
	
	<div id="wcpb-config-review">
		<!-- Attribute Modification -->
		<?php do_action( 'wcpb_include_template', 'templates/wcpb-review-attribute.php' ); ?>
		<!-- Option Lists -->
		<?php do_action( 'wcpb_include_template', 'templates/wcpb-review-thumblist.php' ); ?>		
		<?php do_action( 'wcpb_include_template', 'templates/wcpb-review-textlist.php' ); ?>
		<!-- Price -->
		<?php do_action( 'wcpb_include_template', 'templates/wcpb-review-price.php' ); ?>
	</div>
	
	<div class="clearfix"></div>
	
</div>