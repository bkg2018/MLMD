# VI) Conclusion<A id="a77"></A>

MLMD est utilisé pour rédiger des sources de documentation multilingues tout en maintenant
les différentes parties de la documentation dans des fichiers uniques et générer les
fichiers Markdown de chaque langue à partir des sources.

Voici quelques notes d'usage.

- Fichiers
    - Les fichiers sources doivent être encodés en UTF-8
    - l'extension `.mlmd` est utilisée pour distinguer les sources MLMD des fichiers Markdown
    - le paramètre `-numbering` permet de numéroter l'ensemble des titres des fichiers sources de façon homogène
    - le paramètre `-out` permet de choisir le style des ancres et des liens selon le contexte Markdown ou HTML ciblé
    - le paramètre `-i` permet de choisir manuellement les fichiers sources, en son absence tous les fichiers trouvés seront traités
    - le paramètre `-od` permet de choisir le répertoire où seront générés les fichiers Markdown

- Langues et sections
  - Les codes des langues sont déclarés dans la directive `.languages``
  - Les codes sont valables sur l'ensemble des fichiers sources traités
  - Un code ISO facultatif peut être associé à chaque code de langue
  - Tout texte est ignoré jusqu'à la rencontre de la directive `.languages``
  - Toute langue déclarée avec le code `<code>` possède une directive d'ouverture `.<code>((`
  - Toute section ouverte doit être fermée avec `.))``
  - Tout texte en dehors des directives d'ouverture et fermeture est du texte par défaut
  - Ou on utilise `.((` pour ouvrir une section de texte par défaut
  - Les sections et le texte par défaut sont écrits dans tous les fichiers des langues qui n'ont pas de section spécifique
  - On utilise `.all((` pour forcer l'écriture dans les fichiers de toutes les langues
  - On utilise `.!((` pour du texte à n'écrire dans aucun fichier

- Titres et texte
  - Les titres doivent avoir un préfixe de style Markdown `#' en début de ligne
  - Placez le texte par défaut de vos paragraphes et titres avant d'ouvrir les sections spécifiques aux langues
  - Fermez chaque section avec `.))`
  - Utilisez des fins de ligne entre les directives pour séparer visuellement les sections
  - Utilisez les variables dans le texte ou dans des liens spécifiques à une langue ou un fichier
  - Le style de titres Markdown '===' et '---' pour les niveaux 1 et 2 n'est pas reconnu

- Sommaire (directive `.toc`)
  - Utilisez `level=1` pour générer un sommaire avec des liens vers chacun des fichiers traités
  - Utilisez les niveaux 2 à 9 pour un sommaire local du fichier en cours
  - Donnez un titre à votre sommaire, il aura le niveau 2 dans le fichier en cours
  - Placez la directive `.toc` après le titre de niveau 1 et une introduction
  - Ciblez `#toc` dans un lien vers le sommaire
