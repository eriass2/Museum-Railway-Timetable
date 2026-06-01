# Feedback – granskning juni 2026

Sammanställning av feedback från Jesper och en andra granskare (mail). Bilder refereras nedan — lägg filerna i `docs/feedback/images/` om de sparas i repot.

**Källor:** mail med skärmdumpar  
**Status:** delvis åtgärdad (genomgång juni 2026 — se **Svar** per punkt)

---

## Sammanfattning

| Kategori | Antal | Kommentar |
|----------|-------|-----------|
| Buggar / fel | 4 | **4 åtgärdade** (G1, J4, G5, G6) |
| UI / design | 6 | **G2, G3, J1** åtgärdade/delvis. **J3** kvar |
| UX / flöde | 7 | G5 åtgärdad. G4, G7–G10, G9, J2 obehandlade |
| Frågor / scope | 2 | ~~J5~~ åtgärdad (knapp borttagen). G10 obehandlad |
| Positivt | — | Båda imponerade, ser lovande ut |

### Genomfört (översikt)

| Punkt | Kort svar |
|-------|-----------|
| **G1** | Tidtabellsdata synkad mot PDF; resa Uppsala→Marielund täcks av PHPUnit |
| **J4** | Taxa 2026, eftermiddagsbiljett, heldagsbiljetter i sammanfattning |
| **G5** | Siffror dolda som standard; tydligare legend vid `show_counts=1` |
| **G3** | Lennakatten-färgpalett + kalenderfärger per tidtabellstyp (G7) |
| **G2** | Roboto + Open Sans Bold (Lennakatten profil) |
| **J1** | Delvis — fetstil stationer, smalare tågkolumner; färger via G3 |
| *(underliggande)* | GRÖN/GUL/RÖD/ORANGE + bussar synkade mot `Anslagstidtabell-2026.pdf`; 42 turer verifierade |

---

## Jesper

### J1. Tidtabell – färger, mått, stationer
- **Källa:** mail, `image5.png`
- **Originaltext:** Färgerna är fel i tidtabellen, men det antar jag bara är provisoriskt och är enkelt att ändra. Kanske vill se över måtten, exempelvis är Thun's-Expressen väldigt brett. Kan man också göra så att stationer är skrivna med fet text vore det bra.
- **Område:** Tidtabell (mobil)
- **Typ:** UI / design
- **Prioritet:** medium
- **Status:** åtgärdad (delvis — fetstil + kolumnbredd; färger via G3)
- **Svar:** Stationer i tidtabellsgriden är **fetstil** (`font-weight: 700`). Tågkolumner har fast maxbredd (inte `1fr`) så markerade turer som Thun's-expressen inte drar ut layouten; highlight-kolumnen är smalare med mindre vertikal etikettext. Varumärkesfärger via G3/G7.

### J2. Busstider i huvudtidtabellen
- **Källa:** mail
- **Originaltext:** Föredrar om busstider finns med som extra rader i huvudtidtabellen snarare än en separat tidtabell, men jag vet inte om andra håller med nödvändigtvis.
- **Område:** Tidtabell
- **Typ:** UX / produktbeslut
- **Prioritet:** låg–medium (behöver avstämning)
- **Status:** obehandlad
- **Svar:** Ingen ändring ännu. Busstider finns som egna tidtabeller/anslutningar i datan (synkade mot PDF). Kräver produktbeslut om de ska visas som extra rader i samma tabell som tågen.

### J3. Reseplanerare – klippning och linjetext
- **Källa:** mail, `image4.jpeg`
- **Originaltext:** I reseplaneraren blir det lite konstiga klippningar av symboler och text, se bild. Istället för att det står ”Rälsbuss Uppsala Östra - Faringe 101” kanske det går att göra så det står ”Rälsbuss 101 mot Faringe”, blir lite tydligare tycker jag.
- **Område:** Reseplanerare (mobil)
- **Typ:** UI + copy
- **Prioritet:** medium
- **Status:** obehandlad
- **Svar:** Ej åtgärdat. Förslaget om kortare linjetext (”Rälsbuss 101 mot Faringe”) och bättre mobil-layout för symboler är noterat.

### J4. Biljettpriser – zoner och eftermiddagstaxa
- **Källa:** mail, `image2.png`
- **Originaltext:** Beräkningen av biljettpriser verkar också bli lite fel. Denna resa är två zoner, och skulle normalt kosta 220 för vuxen, 60 för barn 4–15 och 200 för student. Dock är resan en tur- och returresa i sin helhet efter kl 15, så eftermiddagsbiljett gäller vilket innebär att resan blir 160 för vuxen och 140 för student. Hade för övrigt uppskattat om du lade till priserna för heldagsbiljetter under också. Lite fix där alltså, men hoppas inte de gör för svårt att få till (antar att eftermiddagstaxan kan vara lite komplicerad).
- **Område:** Prisberäkning / reseplanerare
- **Typ:** bugg + önskemål
- **Prioritet:** hög
- **Status:** åtgärdad
- **Förväntade priser (exempelresan):**
  - Normal 2-zoner: vuxen 220, barn 4–15 60, student 200
  - Efter kl 15 (tur/retur i sin helhet): vuxen 160, student 140
  - Önskemål: visa heldagsbiljetter också
