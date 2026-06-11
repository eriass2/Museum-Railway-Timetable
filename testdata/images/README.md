# Testbilder

Bundlade bilder för utveckling och demo — inte produktionsassets.

| Fil | Användning |
|-----|------------|
| `wizard-hero-bosshus.jpg` | Reseplanerare — hero-bakgrund (Bosshus). Sätts via `settings.csv` → `hero_background_url` i Lennakatten-fixturen. |

Bilden serveras från plugin-mappen (`MRT_URL` + relativ sökväg). Efter Lennakatten-import syns den på **Wizard-smoketest** (ej inbäddat läge). Komponentdemo använder `embedded="1"` och visar därför ingen hero-bakgrund.
