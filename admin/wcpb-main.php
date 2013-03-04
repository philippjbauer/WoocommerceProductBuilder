<?php
global $wcpb;

function fuzzy_key_search( $haystack, $needle ) {
	foreach ( $haystack as $key => $value ) {
		if ( false !== strpos( $key, $needle ) ) {
			return true;
			exit;
		}
	}
	return false;
}

// Get product categories
$arr_product_cats = get_categories( 'taxonomy=product_cat&hide_empty=0&hierarchical=1' );


// Update DB active product cat
if ( isset( $_POST['product_cat'] ) ) {
	update_option( 'wcpb_product_cat', $_POST['product_cat'] );
	$wcpb->int_product_cat = $_POST['product_cat'];
}

// Get subcategories
$arr_optioncats = get_categories( 'taxonomy=product_cat&hide_empty=0&hierarchical=1&child_of=' . $wcpb->int_product_cat );

// Update DB product option amounts
if ( fuzzy_key_search( $_POST, 'optioncat_amount_' ) ) {
	$arr_tmp = array();
	foreach ( $_POST as $key => $value ) {
		if ( false !== strpos( $key, 'optioncat_amount_' ) )
			$arr_tmp[str_replace( 'optioncat_amount_', '', $key )] = $value;
	}
	update_option( 'wcpb_optioncat_amounts', serialize( $arr_tmp ) );
	$wcpb->arr_optioncat_amounts = $arr_tmp;
}

// Update DB product subcat custom titles
if ( fuzzy_key_search( $_POST, 'optioncat_title_' ) ) {
	$arr_tmp = array();
	
	foreach ( $arr_optioncats as $obj_optioncat )
		$arr_tmp[$obj_optioncat->slug] = $obj_optioncat->name;
		
	foreach ( $_POST as $key => $value ) {
		if ( false !== strpos( $key, 'optioncat_title_' ) && $value !== '' )
			$arr_tmp[str_replace( 'optioncat_title_', '', $key )] = $value;
	}
	update_option( 'wcpb_optioncat_titles', serialize( $arr_tmp ) );
	$wcpb->arr_optioncat_titles = $arr_tmp;
}

?>

<div class="wrap">

	<h2><?php _e( 'Main Settings', 'wcpb' ); ?></h2>
	
	<div class="wcpb-admin-form-wrap">
		<form class="wcpb-admin-form" method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=" . $_GET['page']; ?>">
			
			<fieldset>
				<h3<?php echo ! $wcpb->int_product_cat ? ' class="wcpb-error"' : ''; ?>><?php _e( 'Select Product Builder Parent Category', 'wcpb' ); ?></h3>
				<table class="wcpb-admin-form-elem">
					<tr>
						<td><label for="product-cat"><?php _e( 'Select Parent Category', 'wcpb' ); ?></label></td>
						<td>
							<select name="product_cat" id="product-cat">
							<?php
								foreach ( $arr_product_cats as $obj_product_cat ) :
								if( $obj_product_cat->parent == 0 ) :
							?>
								<option value="<?php echo $obj_product_cat->term_id; ?>"<?php echo $obj_product_cat->term_id == $wcpb->int_product_cat ? ' selected="selected"' : ''; ?>><?php echo $obj_product_cat->name; ?></option>
							<?php
								endif;
								endforeach;
							?>
							</select>
						</td>
					</tr>
				</table>
			</fieldset>
			
			</fieldset>
				<h3><?php _e( 'Define maximum option amount', 'wcpb' ); ?></h3>
				<p><?php _e( 'Set a maximum amount of options a customer can choose for the custom product.', 'wcpb' ); ?></p>
				<table class="wcpb-admin-form-elem">
					<tr>
						<td><label for="optioncat_amount_total"><?php _e( 'Max. Option Amount', 'wcpb' ); ?></label></td>
						<td><input type="text" id="optioncat_amount_total" name="optioncat_amount_total" value="<?php echo $wcpb->arr_optioncat_amounts['total'] > 0 ? $wcpb->arr_optioncat_amounts['total'] : 0; ?>"></td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<h3><?php _e( 'Define maximum option amount per subcategory and custom titles', 'wcpb' ); ?></h3>
				<p><?php _e( 'Set a maximum amount of options a customer can choose from each category and define custom titles (optional). [Amount: 0 = unlimited]', 'wcpb' ); ?></p>
				<?php if ( isset( $wcpb->int_product_cat ) ) : ?>
				
					<?php if ( count( $arr_optioncats ) > 0 ) : ?>
					
					<table class="wcpb-admin-form-elem">
						<tr>
							<th></th>
							<th><?php _e( 'Amount', 'wcpb' ); ?></th>
							<th><?php _e( 'Custom Title', 'wcpb' ); ?></th>
						</tr>
					<?php for ( $i = 0; $i < count( $arr_optioncats ); $i++ ) : ?>
						<tr>
							<td><label for="<?php echo 'option-amount-' . $arr_optioncats[$i]->slug; ?>"><?php echo $arr_optioncats[$i]->name ?></label></td>
							<td><input type="text" id="<?php echo 'option-amount-' . $arr_optioncats[$i]->slug; ?>" name="optioncat_amount_<?php echo $arr_optioncats[$i]->slug; ?>" value="<?php echo $wcpb->arr_optioncat_amounts[$arr_optioncats[$i]->slug] > 0 ? $wcpb->arr_optioncat_amounts[$arr_optioncats[$i]->slug] : 0; ?>"></td>
							<td><input type="text" id="<?php echo 'optioncat-title-' . $arr_optioncats[$i]->slug; ?>" name="optioncat_title_<?php echo $arr_optioncats[$i]->slug; ?>" value="<?php echo $wcpb->arr_optioncat_titles[$arr_optioncats[$i]->slug] != '' ? $wcpb->arr_optioncat_titles[$arr_optioncats[$i]->slug] : ''; ?>"></td>
						</tr>
					<?php endfor; ?>
					</table>
					
					<?php else: ?>
					
					<p><?php _e( 'No subcategories present, please create some!', 'wcpb' ); ?> <a href="edit-tags.php?taxonomy=product_cat&post_type=product"><?php _e( 'Click here to create subcategories.', 'wcpb' ); ?></a></p>
					
					<?php endif; ?>
				
				<?php else : ?>
				
				<p><?php _e( 'No parent category selected!', 'wcpb' ); ?></p>
				
				<?php endif; ?>
			</fieldset>
			
			<div class="wcpb-admin-form-elem">
				<input type="submit" value="<?php _e( 'Save Settings', 'wcpb' ); ?>">
			</div>
	
		</form>
	</div>
	
</div>