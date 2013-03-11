<?php
/**
 * WooCommerce Product Builder Tablist Template
 *
 * @author	Philipp Bauer
 * @version	0.1
 */
global $wcpb;
$unlocked = count( $wcpb->arr_session_data['current_product'] ) > 0 ? true : false;
?>

<ul>
<?php
$i = 1;
foreach ( $wcpb->arr_optioncat_titles as $key => $value ) :
?>
	<li class="<?php echo $i == 1 ? 'first' : ( $i == count( $wcpb->arr_optioncat_titles ) ? 'last' : '' ); echo ( ! isset( $_GET['optioncat'] ) && $i == 1 || isset( $_GET['optioncat'] ) && $_GET['optioncat'] == $key ) ? ' active' : ''; ?>"><a href="<?php echo $unlocked ? get_permalink( get_the_ID() ) . '&optioncat=' . $key : '#'; ?>"><?php echo $value; ?></a></li>
<?php
$i++;
endforeach;
?>
<div class="clearfix"></div>
</ul>