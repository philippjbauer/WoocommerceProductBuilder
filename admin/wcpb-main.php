<?php
/**
 * WooCommerce Product Builder Main Backend Settings Page
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.9
 */
global $wcpb_backend;

/* GET POST DATA AND SETTINGS */
$arr_postdata = ! empty( $_POST['postdata'] ) ? $_POST['postdata'] : null;
$arr_settings = $wcpb_backend->get_settings();

/* SET VARIABLES */
$arr_product_cats = get_categories( 'taxonomy=product_cat&hide_empty=0&hierarchical=1' );
$arr_settings['product_cat'] = isset( $arr_settings['product_cat'] ) ? $arr_settings['product_cat'] : $arr_product_cats[0]->term_id;
$arr_settings_update = $arr_settings;
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
		else if ( $key == 'custom_product_slug' ) {
			$arr_settings_update[$key] = str_replace( array( ",", ".", "_", " ", "@" ), array( "", "", "-", "-", ""), $value);
		}
		else if ( $key == 'allow-same-option' ) {
			$arr_settings_update[$key] = true;
		}
		else if ( $key == 'optioncat_base_slug' ) {
			$arr_settings_update['optioncat_base_slug'] = $arr_postdata['optioncat_base_slug'];
			$arr_settings_update['optioncat_amounts'][$arr_postdata['optioncat_base_slug']] = 1;
		}
		else {
			$arr_settings_update[$key] = $value;
		}

	}

	// clean out old optioncat titles and amounts (maybe they've been changed in the backend)
	$arr_compare_titles = array();
	foreach ( $arr_optioncats as $obj_optioncat )
		$arr_compare_titles[] = $obj_optioncat->slug;
	foreach ( $arr_settings_update['optioncat_titles'] as $key => $value )
		if ( false === array_search( $key, $arr_compare_titles ) )
			unset( $arr_settings_update['optioncat_titles'][$key], $arr_settings_update['optioncat_amounts'][$key] );

	// if not all option categories we're custom-titled, set their original titles
	foreach ( $arr_optioncats as $obj_optioncat )
		if ( empty( $arr_settings_update['optioncat_titles'][$obj_optioncat->slug] ) )
			$arr_settings_update['optioncat_titles'][$obj_optioncat->slug] = $obj_optioncat->name;

	// sort option categories
	$arr_settings_update['optioncat_titles'] = $wcpb_backend->optioncat_sort( $arr_settings_update['optioncat_titles'] );

	// if "allow same option" is set but not posted, set false
	if ( ! array_key_exists( 'allow-same-option', $arr_postdata ) )
		$arr_settings_update['allow-same-option'] = false;

	// if base category is set but not posted, remove base category
	if ( ! array_key_exists( 'optioncat_base_slug', $arr_postdata ) )
		unset( $arr_settings_update['optioncat_base_slug'] );

	// if the settings have changed, update database
	//if ( $arr_settings != $arr_settings_update ) {
		$wcpb_backend->settings_delete();						// remove old settings
		$wcpb_backend->settings_update( $arr_settings_update );	// write new settings
		$wcpb_backend->settings_refresh();						// refresh settings within class
	//}
}

/* GET NEW SETTINGS */
$arr_settings = $arr_settings_update;
$arr_optioncat_amounts = $arr_settings['optioncat_amounts'];
$arr_optioncat_titles = $arr_settings['optioncat_titles'];
?>

