<?php
/**
 * Import del seed dati (data/prodotti.json, data/sistemi.json).
 *
 * Uso:
 *   WP-CLI:  wp pm-catalogo import
 *   Admin:   Sistemi → Importa catalogo (bottone), oppure ?pm_cat_import=1 da admin con nonce.
 *
 * L'import è idempotente: i post esistenti (match su codice, altrimenti su titolo)
 * vengono aggiornati, non duplicati.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pm_cat_import_run() {
	$report = array( 'prodotti' => 0, 'sistemi' => 0, 'errori' => array() );

	if ( ! function_exists( 'update_field' ) ) {
		$report['errori'][] = 'ACF non attivo: impossibile importare i campi.';
		return $report;
	}

	pm_cat_seed_terms();

	$map_prodotti = array(); // nome normalizzato → post_id, per collegare le stratigrafie

	foreach ( pm_cat_import_leggi_json( 'prodotti.json' ) as $p ) {
		$post_id = pm_cat_import_upsert( 'pm_prodotto', $p );
		if ( ! $post_id ) {
			$report['errori'][] = 'Prodotto non importato: ' . ( $p['titolo'] ?? '?' );
			continue;
		}
		$report['prodotti']++;
		$map_prodotti[ pm_cat_norm( $p['titolo'] ) ] = $post_id;

		pm_cat_import_campi( $post_id, $p['campi'] ?? array() );
		pm_cat_import_tassonomie( $post_id, $p );
	}

	foreach ( pm_cat_import_leggi_json( 'sistemi.json' ) as $s ) {
		$post_id = pm_cat_import_upsert( 'pm_sistema', $s );
		if ( ! $post_id ) {
			$report['errori'][] = 'Sistema non importato: ' . ( $s['titolo'] ?? '?' );
			continue;
		}
		$report['sistemi']++;

		$campi = $s['campi'] ?? array();

		// Risolve i nomi prodotto della stratigrafia in ID.
		if ( ! empty( $campi['stratigrafia'] ) ) {
			foreach ( $campi['stratigrafia'] as $i => $strato ) {
				$nome = pm_cat_norm( $strato['prodotto'] ?? '' );
				$campi['stratigrafia'][ $i ]['prodotto'] = isset( $map_prodotti[ $nome ] ) ? array( $map_prodotti[ $nome ] ) : array();
				if ( $nome && ! isset( $map_prodotti[ $nome ] ) ) {
					$report['errori'][] = sprintf( '%s: prodotto "%s" non trovato in anagrafica', $s['campi']['codice'] ?? $s['titolo'], $strato['prodotto'] );
				}
			}
		}

		pm_cat_import_campi( $post_id, $campi );
		pm_cat_import_tassonomie( $post_id, $s );
	}

	return $report;
}

function pm_cat_import_leggi_json( $file ) {
	$path = PM_CAT_PATH . 'data/' . $file;
	if ( ! file_exists( $path ) ) {
		return array();
	}
	$data = json_decode( file_get_contents( $path ), true );
	return is_array( $data ) ? $data : array();
}

function pm_cat_norm( $s ) {
	return strtolower( trim( preg_replace( '/\s+/', ' ', (string) $s ) ) );
}

/**
 * Trova (per codice ACF, poi per titolo) o crea il post. Ritorna l'ID.
 */
function pm_cat_import_upsert( $post_type, $item ) {
	$titolo = $item['titolo'] ?? '';
	$codice = $item['campi']['codice'] ?? '';
	if ( ! $titolo ) {
		return 0;
	}

	$existing = null;
	if ( $codice ) {
		$found = get_posts( array(
			'post_type'      => $post_type,
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'meta_key'       => 'codice',
			'meta_value'     => $codice,
		) );
		$existing = $found ? $found[0] : null;
	}
	if ( ! $existing ) {
		$found = get_posts( array(
			'post_type'      => $post_type,
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'title'          => $titolo,
		) );
		$existing = $found ? $found[0] : null;
	}

	$postarr = array(
		'post_type'    => $post_type,
		'post_title'   => $titolo,
		'post_status'  => 'publish',
		'post_excerpt' => $item['excerpt'] ?? '',
	);
	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		return wp_update_post( $postarr );
	}
	return wp_insert_post( $postarr );
}

function pm_cat_import_campi( $post_id, $campi ) {
	foreach ( $campi as $name => $value ) {
		update_field( $name, $value, $post_id );
	}
}

function pm_cat_import_tassonomie( $post_id, $item ) {
	$mappa = array(
		'linea'        => 'pm_linea',
		'problemi'     => 'pm_problema',
		'destinazioni' => 'pm_destinazione',
		'categorie'    => 'pm_categoria_prodotto',
	);
	foreach ( $mappa as $chiave => $tax ) {
		if ( empty( $item[ $chiave ] ) ) {
			continue;
		}
		$term_ids = array();
		foreach ( (array) $item[ $chiave ] as $nome ) {
			$term = term_exists( $nome, $tax );
			if ( ! $term ) {
				$term = wp_insert_term( $nome, $tax );
			}
			if ( ! is_wp_error( $term ) ) {
				$term_ids[] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
			}
		}
		if ( $term_ids ) {
			wp_set_object_terms( $post_id, $term_ids, $tax );
		}
	}
}

/* ---------------- WP-CLI ---------------- */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'pm-catalogo import', function () {
		$report = pm_cat_import_run();
		WP_CLI::log( sprintf( 'Prodotti importati: %d — Sistemi importati: %d', $report['prodotti'], $report['sistemi'] ) );
		foreach ( $report['errori'] as $err ) {
			WP_CLI::warning( $err );
		}
		WP_CLI::success( 'Import completato.' );
	} );
}

/* ---------------- Admin: bottone di import ---------------- */

add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-pm_sistema' !== $screen->id || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'edit.php?post_type=pm_sistema&pm_cat_import=1' ), 'pm_cat_import' );
	printf(
		'<div class="notice notice-info"><p><strong>Catalogo Progetto Materia:</strong> <a href="%s" class="button">Importa / aggiorna dal seed JSON</a> (idempotente: aggiorna i post esistenti)</p></div>',
		esc_url( $url )
	);
} );

add_action( 'admin_init', function () {
	if ( empty( $_GET['pm_cat_import'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'pm_cat_import' );
	$report = pm_cat_import_run();
	set_transient( 'pm_cat_import_report', $report, 60 );
	wp_safe_redirect( admin_url( 'edit.php?post_type=pm_sistema&pm_cat_imported=1' ) );
	exit;
} );

add_action( 'admin_notices', function () {
	if ( empty( $_GET['pm_cat_imported'] ) ) {
		return;
	}
	$report = get_transient( 'pm_cat_import_report' );
	if ( ! $report ) {
		return;
	}
	printf(
		'<div class="notice notice-success"><p>Import completato: %d prodotti, %d sistemi.%s</p></div>',
		(int) $report['prodotti'],
		(int) $report['sistemi'],
		$report['errori'] ? '<br>' . esc_html( implode( ' · ', $report['errori'] ) ) : ''
	);
} );
