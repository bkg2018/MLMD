# I) Installation<A id="a2"></A>

MLMD est constitué d'un script principal `mlmd.php` et de dépendances. Le script et ses
dépendances peuvent être placés dans n'importe quel répertoire accessible à l'utilisateur.

## I-1) Version PHP<A id="a3"></A>

MLMD a été testé avec PHP 7.3 CLI. La version 7.2 minimale est requise.

La commande suivante permet de vérifier que PHP est accessible depuis une ligne de commande :

```code
php -v
```

Des lignes similaires aux suivantes doivent s'afficher (le texte exact peut varier) :

```code
PHP 7.3.20 (cli) (built: Jul  9 2020 23:50:54) ( NTS )
Copyright (c) 1997-2018 The PHP Group
Zend Engine v3.3.20, Copyright (c) 1998-2018 Zend Technologies
    with Zend OPcache v7.3.20, Copyright (c) 1999-2018, by Zend Technologies
```

Le répertoire d'installation de PHP et de ses fichiers de configuration peut être affiché avec
la commande `php --ini`.

PHP 7.2 peut fonctionner mais n'a pas été testé. L'extension MultiByte (mb) est utilisée mais
ne nécessite pas de réglage particulier car elle est intégrée par défaut dans les distributions
standards de PHP 7.

## I-2) Emplacement de MLMD<A id="a4"></A>

Le script et ses dépendances doivent se situer dans un répertoire accessible à l'utilisateur, par exemple :

- `~/phpscripts` sur macOS/Linux
- `%HOMEDRIVE%%HOMEPATH%\phpscripts` sur Windows

Les paramètres du script sont décrits dans la partie [Utilisation de MLMD](#utilisation-de-mlmd)

## I-3) Utilisation d'un alias pour lancer MLMD<A id="a5"></A>

Ce qui suit est facultatif mais permet d'utiliser le script MLMD comme s'il était une commande
du système d'exploitation. Sans les alias, le script doit être exécuté en tapant `php <chemin>/mlmd.php`
avec le chemin où ont été stockés le script et ses dépendances.

Les commandes indiquées ci-après doivent être adaptées pour le répertoire où se situe
le script.

### I-3.1) Linux / macOS / OS X<A id="a6"></A>

- La commande suivante peut être placée dans le script de démarrage
du shell (en général `~/.bashrc`, `~/.zshrc` etc) :

```code
alias mlmd=php ~/phpscripts/mlmd.php
```

### I-3.2) Windows 10<A id="a7"></A>

- Un fichier texte doit être créé - par exemple avec le Bloc Note - contenant la ligne suivante :

```code
doskey mlmd=php %HOMEDRIVE%%HOMEPATH%\phpscripts\mlmd.php $*
```

- Le fichier est enregistré sous le nom `MLMD.CMD` ou `mlmd.cmd`
(Windows ne différencie pas minuscules et majuscules dans les noms de fichiers.) sur le bureau ou dans
n'importe quel répertoire accessible à l'utilisateur.
- Un raccourci vers ce fichier est créé par un clic de droite suivi de la commande *Créer un raccourci*.
- Le répertoire `shell:startup` est ouvert en tapant les touches *Windows* et *R* puis
en tapant `shell:startup`.
- Le raccourci créé à l'étape précédente est déplacé vers le répertoire startup.
- Windows doit être redémarré.
- A partir de ce moment, l'alias `mlmd` est disponible dans les invites de commande.
- Cette méthode peut fonctionner avec des versions antérieures de Windows mais celles=ci
n'ont pas été testées.
