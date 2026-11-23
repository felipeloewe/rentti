# RENTTI

Portal inmobiliario **RENTTI** (arriendo y compra en Chile).

## Stack

- Landing estática: HTML + CSS + JS
- Backoffice MVP: PHP 8 + SQLite (apto Softnet/cPanel Apache)
- Fotos en `uploads/properties/`
- DB en `data/rentti.sqlite` (protegida con `.htaccess`)

## Desarrollo local

```bash
# Credenciales: copia config/config.sample.php → config/config.local.php
# y define admin_pass_hash (password_hash) o exporta RENTTI_ADMIN_PASSWORD

php -S 127.0.0.1:8080 router.php
```

- Sitio: http://127.0.0.1:8080/
- Admin: http://127.0.0.1:8080/admin/
- Catálogo público: http://127.0.0.1:8080/propiedades.php
- API JSON: http://127.0.0.1:8080/api/listings.php

Seed opcional:

```bash
php scripts/seed_demo.php
```

## Deploy Softnet (rentti.cl)

Requiere secret `RENTTI_KEY` (token cPanel usuario `rentticl`).

```bash
python3 scripts/deploy_softnet.py
```

Sube el sitio a `public_html`. Conserva `config/config.local.php` local (no va a git) y lo sube al final del deploy.

## Estructura

```
index.html          landing
admin/              backoffice (login, listado, wizard)
api/listings.php    JSON de publicadas
propiedades.php     listado público
propiedad.php       ficha pública
lib/                PHP core
config/             sample + local secrets
data/               SQLite
uploads/properties/ fotos
```
