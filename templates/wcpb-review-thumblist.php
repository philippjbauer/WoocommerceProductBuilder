<?php
/**
 * WooCommerce Product Builder Review Thumblist Template
 *
 * @author	Philipp Bauer
 * @version	0.1
 */
global $wcpb;
$str_optioncat_get = ! empty( $_GET['optioncat'] ) ? '&optioncat=' . $_GET['optioncat'] : '';
$int_counter = $wcpb->arr_optioncat_amounts['total'];
?>

<ul>
<?php
if ( ! empty( $wcpb->arr_session_data['current_product'] ) ) :
foreach ( $wcpb->arr_session_data['current_product'] as $arr_optioncat ) :
foreach ( $arr_optioncat as $int_option_id ) :
?>
	<li style="background-image: url('<?php echo $wcpb->arr_session_data['options'][$int_option_id]['thumbnail_guid']; ?>');">
		<a href="<?php echo get_permalink( get_the_ID() ) . $str_optioncat_get . "&action=remove_option&optionid=" . $int_option_id; ?>"><?php _e( 'remove ' . $wcpb->arr_session_data['options'][$int_option_id]['the_title'], 'wcpb' ); ?></a>
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