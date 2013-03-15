<?php
/**
 * WooCommerce Product Builder Main Backend Settings Page
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.8
 */
global $wcpb_backend;

/* GET SETTINGS */
$arr_postdata = ! empty( $_POST['postdata'] ) ? $_POST['postdata'] : null;
$arr_settings = $wcpb_backend->get_settings();
$arr_settings_update = $arr_settings;
$arr_product_cats = get_categories( 'taxonomy=product_cat&hide_empty=0&hierarchical=1' );
$arr_optioncats = isset( $arr_settings['product_cat'] ) ? get_categories( 'taxonomy=product_cat&hide_empty=0&hierarchical=1&child_of=' . $arr_settings['product_cat'] ) : false;

/* UPDATE SETTINGS */
if ( ! empty( $arr_postdata ) ) {

	// get option categories
	$int_product_cat = isset( $arr_postdata['product_cat'] ) ? $arr_postdata['product_cat'] : $arr_settings['product_cat'];
	$arr_optioncats = get_categories( 'taxonomy=product_cat&hide_empty=0&hierarchical=1&child_of=' . $int_product_cat );

	// set new settings in update array
	foreach ( $arr_postdata as $key => $value ) {

		if ( false !== strpos( $key, 'optioncat_amount_' ) ) {
			$arr_settings_update['optioncat_amounts'][str_replace( 'optioncat_amount_', '', $key )] = intval( $value );
		}
		else if ( false !== strpos( $key, 'optioncat_title_' ) ) {
			$arr_settings_update['optioncat_titles'][str_replace( 'optioncat_title_', '', $key)] = strval( $value );
		}
		else if ( $key == 'optioncat_base_slug' ) {
			$arr_settings_update['optioncat_base_slug'] = $arr_postdata['optioncat_base_slug'];
			$arr_settings_update['optioncat_amounts'][$arr_postdata['optioncat_base_slug']] = 1;
		}
		else if ( $key == 'custom_product_slug' ) {
			$arr_settings_update[$key] = str_replace( array( ",", ".", "_", " ", "@" ), array( "", "", "-", "-", ""), $value);
		}
		else {
			$arr_settings_update[$key] = $value;
		}

	}

	// if base category is set but not posted, remove base category
	if ( ! array_key_exists( 'optioncat_base_slug', $arr_postdata ) )
		unset( $arr_settings_update['optioncat_base_slug'] );

	// if not all option categories we're custom-titled, set their original titles
	foreach ( $arr_optioncats as $obj_optioncat )
		if ( empty( $arr_settings_update['optioncat_titles'][$obj_optioncat->slug] ) )
			$arr_settings_update['optioncat_titles'][$obj_optioncat->slug] = $obj_optioncat->name;

	// if the settings have changed, update database
	if ( $arr_settings != $arr_settings_update ) {
		$wcpb_backend->settings_update_db( $arr_settings_update );
		$wcpb_backend->settings_refresh();
	}
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

			<!-- Shop / Product Information -->
			<fieldset class="fieldset-review-info">
				<hgroup class="wcpb-admin-fieldset-header">
					<h3>Shop / Product Information</h3>
					<p>Set misc. shop and product information.</p>
				</hgroup>
				<table class="wcpb-admin-form-elem">
					<tr>
						<td><label for="wcpb-currency-symbol"><?php _e( 'Currency Symbol (e.g. "€")', 'wcpb' ); ?></label></td>
						<td><input type="text" id="wcpb-currency-symbol" name="postdata[currency_symbol]" value="<?php echo isset( $arr_settings['currency_symbol'] ) ? $arr_settings['currency_symbol'] : '' ?>"></td>
					</tr>
					<tr>
						<td><label for="wcpb-tax-information"><?php _e( 'Tax Information (e.g. "incl. VAT, excl. Shipping")', 'wcpb' ); ?></label></td>
						<td><input type="text" id="wcpb-tax-information" name="postdata[tax_information]" value="<?php echo isset( $arr_settings['tax_information'] ) ? $arr_settings['tax_information'] : '' ?>"></td>
					</tr>
					<tr>
						<td><label for="wcpb-custom-product-name"><?php _e( 'Custom Product Name (e.g. "Your custom product")', 'wcpb' ); ?></label></td>
						<td><input type="text" id="wcpb-custom-product-name" name="postdata[custom_product_name]" value="<?php echo isset( $arr_settings['custom_product_name'] ) ? $arr_settings['custom_product_name'] : '' ?>"></td>
					</tr>
					<tr>
						<td><label for="wcpb-custom-product-slug"><?php _e( 'Custom Product Slug (e.g. "custom-product")', 'wcpb' ); ?></label></td>
						<td><input type="text" id="wcpb-custom-product-slug" name="postdata[custom_product_slug]" value="<?php echo isset( $arr_settings['custom_product_slug'] ) ? $arr_settings['custom_product_slug'] : '' ?>"></td>
					</tr>
				</table>
			</fieldset>
			
			<!-- Product Category -->
			<fieldset id="fieldset-product-cat">
				<hgroup class="wcpb-admin-fieldset-header">
					<h3><?php _e( 'Define Parent Category', 'wcpb' ); ?></h3>
					<p><?php _e( 'The parent category that holds the option categories.', $domain = 'default' ) ?></p>
				</hgroup>
				<table class="wcpb-admin-form-elem">
					<tr>
						<td><label for="product-cat"><?php _e( 'Select Parent Category', 'wcpb' ); ?></label></td>
						<td>
							<select name="postdata[product_cat]" id="product-cat">
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
			
			<!-- Max Option Amount -->
			<fieldset id="fieldset-option-amount">
				<hgroup class="wcpb-admin-fieldset-header">
					<h3><?php _e( 'Define Option Amount', 'wcpb' ); ?></h3>
					<p><?php _e( 'Maximum amount of options a customer can choose for the custom product.', 'wcpb' ); ?></p>
				</hgroup>
				<table class="wcpb-admin-form-elem">
					<tr>
						<td><label for="option_amount_total"><?php _e( 'Max. Option Amount', 'wcpb' ); ?></label></td>
						<td><input type="text" id="option_amount_total" name="postdata[optioncat_amount_total]" value="<?php echo $arr_optioncat_amounts['total'] > 1 ? $arr_optioncat_amounts['total'] : 1; ?>"></td>
					</tr>
				</table>
			</fieldset>
			
			<!-- Max Option Amount by Category -->
			<fieldset id="fieldset-optioncat-settings" class="last">
				<hgroup class="wcpb-admin-fieldset-header">
					<h3><?php _e( 'Define Category Amount and Custom Titles', 'wcpb' ); ?></h3>
					<p><?php _e( 'Maximum amount of options a customer can choose from each category and custom titles (optional).', 'wcpb' ); ?></p>
				</hgroup>
				<?php if ( isset( $arr_settings['product_cat'] ) ) : ?>
				
					<?php if ( count( $arr_optioncats ) > 0 ) : ?>
					
					<table class="wcpb-admin-form-elem">
						<tr>
							<th></th>
							<th><?php _e( 'Amount', 'wcpb' ); ?></th>
							<th><?php _e( 'Custom Title', 'wcpb' ); ?></th>
							<th><?php _e( 'Is Base', 'wcpb' ); ?></th>
						</tr>
					<?php for ( $i = 0; $i < count( $arr_optioncats ); $i++ ) : ?>
						<tr>
							<td><label for="<?php echo 'option-amount-' . $arr_optioncats[$i]->slug; ?>"><?php echo $arr_optioncats[$i]->name ?></label></td>
							<td><input type="text" id="<?php echo 'option-amount-' . $arr_optioncats[$i]->slug; ?>" name="postdata[optioncat_amount_<?php echo $arr_optioncats[$i]->slug; ?>]" value="<?php echo $arr_optioncat_amounts[$arr_optioncats[$i]->slug] > 0 && $arr_settings['optioncat_base_slug'] != $arr_optioncats[$i]->slug ? $arr_optioncat_amounts[$arr_optioncats[$i]->slug] : ( $arr_settings['optioncat_base_slug'] == $arr_optioncats[$i]->slug ? 1 : 0 ); ?>"<?php echo $arr_settings['optioncat_base_slug'] == $arr_optioncats[$i]->slug ? ' readonly="readonly"' : ''; ?>></td>
							<td><input type="text" id="<?php echo 'optioncat-title-' . $arr_optioncats[$i]->slug; ?>" name="postdata[optioncat_title_<?php echo $arr_optioncats[$i]->slug; ?>]" value="<?php echo $arr_optioncat_titles[$arr_optioncats[$i]->slug] != '' ? $arr_optioncat_titles[$arr_optioncats[$i]->slug] : ''; ?>"></td>
							<?php if ( $i == 0 ) : ?>
							<td><input type="checkbox" id="<?php echo 'optioncat-base-' . $arr_optioncats[$i]->slug; ?>" name="postdata[optioncat_base_slug]" value="<?php echo $arr_optioncats[$i]->slug; ?>"<?php echo $arr_settings['optioncat_base_slug'] == $arr_optioncats[$i]->slug ? ' checked="checked"' : ''; ?>></td>
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
				<input type="submit" name="settings_submit" value="<?php _e( 'Save Settings', 'wcpb' ); ?>">
			</div>
	
		</form>
	</div>
	
</div>