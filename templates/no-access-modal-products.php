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
		<p class='dlm-mx-auto dlm-max-w-2xl dlm-text-center dlm-text-lg dlm-leading-8'>Buy one of the
			following to get
			access to the desired file.</p>
		<div
			class='dlm-isolate dlm-mx-auto dlm-pt-10 dlm-grid dlm-max-w-md dlm-grid-cols-1 dlm-gap-y-8 lg:dlm-mx-0 dlm-divide-x sm:dlm-mx-auto lg:dlm-mt-0 lg:dlm-max-w-none <?php echo esc_attr( $class ) ?>'>
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