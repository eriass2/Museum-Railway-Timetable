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

## Tier B (pågår)

| # | Uppgift | Status |
|---|---------|--------|
| B1 | Train change i admin + REST + CSV | **Klar** |
| B2 | `operator_name`, global `ticket_url` i settings | Todo |
| B3 | Inställning: eftermiddagsgräns | Todo |
| B4 | Inställning: max byten (UI) | Todo |
| B5 | Operatörshandbok (meta-fält) | Todo |

---

## Nästa steg

1. **B2** — operatörsnamn / biljett-URL globalt
2. **B3–B4** — affärsregler i inställningar
3. **B5** — utöka onboarding med meta-fält (hub, buss, zoner)
