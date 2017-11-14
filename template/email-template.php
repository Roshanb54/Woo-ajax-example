<?php
/**
 * User: bhaskar.kc
 * Date: 14/11/17
 * Time: 3:33 PM
 */
?>
<!--you can add styling here-->
<p>Hi Admin,</p>
<p>You have got a new lead!</p>
<p><strong>Description:</strong></p>
<table>
	<thead>
	<tr>
		<th>
			First Name
		</th>
		<td><?php echo $firstname ?></td>
	</tr>
	<tr>
		<th>
			Last Name
		<td><?php echo $lastname ?></td>
		</th>
	</tr>

	<tr>
		<th>
			Email
		<td><?php echo $email ?></td>
		</th>
	</tr>

	<tr>
		<th>
			Phone
		<td><?php echo $phone ?></td>
		</th>
	</tr>

	<tr>
		<th>
			Product Category
		<td><?php echo $category_title ?></td>
		</th>
	</tr>

	<tr>
		<th>
			Product Name
		<td><?php echo $product_title ?></td>
		</th>
	</tr>

	<tr>
		<th>
			Product Variation
		<td><?php echo $variation_title ?></td>
		</th>
	</tr>
	</thead>
</table>
