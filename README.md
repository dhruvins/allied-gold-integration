# Allied Gold Integration

This plugin is used for integrating Allied Gold iFrame with WooCommerce Product.

### Usage

1. Create a WooCommerce Simple Product and note the Product ID.
2. Create a new page and add the iFrame shared by Allied Gold.
3. Add a shortcode to the same page after the iFrame. Shortcode details: `[ag_product_form id="82"]`. `id` is the Product ID from Step 1.

An "Add to Cart" button will now be available after the iFrame where the products would be displayed by iFrame. Once user clicks on Add to Cart, the selections done by user would be passed on the cart.
Once the order is place the same details would be passed as Order Item Meta.
