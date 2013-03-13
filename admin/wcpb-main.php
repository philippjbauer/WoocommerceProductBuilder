<?php
/**
 * WooCommerce Product Builder Main Backend Settings Page
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.6
 */
global $wcpb_backend;

/* GET SETTINGS */
$arr_settings = $wcpb_backend->get_settings();
$arr_settings_update = $arr_settings;
$arr_product_cats = get_categories( 'taxonomy=product_cat&hide_empty=0&hierarchical=1' );

/* UPDATE SETTINGS */
// Set product cat
if ( isset( $_POST['product_cat'] ) )
	$arr_settings_update['product_cat'] = $_POST['product_cat'];

// Set optioncat(egory) amounts
if ( $wcpb_backend->fuzzy_key_search( $_POST, 'optioncat_amount_' ) ) {
	$arr_tmp = array();
	foreach ( $_POST as $key => $value ) {
		if ( false !== strpos( $key, 'optioncat_amount_' ) )
			$arr_tmp[str_replace( 'optioncat_amount_', '', $key )] = (int) $value;
	}
	$arr_settings_update['optioncat_amounts'] = $arr_tmp;
}

// Set optioncat(egory) custom titles
$mix_product_cat = isset( $_POST['product_cat'] ) ? $_POST['product_cat'] : ( isset( $arr_settings_update['product_cat'] ) ? $arr_settings_update['product_cat'] : false );
if ( false !== $mix_product_cat )
	$mix_optioncats = get_categories( 'taxonomy=product_cat&hide_empty=0&hierarchical=1&child_of=' . $mix_product_cat );	// get subcategories
if ( $wcpb_backend->fuzzy_key_search( $_POST, 'optioncat_title_' ) && false !== $mix_product_cat ) {
	$arr_tmp = array();
	
	foreach ( $mix_optioncats as $obj_optioncat )
		$arr_tmp[$obj_optioncat->slug] = $obj_optioncat->name;
		
	foreach ( $_POST as $key => $value ) {
		if ( false !== strpos( $key, 'optioncat_title_' ) && $value !== '' )
			$arr_tmp[str_replace( 'optioncat_title_', '', $key )] = $value;
	}
	$arr_settings_update['optioncat_titles'] = $arr_tmp;
}

// Set base optioncat(egory)
if ( isset( $_POST['optioncat_base_slug'] ) ) {
	$arr_settings_update['optioncat_base_slug'] = $_POST['optioncat_base_slug'];
	$arr_settings_update['optioncat_amounts'][$_POST['optioncat_base_slug']] = 1;
}
else if ( $wcpb_backend->fuzzy_key_search( $_POST, 'optioncat_amount_' ) ) {
	$arr_settings_update['optioncat_base_slug'] = null;
}

/* UPDATE DATABSE */
if ( $arr_settings != $arr_settings_update ) {
	$wcpb_backend->settings_update_db( $arr_settings_update );
	$wcpb_backend->settings_refresh();
}

/* GET NEW SETTINGS */
$arr_settings = $arr_settings_update;
$arr_optioncat_amounts = $arr_settings['optioncat_amounts'];
$arr_optioncat_titles = $arr_settings['optioncat_titles'];

?>

