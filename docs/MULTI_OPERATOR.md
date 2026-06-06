# Multi-operator – plan och status

**Datum:** 2026-06  
**Princip:** Lennakatten är **referensoperatör** (dev-fixture, tester, designexempel), inte runtime-default för andra föreningar.

Beslut: [REBUILD_PRODUCT_DECISIONS.md](REBUILD_PRODUCT_DECISIONS.md) §11. Onboarding: [OPERATOR_ONBOARDING.md](OPERATOR_ONBOARDING.md).

---

## Tier A — klar

| # | Uppgift | Status |
|---|---------|--------|
| A1 | Neutral wizard-rubrik + `route_title` | **Klar** |
| A2 | Tom pris-default | **Klar** |
| A3 | Ingen titel-fallback priszoner | **Klar** |
| A4 | Ingen titel-fallback tågbyte; CSV vid Lennakatten-import | **Klar** |
| A5 | Neutral `--mrt-*` default + valfri Lennakatten brand pack | **Klar** |
| A6 | Onboarding-dokumentation | **Klar** — [OPERATOR_ONBOARDING.md](OPERATOR_ONBOARDING.md) |

## Tier B — klar

| # | Uppgift | Status |
|---|---------|--------|
| B1 | Train change i admin + REST + CSV | **Klar** |
| B2 | `operator_name`, global `ticket_url` i settings | **Klar** |
| B3 | Inställning: eftermiddagsgräns | **Klar** — `afternoon_return_threshold_minutes` |
| B4 | Inställning: max byten (UI) | **Klar** — `max_transfers` |
| B5 | Operatörshandbok (meta-fält) | **Klar** — [OPERATOR_ONBOARDING.md](OPERATOR_ONBOARDING.md) §7 |

---

## Kvar (ej Tier B)

Valfria framtida förbättringar — inte blockerande för ny operatör:

| Område | Nuvarande läge |
|--------|----------------|
| Resesökningspoäng | Filter `mrt_journey_score_weights`, ingen admin-UI |
| Tidtabellsfärger | Heuristik från titel/kod (grön/gul/…), inte per tidtabell |
| Tågtypikoner | Fast uppsättning steam/diesel/railbus/bus |
| Eftermiddags-returpriser (belopp) | Redigerbart i Priser; defaultbelopp i schema kan vara operatörsspecifika |
| `mrt_transfer_priority` | Meta/filter, ej admin/CSV |
| Dokumentation | [BRAND_UI.md](design/BRAND_UI.md) / [STYLE_GUIDE.md](STYLE_GUIDE.md) beskriver fortfarande Lennakatten som primär profil |
