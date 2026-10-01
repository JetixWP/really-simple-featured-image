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

// Unknown tabs are redirected in Register_Settings::load_settings_page() before any output.
$rs_featured_image_settings_current_tab_label = isset( $tabs[ $rs_featured_image_settings_current_tab ] ) ? $tabs[ $rs_featured_image_settings_current_tab ] : '';
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
						echo '<a href="' . esc_url( admin_url( 'admin.php?page=rs-featured-image-settings&tab=' . rawurlencode( $slug ) ) ) . '" class="nav-tab ' . ( $rs_featured_image_settings_current_tab === $slug ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
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
					<?php
					/**
					 * Filters whether the free plugin's thank-you card shows in the settings sidebar.
					 *
					 * Hidden once PRO has loaded, because PRO adds its own member card.
					 *
					 * @since 1.1.1
					 *
					 * @param bool $show Whether to show the card.
					 */
					if ( apply_filters( 'rs_featured_image_show_sidebar_help_box', ! did_action( 'rs_featured_image_pro_loaded' ) ) ) :
						?>
					<div class="help-box">
						<div>
							<h3><?php esc_html_e( 'Thank you for using our plugin!', 'really-simple-featured-image' ); ?></h3>
							<p class="desc">
								<?php
								printf(
									/* translators: %s: email address link. */
									esc_html__( 'We are looking for feedback to make it better for more sites. If you have an idea or a request, write to us at %s.', 'really-simple-featured-image' ),
									'<a href="mailto:hello@jetixwp.com">hello@jetixwp.com</a>'
								);
								?>
							</p>
						</div>

						<div>
							<p class="desc"><?php esc_html_e( 'If you like this plugin, you will love our other plugins too.', 'really-simple-featured-image' ); ?></p>
						</div>
						<div>
							<a class="button button-primary" href="https://jetixwp.com/plugins?utm_campaign=settings-sidebar&utm_source=rs_featured_image-plugin" target="_blank"><?php esc_html_e( 'View all plugins', 'really-simple-featured-image' ); ?></a>
						</div>
						<div>
							<p><em><?php esc_html_e( 'Thank you for using Really Simple Featured Image. You are not just a user but one of the founders of our small but mighty product agency.', 'really-simple-featured-image' ); ?></em></p>
							<p>
								<?php
								printf(
									/* translators: %s: founder name. */
									esc_html__( '%s, Founder and Lead Developer at JetixWP', 'really-simple-featured-image' ),
									'<strong>Krishna</strong>'
								);
								?>
							</p>
						</div>
					</div>
					<?php endif; ?>

					<?php do_action( 'rs_featured_image_extend_settings_sidebar' ); ?>
				</div>
			</div>

	</div>
</div>
