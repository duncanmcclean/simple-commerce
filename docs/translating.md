---
title: Translating
---

Simple Commerce is developed in English. However, if you know another language, you're welcome to contribute translations to Simple Commerce.

Text within the Control Panel & any front-end validation messages can be translated.

## Configuration

You may read about configuring Statamic to use a language other than English over on the [Statamic Documentation](https://statamic.dev/cp-translations#configuration).

## Translating

> In this example, I'm using `fr` as an example locale. You can find a list of [all possible locales here](https://www.science.co.il/language/Codes.php).

1. In your site, create a `fr.json` file in your `lang` or`resources/lang` folder (whichever folder exists).
2. Inside that file, the format should be like so:

```json
{
    "English Phrase": "Your Translation"
}
```

3. Next, find all of the [translatable strings](https://github.com/search?q=repo%3Aduncanmcclean%2Fsimple-commerce%20__&type=code) & translate them in your `fr.json` file.
4. To contribute your translations back into Simple Commerce, follow these extra steps:
    1. [Fork](https://github.com/duncanmcclean/simple-commerce/fork) the Simple Commerce repository
    2. Create a file in `resources/lang` with the same filename you used in your site (example: `fr.json`)
    3. Copy the contents from your local translations file & commit.
    4. [Submit a pull request](https://github.com/duncanmcclean/simple-commerce/compare)