- **Svar:** Fixat i commit `b098672`. Priserna följer Lennakatten taxa 2026. Zonberäkning (max 3 zoner) och **eftermiddagsbiljett** (tur/retur där båda benen avgår kl 15:00 eller senare → fast pris 160/140/60) är implementerade i PHP och Vue. **Heldagsbiljetter** visas i prissammanfattningen i wizard. Täcks av `JourneyPricesTest.php` och `frontend/vue/tests/prices.test.ts`.

### J5. ”Fortsätt till biljetter” – scope-fråga
- **Källa:** mail
- **Originaltext:** Att det står ”fortsätt till biljetter”, innebär det att du planerat ett biljettbokningssystem också? Det vore ju ballt men det får gärna fungerar så man fortfarande kan hämta ut klassiska edmonson-biljetter för den museala upplevelsen.
- **Område:** Reseplanerare / biljetter
- **Typ:** fråga / produktbeslut
- **Prioritet:** info (svar behövs)
- **Status:** åtgärdad (knapp borttagen)
- **Svar:** Inget biljettbokningssystem är planerat i nuvarande scope. Knappen **”Fortsätt till biljetter”** är borttagen från sammanfattningssteget — den var en placeholder och gav felaktigt intryck. Reseplaneraren visar tider och priser; biljetter köps som idag ombord/station (Edmonson m.m.). Shortcode-attributet `ticket_url` ignoreras tills vidare om det sätts.

---

## Granskare 2 (mail utan signatur)

### G1. Resesökning hittar ingen resa
- **Källa:** mail
- **Originaltext:** Ja, jag försökte söka en enkel resa Uppsala–Marielund men den hittade ingen resa trots att det var på en trafikdag, så nåt strular ju med tidtabellen som du säger.
- **Område:** Reseplanerare / journey scoring
- **Typ:** bugg
- **Prioritet:** hög
- **Status:** åtgärdad (testdata; verifiera på demo efter import)
- **Svar:** Orsaken var felaktig/ofullständig tidtabellsdata i fixturen — inte själva resesökningsmotorn. Vi har synkat GRÖN, GUL, RÖD och ORANGE mot `Anslagstidtabell-2026.pdf` (tider, P/X, bussanslutningar). Resa **Uppsala Östra → Marielund** på grön trafikdag (t.ex. 2026-06-06) ger nu träff (tåg 71, avg 10:00). Täcks av `LennakattenJourneySearchTest.php`. **Demo-sajten** behöver ny import av Lennakatten-fixture om den fortfarande kör gammal data.

### G2. Typsnitt – Roboto + Open Sans Bold
- **Källa:** mail
- **Originaltext:** Jag ser ingen större brist med UI:en, förutom att du gärna får använda Roboto för brödtext och Open Sans Bold för rubriker, så blir det mer stilriktigt.
- **Område:** Global typografi
- **Typ:** design
- **Prioritet:** medium
- **Status:** åtgärdad
- **Referens:** https://lennakatten.se/grafisk-profil/
- **Svar:** **Roboto** (brödtext) och **Open Sans Bold** (rubriker) laddas via `assets/mrt-typography.css` (Google Fonts). Gäller publikt UI, wizard, tidtabell och admin Vue (`--mrt-font-body` / `--mrt-font-heading`).

### G3. Färger mot grafisk profil
- **Källa:** mail
- **Originaltext:** Kanske är den färgerna lite off också mot vad vi brukar ha. Färgkoder etc finns här: https://lennakatten.se/grafisk-profil/
- **Område:** Global färger
- **Typ:** design
- **Prioritet:** medium
- **Status:** delvis åtgärdad (tokens + kalenderfärger; vissa vyer kvar att granska visuellt)
- **Anteckning:** Överlappar J1. Se `assets/mrt-color-tokens.css` och [COLOR_PALETTE.md](../design/COLOR_PALETTE.md).
- **Svar:** Lennakatten varumärkesfärger är införda i `assets/mrt-color-tokens.css` (commit `8b82cb2`): grön `#296310`, guld `#DDD24C`, oliv `#807C1C`. **Svart text på guld** enligt profil (tidigare felaktigt vit). Trafikfärger som `--mrt-color-traffic-*` för tidtabellstyper. Referens-PDF och Word-mallar i `docs/design/reference/`. **Kvar:** kalenderdagar ska färgkodas per tidtabellstyp (G7); vissa vyer kan fortfarande behöva visuell genomgång.

