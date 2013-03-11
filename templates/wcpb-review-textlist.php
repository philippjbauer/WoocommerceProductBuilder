<?php
/**
 * WooCommerce Product Builder Review Textlist Template
 *
 * @author	Philipp Bauer
 * @version	0.1
 */
global $wcpb;
reset( $wcpb->arr_session_data['current_product'] );
?>

<?php if ( ! empty( $wcpb->arr_session_data['current_product'] ) ) : ?>
<ul>
<?php while ( $arr_optioncat = current( $wcpb->arr_session_data['current_product'] ) ) : ?>
	<li>
		<?php echo $wcpb->arr_optioncat_titles[ key( $wcpb->arr_session_data['current_product'] ) ]; ?>
		<ul>
		<?php foreach ( $arr_optioncat as $int_option_id ) : ?>
			<li>
				<?php echo $wcpb->arr_session_data['options'][$int_option_id]['the_title']; ?>
			</li>
		<?php endforeach; ?>
		</ul>
	</li>
<?php next( $wcpb->arr_session_data['current_product'] ); endwhile; ?>
</ul>
<?php endif; ?>