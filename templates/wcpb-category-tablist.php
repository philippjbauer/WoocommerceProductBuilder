<?php
/**
 * WooCommerce Product Builder Tablist Template
 */
global $wcpb;
$unlocked = count( $wcpb->arr_session_data['current_product'] ) > 0 ? true : false;
?>

<ul>
<?php
$i = 1;
foreach ( $wcpb->arr_optioncat_titles as $key => $value ) :
?>
	<li class="<?php echo $i == 1 ? 'first' : ( $i == count( $wcpb->arr_optioncat_titles ) ? 'last' : '' ); echo isset( $_GET['optioncat'] ) && $_GET['optioncat'] == $key ? ' active' : ''; ?>"><a href="<?php echo $unlocked ? '?optioncat=' . $key : '#'; ?>"><?php echo $value; ?></a></li>
<?php
$i++;
endforeach;
?>
</ul>