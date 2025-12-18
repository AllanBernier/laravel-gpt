# Installation du package

Ce package peut être installé de plusieurs façons selon votre situation.

## Option 1 : Installation locale (développement)

Si vous développez le package et voulez le tester dans un projet Laravel :

### Dans votre projet Laravel, modifiez `composer.json` :

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../laravel-gpt"
        }
    ],
    "require": {
        "allanbernier/laravel-gpt": "@dev"
    }
}
```

Puis exécutez :
```bash
composer update allanbernier/laravel-gpt
```

**Note :** Remplacez `../laravel-gpt` par le chemin relatif ou absolu vers le dossier du package.

## Option 2 : Installation via Git (recommandé pour production)

### 2.1. Si le package est sur GitHub/GitLab/Bitbucket

Ajoutez dans le `composer.json` de votre projet :

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/votre-username/laravel-gpt.git"
        }
    ],
    "require": {
        "allanbernier/laravel-gpt": "dev-main"
    }
}
```

Puis :
```bash
composer require allanbernier/laravel-gpt:dev-main
```

### 2.2. Si le package est sur un dépôt privé

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:votre-username/laravel-gpt.git"
        }
    ],
    "require": {
        "allanbernier/laravel-gpt": "dev-main"
    }
}
```

## Option 3 : Installation via Packagist (quand publié)

Une fois le package publié sur Packagist :

```bash
composer require allanbernier/laravel-gpt
```

## Configuration après installation

Quelle que soit la méthode d'installation :

```bash
php artisan vendor:publish --tag=laravel-gpt-config
```

Ajoutez votre clé API dans `.env` :

```env
OPENAI_API_KEY=sk-proj-votre-cle-api
```

## Vérification

Vérifiez que le package est bien installé :

```bash
php artisan list | grep chatTool
```

Vous devriez voir la commande `make:chatTool`.
