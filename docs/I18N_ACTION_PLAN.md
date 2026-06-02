# Åtgärdsplan – översättningar (i18n)

Text domain: `museum-railway-timetable`.

Publikt Vue får strängar från PHP (`strings`, `wizard`, `labels`). Admin Vue får strängar via `mrtAdminVue.strings` och `adminStr()`.

## Status

| Fas | Beskrivning | Status |
|-----|-------------|--------|
| 1 | Dokumentation och wizard PHP/Vue | Klar |
| 2 | Månadskalender – legend-hints från config | Klar |
| 3 | Tidtabellsöversikt – UI-etiketter via `strings` | Klar |
| 4 | Delade komponenter (`MrtAsyncState`, tåg-fallback, REST-fel) | Klar |
| 5 | Admin Vue – `mrtAdminVue.strings` | Klar |

## Kvar (valfritt / backlog)

- Dev/QA-texter i `inc/admin/tools/*` – engelska källsträngar; svenska katalogen fyller `msgstr` från `msgid` (acceptabelt för intern QA)

## Underhåll

Efter PHP-ändringar: kör `powershell -File .\scripts\make-i18n.ps1` (WP-CLI + msgmerge + fyll svenska `msgstr`).  
`inc/assets/frontend.php` använder literal `'museum-railway-timetable'` så WP-CLI hittar strängarna (variabeln `MRT_TEXT_DOMAIN` plockas inte upp av `make-pot`).

Se även [STYLE_GUIDE.md](STYLE_GUIDE.md) §5 Översättning.
