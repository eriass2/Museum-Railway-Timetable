# Lennakatten – grafisk profil (referens)

Officiella källfiler från [lennakatten.se/grafisk-profil](https://lennakatten.se/grafisk-profil/), nedladdade för utveckling och färgavstämning i pluginet.

| Fil | Källa | Användning |
|-----|--------|------------|
| `lennakatten-grafisk-manual.pdf` | [PDF](https://lennakatten.se/wp-content/uploads/2023/04/lennakatten-grafisk-manual.pdf) | Regler, logotyp, färger, typsnitt |
| `Lennakatten-Anslag-1.dotx` | [Word-mall](https://lennakatten.se/wp-content/uploads/2023/04/Lennakatten-Anslag-1.dotx) | Anslagslayout (referens för tidtabell/affisch) |
| `Lennakatten-Anslag-2.dotx` | [Word-mall](https://lennakatten.se/wp-content/uploads/2023/04/Lennakatten-Anslag-2.dotx) | Alternativ anslagslayout |

**Profilfärger** (från webbsidan, april 2023):

| Namn | Hex | Text ovanpå |
|------|-----|-------------|
| Grön | `#296310` | vit |
| Gul/guld | `#DDD24C` | svart |
| Oliv | `#807C1C` | vit |

Typsnitt: **Open Sans** (rubriker), **Roboto** (brödtext), **Charter** (längre texter).

Trafikfärger i anslagstidtabell (GRÖN/GUL/RÖD/ORANGE) finns i [`testdata/reference-pdfs/Anslagstidtabell-2026.pdf`](../../testdata/reference-pdfs/Anslagstidtabell-2026.pdf). Implementerade som `--mrt-color-traffic-*` i [`assets/mrt-color-tokens.css`](../../assets/mrt-color-tokens.css).

**Plugin-UI:** Sammanfattning av hur profilen tillämpas i kod → [BRAND_UI.md](../BRAND_UI.md).

Uppdatera filerna vid behov om Lennakatten publicerar nya versioner på webben.
