<?php
/**
 * WooCommerce Product Builder Options Template
 *
 * Shows all options in a categorized manner.
 *
 * @author	Philipp Bauer
 * @version	0.1
 */
global $wcpb;
// Get subcategories
$arr_optioncats = get_categories( 'taxonomy=product_cat&hide_empty=0&hierarchical=1&child_of=' . $wcpb->arr_settings['product_cat'] );
?>

<?php
$i = 1;
foreach ( $arr_optioncats as $obj_optioncat ) :
?>

<ul id="<?php echo $obj_optioncat->slug; ?>"<?php echo ! isset( $_GET['optioncat'] ) && $i == 1 ? ' class="active"' : ( isset( $_GET['optioncat'] ) && $_GET['optioncat'] == $obj_optioncat->slug ? ' class="active"' : '' ) ?>>

<?php
// Get options from this category
$arr_options_args = array(
	'tax_query'	=> array(
		array(
			'taxonomy'	=> 'product_cat',
			'field'		=> 'slug',
			'terms'		=> $obj_optioncat->slug,
		),
	),
	'post_type'	=> 'product',
	'orderby'	=> 'title',
	'order'		=> 'ASC',
);
$arr_options = new WP_Query( $arr_options_args );
?>

<?php foreach ( $arr_options->posts as $obj_option ) : ?>
	<?php
	// Get option metadata / image
	$arr_option_metadata = get_post_meta( $obj_option->ID );
	$arr_option_image = false;
	if ( isset( $arr_option_metadata['_thumbnail_id'] ) )
		$arr_option_image = get_post( $arr_option_metadata['_thumbnail_id'][0], ARRAY_A );
		
	// var_dump( $obj_option );
	// var_dump( $arr_option_metadata );
	?>
	<li>
		<div class="wcpb-option-thumb">
			<img src="<?php echo ! $arr_option_image ? 'http://placehold.it/279x170' : $arr_option_image['guid']; ?>" alt="">
		</div>
		<h1><?php echo $obj_option->post_title; ?></h1>
		<p><?php echo $obj_option->post_excerpt; ?></p>
		<form class="wcpb-option-form" action="<?php echo get_permalink( get_the_ID() ); echo $i == 1 ? '&optioncat=' . $arr_optioncats[1]->slug : '&optioncat=' . $obj_optioncat->slug; ?>" method="post">
			<input type="hidden" name="option_id" value="<?php echo $obj_option->ID; ?>">
			<input type="hidden" name="option_cat" value="<?php echo $obj_optioncat->slug; ?>">
			<input type="hidden" name="option_qty" value="1">
			<button type="submit" name="action" value="add_option"><?php _e( 'add to product', 'wcpb' ); ?></button>
		</form>
	</li>
<?php endforeach; ?>

</ul>

<?php
$i++;
endforeach;
?>