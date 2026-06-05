# Priszoner (Lennakatten taxa 2026)

Stationer tilldelas priszoner **1–3** enligt Lennakatten. Priserna i admin och reseplaneraren använder max **tre zoner** (zon 4 i prismatrisen = samma pris som zon 3).

**Källa:** [lennakatten.se/biljetter](https://www.lennakatten.se/biljetter/) och zonöversikten nedan (Lennakatten grafisk taxa 2026).

![Priszoner längs Uppsala–Faringe-linjen](design/reference/lennakatten-zoner-taxa-2026.png)

## Stationer per zon

| Zon | Stationer |
|-----|-----------|
| **1** | Uppsala Östra, Fyrislund, Årsta, Skölsta, Bärby |
| **1 och 2** (gräns) | **Gunsta** |
| **2** | Marielund, Lövstahagen, Selknä, Löt, Länna, Fjällnora |
| **2 och 3** (gräns) | **Almunge** |
| **3** | Moga, Faringe, Linnés Hammarby |

Endast **Gunsta** och **Almunge** har två zoner (gränsstationer mellan prisband). Övriga stationer har exakt en zon.

## Implementation

- **PHP-defaults:** `MRT_default_station_price_zones_by_title()` i `inc/domain/pricing/prices.php`
- **Per station (meta/CSV):** `price_zones` — se [CSV_FORMAT.md](CSV_FORMAT.md)
- **Zonberäkning för resa:** `inc/domain/pricing/price-rules.php` — lägsta giltiga zontal längs betjänade hållplatser på **utresan**. Tur och retur använder samma zonband som utresan (A→B); återresan höjer inte zontalet.

## CSV-exempel (`stations.csv`)

```csv
station_code,name,...,price_zones
uppsala-ostra,"Uppsala Östra",...,1
gunsta,Gunsta,...,"1,2"
almunge,Almunge,...,"2,3"
marielund,Marielund,...,2
```

Tom `price_zones` → titel-default ovan. Gränsstationer: kommaseparerade, max två värden.

## Backlog / TODO

### Konfigurerbar prisstruktur (inte bara belopp)

**Status:** Ej påbörjad.

Idag kan administratören ändra **prisbelopp** i prismatrisen (`mrt_price_matrix`), men **strukturen** är hårdkodad:

| Dimension | Nu | Var i kod |
|-----------|-----|-----------|
| **Antal zoner** | 1–4 kolumner i admin, max 3 används vid lookup | `MRT_price_zone_keys()`, `MRT_price_zone_cap()` i `inc/domain/pricing/prices.php` |
| **Biljettyper** | Enkel, retur, dagskort | `MRT_price_ticket_type_keys()` / `PRICE_TYPE_KEYS` |
| **Kundkategorier** | Vuxen, barn 4–15, barn 0–3, student/pensionär | `MRT_price_category_keys()` / `PRICE_CAT_KEYS` |

**Mål:** Kunna lägga till, ta bort och byta namn på zoner, biljettyper och passagerarkategorier via admin (och CSV), utan kodändring i PHP/Vue.

**Påverkar ungefär:** admin `PricesPage.vue`, REST `settings-admin.php`, `prices.php`, `price-rules.php`, `MrtPriceTable.vue`, `useTripPrices`, PDF/sammanfattning, import/export, etiketter/i18n, eftermiddags-retur som specialfall.

**Relaterat:** Eftermiddags-returpriser (`MRT_get_afternoon_return_prices()`) är också hårdkodade — bör ingå i samma konfigurationsmodell eller dokumenteras som undantag.
