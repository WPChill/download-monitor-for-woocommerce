<?php
$class = 'dlm-grid-cols-3';

if ( 1 === count( $products ) ) {
	$class = 'dlm-grid-cols-1';
} elseif ( 2 === count( $products ) ) {
	$class = 'dlm-grid-cols-2';
} elseif ( count( $products ) > 3 ) {
	$products = array_slice( $products, 0, 3 );
}

?>

<div class='dlm-wci-modal dlm-bg-white dlm-pt-10'>
	<div class='dlm-mx-auto dlm-max-w-7xl dlm-px-6 lg:dlm-px-8'>
		<div
			class='dlm-isolate dlm-grid dlm-grid-cols-1 dlm-gap-y-16 dlm-divide-y dlm-divide-gray-100 sm:dlm-mx-auto lg:-dlm-mx-8 lg:dlm-mt-0 lg:dlm-max-w-none lg:dlm-divide-x lg:dlm-divide-y-0 xl:-dlm-mx-4 dlm-mt-1 <?php echo esc_attr( $class ) ?>'>
			<?php
			foreach ( $products as $id ) {
				$template_handler = new DLM_Template_Handler();
				$template_handler->get_template_part(
					'single-item',
					'',
					DLM_WC_PATH . 'templates/parts/',
					array(
						'id' => $id
					)
				);
			}
			?>
		</div>
	</div>
</div>