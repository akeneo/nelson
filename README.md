Collection of commands to synchronize Github repositories with Crowdin translations

[![Build Status](https://travis-ci.org/akeneo/nelson.svg?branch=master)](https://travis-ci.org/akeneo/nelson)


# How it works?

<img align="right" src="nelson.jpg"/>

Synchronize its Github repository with Crowdin translations go through several stages.

First, you must tell Crowdin what are the keys to translate. For that, select your "source" locale from which your contributors will make their translations.
All key translate are located in different files, you can configure through a Symfony Finder.
This tool has a command (`push-translation-keys`) to send new translations to Crowdin from the source locale.

Once the keys have been sent, your contributors now have the ability to edit, add, delete, new translations in their language.

Once the keys have been translated, you must pull them in your Github repository. You can set an option in this tool to not only pull language translated from a certain percentage (eg 90%).
This command (`pull-translations`) tells Crowdin to create a package for each language, downloads it and checks the differences with your up-to-date repository.
If it detects differences (new translations, translations deleted or modified translations), it automatically creates a new Pull Request on your repository.
Then you have the option to manually accept the Pull Request or not depending on its content. This latest command may of course be automated via a Cron for translations daily.

And voilà! Your repository Github will be continuously updated and your users will have the latest translations.


# Installation

**Warning** You must create your own forks in your git repositories if you want to pull translations and create pull requests.
You must **never** run these next commands using main repositories, because this script merge commits automatically.

```
  $ git clone git@github.com:akeneo/nelson.git
  $ cd nelson
  $ curl -sS https://getcomposer.org/installer | php
  $ php ../composer.phar update
```

# Create your own configuration

This package includes a configuration example in `app/config.example.yml`.
Copy paste this file into `app/config.yml`, then update it following the instructions.
If you want to manage several configurations for several projects, you can use `--config_file=yourconfig.yml`.


# How to use it?

- To show the language up to 80% of translated progress

  `$ php app/console nelson:info-translated-progress`

- To create a new build in nelson

  `$ php app/console nelson:refresh-packages`

- To push the new translations to Crowdin

  `$ php app/console nelson:push-translation-keys`

- To pull translations (creates PR to your main repository)

  `$ php app/console nelson:pull-translations`


# Update the crontab

You can set up a crontab to automatize Nelson process.
First step is always to refresh the Crowdin package to get the last updates.
To update crontab, use `crontab -e`.

An example crontab to push new keys every friday and create pull requests every saturday:
```
30 * * * * cd /path/to/nelson/ && app/console nelson:refresh-packages > /tmp/nelson_refresh.log
0 4 * * 1 cd /path/to/nelson/ && app/console nelson:pull-translations > /tmp/nelson_pull.log
0 5 * * 1 cd /path/to/nelson/ && app/console nelson:push-translation-keys > /tmp/nelson_push.log
```
