# Progetto Materia — Catalogo (plugin WordPress)

Architettura dati del catalogo progettomateria.com. Non è un plugin "grafico": modella i
**blocchi di contenuto** (linee → sistemi → prodotti, con logica problema→soluzione) e li
espone al frontend (React via REST, o Elementor via shortcode). La grafica si decide dopo.

## Modello dati

```
pm_linea (tassonomia gerarchica)
├── Procoating          (finiture murali: interni, esterni, legno/metallo)
├── Prosurfaces         (resine per pavimentazioni industriali — sistemi PS.01…PS.10)
├── Waterproof          (impermeabilizzanti — sistemi WP.01…WP.10)
├── Cromateria          (micro-resine decorative — sistemi C.01…C.03)
│   └── Cromateria Pool (piscine — CP.01, CP.02)
├── Tintosystem         (coloranti e attrezzature tintometriche)
└── Ausiliari           (sigillanti, quarzi, reti, additivi)

pm_sistema (CPT) — LA SOLUZIONE
  Il sistema risponde a un problema applicativo con una stratigrafia di prodotti.
  Campi: codice (PS.02…), nome tecnico, descrizione, problema (titolo+testo),
  soluzione, vantaggi[], stratigrafia[] = {step, ruolo, →prodotto, consumo, note},
  spessore, dati_tecnici[], certificazioni[], gallery, scheda PDF.
  Tassonomie: pm_linea, pm_problema, pm_destinazione.

pm_prodotto (CPT) — L'UNITÀ A LISTINO (livello famiglia, es. "ECO FIX", "KOLLACEM")
  Campi: codice, sottotitolo, descrizione, caratteristiche[], resa, diluizione,
  essiccazione, dati_tecnici[], certificazioni[], confezioni[] (varianti di listino:
  codice, formato, colore/base, componenti, prezzo, pz minimo, pallet, note),
  tintometro (bool), colori[], scheda tecnica PDF, scheda sicurezza PDF.
  Tassonomie: pm_linea, pm_categoria_prodotto, pm_destinazione.

pm_problema (tassonomia) — driver problema→soluzione
  Es. "terrazzo che infiltra", "pavimento industriale usurato", "umidità di risalita".
  Il frontend parte dal problema e propone i sistemi taggati.

pm_destinazione (tassonomia) — dove si applica
  Es. balcone, terrazzo, piscina, capannone, parete interna, facciata.
```

La relazione sistema→prodotto sta nella stratigrafia (campo relationship per strato);
la relazione inversa (in quali sistemi è usato un prodotto) è calcolata dalla REST API.

## REST API (per React)

```
GET /wp-json/pm/v1/linee
GET /wp-json/pm/v1/sistemi?linea=waterproof&problema=…&destinazione=…
GET /wp-json/pm/v1/sistemi/{id|codice}     es. /sistemi/WP.04 (anche wp-04)
GET /wp-json/pm/v1/prodotti?linea=…&categoria=…
```

Tutti gli endpoint restituiscono JSON con campi ACF già risolti; il singolo sistema
embedda i prodotti della stratigrafia.

## Seed dei dati

I dati estratti dai cataloghi ufficiali (PDF linee + listino Aprile26 v8.3 + PM-ST)
vivono in `data/prodotti.json` e `data/sistemi.json`.

Import (idempotente — aggiorna, non duplica):

- WP-CLI: `wp pm-catalogo import`
- Admin: bacheca → Sistemi → bottone "Importa / aggiorna dal seed JSON"

## Requisiti

- WordPress 6.x
- Advanced Custom Fields PRO
- Dopo l'attivazione: Impostazioni → Permalink → Salva (rigenera i rewrite)

## Struttura

```
pm-catalogo/
├── pm-catalogo.php            ← bootstrap
├── includes/
│   ├── post-types.php         ← CPT + tassonomie + termini base
│   ├── fields-sistema.php     ← ACF sistema
│   ├── fields-prodotto.php    ← ACF prodotto
│   ├── rest.php               ← endpoint pm/v1
│   └── import.php             ← importer seed JSON (WP-CLI + admin)
└── data/
    ├── sistemi.json           ← seed sistemi (generato dai PDF di linea)
    └── prodotti.json          ← seed prodotti (generato da listino + PM-ST)
```
