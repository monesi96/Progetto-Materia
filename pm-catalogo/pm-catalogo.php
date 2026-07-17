<?php
/**
 * Plugin Name: Progetto Materia — Catalogo
 * Description: Architettura dati del catalogo Progetto Materia: linee (Procoating, Prosurfaces, Waterproof, Cromateria), sistemi (soluzioni codificate PS/WP/C/CP) e prodotti, con logica problema→soluzione. Campi ACF via codice, REST API per frontend React, shortcode per Elementor.
 * Version:     0.1.0
 * Author:      Astrolancer
 * Text Domain: pm-catalogo
 *
 * REQUISITI: Advanced Custom Fields PRO (repeater, relationship, group).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PM_CAT_VERSION', '0.1.0' );
define( 'PM_CAT_PATH', plugin_dir_path( __FILE__ ) );
define( 'PM_CAT_URL', plugin_dir_url( __FILE__ ) );

require_once PM_CAT_PATH . 'includes/post-types.php';
require_once PM_CAT_PATH . 'includes/fields-sistema.php';
require_once PM_CAT_PATH . 'includes/fields-prodotto.php';
require_once PM_CAT_PATH . 'includes/rest.php';
require_once PM_CAT_PATH . 'includes/import.php';
require_once PM_CAT_PATH . 'includes/hero.php';

register_activation_hook( __FILE__, function () {
	pm_cat_register_taxonomies();
	pm_cat_register_post_types();
	pm_cat_seed_terms();
	flush_rewrite_rules();
} );
