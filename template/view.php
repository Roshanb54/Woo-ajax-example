<?php
/**
 * User: bhaskark
 * Date: 3/11/17
 * Time: 11:48 PM
 */

get_header();
?>

<?php if ( 'success' == $_GET['status'] ) { ?>
	<p>
		<span style="color:green">Form submitted successfully!!</span>
	</p>
<?php } ?>

<!--Form goes here-->
<form name="send-email" method="post">
	<!-- WordPress Nonce -->
	<?php wp_nonce_field( 'ass_nonce_action_purchase_order', 'ass_nonce_name_purchase_order' ); ?>
	<!--CATEGORY DROP DOWN-->
	<section class="right-col">
		<div id="select-category">
			<?php echo Ass_Purchase_Order::get_instance()->get_product_category_dropdown(); ?>
		</div>
		<div id="select-product"></div>
		<div id="select-product-variation"></div>
	</section>

	<section class="left-col">
		<!--EMAIL FORM START -->
		<label>
			First Name
			<input type="text" name="first_name">
		</label>

		<label>
			Last Name
			<input type="text" name="last_name">
		</label>
		<label>
			Email
			<input type="email" name="email">
		</label>
		<label>
			Phone
			<input type="tel" name="phone">
		</label>
		<input type="submit" name="purchase-order" value="Send">
		<!--EMAIL FORM END-->
	</section>
</form>
<?php get_footer();
