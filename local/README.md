# Local development (Local by Flywheel)

- **`deploy.ps1`** – Kopierar plugin till din Local-site (`inc`, `assets`, `languages`, huvudfiler).
- **`deploy.config.example.json`** – Kopiera till `deploy.config.json` och sätt `localPath` och `localUrl`. `deploy.config.json` är gitignored.

Om du får **“running scripts is disabled”** (Execution Policy), kör med bypass:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\local\deploy.ps1
```

Eller aktivera skript för din användare en gång: `Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned`

Kör från projektroten:

```powershell
.\local\deploy.ps1
.\local\deploy.ps1 -OpenBrowser
```
