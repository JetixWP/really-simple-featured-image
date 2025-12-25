<?php
/**
 * Admin View: Settings
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Settings\Views;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Note for reviewer: This is a false positive! The variable $tab_exists is not a global variable. This is a template file which gets included inside Admin_Settings::output class method.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$tab_exists                                   = isset( $tabs[ $rs_featured_image_settings_current_tab ] ) || has_action( 'rs_featured_image_sections_' . $rs_featured_image_settings_current_tab ) || has_action( 'rs_featured_image_settings_' . $rs_featured_image_settings_current_tab ) || has_action( 'rs_featured_image_settings_tabs_' . $rs_featured_image_settings_current_tab );
$rs_featured_image_settings_current_tab_label = isset( $tabs[ $rs_featured_image_settings_current_tab ] ) ? $tabs[ $rs_featured_image_settings_current_tab ] : '';

global $current_user;

if ( ! $tab_exists ) {
	wp_safe_redirect( admin_url( 'admin.php?page=rs-featured-image-settings' ) );
	exit;
}
?>
<div class="wrap rs-featured-image <?php echo esc_attr( $rs_featured_image_settings_current_tab ); ?>">
	<div class="plugin-header">
		<div class="plugin-header-wrap">
			<div class="plugin-info">
				<h1 class="menu-title"><?php esc_html_e( 'Really Simple Featured Image', 'really-simple-featured-image' ); ?></h1>
				<?php do_action( 'rs_featured_image_extend_plugin_header' ); ?>
				<div class="plugin-version">
					<span>v<?php echo esc_html( RS_FEATURED_IMAGE_VERSION ); ?></span>
				</div>
			</div>

			<div class="brand-info">
				<a href="https://jetixwp.com?utm_campaign=settings-header&utm_source=rs_featured_image-plugin" target="_blank"><img class="brand-logo" src="<?php echo esc_url( RS_FEATURED_IMAGE_PLUGIN_URL . 'assets/images/jwp-icon-dark.svg' ); ?>" alt="ReallySimpleFeaturedImage"></a>
			</div>
		</div>
	</div>
	<div class="rs-featured-image-wrapper">

			<div class="nav-content">
				<nav class="nav-tab-wrapper rs-featured-image-nav-tab-wrapper">
					<?php

					/**
					 * Note for reviewer: This is a false positive! The variables $slug & $label are not global variables. This is a template file which gets included inside Admin_Settings::output class method.
					 */
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					foreach ( $tabs as $slug => $label ) {
						echo '<a href="' . esc_html( admin_url( 'admin.php?page=rs-featured-image-settings&tab=' . esc_attr( $slug ) ) ) . '" class="nav-tab ' . ( $rs_featured_image_settings_current_tab === $slug ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
					}

					do_action( 'rs_featured_image_settings_tabs' );

					?>
				</nav>
			</div>
			<div class="tab-content">
				<form method="<?php echo esc_attr( apply_filters( 'rs_featured_image_settings_form_method_tab_' . $rs_featured_image_settings_current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
					<div class="content">
						<h1 class="screen-reader-text"><?php echo esc_html( $rs_featured_image_settings_current_tab_label ); ?></h1>
						<?php
						do_action( 'rs_featured_image_sections_' . $rs_featured_image_settings_current_tab );

						self::show_messages();

						do_action( 'rs_featured_image_settings_' . $rs_featured_image_settings_current_tab );
						?>
						<p class="submit">
							<?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
								<button name="save" class="button-primary rs-featured-image-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'really-simple-featured-image' ); ?>"><?php esc_html_e( 'Save changes', 'really-simple-featured-image' ); ?></button>
							<?php endif; ?>
							<?php wp_nonce_field( 'rs-featured-image-settings' ); ?>
						</p>
					</div>
				</form>

				<div class="sidebar">
					<div class="help-box">
						<div>
							<h3>👋 Thank you for using our plugin!</h3>
							<p class="desc">We are currently looking for user feedback to improve it further for most use cases. If you have something to suggest, please feel free to drop your request at - <a href="mailto:hello@jetixwp.com" target="_blank">hello@jetixwp.com</a>.</p>
						</div>

						<div>
							<p class="desc">If you like this plugin, you will absolutely love our other plugins.</p>
						</div>
						<div>
							<a class="button button-primary" href="https://jetixwp.com/plugins?utm_campaign=settings-sidebar&utm_source=rs_free_shipping-plugin" target="_blank">✨ View all Plugins</a>
						</div>
						<div>
							<p><em>Thank you for using our Free Shipping addon for WooCommerce again, you are not just any supporter but truly the founders of our small but mighty product agency.</em></p>
							<p><strong>Krishna</strong>, Founder and Lead Developer at JetixWP</p>
						</div>
					</div>

					<?php do_action( 'rs_featured_image_extend_settings_sidebar' ); ?>
				</div>
			</div>

	</div>
</div>
