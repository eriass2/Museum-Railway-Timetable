# Dokumentation

**[DEVELOPER.md](DEVELOPER.md)** – snabbstart, Docker, kommandon, deploy-checklista, bidra.

## Produkt och regler

| Dokument | Innehåll |
|----------|----------|
| [REBUILD_PRODUCT_DECISIONS.md](REBUILD_PRODUCT_DECISIONS.md) | MVP-beslut: wizard-only, shortcodes, import |
| [MULTI_OPERATOR.md](MULTI_OPERATOR.md) | Plan: plugin för flera föreningar (Lennakatten = referens, inte default) |
| [OPERATOR_ONBOARDING.md](OPERATOR_ONBOARDING.md) | Kom igång för ny förening (import, shortcodes, tema) |
| [REBUILD_RULES.md](REBUILD_RULES.md) | **Primär regelbok** – arkitektur, design, kvalitet vid ny kod |

Tågtypsikoner: `assets/icons/train-types/`.

## Design

| Dokument | Innehåll |
|----------|----------|
| [design/BRAND_UI.md](design/BRAND_UI.md) | Lennakatten profil → plugin-UI (scope, typografi, formspråk) |
| [design/COLOR_PALETTE.md](design/COLOR_PALETTE.md) | Färgpalett och tokens (`assets/mrt-color-tokens.css`) |
| [mockups/DESIGN_TOKENS.md](mockups/DESIGN_TOKENS.md) | Wizard-specifika tokens och layout |
| [mockups/](mockups/) | Arkiverade mockups (referens) |

## Åtgärdsplaner

| Dokument | Innehåll |
|----------|----------|
| [TRAFFIC_NOTICES.md](TRAFFIC_NOTICES.md) | Trafikmeddelanden-shortcode + admin (generella meddelanden + avvikelser) |
| [WIZARD_PERFORMANCE_PLAN.md](WIZARD_PERFORMANCE_PLAN.md) | Reseplanerare – prestanda (fas 1–4) |

## Feedback / QA

Betafeedback och granskningsloggar — **inte** aktiva utvecklingsplaner.

| Dokument | Innehåll |
|----------|----------|
| [feedback/2026-06-05-reseplanerare-beta.md](feedback/2026-06-05-reseplanerare-beta.md) | Reseplanerare beta (juni 2026) — öppna deploy-verifieringar |
| [feedback/2026-06-01-granskning.md](feedback/2026-06-01-granskning.md) | Granskning juni 2026 — majoriteten åtgärdad |

## Daglig utveckling

| Dokument | Innehåll |
|----------|----------|
| [ARCHITECTURE.md](ARCHITECTURE.md) | Lager, bootstrap, `inc/`-struktur, testning |
| [STYLE_GUIDE.md](STYLE_GUIDE.md) | PHP/CSS/JS-konventioner (`.mrt-*`, `MRT_*`, säkerhet, i18n) |
| [DATA_MODEL.md](DATA_MODEL.md) | Post types, meta, `mrt_stoptimes`, relationer |
| [PRICE_ZONES.md](PRICE_ZONES.md) | Priszoner (Lennakatten), zonlookup, admin/CSV |
| [CSV_FORMAT.md](CSV_FORMAT.md) | Import/export av tidtabellsdata (zip, kolumner, lägen) |
| [SHORTCODES.md](SHORTCODES.md) | Shortcodes: månad, översikt, wizard, index |
| [REST_API.md](REST_API.md) | REST-only API — admin och publikt frontend |
| [ADMIN_WORKFLOW.md](ADMIN_WORKFLOW.md) | Skapa tidtabell i Vue-admin (steg-för-steg) |
| [DEVELOPMENT_MODE.md](DEVELOPMENT_MODE.md) | Utvecklingsmeny, component demo, import |
| [VUE_FRONTEND.md](VUE_FRONTEND.md) | Publikt Vue-frontend (build, bundle, integration) |
| [VUE_UTILS.md](VUE_UTILS.md) | Var lägger jag ny Vue-kod? (api, composables, utils) |
| [VUE_UI_COMPONENTS.md](VUE_UI_COMPONENTS.md) | Delade `Mrt*`-komponenter, tokens, regler |
| [SMOKE_CHECKLIST.md](SMOKE_CHECKLIST.md) | Manuell rökning i Docker (+ Vue/E2E) |
| [ACCESSIBILITY.md](ACCESSIBILITY.md) | WCAG-krav, checklista och release-logg |
| [PHP_INSTALL_WINDOWS.md](PHP_INSTALL_WINDOWS.md) | PHP och Composer på Windows |

**Kodregler:** Följ [REBUILD_RULES.md](REBUILD_RULES.md) för arkitektur och frontend-design; [STYLE_GUIDE.md](STYLE_GUIDE.md) för namngivning, escape/sanitize och filkonventioner.
