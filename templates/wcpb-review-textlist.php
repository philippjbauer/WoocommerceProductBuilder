<?php
/**
 * WooCommerce Product Builder Review Textlist Template
 *
 * @author	Philipp Bauer
 * @version	0.1
 */
global $wcpb;
$str_optioncat_get = ! empty( $_GET['optioncat'] ) ? '&optioncat=' . $_GET['optioncat'] : '';
?>

<?php if ( ! empty( $wcpb->arr_session_data['current_product'] ) ) : ?>
<ul>
<?php foreach ( $wcpb->arr_session_data['current_product'] as $str_optioncat_slug => $arr_optioncat ) : ?>
	<?php if ( count( $arr_optioncat ) > 0 ) : ?>
	<li>
		<?php echo $wcpb->arr_optioncat_titles[$str_optioncat_slug]; ?>
		<ul>
		<?php foreach ( $arr_optioncat as $int_key => $int_option_id ) : ?>
			<li>
				<?php echo $wcpb->arr_session_data['options'][$int_option_id]['the_title']; ?>
				<span class="wcpb-align-right">
					<a href="<?php echo get_permalink( get_the_ID() ) . $str_optioncat_get . "&action=remove_option&optionid=" . $int_option_id; ?>"><?php _e( 'remove', 'wcpb' ); ?></a>
				</span>
			</li>
		<?php endforeach; ?>
		</ul>
	</li>
	<?php endif; ?>
<?php endforeach; ?>
</ul>
<?php endif; ?>