### G4. Första vyn – kalender istället för tidtabellslista
- **Källa:** mail
- **Originaltext:** Jag tror inte på den första vyn, med att välja en tidtabell i en lista. Man vet ju inte vilka dagar ”gul tidtabell” går. Bättre att ha en kalender, klicka på en dag och, att man därifrån kan få upp tidtabellen där.
- **Område:** Tidtabell / startvy
- **Typ:** UX / större förändring
- **Prioritet:** medium–hög (produktbeslut)
- **Status:** obehandlad
- **Svar:** Ingen ändring av startvy ännu. Månadskalender-shortcode finns redan; frågan är om den ska bli primär ingång istället för tidtabellslistan.

### G5. Kalendersiffror – oklar betydelse
- **Källa:** mail
- **Originaltext:** Fattar inte vad siffrorna vid kalendern ska visa? Är det antal avgångar? Fast det verkar inte stämma och det är olika beroende på station och riktning.
- **Område:** Kalender
- **Typ:** bugg eller UX (oklarhet)
- **Prioritet:** hög
- **Status:** åtgärdad
- **Anteckning:** Siffrorna var antal **turer** (services) alla linjer — inte avgångar för vald sträcka. Reseplanerarens kalender visar inga siffror.
- **Svar:** Fixat i commit `63c8eb9`. **Siffror är av som standard** (`show_counts=0`) — dagar markeras med prick/färg istället. Om siffror aktiveras (`show_counts=1`) förklaras de i legenden som antal turer alla linjer och riktningar (inte avgångar för en viss sträcka). Reseplanerarens datumsteg visar aldrig per-dag-siffror. Hjälptext uppdaterad i admin.

### G6. ”ÔÅ / Laddar…” vid laddning
- **Källa:** mail
- **Originaltext:** Varför står det för övrigt ”ÔÅ / Laddar…” när den laddar en ny vy? Ser lite udda ut.
- **Område:** Laddningstillstånd
- **Typ:** bugg (teckenkodning?) + copy
- **Prioritet:** medium
- **Status:** åtgärdad
- **Svar:** Orsaken var en korrupt UTF-8-teckensträng i `assets/frontend/ui/primitives.css` (`.mrt-empty--loading::before { content: "ÔÅ│ " }`; filen hette tidigare `components-ui.css`) — troligen avsedd som laddningsikon. Ersatt med ren CSS-spinner (ingen text i `content`), så användare ser bara ”Laddar…”.

### G7. Kalender – färger per tidtabell
- **Källa:** mail (önskemål)
- **Originaltext:** Skulle gilla om kalendern hade olika färger på dagarna beroende på vilken tidtabell som gäller. Fattar om mitt första förslag var överambitiöst med alla symboler, men färger kanske inte är alltför svårt att lösa?
- **Område:** Kalender
- **Typ:** önskemål
- **Prioritet:** medium
- **Status:** åtgärdad
- **Svar:** Månadskalender och reseplanerarens datumsteg färgar trafikdagar enligt `mrt_timetable_type` (green/yellow/red/orange) via `--mrt-color-traffic-*`. Legend visar vilka tidtabellstyper som förekommer i månaden. Om flera typer delar samma dag väljs en dominerande typ (prioritet: grön → gul → röd → orange).

### G8. Ingen sidladdning vid månadsklick
- **Källa:** mail (önskemål)
- **Originaltext:** Går det också att göra så att sidan inte laddar om när man klickar runt mellan månaderna?
- **Område:** Kalender / SPA-beteende
- **Typ:** önskemål / bugg
- **Prioritet:** medium
- **Status:** obehandlad
- **Svar:** Vue-månadskalendern (`MonthCalendarApp`) laddar om utan full sidreload om shortcoden används i Vue-läge. Om full sidladdning fortfarande syns kan det bero på äldre PHP-shortcode-läge eller länkar med query-parametrar — behöver verifieras i vilken vy granskaren testade.

### G9. Tur och retur – separat steg för återresa
- **Källa:** mail (önskemål)
- **Originaltext:** När man väljer tur- och retur ska det väl komma med ett till steg i planeringen, dvs välj en återresa. Tanken där är väl att i återresa-steget visas endast resor som går tillbaka efter den valda ankomsten.
- **Område:** Reseplanerare / wizard
- **Typ:** UX (kan delvis finnas redan — verifiera)
- **Prioritet:** medium–hög
- **Status:** obehandlad (behöver verifieras mot nuvarande wizard)
- **Svar:** Ej verifierat/skrivet svar ännu. Wizard har tur/retur-läge — ska kontrolleras om det redan finns separat återresesteg med filtrering efter ankomsttid.

