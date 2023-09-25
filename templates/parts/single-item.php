<?php
$product = wc_get_product( $id );
?>
<div
	class='dlm-flex dlm-flex-col dlm-justify-between dlm-bg-white dlm-p-8 xl:dlm-p-10 dlm-items-center dlm-text-center'>
	<div>
		<div class='dlm-flex dlm-items-center dlm-justify-between dlm-gap-x-4'>
			<a href="<?php echo esc_url( get_post_permalink( $id ) ); ?>" class="dlm-block dlm-w-full">
				<h3
					class='dlm-text-lg dlm-font-semibold dlm-leading-8 dlm-text-gray-900 dlm-w-full dlm-inline-block dlm-text-center'>
					<?php echo esc_html( $product->get_name() ) ?></h3>
			</a>
		</div>
		<p class='dlm-mt-4 dlm-text-sm dlm-leading-6 dlm-text-gray-600 dlm-text-center'><?php echo $product->get_image(); ?></p>
		<p class='dlm-dlm-mt-6 dlm-flex dlm-items-baseline dlm-gap-x-1'>
							<span
								class='dlm-font-bold dlm-tracking-tight'><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
		</p>
		<div
			class="dlm-w-full dlm-inline-block dlm-text-left"><?php echo wp_kses_post( wp_trim_words( $product->get_description(), 10 ) ); ?></div>
	</div>
	<a href='<?php echo $product->add_to_cart_url() ?>'
	   class='dlm-mt-8 block dlm-rounded-md dlm-py-2 dlm-px-3 dlm-text-center dlm-text-sm dlm-font-semibold dlm-leading-6 focus-visible:dlm-outline-indigo-600 dlm-text-indigo-600 dlm-ring-1 dlm-ring-inset dlm-ring-indigo-200 hover:dlm-ring-indigo-300 focus-visible:dlm-outline-indigo-600 dlm-bg-indigo-600 dlm-text-white dlm-shadow-sm hover:dlm-bg-indigo-500 dlm-w-full'>Buy
		plan</a>
</div>