<div class="wrap">
	
	<hgroup class="wcpb-admin-header">
		<h2><?php _e( 'Main Settings', 'wcpb' ); ?></h2>
		<p><?php _e( 'WooCommerce Product Builder ' . get_option( 'wcpb_version' ), 'wcpb') ?></p>
	</hgroup>
	
	<div class="wcpb-admin-form-wrap">
		<form class="wcpb-admin-form" method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?page=" . $_GET['page']; ?>">
			
			<fieldset id="fieldset-product-cat">
				<hgroup class="wcpb-admin-fieldset-header">
					<h3><?php _e( 'Define Parent Category', 'wcpb' ); ?></h3>
					<p><?php _e( 'The parent category that holds the option categories.', $domain = 'default' ) ?></p>
				</hgroup>
				<table class="wcpb-admin-form-elem">
					<tr>
						<td><label for="product-cat"><?php _e( 'Select Parent Category', 'wcpb' ); ?></label></td>
						<td>
							<select name="product_cat" id="product-cat">
							<?php
								foreach ( $arr_product_cats as $obj_product_cat ) :
								if( $obj_product_cat->parent == 0 ) :
							?>
								<option value="<?php echo $obj_product_cat->term_id; ?>"<?php echo $obj_product_cat->term_id == $arr_settings['product_cat'] ? ' selected="selected"' : ''; ?>><?php echo $obj_product_cat->name; ?></option>
							<?php
								endif;
								endforeach;
							?>
							</select>
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset id="fieldset-optioncat-amount-total">
				<hgroup class="wcpb-admin-fieldset-header">
					<h3><?php _e( 'Define Option Amount', 'wcpb' ); ?></h3>
					<p><?php _e( 'Maximum amount of options a customer can choose for the custom product.', 'wcpb' ); ?></p>
				</hgroup>
				<table class="wcpb-admin-form-elem">
					<tr>
						<td><label for="optioncat_amount_total"><?php _e( 'Max. Option Amount', 'wcpb' ); ?></label></td>
						<td><input type="text" id="optioncat_amount_total" name="optioncat_amount_total" value="<?php echo $arr_optioncat_amounts['total'] > 1 ? $arr_optioncat_amounts['total'] : 1; ?>"></td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset id="fieldset-optioncat-settings" class="last">
				<hgroup class="wcpb-admin-fieldset-header">
					<h3><?php _e( 'Define Category Amount and Custom Titles', 'wcpb' ); ?></h3>
					<p><?php _e( 'Maximum amount of options a customer can choose from each category and custom titles (optional).', 'wcpb' ); ?></p>
				</hgroup>
				<?php if ( isset( $arr_settings['product_cat'] ) ) : ?>
				
					<?php if ( count( $mix_optioncats ) > 0 ) : ?>
					
					<table class="wcpb-admin-form-elem">
						<tr>
							<th></th>
							<th><?php _e( 'Amount', 'wcpb' ); ?></th>
							<th><?php _e( 'Custom Title', 'wcpb' ); ?></th>
							<th><?php _e( 'Is Base', 'wcpb' ); ?></th>
						</tr>
					<?php for ( $i = 0; $i < count( $mix_optioncats ); $i++ ) : ?>
						<tr>
							<td><label for="<?php echo 'option-amount-' . $mix_optioncats[$i]->slug; ?>"><?php echo $mix_optioncats[$i]->name ?></label></td>
							<td><input type="text" id="<?php echo 'option-amount-' . $mix_optioncats[$i]->slug; ?>" name="optioncat_amount_<?php echo $mix_optioncats[$i]->slug; ?>" value="<?php echo $arr_optioncat_amounts[$mix_optioncats[$i]->slug] > 0 && $arr_settings['optioncat_base_slug'] != $mix_optioncats[$i]->slug ? $arr_optioncat_amounts[$mix_optioncats[$i]->slug] : ( $arr_settings['optioncat_base_slug'] == $mix_optioncats[$i]->slug ? 1 : 0 ); ?>"<?php echo $arr_settings['optioncat_base_slug'] == $mix_optioncats[$i]->slug ? ' readonly="readonly"' : ''; ?>></td>
							<td><input type="text" id="<?php echo 'optioncat-title-' . $mix_optioncats[$i]->slug; ?>" name="optioncat_title_<?php echo $mix_optioncats[$i]->slug; ?>" value="<?php echo $arr_optioncat_titles[$mix_optioncats[$i]->slug] != '' ? $arr_optioncat_titles[$mix_optioncats[$i]->slug] : ''; ?>"></td>
							<?php if ( $i == 0 ) : ?>
							<td><input type="checkbox" id="<?php echo 'optioncat-base-' . $mix_optioncats[$i]->slug; ?>" name="optioncat_base_slug" value="<?php echo $mix_optioncats[$i]->slug; ?>"<?php echo $arr_settings['optioncat_base_slug'] == $mix_optioncats[$i]->slug ? ' checked="checked"' : ''; ?>></td>
							<?php endif; ?>
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
			
			<div class="wcpb-admin-save-settings">
				<input type="submit" value="<?php _e( 'Save Settings', 'wcpb' ); ?>">
			</div>
	
		</form>
	</div>
	
</div>