### G10. Spara resa som PNG/PDF
- **Källa:** mail (önskemål)
- **Originaltext:** När man i sista steget får fram ”din resa” kanske det ska finnas en knapp för att spara resan som en png eller pdf eller liknande, så man kan spara i mobilen och ha med sig under dagen.
- **Område:** Reseplanerare / sammanfattning
- **Typ:** önskemål / ny funktion
- **Prioritet:** låg–medium
- **Status:** åtgärdad (A + B: utskrift/PDF + dela/kopiera)
- **Svar:** Knappar i sammanfattningssteget: **Skriv ut / spara som PDF** (`window.print()` + `@media print`) och **Dela resa** / **Kopiera resa** (Web Share API med fallback till urklipp). PNG/länk (C/D) ej implementerat.

---

## Prioriterad åtgärdslista

### Fixa först (buggar / felaktigt beteende)
1. ~~**G1** – Resa Uppsala–Marielund hittas inte på trafikdag~~ ✓ (testdata; verifiera demo-import)
2. ~~**J4** – Fel biljettpriser (zoner + eftermiddagstaxa)~~ ✓
3. ~~**G5** – Kalendersiffror stämmer inte / otydliga~~ ✓
4. ~~**G6** – ”ÔÅ / Laddar…” (encoding/copy)~~ ✓

### Design som är relativt enkelt
5. ~~**J1 / G3** – Färgpalett, fetstil stationer, kolumnbredder~~ ✓
6. ~~**G2** – Roboto + Open Sans Bold~~ ✓
7. **J3** – Mobil klippning + kortare linjetext

### Produktbeslut / större arbete
8. **G4** – Kalender som startvy istället för tidtabellslista
9. **G9** – Tur/retur med dedikerat återresesteg
10. **J2** – Busstider integrerade i huvudtidtabell
11. **G7 / G8** – ~~Kalenderfärger per tidtabell~~ ✓ / SPA utan reload
12. ~~**G10** – Exportera resa (PNG/PDF)~~ ✓ (print + dela/kopiera)
13. ~~**J5** – Svar om biljettsystem vs Edmonson~~ ✓ (knapp borttagen)

---

## G10 – praktiska förslag (ej beslutade)

| Alternativ | Insats | För / emot |
|------------|--------|------------|
| **A. Skriv ut / Spara som PDF** | Låg | `@media print`-CSS på sammanfattningskortet + knapp som anropar `window.print()`. Användaren väljer ”Spara som PDF” i webbläsaren. Ingen dependency, fungerar desktop + mobil. |
| **B. Web Share API** | Låg | Knapp ”Dela resa” som anropar `navigator.share()` med text (datum, tider, sträcka, pris). Bra på mobil; fallback till kopiera text till urklipp. |
| **C. PNG via html2canvas** | Medium | Screenshot av `.mrt-summary-list` + prisblock, ladda ner som bild. Bra offline på mobilen; kräver npm-paket och kan klippa konstigt (J3-problem). |
| **D. Delbar länk** | Medium–hög | URL med kodade parametrar (från/till/datum/tåg-id) som återskapar sammanfattning. Kräver att wizard kan läsa query vid start + ev. backend-validering. |
| **E. Server-PDF (TCPDF/Dompdf)** | Hög | WordPress genererar PDF — överkill för pluginet, svårare att underhålla. |

**Rekommendation:** börja med **A + B** (print/PDF + dela/kopiera text). Täcker ”ha med sig under dagen” utan bokningssystem eller tunga beroenden.

---

## Bilder

| Fil | Kopplad punkt | Beskrivning |
|-----|---------------|-------------|
| `image5.png` | J1 | Tidtabell mobil – fel färger, bred kolumn |
| `image4.jpeg` | J3 | Reseplanerare – klippning av symboler/text |
| `image2.png` | J4 | Prisexempel – 2 zoner, tur/retur efter kl 15 |

> Lägg bilderna i `docs/feedback/images/` om de ska versioneras. Annars räcker det att dra in dem i chatten vid genomgång.

---

## Nästa steg

- [x] Granska punkt för punkt: tydlig / oklar / redan fixad / avvisad
- [ ] Verifiera G9 mot nuvarande wizard-flöde
- [x] Jämför färger mot lennakatten.se/grafisk-profil
- [x] Reproducera G1 och J4 med konkreta testdata
- [ ] Svara Jesper på J5 (biljettsystem) — *knapp borttagen; svar i doc*
- [ ] Verifiera G1 på demo efter ny fixture-import
- [x] **G6** — hitta och rätta laddningstext (teckenkodning)
- [x] **G7** — kalenderfärger per `mrt_timetable_type`
