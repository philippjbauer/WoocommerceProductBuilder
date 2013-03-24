<?php
/**
 * WooCommerce Product Builder Order Export Page
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.9
 */
global $wcpb_backend, $wpdb;

/* EXPORT FUNCTION */
function gen_export_order( $arr_order ) {
	$arr_export_headers = array();
	$arr_export_data = array();

	foreach ( $arr_order['shipping'] as $key => $value ) {
		$arr_export_headers[] = '"' . ucwords( str_replace( '_', '', $key ) ) . '"';
		$arr_export_data[] = '"' . $value . '"';
	}

	foreach ( $arr_order['items'] as $arr_item ) {
		$arr_export_headers[] = '"' . $arr_item['name'] . '"';
		$arr_tmp_data = array();
		foreach ( $arr_item['options'] as $obj_option )
			$arr_tmp_data[] = $obj_option->ID . ': ' . $obj_option->post_title;
		$arr_export_data[] = '"' . implode( ', ', $arr_tmp_data ) . '"';
	}

	$str_export_headers = implode( ',', $arr_export_headers );
	$str_export_data = implode( ',', $arr_export_data );

	$str_return = $str_export_headers . "\n" . $str_export_data;

	return $str_return;
}

/* PREPARE */
$arr_orders_meta_keys = array(
	"_shipping_first_name",
	"_shipping_last_name",
	"_shipping_company",
	"_shipping_address_1",
	"_shipping_address_2",
	"_shipping_city",
	"_shipping_postcode",
	"_shipping_state",
	"_shipping_country",
);

/* GET ORDERS */
$arr_orders = array();
$arr_orders_data = get_posts( array( 'orderby' => 'post_date', 'order' => 'DESC', 'post_type' => 'shop_order', 'post_status' => 'publish' ) );
$arr_orders_meta = array();

foreach ( $arr_orders_data as $obj_order ) {

	/* ORDER */
	// Save Order Data
	$arr_orders[$obj_order->ID]['order'] = $obj_order;

	// Get Order Meta
	$arr_tmp = array();
	foreach ( $arr_orders_meta_keys as $str_meta_key )
		$arr_tmp[str_replace( '_shipping_', '', $str_meta_key )] = get_post_meta( $obj_order->ID, $str_meta_key, true );
	
	// Save Order Meta
	$arr_orders[$obj_order->ID]['shipping'] = $arr_tmp;

	/* ITEMS */
	// Get Order Items
	$arr_tmp = array();
	$arr_items = $wpdb->get_results( "SELECT order_item_id, order_item_name FROM wp_woocommerce_order_items WHERE order_id = " . $obj_order->ID );
	foreach ( $arr_items as $obj_item ) {
		$arr_item = array();

		$arr_item['id']		= $obj_item->order_item_id;
		$arr_item['name']	= $obj_item->order_item_name;

		// Get Item Options
		$obj_itemmeta = $wpdb->get_row( "SELECT meta_value AS product_id FROM wp_woocommerce_order_itemmeta WHERE meta_key = '_product_id' AND order_item_id = '$obj_item->order_item_id'" );
		$obj_serialized_options = $wpdb->get_row( "SELECT meta_value AS options FROM $wpdb->postmeta WHERE meta_key = '_product_options' AND post_id = '$obj_itemmeta->product_id'");
		$arr_optioncats = unserialize( $obj_serialized_options->options );

		// Get Item Options Meta
		$arr_options = array();
		foreach ($arr_optioncats as $arr_optioncat) {
			foreach ($arr_optioncat as $int_option_id) {
				$arr_options[$int_option_id] = get_post( $int_option_id );
			}
		}

		$arr_item['options'] = $arr_options;

		$arr_tmp[] = $arr_item;
	}

	// Save Order Items
	$arr_orders[$obj_order->ID]['items'] = $arr_tmp;

}

/* DOWNLOAD EXPORT */
if ( ! empty ( $_POST['postdata'] ) ) {
	
	// VARS
	$arr_export = array();
	$arr_upload_dir = wp_upload_dir();

	// GET DATA
	switch ( $_POST['postdata']['export'] ) {
		case 'filter':
			echo 'filter orders';
			break;
		case 'all':
			foreach ( $arr_orders as $arr_order )
				$arr_export[$arr_order['order']->post_name] = gen_export_order( $arr_order );
			break;
		default:
			$arr_export[$arr_orders[$_POST['postdata']['export']]['order']->post_name] = gen_export_order( $arr_orders[$_POST['postdata']['export']] );
			break;
	}

	// CREATE ARCHIVE
	$zip = new ZipArchive();
	$zip_name = 'export' . date('_Ymd_Hi') . '.zip';
	$zip_file = $arr_upload_dir['basedir'] . '/wcpb_exports/' . $zip_name;
	$zip_export = $zip->open( $zip_file, ZipArchive::CREATE );
	
	if ( $zip_export ) {
		// Add files
		foreach ($arr_export as $key => $value)
			$zip->addFromString( $key . '.csv', $value );
		$zip->close();

		// Read Archive Data
		$handle = fopen( $zip_file, "r" );
		$str_content = fread( $handle, filesize( $zip_file ) );
		fclose($handle);

	}
	else die( __( 'Could not export orders!', 'wcpb' ) );

	if ( ! empty( $str_content ) ) {
		ob_clean();
		ob_start();
		header('Content-Description: WCPB Order Export');
		header('Content-Type: application/zip');
		header('Content-disposition: attachment; filename=' . $zip_name);
		header('Content-Length: '.strlen($str_content));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Expires: 0');
		header('Pragma: public');
		// header('Location: ' . $_SERVER['PHP_SELF']);
		echo $str_content;
		ob_end_flush();
		exit;
	}


}

