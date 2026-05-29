# Dokumentation

**[DEVELOPER.md](DEVELOPER.md)** – snabbstart, Docker, kommandon, deploy-checklista.

## Produkt och rebuild

| Dokument | Innehåll |
|----------|----------|
| [REBUILD_SKETCH.md](REBUILD_SKETCH.md) | Målbild, MVP, cleanup-status |
| [REBUILD_PRODUCT_DECISIONS.md](REBUILD_PRODUCT_DECISIONS.md) | Beslut: wizard-only, shortcodes, import |
| [REBUILD_RULES.md](REBUILD_RULES.md) | **Primär regelbok** – arkitektur, design, kvalitet vid ny kod |
| [REBUILD_MODULE_MAP.md](REBUILD_MODULE_MAP.md) | Modulansvar och testkrav |
| [REBUILD_CLEANUP_INVENTORY.md](REBUILD_CLEANUP_INVENTORY.md) | Filklassning (`keep` / `move` / `done`) |

Designreferens: [mockups/](mockups/) och [mockups/DESIGN_TOKENS.md](mockups/DESIGN_TOKENS.md). Tågtypsikoner: `assets/icons/train-types/`.

## Daglig utveckling

| Dokument | Innehåll |
|----------|----------|
| [ARCHITECTURE.md](ARCHITECTURE.md) | Lager, bootstrap, `inc/`-struktur, testning |
| [STYLE_GUIDE.md](STYLE_GUIDE.md) | PHP/CSS/JS-konventioner (`.mrt-*`, `MRT_*`, säkerhet) |
| [DATA_MODEL.md](DATA_MODEL.md) | Post types, meta, `mrt_stoptimes`, relationer |
| [CSV_FORMAT.md](CSV_FORMAT.md) | Import/export av tidtabellsdata (zip, kolumner, lägen) |
| [SHORTCODES.md](SHORTCODES.md) | Tre shortcodes: månad, översikt, wizard |
| [ADMIN_WORKFLOW.md](ADMIN_WORKFLOW.md) | Skapa tidtabell i WordPress-admin |
| [DEVELOPMENT_MODE.md](DEVELOPMENT_MODE.md) | Utvecklingsmeny, component demo, import |
| [VUE_FRONTEND.md](VUE_FRONTEND.md) | Publikt Vue-frontend (build, bundle, integration) |
| [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md) | Manuell rökning i Docker |
| [ACCESSIBILITY.md](ACCESSIBILITY.md) | WCAG-krav per modul |
| [ACCESSIBILITY_SMOKE.md](ACCESSIBILITY_SMOKE.md) | Manuell release-logg |
| [PHP_INSTALL_WINDOWS.md](PHP_INSTALL_WINDOWS.md) | PHP och Composer på Windows |

**Kodregler:** Följ [REBUILD_RULES.md](REBUILD_RULES.md) för arkitektur och frontend-design; [STYLE_GUIDE.md](STYLE_GUIDE.md) för namngivning, escape/sanitize och filkonventioner.

Bidrag: [../CONTRIBUTING.md](../CONTRIBUTING.md).
