<?php
/**
 * WooCommerce Product Builder Review Thumblist Template
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.9
 */
global $wcpb;
$arr_session_data = $wcpb->get_session_data();
$arr_optioncat_amounts = $wcpb->get_optioncat_amounts();
$int_counter = $arr_optioncat_amounts['total'];
$str_optioncat_get = ! empty( $_GET['optioncat'] ) ? '&optioncat=' . $_GET['optioncat'] : '';
?>

<div class="wcpb-config-review-thumbs">
<ul>
<?php
if ( ! empty( $arr_session_data['current_product'] ) ) :
foreach ( $arr_session_data['current_product'] as $arr_optioncat ) :
foreach ( $arr_optioncat as $int_option_id ) :
?>
	<li class="has-product" style="background-image: url('<?php echo $wcpb->arr_session_data['options'][$int_option_id]['thumbnail_guid']; ?>');">
		<a href="<?php echo get_permalink( get_the_ID() ) . $str_optioncat_get . "&action=remove_option&optionid=" . $int_option_id; ?>"><div class="wcpb-tooltip"><?php sprintf( _e( 'remove %s', 'wcpb' ), $arr_session_data['options'][$int_option_id]['title'] ); ?></div></a>
	</li>
<?php
$int_counter--;
endforeach;
endforeach;
endif;
?>
<?php while ( $int_counter > 0 ) : ?>
	<li><?php _e( 'empty optionslot', 'wcpb' ) ?></li>
<?php $int_counter--; endwhile; ?>
	<div class="clearfix"></div>
</ul>
</div>