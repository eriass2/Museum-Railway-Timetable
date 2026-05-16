# Developer Guide – Museum Railway Timetable

En ingång för utvecklare. Läs detta först.

---

## Snabbstart

```sh
git clone <repo>
cd Museum-Railway-Timetable
composer install

composer check
```

---

## Dokumentation

| Dokument | Innehåll |
|----------|----------|
| [README.md](README.md) | Index |
| [STYLE_GUIDE.md](STYLE_GUIDE.md) | PHP, CSS, JS, `.mrt-*` |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Lager, testning, `inc/`-struktur |
| [DATA_MODEL.md](DATA_MODEL.md) | Post types, relationer |
| [SHORTCODES.md](SHORTCODES.md) | Alla shortcodes |
| [ACCESSIBILITY.md](ACCESSIBILITY.md) | WCAG + release-rökning |
| [ADMIN_WORKFLOW.md](ADMIN_WORKFLOW.md) | Skapa tidtabell i admin |
| [PHP_INSTALL_WINDOWS.md](PHP_INSTALL_WINDOWS.md) | PHP/Composer på Windows |
| [assets/CSS_STRUCTURE.md](../assets/CSS_STRUCTURE.md) | CSS-moduler |

---

## Kommandon och CI

| Kommando | Vad det gör |
|----------|-------------|
| `composer check` | Kör lokal snabbkontroll: plugin-check, PHPStan, PHPUnit och JS-tester |
| `composer plugin-check` | `php scripts/validate.php` – filer, syntax, ABSPATH, text domain |
| `composer test` | PHPUnit |
| `composer test:js` | Node-baserade JS-tester för delade assets |
| `composer phpstan` / `phpcs` / `lint` | Statisk analys + WPCS |

GitHub Actions (`.github/workflows/ci.yml`) kör `composer check` vid push/PR. Dependabot uppdaterar Composer månadsvis.

**PHPStan:** `phpstan-wordpress`, config `phpstan.neon`. **PHPCS:** WPCS; prefix `MRT_` kan flaggas – bedöm mot [STYLE_GUIDE.md](STYLE_GUIDE.md). `composer phpcbf` fixar formatering där det går.

**Pre-commit:** kräver bash (WSL/Git Bash på Windows) – `pre-commit install`.

---

## Lokala testlägen

### Snabbtest utan WordPress

Kör detta för ren PHP/JS-logik och statisk kontroll. Det kräver bara PHP 8.2+, Composer och Node:

```sh
composer install
composer check
```

### Full WordPress-testning

Använd Docker när du behöver klicka i admin, prova shortcodes eller testa dataflöden i en riktig WordPress-installation:

```sh
docker compose up -d --build
```

- Webbplats: <http://localhost:8080>
- Admin: <http://localhost:8080/wp-admin>
- Login: `admin` / `admin`

Kör Composer-kommandon i Docker om datorn saknar lokal PHP/Composer:

```sh
docker compose run --rm composer install
docker compose run --rm composer check
```

Local by Flywheel kan fortfarande användas via `local/deploy.ps1`, men Docker är den portabla standarden för manuell WordPress-testning.

### Manuell smoke-checklista i WordPress

Kör detta efter `docker compose up -d --build` när ändringen påverkar admin, shortcodes eller dataflöden:

- Logga in på <http://localhost:8080/wp-admin> med `admin` / `admin`.
- Kontrollera att menyn **Railway Timetable** syns och att pluginet är aktivt.
- Skapa minst två stationer, en rutt, en tidtabell och en trip/service.
- Lägg in stopptider för trippen och spara utan felmeddelanden.
- Skapa eller öppna en sida med relevant shortcode, till exempel `[museum_journey_wizard]` eller `[museum_journey_planner]`.
- Vid behov: **Import demo data** på dashboard eller **Component demo page** för alla shortcodes.
- Kontrollera frontend: formulär/tabell visas, sökning går att köra och inga PHP-fel syns.
- Kontrollera loggar vid fel: `docker compose logs wordpress`.

---

## Checklista före deploy

- [ ] `composer check` grönt
- [ ] Manuell smoke-checklista i WordPress är genomförd vid UI/dataflödesändringar
- [ ] [ACCESSIBILITY.md](ACCESSIBILITY.md) – kort rökning vid UI-ändringar
- [ ] Nya strängar: `languages/*.po` → kompilera `.mo` (Poedit eller `msgfmt … -o …/sv_SE.mo`)
- [ ] `docker compose up -d --build` eller `.\local\deploy.ps1` om du testar i full WordPress

---

## Krav

- PHP 8.2+ (plugin), Composer, WordPress 6.0+
- Windows utan PHP: [PHP_INSTALL_WINDOWS.md](PHP_INSTALL_WINDOWS.md)

---

## Konstanter (`inc/constants.php`)

`MRT_TEXT_DOMAIN`, `MRT_POST_TYPE_*`, `MRT_TAXONOMY_TRAIN_TYPE`, `MRT_POST_TYPES`.

PHP: `declare(strict_types=1)` där det passar.
