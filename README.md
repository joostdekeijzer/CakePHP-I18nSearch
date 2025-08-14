CakePHP-I18nSearch (deprecated)
===============================

> [!WARNING]
> As of august 2025 this is deprecated software.

Behavior to make i18 searches easier

Models can now `$Model->findI18n($type, $query);` and the behavior will parse all conditions and rewrite
them to have them match the relevant i18n bindings.
