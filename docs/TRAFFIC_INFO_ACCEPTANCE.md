# Trafikinfo UL 1:1 — acceptans och verifiering

**Relaterat:** [TRAFFIC_INFO_UL_PLAN.md](TRAFFIC_INFO_UL_PLAN.md) §14, [TODO.md](TODO.md) (`TF-H2`, `TF-G1`, `TF-G2`)

---

## Automatiserat (klart i repo)

| Kontroll | Var | Status |
|----------|-----|--------|
| `traffic_notices` mount får `alignwide` | `vue-mount-layout.php`, `VueMountTest` | ✅ |
| `MrtPublicAppShell` på trafikinfo | `TrafficNoticesApp.vue` | ✅ |
| Token enqueue (Vue + shortcode) | `traffic-info-tokens.php` | ✅ |
| UL-layout E2E + screenshots | `traffic-notices-ul-layout.spec.ts` | ✅ |
| Edge: bara `upcoming` | `traffic-notices-mount.spec.ts`, `buildUpcomingOnlyDisruptionFeedPayload` | ✅ |

---

## TF-H2 — manuell check på test3 (Lennakatten-tema)

Kör på `/trafikstorningar` eller sida med shortcode `[museum_railway_traffic_notices]`.

1. **Bredd:** Feeden är inte smal kolumn i mitten — `alignwide` + `MrtPublicAppShell` ger rimlig maxbredd (~36rem feed inuti shell).
2. **Tema:** Inga konflikter med Lennakatten block-tema (overflow, dubbel padding, fel bakgrund).
3. **Tokens:** Korall alert-ruta, gul aktiv kategori, grå sektionsrubriker (inte wizard-grönt).
4. **Noscript:** Stäng av JS — samma hierarki och färger (BEM `.mrt-tf-*`).
5. **Mobil:** Giltighetsrad bryts läsbart under alert-rubrik.

**Sign-off:** Testare + produkt (Erik/Jesper) — datum: ___________

---

## TF-G1 — Jesper-checklista §14

Kör mobil + desktop på test3.

- [ ] Rubriker: «Aktuellt trafikläge» och «Planerade avvikelser» med ikon.
- [ ] Kategori-rad (Tåg/Buss) med **i**- och vid behov **!**-siffra **innan** expand.
- [ ] Gul markering på expanderad kategori.
- [ ] Tågnummer i **svart badge**, inte i löptext.
- [ ] Meddelande i **korallfärgad ruta** — läsbart utan expand.
- [ ] «Gäller …» på egen rad under, med klocka — **inte** dubbelt datum i samma rad.
- [ ] Inga rader «Mer information»; expand med **+** som UL.
- [ ] Jämför med UL: «känns samma nivå av överblick».

---

## TF-G2 — Jesper OK på UL-målbild

- [ ] Jesper godkänner visuell paritet mot UL-referens (uppföljning J11).
- [ ] Ev. avvikelser dokumenterade i [feedback/](feedback/) med datum.

**Sign-off Jesper:** ___________
