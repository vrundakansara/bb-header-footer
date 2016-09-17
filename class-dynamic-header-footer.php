<?php

/**
 *
 */
class Dynamic_Header_Footer {

	private $supported_themes = array();

	private $templates;
	private $template_dir;
	private $template_file = array();

	function __construct() {

		$this->supported_themes = array(
				'bb-theme'
			);

		$this->set_template_path();

		// Check where we want to force the page template
		$this->check_forced_template();

		// Add a filter to the attributes metabox to inject template into the cache.
		add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'dhf_register_project_templates' ) );

		// Add a filter to the save post to inject out template into the page cache
		add_filter( 'wp_insert_post_data', array( $this, 'dhf_register_project_templates' ) );

		add_filter( 'template_include', array( $this, 'dhf_view_project_template' ) );

		// Scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	function enqueue_scripts() {
		wp_enqueue_style( 'shf-style', DHF_URL . 'assets/css/style.css', array(), '1.0' );
	}

	function check_forced_template() {
		add_filter( 'page_template', array( $this, 'force_page_template' ) );
		add_filter( 'single_template', array( $this, 'force_page_template' ) );
		add_filter( 'archive_template', array( $this, 'force_page_template' ) );
		add_filter( 'index_template', array( $this, 'force_page_template' ) );
		add_filter( '404_template', array( $this, 'force_page_template' ) );
		add_filter( 'author_template', array( $this, 'force_page_template' ) );
		add_filter( 'category_template', array( $this, 'force_page_template' ) );
		add_filter( 'tag_template', array( $this, 'force_page_template' ) );
		add_filter( 'taxonomy_template', array( $this, 'force_page_template' ) );
		add_filter( 'date_template', array( $this, 'force_page_template' ) );
		add_filter( 'home_template', array( $this, 'force_page_template' ) );
		add_filter( 'front_page_template', array( $this, 'force_page_template' ) );
		add_filter( 'paged_template', array( $this, 'force_page_template' ) );
		add_filter( 'search_template', array( $this, 'force_page_template' ) );
		add_filter( 'attachment_template', array( $this, 'force_page_template' ) );
	}

	function force_page_template( $page_template ) {

		$page_template = $this->template_file;

		return $page_template;
	}

	function dhf_register_project_templates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		wp_cache_delete( $cache_key, 'themes' );

		$templates = array_merge( $templates, $this->templates );
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;
	}

	function dhf_view_project_template( $template ) {

		global $post;

		if ( ! isset( $this->templates[ get_post_meta( $post->ID, '_wp_page_template', true ) ] ) ) {

			return $template;
		}

		$file = DHF_DIR . '/templates/default/' . get_post_meta( $post->ID, '_wp_page_template', true );

		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		return $template;
	}

	public function set_template_path() {

		$template = get_template();
		
		if ( in_array( $template, $this->supported_themes ) ) {
						
			$this->template_file = DHF_DIR . 'templates/'. $template .'/template-page-builder.php';
			$this->template_dir = DHF_DIR . 'templates/'. $template .'';

			$this->templates = array(
				'template-page-builder.php' => 'Page Builder Template'
			);
		} else {

			$this->template_file = DHF_DIR . 'templates/default/template-page-builder.php';
			$this->template_dir = DHF_DIR . 'templates/default';

			$this->templates = array(
				'template-page-builder.php' => 'Page Builder Template'
			);
		}
		
	}

	public function get_header() {

		$header_id = Dynamic_Header_Footer::get_settings( 'dhf_header_id', '' );

		if ( $header_id !== '' ) {
			load_template( $this->template_dir . '/header.php' );
		} else {
			get_header();
		}		
	}

	public function get_footer() {

		$footer_id = Dynamic_Header_Footer::get_settings( 'dhf_footer_id', '' );

		if ( $footer_id !== '' ) {
			load_template( $this->template_dir . '/footer.php' );
		} else {
			get_footer();
		}
	}

	public static function get_header_content() {

		$header_id = Dynamic_Header_Footer::get_settings( 'dhf_header_id', '' );
		echo do_shortcode( '[fl_builder_insert_layout id="' .$header_id. '"]' );
	}

	public static function get_footer_content() {

		$footer_id = Dynamic_Header_Footer::get_settings( 'dhf_footer_id', '' );
		echo "<div class='footer-width-fixer'>";
		echo do_shortcode( '[fl_builder_insert_layout id="' .$footer_id. '"]' );
		echo "</div>";
	}

	public static function get_settings( $setting = '', $default = '' ) {

		$options = get_option( 'dhf_settings' );

		if ( isset( $options[ $setting ] ) ) {
			return $options[ $setting ];
		}

		return $default;
	}

}