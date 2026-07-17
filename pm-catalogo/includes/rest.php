<?php
/**
 * REST API per il frontend (React o altro).
 *
 * Endpoint:
 *   GET /wp-json/pm/v1/linee                       → alberatura linee con conteggi
 *   GET /wp-json/pm/v1/sistemi?linea=waterproof    → sistemi (campi ACF risolti, prodotti embedded)
 *   GET /wp-json/pm/v1/sistemi/{id|codice}         → singolo sistema completo
 *   GET /wp-json/pm/v1/prodotti?linea=…&categoria=…→ prodotti con campi risolti + sistemi che li usano
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', function () {

	register_rest_route( 'pm/v1', '/linee', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function () {
			$terms = get_terms( array(
				'taxonomy'   => 'pm_linea',
				'hide_empty' => false,
			) );
			return array_map( function ( $t ) {
				return array(
					'id'     => $t->term_id,
					'slug'   => $t->slug,
					'nome'   => $t->name,
					'parent' => $t->parent,
					'count'  => $t->count,
				);
			}, $terms );
		},
	) );

	register_rest_route( 'pm/v1', '/sistemi', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function ( WP_REST_Request $req ) {
			$args = array(
				'post_type'      => 'pm_sistema',
				'posts_per_page' => -1,
				'orderby'        => 'meta_value',
				'meta_key'       => 'codice',
				'order'          => 'ASC',
			);
			$tax_query = array();
			if ( $req['linea'] ) {
				$tax_query[] = array(
					'taxonomy' => 'pm_linea',
					'field'    => 'slug',
					'terms'    => sanitize_title( $req['linea'] ),
				);
			}
			if ( $req['problema'] ) {
				$tax_query[] = array(
					'taxonomy' => 'pm_problema',
					'field'    => 'slug',
					'terms'    => sanitize_title( $req['problema'] ),
				);
			}
			if ( $req['destinazione'] ) {
				$tax_query[] = array(
					'taxonomy' => 'pm_destinazione',
					'field'    => 'slug',
					'terms'    => sanitize_title( $req['destinazione'] ),
				);
			}
			if ( $tax_query ) {
				$args['tax_query'] = $tax_query;
			}
			return array_map( 'pm_cat_serialize_sistema', get_posts( $args ) );
		},
	) );

	register_rest_route( 'pm/v1', '/sistemi/(?P<id>[\w\.\-]+)', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function ( WP_REST_Request $req ) {
			$post = pm_cat_find_by_id_or_codice( 'pm_sistema', $req['id'] );
			if ( ! $post ) {
				return new WP_Error( 'not_found', 'Sistema non trovato', array( 'status' => 404 ) );
			}
			return pm_cat_serialize_sistema( $post, true );
		},
	) );

	register_rest_route( 'pm/v1', '/prodotti', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function ( WP_REST_Request $req ) {
			$args = array(
				'post_type'      => 'pm_prodotto',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			);
			$tax_query = array();
			if ( $req['linea'] ) {
				$tax_query[] = array(
					'taxonomy' => 'pm_linea',
					'field'    => 'slug',
					'terms'    => sanitize_title( $req['linea'] ),
				);
			}
			if ( $req['categoria'] ) {
				$tax_query[] = array(
					'taxonomy' => 'pm_categoria_prodotto',
					'field'    => 'slug',
					'terms'    => sanitize_title( $req['categoria'] ),
				);
			}
			if ( $tax_query ) {
				$args['tax_query'] = $tax_query;
			}
			return array_map( 'pm_cat_serialize_prodotto', get_posts( $args ) );
		},
	) );
} );

function pm_cat_find_by_id_or_codice( $post_type, $id ) {
	if ( is_numeric( $id ) ) {
		$post = get_post( (int) $id );
		return ( $post && $post->post_type === $post_type ) ? $post : null;
	}
	$posts = get_posts( array(
		'post_type'      => $post_type,
		'posts_per_page' => 1,
		'meta_key'       => 'codice',
		'meta_value'     => strtoupper( str_replace( '-', '.', $id ) ),
	) );
	return $posts ? $posts[0] : null;
}

function pm_cat_terms_slugs( $post_id, $tax ) {
	$terms = get_the_terms( $post_id, $tax );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return array();
	}
	return array_map( function ( $t ) {
		return array( 'slug' => $t->slug, 'nome' => $t->name );
	}, $terms );
}

function pm_cat_serialize_sistema( $post, $embed_prodotti = false ) {
	$id     = $post->ID;
	$fields = function_exists( 'get_fields' ) ? (array) get_fields( $id ) : array();

	$stratigrafia = array();
	if ( ! empty( $fields['stratigrafia'] ) ) {
		foreach ( $fields['stratigrafia'] as $strato ) {
			$prodotto_id = is_array( $strato['prodotto'] ) ? (int) reset( $strato['prodotto'] ) : (int) $strato['prodotto'];
			$strato['prodotto'] = $prodotto_id ? array(
				'id'   => $prodotto_id,
				'nome' => get_the_title( $prodotto_id ),
				'slug' => get_post_field( 'post_name', $prodotto_id ),
			) : null;
			$stratigrafia[] = $strato;
		}
	}
	$fields['stratigrafia'] = $stratigrafia;

	$data = array(
		'id'           => $id,
		'slug'         => $post->post_name,
		'titolo'       => get_the_title( $id ),
		'excerpt'      => get_the_excerpt( $id ),
		'immagine'     => get_the_post_thumbnail_url( $id, 'large' ),
		'linea'        => pm_cat_terms_slugs( $id, 'pm_linea' ),
		'problemi'     => pm_cat_terms_slugs( $id, 'pm_problema' ),
		'destinazioni' => pm_cat_terms_slugs( $id, 'pm_destinazione' ),
		'campi'        => $fields,
	);

	if ( $embed_prodotti ) {
		$prodotti = array();
		foreach ( $stratigrafia as $strato ) {
			if ( ! empty( $strato['prodotto']['id'] ) ) {
				$prodotti[ $strato['prodotto']['id'] ] = true;
			}
		}
		$data['prodotti'] = array_map( function ( $pid ) {
			return pm_cat_serialize_prodotto( get_post( $pid ) );
		}, array_keys( $prodotti ) );
	}

	return $data;
}

function pm_cat_serialize_prodotto( $post ) {
	$id     = $post->ID;
	$fields = function_exists( 'get_fields' ) ? (array) get_fields( $id ) : array();

	return array(
		'id'           => $id,
		'slug'         => $post->post_name,
		'nome'         => get_the_title( $id ),
		'excerpt'      => get_the_excerpt( $id ),
		'immagine'     => get_the_post_thumbnail_url( $id, 'large' ),
		'linea'        => pm_cat_terms_slugs( $id, 'pm_linea' ),
		'categorie'    => pm_cat_terms_slugs( $id, 'pm_categoria_prodotto' ),
		'destinazioni' => pm_cat_terms_slugs( $id, 'pm_destinazione' ),
		'campi'        => $fields,
		'sistemi'      => pm_cat_sistemi_che_usano( $id ),
	);
}

/**
 * Sistemi che usano un prodotto nella stratigrafia (relazione inversa).
 */
function pm_cat_sistemi_che_usano( $prodotto_id ) {
	$sistemi = get_posts( array(
		'post_type'      => 'pm_sistema',
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'     => 'stratigrafia_$_prodotto',
				'compare' => 'LIKE',
				'value'   => '"' . $prodotto_id . '"',
			),
		),
	) );
	// Il meta_key con $ jolly richiede il filtro sottostante.
	return array_map( function ( $p ) {
		return array(
			'id'     => $p->ID,
			'codice' => get_post_meta( $p->ID, 'codice', true ),
			'titolo' => get_the_title( $p->ID ),
			'slug'   => $p->post_name,
		);
	}, $sistemi );
}

/**
 * Permette meta_query su sub-field dei repeater (stratigrafia_0_prodotto, _1_…) con la chiave jolly "$".
 */
add_filter( 'posts_where', function ( $where ) {
	if ( strpos( $where, 'stratigrafia_$_prodotto' ) !== false ) {
		$where = str_replace( "meta_key = 'stratigrafia_\$_prodotto'", "meta_key LIKE 'stratigrafia_%_prodotto'", $where );
	}
	return $where;
} );
