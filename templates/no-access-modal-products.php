<div class='dlm-bg-white dlm-py-24 sm:dlm-py-32'>
	<div class='dlm-mx-auto dlm-max-w-7xl dlm-px-6 lg:dlm-px-8'>
		<div class='dlm-mx-auto dlm-max-w-4xl dlm-text-center'>
			<p class='dlm-mt-2 dlm-text-4xl dlm-font-bold dlm-tracking-tight dlm-text-gray-900 sm:dlm-text-5xl'>Products</p>
		</div>
		<p class='dlm-mx-auto dlm-mt-6 dlm-max-w-2xl dlm-text-center dlm-text-lg dlm-leading-8 dlm-text-gray-600'>Buy one of the following to get
			access to the desired file.</p>
		<?php
		foreach ( $products as $id ) {
			$product = wc_get_product( $id );
			?>
			<div
				class='dlm-isolate dlm-mx-auto dlm-mt-16 dlm-grid dlm-max-w-md dlm-grid-cols-1 dlm-gap-y-8 sm:dlm-mt-20 lg:dlm-mx-0 lg:dlm-max-w-none lg:dlm-grid-cols-3'>
				<div
					class='dlm-flex dlm-flex-col dlm-justify-between dlm-rounded-3xl dlm-bg-white dlm-p-8 dlm-ring-1 dlm-ring-gray-200 xl:dlm-p-10 lg:dlm-mt-8 lg:dlm-rounded-r-none'>
					<div>
						<div class='dlm-flex dlm-items-center dlm-justify-between dlm-gap-x-4'>
							<h3 class='dlm-text-lg dlm-font-semibold dlm-leading-8 dlm-text-gray-900'>
								<?php $product->get_name() ?></h3>
						</div>
						<p class='dlm-mt-4 dlm-text-sm dlm-leading-6 dlm-text-gray-600'><?php echo $product->get_image(); ?></p>
						<p class='dlm-dlm-mt-6 dlm-flex dlm-items-baseline dlm-gap-x-1'>
							<span
								class='dlm-text-4xl dlm-font-bold dlm-tracking-tight dlm-text-gray-900'><?php $product->get_price_html() ?></span>
						</p>
						<?php echo wp_kses_post( $product->get_description() ); ?>
					</div>
					<a href='#' class='dlm-mt-8 block dlm-rounded-md dlm-py-2 dlm-px-3 dlm-text-center dlm-text-sm dlm-font-semibold dlm-leading-6 focus-visible:dlm-outline focus-visible:dlm-outline-2 focus-visible:dlm-outline-offset-2 focus-visible:dlm-outline-indigo-600 dlm-text-indigo-600 dlm-ring-1 dlm-ring-inset dlm-ring-indigo-200 hover:dlm-ring-indigo-300'>Buy
						plan</a>
				</div>
			</div>
			<?php
		}
		?>
	</div>
</div>