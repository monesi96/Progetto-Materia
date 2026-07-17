<?php
/**
 * Hero parallax della homepage.
 *
 * Blocco: video full-screen (generato dalla foto di catalogo) + titolo/claim su piano
 * a velocità diversa + card delle 4 linee che entrano a profondità sfalsate.
 *
 * Uso in Elementor: widget Shortcode con [pm_hero].
 * I contenuti si gestiscono dai campi "Hero Homepage" sulla Pagina.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', function () {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_pm_hero',
		'title'    => 'Hero Homepage',
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'page',
				),
			),
		),
		'position' => 'normal',
		'fields'   => array(
			array(
				'key'  => 'f_pmh_video', 'name' => 'hero_video', 'label' => 'Video di sfondo (mp4)',
				'type' => 'file', 'return_format' => 'url', 'mime_types' => 'mp4,webm',
				'instructions' => 'Loop 6–8 s, muto. Consigliato < 4 MB (comprimi con Handbrake/ffmpeg).',
			),
			array(
				'key'  => 'f_pmh_poster', 'name' => 'hero_poster', 'label' => 'Poster (immagine di fallback)',
				'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium',
				'instructions' => 'Mostrata su mobile, connessioni lente e per chi ha ridotto le animazioni.',
			),
			array(
				'key'  => 'f_pmh_eyebrow', 'name' => 'hero_eyebrow', 'label' => 'Occhiello',
				'type' => 'text', 'default_value' => 'Chimica per l\'edilizia dal 1964',
			),
			array(
				'key'  => 'f_pmh_title', 'name' => 'hero_title', 'label' => 'Titolo (usa | per andare a capo)',
				'type' => 'text', 'default_value' => 'La materia giusta|per ogni superficie.',
			),
			array(
				'key'  => 'f_pmh_lead', 'name' => 'hero_lead', 'label' => 'Sottotitolo',
				'type' => 'textarea', 'rows' => 2,
				'default_value' => 'Sistemi certificati per proteggere, impermeabilizzare e decorare: dal problema alla soluzione, con un unico interlocutore.',
			),
			array(
				'key'  => 'f_pmh_cta1', 'name' => 'hero_cta1_label', 'label' => 'Bottone 1 — testo',
				'type' => 'text', 'default_value' => 'Trova il tuo sistema', 'wrapper' => array( 'width' => '50' ),
			),
			array(
				'key'  => 'f_pmh_cta1_url', 'name' => 'hero_cta1_url', 'label' => 'Bottone 1 — link',
				'type' => 'text', 'default_value' => '#sistemi', 'wrapper' => array( 'width' => '50' ),
			),
			array(
				'key'  => 'f_pmh_cta2', 'name' => 'hero_cta2_label', 'label' => 'Bottone 2 — testo (vuoto = nascosto)',
				'type' => 'text', 'default_value' => 'Le nostre linee', 'wrapper' => array( 'width' => '50' ),
			),
			array(
				'key'  => 'f_pmh_cta2_url', 'name' => 'hero_cta2_url', 'label' => 'Bottone 2 — link',
				'type' => 'text', 'default_value' => '#linee', 'wrapper' => array( 'width' => '50' ),
			),
			array(
				'key'  => 'f_pmh_linee', 'name' => 'hero_linee', 'label' => 'Card linee (piani parallax)',
				'type' => 'repeater', 'layout' => 'table', 'max' => 4, 'button_label' => 'Aggiungi linea',
				'instructions' => 'Le card entrano sfalsate sullo scroll. Lascia vuoto per usare le 4 linee di default.',
				'sub_fields' => array(
					array( 'key' => 'f_pmh_l_n', 'name' => 'nome', 'label' => 'Nome', 'type' => 'text' ),
					array( 'key' => 'f_pmh_l_d', 'name' => 'descrizione', 'label' => 'Descrizione breve', 'type' => 'text' ),
					array( 'key' => 'f_pmh_l_u', 'name' => 'url', 'label' => 'Link', 'type' => 'text' ),
				),
			),
		),
	) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_register_style( 'pm-hero', PM_CAT_URL . 'assets/hero.css', array(), PM_CAT_VERSION );
	wp_register_script( 'pm-hero', PM_CAT_URL . 'assets/hero.js', array(), PM_CAT_VERSION, true );
} );

add_shortcode( 'pm_hero', function () {
	if ( ! function_exists( 'get_field' ) ) {
		return '<p><strong>PM Catalogo:</strong> richiede Advanced Custom Fields PRO.</p>';
	}

	wp_enqueue_style( 'pm-hero' );
	wp_enqueue_script( 'pm-hero' );

	$video   = get_field( 'hero_video' );
	$poster  = get_field( 'hero_poster' );
	$eyebrow = get_field( 'hero_eyebrow' );
	$title   = get_field( 'hero_title' );
	$lead    = get_field( 'hero_lead' );
	$cta1    = get_field( 'hero_cta1_label' );
	$cta1u   = get_field( 'hero_cta1_url' ) ?: '#sistemi';
	$cta2    = get_field( 'hero_cta2_label' );
	$cta2u   = get_field( 'hero_cta2_url' ) ?: '#linee';
	$linee   = get_field( 'hero_linee' );

	if ( ! $linee ) {
		$linee = array(
			array( 'nome' => 'Procoating', 'descrizione' => 'Pitture, smalti e finiture antimuffa', 'url' => '/linea/procoating/' ),
			array( 'nome' => 'Prosurfaces', 'descrizione' => 'Resine per pavimentazioni industriali', 'url' => '/linea/prosurfaces/' ),
			array( 'nome' => 'Waterproof', 'descrizione' => 'Impermeabilizzanti liquidi a vista', 'url' => '/linea/waterproof/' ),
			array( 'nome' => 'Cromateria', 'descrizione' => 'Micro-resine decorative continue', 'url' => '/linea/cromateria/' ),
		);
	}

	$title_html = implode( '<br>', array_map( 'esc_html', explode( '|', (string) $title ) ) );

	ob_start();
	?>
	<section class="pm-hero" id="pm-hero">
		<div class="pm-hero__media" data-pm-depth="0.25">
			<?php if ( $video ) : ?>
				<video class="pm-hero__video" autoplay muted loop playsinline preload="metadata"
					<?php echo $poster ? 'poster="' . esc_url( $poster ) . '"' : ''; ?>>
					<source src="<?php echo esc_url( $video ); ?>" type="video/mp4">
				</video>
			<?php elseif ( $poster ) : ?>
				<img class="pm-hero__video" src="<?php echo esc_url( $poster ); ?>" alt="">
			<?php endif; ?>
			<div class="pm-hero__scrim"></div>
		</div>

		<div class="pm-hero__content" data-pm-depth="0.55">
			<?php if ( $eyebrow ) : ?>
				<p class="pm-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<h1 class="pm-hero__title"><?php echo $title_html; // già escapata riga per riga ?></h1>
			<?php if ( $lead ) : ?>
				<p class="pm-hero__lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
			<div class="pm-hero__ctas">
				<?php if ( $cta1 ) : ?>
					<a class="pm-hero__cta pm-hero__cta--solid" href="<?php echo esc_url( $cta1u ); ?>"><?php echo esc_html( $cta1 ); ?></a>
				<?php endif; ?>
				<?php if ( $cta2 ) : ?>
					<a class="pm-hero__cta pm-hero__cta--ghost" href="<?php echo esc_url( $cta2u ); ?>"><?php echo esc_html( $cta2 ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<div class="pm-hero__cards" id="linee">
			<?php foreach ( $linee as $i => $l ) : ?>
				<a class="pm-hero__card" href="<?php echo esc_url( $l['url'] ?: '#' ); ?>" data-pm-card="<?php echo (int) $i; ?>">
					<span class="pm-hero__card-name"><?php echo esc_html( $l['nome'] ); ?></span>
					<span class="pm-hero__card-desc"><?php echo esc_html( $l['descrizione'] ); ?></span>
					<span class="pm-hero__card-arrow" aria-hidden="true">→</span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
} );
