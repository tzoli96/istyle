# iStyle M2

**Utoljára frissítve:** 2017.05.13.

**Tartalomjegyzék:**

* [Sass-lint](#sass-lint)
 
## <a name="sass-lint"></a>Sass-lint
A projekthez be lett állítva **sass-lint**. Ezzel egységesen (konfigurációs .yml fájlból) lehet scss-t írni. A leírás **PhpStorm**-hoz készült.

 **Telepítés menete: **
 
 1. Terminálban a <project-root>-ban adjuk ki az `npm install` parancsot
 2. Telepítsük fel és kapcsoljuk be a plugint: https://plugins.jetbrains.com/plugin/8171-sass-lint
 3. Beállítások között az **other settings ** szekció alatt találjuk a sass-lint konfigurációját
 4. Beállítások:
    1. Node interpreter: `/usr/bin/node`
    2. Path to sass-lint bin (betallózni!): `{PROJECT-ROOT}/node_modules/sass-lint/bin/sass-lint.js`
    3. Use specific config file (betallózni!): `{PROJECT-ROOT}/.sass-lint.yml`
 
 Angol nyelvű dev dokumentáció: /usr/bin/node