jQuery(document).ready(function () {
	if ( jQuery( '#product-showcase' ).length > 0 ) {
		initRingBuilder({
			iframeId: "product-showcase",
			log: true
		});
	}

	jQuery( '#ag_contact_form' ).submit( function(e) {

		// Prevent form submission which refreshes page
		e.preventDefault();
	
		// Serialize data
		const data = {
			action: 'ag_send_form',
			name: jQuery( '#ag_name' ).val(),
			email: jQuery( '#ag_email' ).val(),
			phone: jQuery( '#ag_phone' ).val(),
			message: jQuery( '#ag_message' ).val(),
			prod_name: ( jQuery( '.ag_product_name' ).val() ) ? jQuery( '.ag_product_name' ).val() : 'none'	
		}

		jQuery.post( ag_vars.ajax_url, data, function(response) {
			jQuery( '.ag_contact_form' ).text('Thank you for contacting us. We will get back to you at the earliest');
		});
	});
	
});

function ag_received_pricing( response ) {
	const $ = jQuery;
	if ( typeof response === 'object' && response !== null && response.event && response.event === 'orderUpdated' && response.price && response.price !== null ) {
		const img360 = ( response.productImg360 && response.productImg360.url ) ? response.productImg360.url : '';

		$( '.ag_product_name' ).show();
		$( '.ag_gold_final_price' ).show();

		$( '.ag_product_name' ).text( 'Product Name: ' + response.product.name );

		let prod_price = ( response.price ) ? response.price : 0;
		prod_price = Math.round( ( prod_price + Number.EPSILON ) * 100 ) / 100;

		$( '.ag_gold_final_price' ).text( 'Product Price: ' + ag_vars.currency_symbol + '' + prod_price );

		$( '.single_add_to_cart_button' ).prop( 'disabled', false );

		$( '.ag_price' ).val( prod_price );
		$( '.ag_product_id' ).val( response.product.id );
		$( '.ag_product_name' ).val( response.product.name );
		$( '.ag_product_img360' ).val( img360 );
		$( '.ag_product_imgURL' ).val( response.productImgUrl );
		$( '.ag_size_code' ).val( response.size.code );
		$( '.ag_size_id' ).val( response.size.id );
		$( '.ag_size_name' ).val( response.size.name );

		response.options.forEach(option => {
			if ('Polishing/Texture' === option.optionName) {
				$( '.ag_polishing_texture' ).val( option.variationName );
			} else if ('Engraving Inside' === option.optionName) {
				$( '.ag_eng_font' ).val( option.variationName );
				$( '.ag_eng_text' ).val( option.text );
			}
		});
	} else {
		$( '.ag_product_name' ).hide();
		$( '.ag_product_name' ).text( '' );

		$( '.ag_gold_final_price' ).text( '' );
		$( '.ag_gold_final_price' ).hide();

		$( '.single_add_to_cart_button' ).prop( 'disabled', true );
	}
}