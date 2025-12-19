# Changelog

Tous les changements notables de ce projet seront documentés dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère à [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-12-18

### Added
- Support complet de l'API ChatGPT avec interface fluide
- Système d'outils (function calling) pour intégrer des fonctions personnalisées
- Commande artisan `make:chatTool` pour créer facilement des outils
- Gestion automatique des erreurs avec retry logic
- Support des conversations multi-tours
- Support de tous les modèles OpenAI (gpt-3.5-turbo, gpt-4, etc.)
- Gestion des exceptions spécifiques (AuthenticationException, RateLimitException)
- Configuration flexible via fichier de configuration Laravel
- Parsing automatique des réponses avec extraction des informations d'usage
- Exécution automatique des outils choisis par ChatGPT
- Documentation complète avec exemples d'intégration

### Changed
- Initial release

[1.0.0]: https://github.com/allanbernier/laravel-gpt/releases/tag/v1.0.0
