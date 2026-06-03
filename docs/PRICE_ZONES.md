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
- **Zonberäkning för resa:** `inc/domain/pricing/price-rules.php` — lägsta giltiga zontal längs betjänade hållplatser

## CSV-exempel (`stations.csv`)

```csv
station_code,name,...,price_zones
uppsala-ostra,"Uppsala Östra",...,1
gunsta,Gunsta,...,"1,2"
almunge,Almunge,...,"2,3"
marielund,Marielund,...,2
```

Tom `price_zones` → titel-default ovan. Gränsstationer: kommaseparerade, max två värden.
