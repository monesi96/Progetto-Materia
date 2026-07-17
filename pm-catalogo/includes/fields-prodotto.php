<?php
/**
 * Campi ACF del CPT Prodotto.
 * Il prodotto è l'unità fisica a listino, usata dai sistemi negli step della stratigrafia.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', function () {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_pm_prodotto',
		'title'    => 'Dati Prodotto',
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'pm_prodotto',
				),
			),
		),
		'position' => 'normal',
		'fields'   => array(

			/* ---------------- IDENTITÀ ---------------- */
			array( 'key' => 'f_pmp_tab_id', 'label' => 'Identità', 'type' => 'tab' ),
			array(
				'key'  => 'f_pmp_codice', 'name' => 'codice', 'label' => 'Codice prodotto',
				'type' => 'text', 'instructions' => 'Codice interno / listino.',
			),
			array(
				'key'  => 'f_pmp_sottotitolo', 'name' => 'sottotitolo', 'label' => 'Sottotitolo / claim',
				'type' => 'text', 'instructions' => 'Es. "Resina epossidica bicomponente all\'acqua".',
			),
			array(
				'key'  => 'f_pmp_descrizione', 'name' => 'descrizione', 'label' => 'Descrizione',
				'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0,
			),
			array(
				'key'  => 'f_pmp_caratteristiche', 'name' => 'caratteristiche', 'label' => 'Caratteristiche (bullet)',
				'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Aggiungi caratteristica',
				'sub_fields' => array(
					array( 'key' => 'f_pmp_car_t', 'name' => 'testo', 'label' => 'Testo', 'type' => 'text' ),
				),
			),

			/* ---------------- DATI TECNICI ---------------- */
			array( 'key' => 'f_pmp_tab_dt', 'label' => 'Dati tecnici', 'type' => 'tab' ),
			array( 'key' => 'f_pmp_resa', 'name' => 'resa', 'label' => 'Resa / consumo', 'type' => 'text', 'instructions' => 'Es. 8–10 m²/L per mano.' ),
			array( 'key' => 'f_pmp_diluizione', 'name' => 'diluizione', 'label' => 'Diluizione', 'type' => 'text' ),
			array( 'key' => 'f_pmp_essiccazione', 'name' => 'essiccazione', 'label' => 'Essiccazione', 'type' => 'text' ),
			array(
				'key'  => 'f_pmp_dati_tecnici', 'name' => 'dati_tecnici', 'label' => 'Altri dati tecnici',
				'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Aggiungi dato',
				'sub_fields' => array(
					array( 'key' => 'f_pmp_dt_l', 'name' => 'etichetta', 'label' => 'Etichetta', 'type' => 'text' ),
					array( 'key' => 'f_pmp_dt_v', 'name' => 'valore', 'label' => 'Valore', 'type' => 'text' ),
				),
			),
			array(
				'key'  => 'f_pmp_certificazioni', 'name' => 'certificazioni', 'label' => 'Certificazioni',
				'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Aggiungi certificazione',
				'sub_fields' => array(
					array( 'key' => 'f_pmp_c_n', 'name' => 'nome', 'label' => 'Nome', 'type' => 'text' ),
				),
			),

			/* ---------------- LISTINO ---------------- */
			array( 'key' => 'f_pmp_tab_listino', 'label' => 'Listino', 'type' => 'tab' ),
			array(
				'key'  => 'f_pmp_confezioni', 'name' => 'confezioni', 'label' => 'Varianti a listino',
				'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Aggiungi variante',
				'instructions' => 'Una riga per variante di listino (formato × colore/base). Prezzi in Euro, IVA esclusa, franco fabbrica.',
				'sub_fields' => array(
					array( 'key' => 'f_pmp_cf_c', 'name' => 'codice', 'label' => 'Codice', 'type' => 'text' ),
					array( 'key' => 'f_pmp_cf_f', 'name' => 'formato', 'label' => 'Formato', 'type' => 'text' ),
					array( 'key' => 'f_pmp_cf_col', 'name' => 'colore', 'label' => 'Colore / Base', 'type' => 'text' ),
					array( 'key' => 'f_pmp_cf_comp', 'name' => 'componenti', 'label' => 'Componenti', 'type' => 'text' ),
					array( 'key' => 'f_pmp_cf_p', 'name' => 'prezzo', 'label' => 'Prezzo €', 'type' => 'text' ),
					array( 'key' => 'f_pmp_cf_min', 'name' => 'pezzi_minimo', 'label' => 'Pz. minimo', 'type' => 'text' ),
					array( 'key' => 'f_pmp_cf_pal', 'name' => 'pallet', 'label' => 'Pallet', 'type' => 'text' ),
					array( 'key' => 'f_pmp_cf_note', 'name' => 'note', 'label' => 'Note', 'type' => 'text' ),
				),
			),

			/* ---------------- COLORI ---------------- */
			array( 'key' => 'f_pmp_tab_colori', 'label' => 'Colori', 'type' => 'tab' ),
			array(
				'key'  => 'f_pmp_tintometro', 'name' => 'tintometro', 'label' => 'Colorabile a tintometro',
				'type' => 'true_false', 'ui' => 1,
			),
			array(
				'key'  => 'f_pmp_colori', 'name' => 'colori', 'label' => 'Cartella colori',
				'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Aggiungi colore',
				'sub_fields' => array(
					array( 'key' => 'f_pmp_col_n', 'name' => 'nome', 'label' => 'Nome / codice', 'type' => 'text' ),
					array( 'key' => 'f_pmp_col_i', 'name' => 'campione', 'label' => 'Campione (immagine)', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail' ),
				),
			),

			/* ---------------- DOCUMENTI ---------------- */
			array( 'key' => 'f_pmp_tab_doc', 'label' => 'Documenti', 'type' => 'tab' ),
			array(
				'key'  => 'f_pmp_scheda_tecnica', 'name' => 'scheda_tecnica', 'label' => 'Scheda tecnica (PDF)',
				'type' => 'file', 'return_format' => 'url', 'mime_types' => 'pdf',
			),
			array(
				'key'  => 'f_pmp_scheda_sicurezza', 'name' => 'scheda_sicurezza', 'label' => 'Scheda di sicurezza (PDF)',
				'type' => 'file', 'return_format' => 'url', 'mime_types' => 'pdf',
			),
		),
	) );
} );
