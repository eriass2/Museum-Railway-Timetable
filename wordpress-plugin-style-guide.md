# 🧩 WordPress Plugin Style Guide

Best practices för struktur, kvalitet, prestanda och skalbarhet.

Denna Style Guide definierar standarder för hur ett WordPress-plugin ska
utvecklas, dokumenteras och underhållas. Syftet är att säkerställa
konsekvens, god kvalitet och långsiktig stabilitet i projekt med flera
utvecklare.

## 📁 Projektstruktur

    plugin-name/
    │
    ├─ plugin-name.php
    ├─ uninstall.php
    ├─ readme.txt
    │
    ├─ assets/
    │   ├─ css/
    │   ├─ js/
    │   ├─ images/
    │   └─ dist/
    │
    ├─ includes/
    │   ├─ class-plugin.php
    │   ├─ class-loader.php
    │   ├─ class-activator.php
    │   ├─ class-deactivator.php
    │   ├─ admin/
    │   │   ├─ class-admin.php
    │   │   └─ class-admin-menu.php
    │   ├─ public/
    │   │   └─ class-public.php
    │   ├─ api/
    │   │   └─ class-rest-controller.php
    │   ├─ database/
    │   │   └─ class-schema.php
    │   └─ helpers/
    │       └─ functions-template.php
    │
    └─ languages/

## 🧱 Kodstandard (PHP, JS, CSS)

... (content trimmed for brevity in this example)
