<?php
/**
 * CPT e tassonomie del catalogo.
 *
 * Gerarchia dati:
 *   pm_linea (tax gerarchica)  → Procoating / Prosurfaces / Waterproof / Cromateria (+ Cromateria Pool figlia)
 *   pm_sistema (CPT)           → la SOLUZIONE codificata (PS.01…, WP.01…, C.01…, CP.01…)
 *   pm_prodotto (CPT)          → il prodotto fisico, usato dai sistemi negli step della stratigrafia
 *   pm_problema (tax)          → il problema applicativo che il sistema risolve (driver problema→soluzione)
 *   pm_destinazione (tax)      → dove si applica (balcone, terrazzo, piscina, capannone, parete…)
 *   pm_categoria_prodotto (tax gerarchica) → famiglie prodotto (idropitture, stucchi, epossidici, guaine…)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pm_cat_register_taxonomies() {

	register_taxonomy( 'pm_linea', array( 'pm_sistema', 'pm_prodotto' ), array(
		'labels'            => array(
			'name'          => 'Linee',
			'singular_name' => 'Linea',
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'linea' ),
	) );

	register_taxonomy( 'pm_problema', array( 'pm_sistema' ), array(
		'labels'            => array(
			'name'          => 'Problemi',
			'singular_name' => 'Problema',
		),
		'hierarchical'      => false,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'problema' ),
	) );

	register_taxonomy( 'pm_destinazione', array( 'pm_sistema', 'pm_prodotto' ), array(
		'labels'            => array(
			'name'          => 'Destinazioni d\'uso',
			'singular_name' => 'Destinazione d\'uso',
		),
		'hierarchical'      => false,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'destinazione' ),
	) );

	register_taxonomy( 'pm_categoria_prodotto', array( 'pm_prodotto' ), array(
		'labels'            => array(
			'name'          => 'Categorie prodotto',
			'singular_name' => 'Categoria prodotto',
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'categoria-prodotto' ),
	) );
}
add_action( 'init', 'pm_cat_register_taxonomies', 5 );

function pm_cat_register_post_types() {

	register_post_type( 'pm_sistema', array(
		'labels'       => array(
			'name'          => 'Sistemi',
			'singular_name' => 'Sistema',
			'add_new_item'  => 'Aggiungi Sistema',
			'edit_item'     => 'Modifica Sistema',
		),
		'public'       => true,
		'menu_icon'    => 'dashicons-layout',
		'menu_position'=> 21,
		'has_archive'  => 'sistemi',
		'rewrite'      => array( 'slug' => 'sistema' ),
		'supports'     => array( 'title', 'thumbnail', 'excerpt' ),
		'show_in_rest' => true,
		'taxonomies'   => array( 'pm_linea', 'pm_problema', 'pm_destinazione' ),
	) );

	register_post_type( 'pm_prodotto', array(
		'labels'       => array(
			'name'          => 'Prodotti',
			'singular_name' => 'Prodotto',
			'add_new_item'  => 'Aggiungi Prodotto',
			'edit_item'     => 'Modifica Prodotto',
		),
		'public'       => true,
		'menu_icon'    => 'dashicons-products',
		'menu_position'=> 22,
		'has_archive'  => 'prodotti',
		'rewrite'      => array( 'slug' => 'prodotto' ),
		'supports'     => array( 'title', 'thumbnail', 'excerpt' ),
		'show_in_rest' => true,
		'taxonomies'   => array( 'pm_linea', 'pm_destinazione', 'pm_categoria_prodotto' ),
	) );
}
add_action( 'init', 'pm_cat_register_post_types', 6 );

/**
 * Termini base delle linee, creati all'attivazione.
 */
function pm_cat_seed_terms() {
	$linee = array(
		'procoating'  => 'Procoating',
		'prosurfaces' => 'Prosurfaces',
		'waterproof'  => 'Waterproof',
		'cromateria'  => 'Cromateria',
		'tintosystem' => 'Tintosystem',
		'ausiliari'   => 'Ausiliari',
	);
	$ids = array();
	foreach ( $linee as $slug => $name ) {
		$term = term_exists( $slug, 'pm_linea' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'pm_linea', array( 'slug' => $slug ) );
		}
		if ( ! is_wp_error( $term ) ) {
			$ids[ $slug ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
		}
	}
	if ( isset( $ids['cromateria'] ) && ! term_exists( 'cromateria-pool', 'pm_linea' ) ) {
		wp_insert_term( 'Cromateria Pool', 'pm_linea', array(
			'slug'   => 'cromateria-pool',
			'parent' => $ids['cromateria'],
		) );
	}
}
