# Vue utilities – var lägger jag ny kod?

Kort guide för `frontend/vue/`. REST-policy: [REST_API.md](REST_API.md).

---

## Snabbreferens

| Behöver du… | Lägg koden i… |
|-------------|----------------|
| Bygga REST-URL från PHP-config | `src/api/restUrl.ts` |
| Anropa publikt REST | `src/api/mrtRest.ts` → `mrtRestRequest({ method, path, query?, body? })` |
| Anropa admin REST | `src/admin/api/adminRest.ts` |
| Läsa mount-config från shortcode | `src/config/parseMountConfig.ts` |
| Översatt sträng från PHP | `src/utils/mrtStrings.ts` → `resolveMrtString()` |
| Vue-state kring REST (loading/error) | `src/composables/useMrtRest.ts` |
| Delad UI-komponent | `src/components/ui/` |
| Datum/tid (YMD, HH:MM, minuter) | `src/utils/datetime.ts` — wrappers: `settingsTime.ts`, `tripClock.ts` |
| Kalender/grid (månad + wizard) | `src/utils/calendarDate.ts`, `calendarGrid.ts`, `monthGrid.ts` |
| Wizard-specifik logik | `src/wizard/utils/` eller `src/wizard/composables/` |
| Admin-specifik logik | `src/admin/composables/` eller `src/admin/utils/` |
| Priser / prismatris | `src/shared/prices.ts` |

---

## Lager

```
PHP shortcode → JSON config (restUrl, strings, …)
       ↓
parseMountConfig() → typed MrtVueConfig
       ↓
App / composable → useMrtRest() eller adminFetch()
       ↓
restUrl.ts → buildMrtRestUrlFromConfig()
```

**Regel:** Klienten hårdkodar aldrig host (`localhost`, live-domän). `restUrl` kommer alltid från PHP (`MRT_rest_client_config()`).

---

## Strängar / översättningar

En gemensam resolver för text från PHP:

```ts
import { resolveMrtString } from '@/utils/mrtStrings';

resolveMrtString(config, 'loading', 'Laddar…');
```

Wizard använder samma helper via `cfgStr()` i `wizard/utils/wizardLabels.ts` (merged `wizard` + `labels` config).

Prioritet: `strings` → `wizard` → `labels` → fallback.

---

## Composables vs utils

| Typ | När |
|-----|-----|
| **Utils** (`src/utils/`, `*/utils/`) | Ren funktion, ingen Vue-state, enkel att unit-testa |
| **Composables** (`src/composables/`, `*/composables/`) | `ref`/`computed`, livscykel, delad komponentlogik |

`useMrtRest` — loading/error/run kring `mrtRestRequest`.

---

## Tester

- Utils: `frontend/vue/tests/*.test.ts` (Vitest)
- Kör: `cd frontend/vue && npm test`
- Ny ren funktion → lägg test i samma område som befintliga (t.ex. `restUrl.test.ts`, `mrtStrings.test.ts`)

---

## Förbjudet / undvik

- `fetch` med hårdkodad `/wp-json/…` utan `buildMrtRestUrlFromConfig`
- `admin-ajax.php` / `action: 'mrt_*'` (se REST_API.md)
- Duplicera sträng-uppslag — använd `resolveMrtString` / `cfgStr`

---

## Relaterat

- [frontend/vue/README.md](../frontend/vue/README.md) — build, appar, kommandon
- [REST_API.md](REST_API.md) — endpoints och PHP-config
- [ARCHITECTURE.md](ARCHITECTURE.md) — plugin-lager
