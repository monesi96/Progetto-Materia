<?php
/**
 * Campi ACF del CPT Sistema.
 * Il sistema è la SOLUZIONE: risponde a un problema con una stratigrafia di prodotti.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', function () {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_pm_sistema',
		'title'    => 'Dati Sistema',
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'pm_sistema',
				),
			),
		),
		'position' => 'normal',
		'fields'   => array(

			/* ---------------- IDENTITÀ ---------------- */
			array( 'key' => 'f_pms_tab_id', 'label' => 'Identità', 'type' => 'tab' ),
			array(
				'key'  => 'f_pms_codice', 'name' => 'codice', 'label' => 'Codice sistema',
				'type' => 'text',
				'instructions' => 'Es. PS.02, WP.04, C.01, CP.02. È l\'identificativo ufficiale del sistema.',
			),
			array(
				'key'  => 'f_pms_nome_tecnico', 'name' => 'nome_tecnico', 'label' => 'Nome tecnico esteso',
				'type' => 'textarea', 'rows' => 2,
				'instructions' => 'La denominazione completa da catalogo. Il titolo del post può restare più corto/commerciale.',
			),
			array(
				'key'  => 'f_pms_descrizione', 'name' => 'descrizione', 'label' => 'Descrizione',
				'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0,
			),

			/* ---------------- PROBLEMA → SOLUZIONE ---------------- */
			array( 'key' => 'f_pms_tab_ps', 'label' => 'Problema → Soluzione', 'type' => 'tab' ),
			array(
				'key'  => 'f_pms_problema_titolo', 'name' => 'problema_titolo', 'label' => 'Problema — titolo',
				'type' => 'text',
				'instructions' => 'Il problema applicativo in linguaggio cliente. Es. "Il terrazzo infiltra e rifare tutto costa troppo".',
			),
			array(
				'key'  => 'f_pms_problema_testo', 'name' => 'problema_testo', 'label' => 'Problema — testo',
				'type' => 'textarea', 'rows' => 3,
			),
			array(
				'key'  => 'f_pms_soluzione_testo', 'name' => 'soluzione_testo', 'label' => 'Soluzione — testo',
				'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0,
				'instructions' => 'Come il sistema risolve il problema.',
			),
			array(
				'key'  => 'f_pms_vantaggi', 'name' => 'vantaggi', 'label' => 'Vantaggi',
				'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Aggiungi vantaggio',
				'sub_fields' => array(
					array( 'key' => 'f_pms_v_t', 'name' => 'testo', 'label' => 'Vantaggio', 'type' => 'text' ),
				),
			),

			/* ---------------- STRATIGRAFIA ---------------- */
			array( 'key' => 'f_pms_tab_strat', 'label' => 'Stratigrafia', 'type' => 'tab' ),
			array(
				'key'  => 'f_pms_stratigrafia', 'name' => 'stratigrafia', 'label' => 'Strati / Step',
				'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Aggiungi strato',
				'instructions' => 'Dal basso verso l\'alto: ogni strato è uno step applicativo con il suo prodotto.',
				'sub_fields' => array(
					array( 'key' => 'f_pms_s_step', 'name' => 'step', 'label' => 'Step', 'type' => 'text', 'wrapper' => array( 'width' => '15' ), 'instructions' => 'Es. 1, 2, 3…' ),
					array( 'key' => 'f_pms_s_ruolo', 'name' => 'ruolo', 'label' => 'Ruolo', 'type' => 'text', 'wrapper' => array( 'width' => '25' ), 'instructions' => 'Es. Primer, Corpo, Finitura, Top protettivo.' ),
					array(
						'key'  => 'f_pms_s_prodotto', 'name' => 'prodotto', 'label' => 'Prodotto',
						'type' => 'relationship', 'post_type' => array( 'pm_prodotto' ),
						'max'  => 1, 'return_format' => 'id', 'wrapper' => array( 'width' => '35' ),
					),
					array( 'key' => 'f_pms_s_consumo', 'name' => 'consumo', 'label' => 'Consumo', 'type' => 'text', 'wrapper' => array( 'width' => '25' ), 'instructions' => 'Es. 0,3 kg/m² per mano.' ),
					array( 'key' => 'f_pms_s_note', 'name' => 'note', 'label' => 'Note applicative', 'type' => 'textarea', 'rows' => 2 ),
				),
			),
			array(
				'key'  => 'f_pms_img_stratigrafia', 'name' => 'immagine_stratigrafia', 'label' => 'Immagine stratigrafia (esploso)',
				'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium',
			),

			/* ---------------- DATI TECNICI ---------------- */
			array( 'key' => 'f_pms_tab_dt', 'label' => 'Dati tecnici', 'type' => 'tab' ),
			array(
				'key'  => 'f_pms_spessore', 'name' => 'spessore', 'label' => 'Spessore totale',
				'type' => 'text', 'instructions' => 'Es. 0,8–1,0 mm.',
			),
			array(
				'key'  => 'f_pms_dati_tecnici', 'name' => 'dati_tecnici', 'label' => 'Altri dati tecnici',
				'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Aggiungi dato',
				'sub_fields' => array(
					array( 'key' => 'f_pms_dt_l', 'name' => 'etichetta', 'label' => 'Etichetta', 'type' => 'text' ),
					array( 'key' => 'f_pms_dt_v', 'name' => 'valore', 'label' => 'Valore', 'type' => 'text' ),
				),
			),
			array(
				'key'  => 'f_pms_certificazioni', 'name' => 'certificazioni', 'label' => 'Certificazioni',
				'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Aggiungi certificazione',
				'sub_fields' => array(
					array( 'key' => 'f_pms_c_n', 'name' => 'nome', 'label' => 'Nome', 'type' => 'text' ),
				),
			),

			/* ---------------- MEDIA ---------------- */
			array( 'key' => 'f_pms_tab_media', 'label' => 'Media', 'type' => 'tab' ),
			array(
				'key'  => 'f_pms_gallery', 'name' => 'gallery', 'label' => 'Galleria realizzazioni',
				'type' => 'gallery', 'return_format' => 'id',
			),
			array(
				'key'  => 'f_pms_scheda_pdf', 'name' => 'scheda_pdf', 'label' => 'Scheda sistema (PDF)',
				'type' => 'file', 'return_format' => 'url', 'mime_types' => 'pdf',
			),
		),
	) );
} );
