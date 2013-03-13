<?php
/**
 * WooCommerce Product Builder Tablist Template
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.6
 */
global $wcpb;
$arr_session_data = $wcpb->get_session_data();
$unlocked = count( $arr_session_data['current_product'] ) > 0 ? true : false;
?>

<nav class="wcpb-category-tablist">
	<ul>
	<?php $i = 1; foreach ( $wcpb->get_optioncat_titles() as $key => $value ) : ?>
		<li class="<?php echo $i == 1 ? 'first' : ( $i == count( $wcpb->get_optioncat_titles() ) ? 'last' : '' ); echo ( ! isset( $_GET['optioncat'] ) && $i == 1 || isset( $_GET['optioncat'] ) && count( $arr_session_data['options'] ) == 0 && $i == 1 ) ? ' active' : ( isset( $_GET['optioncat'] ) && count( $arr_session_data['options'] ) > 0 && $_GET['optioncat'] == $key ? ' active' : '' ); ?>"><a href="<?php echo $unlocked ? get_permalink( get_the_ID() ) . '&optioncat=' . $key : '#'; ?>"><?php echo $value; ?></a></li>
	<?php 	$i++; endforeach; ?>
	<div class="clearfix"></div>
	</ul>
</nav>