/* OUTPUT ORDERS */
?>
<div class="wrap">

	<!-- <hgroup class="wcpb-admin-header"> -->
		<div id="icon-tools" class="icon32"></div>
		<h2><?php _e( 'Export Orders', 'wcpb' ); ?></h2>
		<!-- <p><?php _e( 'WooCommerce Product Builder ' . get_option( 'wcpb_version' ), 'wcpb' ) ?></p> -->
	<!-- </hgroup> -->
	
	<form action="<?php echo $_SERVER['PHP_SELF'] . "?page=" . $_GET['page']; ?>" method="post" name="wcpb-export-form">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-1">
				<div id="postbox-container-1" class="postbox-container">
					<div id="normal-sortables" class="meta-box-sortables ui-sortable">
						<!-- EXPORT FORM -->
						<div id="wcpb-export-data" class="wcpb-admin-postbox postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle"><span><?php _e( 'Export Settings', 'wcpb' ); ?></span></h3>
							<div class="inside">
								<div class="wcpb-admin-form-elem wcpb-admin-inline">
									<label for="wcpb-date-start" class="wcpb-admin-disabled-label"><?php _e( 'Start Date', 'wcpb' ); ?></label>
									<input type="text" name="postdata[export-date-start]" placeholder="<?php _e( 'dd-mm-yyyy', 'wcpb' ); ?>" id="wcpb-date-start" disabled="disabled">
								</div>
								<div class="wcpb-admin-form-elem wcpb-admin-inline">
									<label for="wcpb-date-end" class="wcpb-admin-disabled-label"><?php _e( 'End Date', 'wcpb' ); ?></label>
									<input type="text" name="postdata[export-date-end]" placeholder="<?php _e( 'dd-mm-yyyy', 'wcpb' ); ?>" id="wcpb-date-start" disabled="disabled">
								</div>
								<div class="wcpb-admin-form-elem wcpb-admin-inline">
									<button class="button button-secondary" type="submit" name="postdata[export]" value="filter" disabled="disabled"><?php _e( 'filter orders', 'wcpb' ); ?></button>
								</div>
								<div class="wcpb-admin-form-elem wcpb-admin-inline">
									<button class="button button-primary" type="submit" name="postdata[export]" value="all"><?php _e( 'export all orders', 'wcpb' ); ?></button>
								</div>
								<div class="clear"></div>
							</div>
						</div>
						
						<!-- ORDERS -->
						<?php foreach ( $arr_orders as $arr_order ) : ?>
						<div class="wcpb-admin-postbox postbox">
							<div class="handlediv" title="Click to toggle"><br></div>
							<h3 class="hndle">
								<span><strong>#<?php echo $arr_order['order']->ID; ?></strong> // <?php echo $arr_order['order']->post_date; ?> // <a href="<?php echo $arr_order['order']->guid; ?>"><?php _e( 'Details', 'wcpb' ); ?></a></span>
							</h3>
							<div class="inside">
								<div class="wcpb-admin-order-info">
									<div class="wcpb-admin-shipping">
										<h4><?php _e( 'Shipping Information', 'wcpb' ); ?></h4>
										<table>
											<?php foreach ( $arr_order['shipping'] as $key => $value ) : ?>
											<tr>
												<th class="wcpb-admin-shipping-title"><?php echo ucwords( str_replace( '_', '', $key ) ); ?></th>
												<td class="wcpb-admin-shipping-value"><?php echo $value; ?></td>
											</tr>
											<?php endforeach; ?>
										</table>
									</div>
									<div class="wcpb-admin-products">
										<?php foreach ( $arr_order['items'] as $arr_item ) : ?>
										<h4><?php echo $arr_item['name']; ?></h4>
										<table>
											<thead>
												<tr>
													<th class="wcpb-admin-product-id"><?php _e( 'ID', 'wcpb' ); ?></th>	
													<th class="wcpb-admin-product-titled"><?php _e( 'Option Title', 'wcpb' ); ?></th>	
													<th class="wcpb-admin-product-details"><?php _e( 'Details', 'wcpb' ); ?></th>	
												</tr>
											</thead>
											<tbody>
												<?php foreach ( $arr_item['options'] as $obj_option ) : ?>
												<tr>
													<td class="wcpb-admin-product-id"><?php echo $obj_option->ID; ?></td>
													<td class="wcpb-admin-product-title"><?php echo $obj_option->post_title; ?></td>
													<td class="wcpb-admin-product-details"><a href="post.php?post=<?php echo $obj_option->ID; ?>&action=edit"><?php _e( 'click here', 'wcpb' ); ?></a></td>
												</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
										<?php endforeach; ?>
									</div>
								</div>
								<div class="wcpb-admin-form-elem">
									<button class="button button-primary" type="submit" name="postdata[export]" value="<?php echo $arr_order['order']->ID; ?>"><?php printf( __( 'export order #%d', 'wcpb' ), $arr_order['order']->ID ); ?></button>
								</div>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</form>
	
</div>