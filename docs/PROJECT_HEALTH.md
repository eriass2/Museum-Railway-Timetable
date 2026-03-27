# Projektstatus och kontroller

Snabb överblick över **vad som körs** och **var** – så du har koll utan att leta i flera filer.

---

## Daglig / vid ändring

| Kommando | Vad det gör |
|----------|-------------|
| `composer plugin-check` | Samma som `php scripts/validate.php` – filer, syntax, ABSPATH, text domain, CSS/JS |
| `php scripts/validate.php` | Direkt körning (kräver PHP i PATH) |
| `composer phpcs` | Kodstil (WordPress); kan flagga `MRT_`-prefix – se [DEVELOPER.md](DEVELOPER.md) |
| `composer phpstan` | Statisk analys; kräver WordPress-stubs för meningsfull utdata – se [FUTURE_WORK.md](FUTURE_WORK.md) |
| `composer lint` | `phpstan` + `phpcs` i ett steg |

---

## CI (GitHub)

Om repot ligger på GitHub körs **`.github/workflows/ci.yml`** vid push/PR mot `main` eller `master`:

- `composer install`
- `php scripts/validate.php`

Du ser status under fliken **Actions** i repot.

---

## Automatiska uppdateringar

**`.github/dependabot.yml`** skapar månadsvis PR:er för Composer-paket (phpstan, phpcs, wpcs). Granska och merga när det passar.

---

## Övrigt

| Dokument | Innehåll |
|----------|----------|
| [DEVELOPER.md](DEVELOPER.md) | Snabbstart, verktyg, begränsningar |
| [VALIDATION.md](VALIDATION.md) | Manuell checklista före release |
| [FUTURE_WORK.md](FUTURE_WORK.md) | PHPUnit, PHPStan-stubs, CHANGELOG, m.m. |
