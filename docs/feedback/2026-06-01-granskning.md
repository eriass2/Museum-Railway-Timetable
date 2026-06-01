# Feedback – granskning juni 2026

Sammanställning av feedback från Jesper och en andra granskare (mail). Bilder refereras nedan — lägg filerna i `docs/feedback/images/` om de sparas i repot.

**Källor:** mail med skärmdumpar  
**Status:** obehandlad (väntar på genomgång)

---

## Sammanfattning

| Kategori | Antal | Kommentar |
|----------|-------|-----------|
| Buggar / fel | 4 | Resesökning, priser, kalendersiffror, teckenkodning |
| UI / design | 6 | Färger, typsnitt, tidtabell, klippning mobil |
| UX / flöde | 7 | Kalender vs lista, tur/retur-steg, busstider, spara resa |
| Frågor / scope | 2 | Biljettsystem, Edmonson-biljetter |
| Positivt | — | Båda imponerade, ser lovande ut |

---

## Jesper

### J1. Tidtabell – färger, mått, stationer
- **Källa:** mail, `image5.png`
- **Originaltext:** Färgerna är fel i tidtabellen, men det antar jag bara är provisoriskt och är enkelt att ändra. Kanske vill se över måtten, exempelvis är Thun's-Expressen väldigt brett. Kan man också göra så att stationer är skrivna med fet text vore det bra.
- **Område:** Tidtabell (mobil)
- **Typ:** UI / design
- **Prioritet:** medium
- **Status:** obehandlad
- **Anteckning:** Se befintlig palett i [COLOR_PALETTE.md](../design/COLOR_PALETTE.md). Officiell grafisk profil: https://lennakatten.se/grafisk-profil/

### J2. Busstider i huvudtidtabellen
- **Källa:** mail
- **Originaltext:** Föredrar om busstider finns med som extra rader i huvudtidtabellen snarare än en separat tidtabell, men jag vet inte om andra håller med nödvändigtvis.
- **Område:** Tidtabell
- **Typ:** UX / produktbeslut
- **Prioritet:** låg–medium (behöver avstämning)
- **Status:** obehandlad

### J3. Reseplanerare – klippning och linjetext
- **Källa:** mail, `image4.jpeg`
- **Originaltext:** I reseplaneraren blir det lite konstiga klippningar av symboler och text, se bild. Istället för att det står ”Rälsbuss Uppsala Östra - Faringe 101” kanske det går att göra så det står ”Rälsbuss 101 mot Faringe”, blir lite tydligare tycker jag.
- **Område:** Reseplanerare (mobil)
- **Typ:** UI + copy
- **Prioritet:** medium
- **Status:** obehandlad

### J4. Biljettpriser – zoner och eftermiddagstaxa
- **Källa:** mail, `image2.png`
- **Originaltext:** Beräkningen av biljettpriser verkar också bli lite fel. Denna resa är två zoner, och skulle normalt kosta 220 för vuxen, 60 för barn 4–15 och 200 för student. Dock är resan en tur- och returresa i sin helhet efter kl 15, så eftermiddagsbiljett gäller vilket innebär att resan blir 160 för vuxen och 140 för student. Hade för övrigt uppskattat om du lade till priserna för heldagsbiljetter under också. Lite fix där alltså, men hoppas inte de gör för svårt att få till (antar att eftermiddagstaxan kan vara lite komplicerad).
- **Område:** Prisberäkning / reseplanerare
- **Typ:** bugg + önskemål
- **Prioritet:** hög
- **Status:** åtgärdad (taxa 2026, eftermiddagsbiljett, heldagsbiljett i sammanfattning)
- **Förväntade priser (exempelresan):**
  - Normal 2-zoner: vuxen 220, barn 4–15 60, student 200
  - Efter kl 15 (tur/retur i sin helhet): vuxen 160, student 140
  - Önskemål: visa heldagsbiljetter också

### J5. ”Fortsätt till biljetter” – scope-fråga
- **Källa:** mail
- **Originaltext:** Att det står ”fortsätt till biljetter”, innebär det att du planerat ett biljettbokningssystem också? Det vore ju ballt men det får gärna fungerar så man fortfarande kan hämta ut klassiska edmonson-biljetter för den museala upplevelsen.
- **Område:** Reseplanerare / biljetter
- **Typ:** fråga / produktbeslut
- **Prioritet:** info (svar behövs)
- **Status:** obehandlad

---

## Granskare 2 (mail utan signatur)

### G1. Resesökning hittar ingen resa
- **Källa:** mail
- **Originaltext:** Ja, jag försökte söka en enkel resa Uppsala–Marielund men den hittade ingen resa trots att det var på en trafikdag, så nåt strular ju med tidtabellen som du säger.
- **Område:** Reseplanerare / journey scoring
- **Typ:** bugg
- **Prioritet:** hög
- **Status:** obehandlad

### G2. Typsnitt – Roboto + Open Sans Bold
- **Källa:** mail
- **Originaltext:** Jag ser ingen större brist med UI:en, förutom att du gärna får använda Roboto för brödtext och Open Sans Bold för rubriker, så blir det mer stilriktigt.
- **Område:** Global typografi
- **Typ:** design
- **Prioritet:** medium
- **Status:** obehandlad
- **Referens:** https://lennakatten.se/grafisk-profil/