<div class="wrap">
	
	<!-- <hgroup class="wcpb-admin-header"> -->
	<div id="icon-edit" class="icon32"></div>
	<h2><?php _e( 'Main Settings', 'wcpb' ); ?></h2>
	<!-- <p><?php _e( 'WooCommerce Product Builder ' . get_option( 'wcpb_version' ), 'wcpb' ) ?></p> -->
	<!-- </hgroup> -->
	
	<form action="<?php echo $_SERVER['PHP_SELF'] . "?page=" . $_GET['page']; ?>" method="post" name="wcpb-settings-form">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-1">
				<div id="postbox-container-1" class="postbox-container">
					<div id="normal-sortables" class="meta-box-sortables ui-sortable">

						<div id="wcpb-product_cat" class="wcpb-admin-postbox postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Shop / Product Information', 'wcpb' ); ?></span></h3>
							<div class="inside">
								<p><strong><?php _e( 'Set misc. Shop and Product Information', 'wcpb' ); ?></strong></p>
								<table class="wcpb-admin-form-elem">
									<tr>
										<td><label for="wcpb-currency-symbol"><?php _e( 'Currency Symbol', 'wcpb' ); ?></label></td>
										<td><input type="text" id="wcpb-currency-symbol" name="postdata[currency_symbol]" placeholder="<?php _e( 'e.g. €', 'wcpb' ); ?>" value="<?php echo isset( $arr_settings['currency_symbol'] ) ? $arr_settings['currency_symbol'] : '' ?>"></td>
									</tr>
									<tr>
										<td><label for="wcpb-tax-information"><?php _e( 'Tax Information', 'wcpb' ); ?></label></td>
										<td><input type="text" id="wcpb-tax-information" name="postdata[tax_information]" placeholder="<?php _e( 'e.g. incl. VAT, excl. Shipping', 'wcpb' ); ?>" value="<?php echo isset( $arr_settings['tax_information'] ) ? $arr_settings['tax_information'] : '' ?>"></td>
									</tr>
									<tr>
										<td><label for="wcpb-custom-product-name"><?php _e( 'Custom Product Name', 'wcpb' ); ?></label></td>
										<td><input type="text" id="wcpb-custom-product-name" name="postdata[custom_product_name]" placeholder="<?php _e( 'e.g. Your custom product', 'wcpb' ); ?>" value="<?php echo isset( $arr_settings['custom_product_name'] ) ? $arr_settings['custom_product_name'] : '' ?>"></td>
									</tr>
									<tr>
										<td><label for="wcpb-custom-product-slug"><?php _e( 'Custom Product Slug', 'wcpb' ); ?></label></td>
										<td><input type="text" id="wcpb-custom-product-slug" name="postdata[custom_product_slug]" placeholder="<?php _e( 'e.g. custom-product', 'wcpb' ); ?>" value="<?php echo isset( $arr_settings['custom_product_slug'] ) ? $arr_settings['custom_product_slug'] : '' ?>"></td>
									</tr>
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
									<tr>
										<td><label for="option_amount_total"><?php _e( 'Max. Option Amount', 'wcpb' ); ?></label></td>
										<td><input type="text" id="option_amount_total" name="postdata[optioncat_amount_total]" value="<?php echo $arr_optioncat_amounts['total'] > 1 ? $arr_optioncat_amounts['total'] : 1; ?>"></td>
									</tr>
									<tr>
										<td><label for="wcpb-allow-same-option"><?php _e( 'Allow same Option Multiple Times', 'wcpb' ); ?></label></td>
										<td><input type="checkbox" id="wcpb-allow-same-option" name="postdata[allow-same-option]" value="true"<?php echo $arr_settings['allow-same-option'] ? ' checked="checked"' : ''; ?>></td>
									</tr>
								</table>
								<div class="clear"></div>
							</div>
						</div>
						
						<div id="wcpb-product_cat" class="wcpb-admin-postbox postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Define Category Amount and Custom Titles', 'wcpb' ); ?></span></h3>
							<div class="inside">
								<p><strong><?php _e( 'Maximum amount of options a customer can choose from each category and custom titles (optional).', 'wcpb' ); ?></strong></p>
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
								<div class="clear"></div>
							</div>
						</div>
						
						<div class="wcpb-admin-form-elem">
							<input class="button button-primary button-large" type="submit" name="settings_submit" value="<?php _e( 'Save Settings', 'wcpb' ); ?>">
						</div>

					</div>
				</div>
			</div>
		</div>
	</form>
	
</div>