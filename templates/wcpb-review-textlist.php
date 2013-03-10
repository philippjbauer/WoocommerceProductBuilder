<?php
/**
 * WooCommerce Product Builder Review Textlist Template
 *
 * @author	Philipp Bauer
 * @version	0.1
 */
global $wcpb;
?>

<ul>
	<?php while ( $arr_optioncat = current( $wcpb->arr_session_data['current_product'] ) ) : ?>
	<?php var_dump($wcpb->arr_session_data['current_product']) ?>
	<li>
		<?php echo $wcpb->arr_optioncat_titles[key( $wcpb->arr_session_data['current_product'] )]; ?>
		<ul>
		<?php foreach ( $arr_optioncat as $key => $value ) : ?>
			<li>
				<?php
					$obj_option_postdata = get_post( $value );
					echo $obj_option_postdata->post_title;
				?>
			</li>
		<?php endforeach; ?>
		</ul>
	</li>
	<?php next( $wcpb->arr_session_data['current_product'] ); ?>
	<?php endwhile; ?>
</ul>