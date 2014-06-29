<div class="trigger-warning-deluxe wrap">
	<div id="icon-themes" class="icon32"></div>
	<h2>Trigger Warning Deluxe</h2>
	<?php settings_errors() ?>
	<?php $activetab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general' ?>
	<h2 class="nav-tab-wrapper">  
		<a href="?page=<?php echo TWD_WordPressAdminIntegration::admin_page ?>&tab=general" class="nav-tab<?php echo $activetab == 'general' ? ' nav-tab-active' : '' ?>"><?php _e( 'General Options', 'trigger-warning-deluxe' ) ?></a>
		<a href="?page=<?php echo TWD_WordPressAdminIntegration::admin_page ?>&tab=about" class="nav-tab<?php echo $activetab == 'about' ? ' nav-tab-active' : '' ?>"><?php _e( 'About Trigger Warning Deluxe', 'trigger-warning-deluxe' ) ?></a>
	</h2>
	<?php
	switch( $activetab ) {
		case 'about' :
		include 'twd-about.php';
		break;

		case 'general' :
		default :
		echo '<form method="post" action="options.php">';
		settings_fields( TriggerWarningDeluxe::slug );
		do_settings_sections( TWD_WordPressAdminIntegration::admin_page );
		submit_button();
		echo '</form>';
	}
	?>
</div>