# WP Simple Member Sections – Webklient

Uzamčená členská sekce pro WordPress s přístupem přes e-mail a PIN. Ideální pro distribuci kurzů, kvízů a neveřejných materiálů konkrétním uživatelům.

## Funkce
- Přihlášení pomocí e-mailu a čtyřmístného PINu
- Přesměrování na přehled vlastních kurzů po přihlášení
- Uzamčení obsahu podle přiřazených URL
- Admin stránka „Kurzy“ pro správu uživatelů a jejich přístupů
- Shortcody pro login formulář i logout odkaz

## Instalace
1. Nakopírujte plugin do `wp-content/plugins/wp_simple_member_sections/`
2. Aktivujte plugin v administraci WordPressu
3. Vytvořte stránku `/portal/` a vložte `[smsw_login_form]`
4. Po přihlášení bude uživatel přesměrován na `/portal/list/`

## Shortcody
- `[smsw_login_form]` zobrazí login formulář
- `[logout]` zobrazí odkaz pro odhlášení

## Datový model (ukládáno přes `update_option('smsw_users')`)
```php
[
  [
    'email' => 'user@example.com',
    'pin' => '1234',
    'access_pages' => [
      'portal/kurz-matematika',
      'portal/kviz-prvni-lekce'
    ]
  ],
  ...
]
```

## Odinstalace
Při odstranění pluginu se smažou všechna přístupová data (`smsw_users`).

---

Plugin vytvořen Michalem Kubíčkem / [Webklient.cz](https://webklient.cz)
Repozitář: [github.com/mediatoring/wp_simple_member_sections](https://github.com/mediatoring/wp_simple_member_sections)

Licence: GPLv2+