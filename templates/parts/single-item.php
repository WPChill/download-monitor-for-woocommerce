<?php
$product = wc_get_product( $id );
?>
<div class='dlm-rounded-3xl dlm-p-8 xl:dlm-p-10 dlm-ring-1 dlm-ring-gray-200 dlm-m-2'>
	<div class='dlm-flex dlm-items-center dlm-justify-between dlm-gap-x-4'>
		<h3 id='tier-freelancer' class='dlm-text-lg dlm-font-semibold dlm-leading-8 dlm-text-gray-900'><a
				href="<?php echo esc_url( get_post_permalink( $id ) ); ?>" class='dlm-block dlm-w-full'>
				<h3
					class='dlm-text-lg dlm-font-semibold dlm-leading-8 dlm-text-gray-900 dlm-w-full dlm-inline-block dlm-text-center'>
					<?php echo esc_html( $product->get_name() ); ?></h3>
			</a></h3>
	</div>
	<p class='dlm-mt-4 dlm-text-sm dlm-leading-6 dlm-text-gray-600'><?php echo wp_kses_post( wp_trim_words( $product->get_description(), 10 ) ); ?></p>
	<p class='dlm-mt-6 dlm-flex dlm-items-baseline dlm-gap-x-1'>
		<!-- Price, update based on frequency toggle state -->
		<span class='dlm-text-4xl dlm-font-bold dlm-tracking-tight dlm-text-gray-900'><?php echo wp_kses_post( $product->get_price_html() );  ?></span>
	</p>
	<a href='<?php echo esc_url( wc_get_cart_url() .'?' . http_build_query( array( 'add-to-cart' => $id ) ) ); ?>'
	   class='dlm-mt-6 dlm-block dlm-rounded-md dlm-py-2 dlm-px-3 dlm-text-center dlm-text-sm dlm-font-semibold dlm-leading-6 focus-visible:dlm-outline focus-visible:dlm-outline-2 focus-visible:dlm-outline-offset-2 focus-visible:dlm-outline-indigo-600 dlm-text-indigo-600 dlm-ring-1 dlm-ring-inset dlm-ring-indigo-200 hover:dlm-ring-indigo-300'><?php esc_html_e( 'Buy now', 'download-monitor-woocommerce-integration' ) ?></a>
</div>
<?php
