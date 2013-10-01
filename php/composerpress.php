<?php

namespace Tomjn\ComposerPress;

class ComposerPress {

	private $model = null;
	public function __construct( Model $model ) {
		$this->model = $model;
	}

	public function run() {
		add_action( 'admin_menu', array( $this, 'on_admin_menu' ) );
	}

	public function on_admin_menu() {
		add_submenu_page( 'tools.php', 'Composer.json', 'Composer.json', 'manage_options', 'composer-json-page', array( $this, 'options_page' ) );
	}

	function options_page() {
		$this->fill_model();
		echo '<div class="wrap">';
		echo '<h2>Composer.json</h2>';
		echo '<style>.composerpress_json { padding:1em; background:#fff; border: 1px solid #ddd; }</style>';
		echo '<pre class="composerpress_json">';
		echo $this->model->to_json();
		echo '</pre>';
		echo '</div>';
	}

	public function fill_model() {
		$plugins = get_plugins();

		$this->model->required( 'johnpbloch/wordpress', '>='.get_bloginfo( 'version' ) );
		$this->model->required( 'php', '>=5.3.2' );

		$this->model->set_name( 'wpsite/'.sanitize_title( get_bloginfo( 'name' ) ) );
		$this->model->set_homepage( home_url() );
		$description = get_bloginfo( 'description' );
		if ( !empty( $description ) ) {
			$this->model->set_description( $description );
		}
		$this->model->set_version( get_bloginfo( 'version' ) );

		$this->model->add_repository( 'composer', 'http://wpackagist.org' );

		foreach ( $plugins as $key => $plugin_data ) {
			$path = plugin_dir_path( $key );
			$fullpath = WP_CONTENT_DIR.'/plugins/'.$path;
			$plugin = null;
			if ( file_exists( $fullpath.'.hg/' ) ) {
				$plugin = new \Tomjn\ComposerPress\Plugin\HGPlugin( $fullpath, $plugin_data );
			} else if ( file_exists( $fullpath.'.git/' ) ) {
				$plugin = new \Tomjn\ComposerPress\Plugin\GitPlugin( $fullpath, $plugin_data );
			} else if ( file_exists( $fullpath.'.svn/' ) ) {
				$plugin = new \Tomjn\ComposerPress\Plugin\SVNPlugin( $fullpath, $plugin_data );
			} else {
				$plugin = new \Tomjn\ComposerPress\Plugin\WPackagistPlugin( $fullpath, $plugin_data );
			}
			if ( $plugin != null ) {
				$this->model->add_plugin( $plugin );
			}
		}
	}
}




