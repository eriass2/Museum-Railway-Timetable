## Beskrivning

<!-- Kort vad som ändrats och varför -->

## Checklista (arkitektur)

Se [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) för bakgrund.

- [ ] **Affärslogik** ligger i `inc/functions/` (eller tydlig domänfil), inte gömd i templates/JS där det går att undvika
- [ ] **Shortcode / AJAX** är tunt: input → `MRT_*` / domän → output
- [ ] **JavaScript** duplicerar inte regler som redan finns i PHP (sökning, priser, datum)
- [ ] **Tester:** nya/ändrade rena PHP-funktioner har motsvarande uppdatering i `tests/` där det är rimligt
- [ ] **`composer test`** och `php scripts/validate.php` är gröna lokalt