### G3. Färger mot grafisk profil
- **Källa:** mail
- **Originaltext:** Kanske är den färgerna lite off också mot vad vi brukar ha. Färgkoder etc finns här: https://lennakatten.se/grafisk-profil/
- **Område:** Global färger
- **Typ:** design
- **Prioritet:** medium
- **Status:** obehandlad
- **Anteckning:** Överlappar J1. Jämför med `assets/mrt-color-tokens.css` och [COLOR_PALETTE.md](../design/COLOR_PALETTE.md).

### G4. Första vyn – kalender istället för tidtabellslista
- **Källa:** mail
- **Originaltext:** Jag tror inte på den första vyn, med att välja en tidtabell i en lista. Man vet ju inte vilka dagar ”gul tidtabell” går. Bättre att ha en kalender, klicka på en dag och, att man därifrån kan få upp tidtabellen där.
- **Område:** Tidtabell / startvy
- **Typ:** UX / större förändring
- **Prioritet:** medium–hög (produktbeslut)
- **Status:** obehandlad

### G5. Kalendersiffror – oklar betydelse
- **Källa:** mail
- **Originaltext:** Fattar inte vad siffrorna vid kalendern ska visa? Är det antal avgångar? Fast det verkar inte stämma och det är olika beroende på station och riktning.
- **Område:** Kalender
- **Typ:** bugg eller UX (oklarhet)
- **Prioritet:** hög
- **Status:** obehandlad

### G6. ”ÔÅ / Laddar…” vid laddning
- **Källa:** mail
- **Originaltext:** Varför står det för övrigt ”ÔÅ / Laddar…” när den laddar en ny vy? Ser lite udda ut.
- **Område:** Laddningstillstånd
- **Typ:** bugg (teckenkodning?) + copy
- **Prioritet:** medium
- **Status:** obehandlad
- **Anteckning:** Troligen felaktig encoding av ”Å” eller liknande — undersök var strängen kommer ifrån.

### G7. Kalender – färger per tidtabell
- **Källa:** mail (önskemål)
- **Originaltext:** Skulle gilla om kalendern hade olika färger på dagarna beroende på vilken tidtabell som gäller. Fattar om mitt första förslag var överambitiöst med alla symboler, men färger kanske inte är alltför svårt att lösa?
- **Område:** Kalender
- **Typ:** önskemål
- **Prioritet:** medium
- **Status:** obehandlad

### G8. Ingen sidladdning vid månadsklick
- **Källa:** mail (önskemål)
- **Originaltext:** Går det också att göra så att sidan inte laddar om när man klickar runt mellan månaderna?
- **Område:** Kalender / SPA-beteende
- **Typ:** önskemål / bugg
- **Prioritet:** medium
- **Status:** obehandlad

### G9. Tur och retur – separat steg för återresa
- **Källa:** mail (önskemål)
- **Originaltext:** När man väljer tur- och retur ska det väl komma med ett till steg i planeringen, dvs välj en återresa. Tanken där är väl att i återresa-steget visas endast resor som går tillbaka efter den valda ankomsten.
- **Område:** Reseplanerare / wizard
- **Typ:** UX (kan delvis finnas redan — verifiera)
- **Prioritet:** medium–hög
- **Status:** obehandlad

### G10. Spara resa som PNG/PDF
- **Källa:** mail (önskemål)
- **Originaltext:** När man i sista steget får fram ”din resa” kanske det ska finnas en knapp för att spara resan som en png eller pdf eller liknande, så man kan spara i mobilen och ha med sig under dagen.
- **Område:** Reseplanerare / sammanfattning
- **Typ:** önskemål / ny funktion
- **Prioritet:** låg–medium
- **Status:** obehandlad

---

## Prioriterad åtgärdslista (utkast)

### Fixa först (buggar / felaktigt beteende)
1. **G1** – Resa Uppsala–Marielund hittas inte på trafikdag
2. **J4** – Fel biljettpriser (zoner + eftermiddagstaxa)
3. **G5** – Kalendersiffror stämmer inte / otydliga
4. **G6** – ”ÔÅ / Laddar…” (encoding/copy)

### Design som är relativt enkelt
5. **J1 / G3** – Färger och tidtabell (stationer fetstil, kolumnbredder)
6. **G2** – Roboto + Open Sans Bold
7. **J3** – Mobil klippning + kortare linjetext

### Produktbeslut / större arbete
8. **G4** – Kalender som startvy istället för tidtabellslista
9. **G9** – Tur/retur med dedikerat återresesteg
10. **J2** – Busstider integrerade i huvudtidtabell
11. **G7 / G8** – Kalenderfärger per tidtabell, SPA utan reload
12. **G10** – Exportera resa (PNG/PDF)
13. **J5** – Svar om biljettsystem vs Edmonson

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

- [ ] Granska punkt för punkt: tydlig / oklar / redan fixad / avvisad
- [ ] Verifiera G9 mot nuvarande wizard-flöde
- [ ] Jämför färger mot lennakatten.se/grafisk-profil
- [ ] Reproducera G1 och J4 med konkreta testdata
- [ ] Svara Jesper på J5 (biljettsystem)
