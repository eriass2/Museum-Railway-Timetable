# Oavklarade Saker i Projektet

**Datum**: 2025-01-27  
**Uppdaterad**: 2025-01-27 - CSV import har tagits bort från projektet

## ✅ Status: Inga Kritiska Oavklarade Saker

Alla CSV import-referenser har tagits bort från projektet enligt beslut. Projektet är nu komplett utan CSV import-funktionalitet.

### Borttaget:
- ✅ CSV import-referenser från README.md
- ✅ CSV import-referenser från validate.php och validate.ps1
- ✅ CSV import-checklistor från VALIDATION_CHECKLIST.md och VALIDATION_REPORT.md
- ✅ CSV import-referenser från STYLE_GUIDE_COMPLIANCE.md
- ✅ CSV import-stilar från admin.css
- ✅ CSV import-översättningar från translation-filer (.pot och .po)

---

## ✅ Vad som ÄR implementerat

### Fungerande Funktioner:
- ✅ Custom Post Types (Stations, Routes, Services)
- ✅ Custom Taxonomy (Train Types)
- ✅ Shortcodes (museum_timetable, museum_timetable_picker, museum_timetable_month)
- ✅ Admin meta boxes för Stop Times och Calendar
- ✅ AJAX-hantering för CRUD-operationer
- ✅ Stations Overview-sida
- ✅ Settings-sida
- ✅ Translation support (svenska)
- ✅ Security (nonces, capability checks, sanitization)
- ✅ Database tables (mrt_stoptimes, mrt_calendar)
- ✅ Route-baserad Stop Times-hantering
- ✅ Inline editing för Stop Times och Calendar

---

## 📋 Rekommenderad Åtgärdsplan

### Prioritet 1: Implementera CSV Import
1. Skapa `inc/import/` mapp
2. Implementera alla 6 import-filer enligt dokumentationen i STYLE_GUIDE_COMPLIANCE.md
3. Lägg till menyalternativ i `inc/admin-page.php`
4. Ladda `inc/import.php` i huvudfilen
5. Testa att valideringsskripten passerar

### Prioritet 2: Uppdatera Dokumentation
1. Uppdatera VALIDATION_REPORT.md om CSV import inte är kritiskt
2. Uppdatera README.md om CSV import är valfritt
3. Eller: Ta bort referenser till CSV import om det inte ska implementeras

---

## 🔍 Ytterligare Observationer

### Dokumentation vs. Implementation
- Dokumentationen (README, VALIDATION_CHECKLIST, etc.) nämner CSV import som en funktion
- CSS och translations är förberedda för CSV import
- Men själva implementationen saknas helt

### Valideringsskript
- `validate.php` och `validate.ps1` kommer att misslyckas eftersom de förväntar sig import-filerna
- Detta indikerar att CSV import var planerat men inte implementerat

---

## 💡 Rekommendation

**Alternativ 1**: Implementera CSV import-funktionaliteten
- Följ dokumentationen i STYLE_GUIDE_COMPLIANCE.md
- Använd översättningar och CSS som redan finns
- Detta skulle göra projektet komplett enligt planen

**Alternativ 2**: Ta bort CSV import-referenser
- Ta bort referenser från README.md
- Ta bort från validate.php/validate.ps1
- Ta bort CSS-stilar för import
- Uppdatera dokumentationen

**Rekommendation**: Alternativ 1 (implementera) eftersom:
- Alla förberedelser redan finns (CSS, translations)
- Dokumentationen är tydlig om vad som behövs
- Valideringsskripten förväntar sig funktionaliteten
- Det verkar vara en viktig funktion för användbarheten

