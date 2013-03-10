<?php
/**
 * WooCommerce Product Builder Review Thumblist Template
 *
 * @author	Philipp Bauer
 * @version	0.1
 */
global $wcpb;
$str_optioncat_get = ! empty( $_GET['optioncat'] ) ? '&optioncat=' . $_GET['optioncat'] : '';
?>

<ul>
	<?php foreach ( $wcpb->arr_session_data['current_product'] as $arr_optioncat ) : ?>
		<?php foreach ( $arr_optioncat as $key => $value ) : ?>
			<?php
				$obj_option_postdata = get_post( $value );
				$int_option_thumb_id = get_post_meta( $value, $key = '_thumbnail_id', true );
				$str_option_thumb_guid = plugins_url( 'assets/img/thumb-placeholder.png', dirname( __FILE__ ) );
				if ( ! empty( $int_option_thumb_id ) ) {
					$obj_option_thumbdata = get_post( $int_option_thumb_id );
					$str_option_thumb_guid = $obj_option_thumbdata->guid;
				}
			?>
			<li style="background-image: url('<?php echo $str_option_thumb_guid; ?>');">
				<a href="<?php echo get_permalink( get_the_ID() ) . $str_optioncat_get . "&action=remove&optionid=" . $value; ?>"><?php _e( 'remove ' . $obj_option_postdata->post_title, 'wcpb' ); ?></a>
			</li>
		<?php endforeach; ?>
	<?php endforeach; ?>
</ul>