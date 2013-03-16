<?php
/**
 * WooCommerce Product Builder Review Textlist Template
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.9
 */
global $wcpb;
$arr_session_data = $wcpb->get_session_data();
$arr_optioncat_titles = $wcpb->get_optioncat_titles();
$str_optioncat_get = ! empty( $_GET['optioncat'] ) ? '&optioncat=' . $_GET['optioncat'] : '';
?>

<?php if ( ! empty( $arr_session_data['current_product'] ) ) : ?>
<div class="wcpb-config-review-list<?php echo isset( $_GET['action'] ) && $_GET['action'] == 'show_details' ? ' active' : ''; ?>">
<ul>
<?php foreach ( $arr_session_data['current_product'] as $str_optioncat_slug => $arr_optioncat ) : ?>
	<?php if ( count( $arr_optioncat ) > 0 ) : ?>
	<li>
		<?php echo $arr_optioncat_titles[$str_optioncat_slug]; ?>
		<ul>
		<?php foreach ( $arr_optioncat as $int_key => $int_option_id ) : ?>
			<li>
				<?php echo $arr_session_data['options'][$int_option_id]['title']; ?>
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
</div>
<div class="wcpb-config-review-actions">
	<a href="<?php echo get_permalink( get_the_id() ) . $str_optioncat_get . "&action=show_details"; ?>"><?php _e( 'show details', 'wcpb' ); ?></a> |
	<a href="<?php echo get_permalink( get_the_id() ) . "&action=restart"; ?>"><?php _e( 'start over', 'wcpb' ); ?></a>
</div>
<?php endif; ?>