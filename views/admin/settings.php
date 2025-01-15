<div id="<?php echo esc_attr( $page ); ?>" class="wrap">

	<?php
		if ( HSD_FREE ) {
			$helpscout_url = 'https://wphelpscout.com/?utm_medium=settings&utm_campaign=hsfree&utm_source=wordpress.org/';
			printf(
				'<div class="upgrade_message clearfix"><p><span class="icon-sproutapps-flat"></span><strong>%s</strong> %s</p></div>',
				esc_html__( 'Looking for more?', 'help-scout' ),
				sprintf( 'Checkout <a href="%s" target="_blank">Help Scout Desk</a>', 'help-scout' ),
				esc_url( $helpscout_url )
			);
		}
	?>

	<span id="ajax_saving" style="display:none" data-message="<?php esc_html_e( 'Saving...', 'help-scout' ); ?>"></span>
	<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" class="sprout_settings_form
		<?php
		echo esc_html( $page );
		if ( $ajax ) {
			echo esc_html( ' ajax_save' );
		} if ( $ajax_full_page ) {
			echo esc_html( ' full_page_ajax' ); }
		?>
	">
		<?php settings_fields( $page ); ?>
		<table class="form-table">
			<?php do_settings_fields( $page, 'default' ); ?>
		</table>
		<?php do_settings_sections( $page ); ?>
		<?php submit_button(); ?>
		<?php if ( $reset ) : ?>
			<?php submit_button( hsd__( 'Reset Defaults' ), 'secondary', $page . '-reset', false ); ?>
		<?php endif ?>
	</form>

	<?php do_action( 'sprout_settings_page', $page ); ?>
	<?php do_action( 'sprout_settings_page_' . $page, $page ); ?>
</div>
