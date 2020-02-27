Collection of commands to synchronize Github repositories with Crowdin translations

[![Build Status](https://travis-ci.org/akeneo/nelson.svg?branch=master)](https://travis-ci.org/akeneo/nelson)


## How does it work?

<img align="right" src="nelson.png" width="250"/>

Synchronizing a Github repository with Crowdin translations can be performed in a few steps.

First, you must tell Crowdin which keys to translate. In order to do that, you have to select the "source" locale from which your contributors will make their translations.
All translation keys are located in different files that you can configure using a Symfony Finder.
Nelson provides a command (`push-translation-keys`) to send new translations to Crowdin from the source locale.

Once the keys have been sent, your contributors will have the ability to edit, add and delete new translations in their language.

Next, When the keys have been translated, you must pull them back in your Github repository. 
This command (`pull-translations`) tells Crowdin to create a package for each language, downloads the packages and checks the differences with your up-to-date repository.
You can set an option in this tool to only pull languages that are translated to at least a given percentage (e.g. 90%).
If it detects differences (new translations, deleted translations or modified translations), it automatically creates a new Pull Request on your repository.
Finally, you have the option to manually accept or refuse the Pull Request, depending on its content. This last command can be automated via a Cron.

And voilà! Your GitHub repository will always be up-to-date and users will have the latest translations.

## Installation

**Warning** You must create your own fork in a dedicated git repository that will only be used for this purpose. This will allow you to pull translations and create pull requests.
You must **never** run the following commands using your main repository, because Nestor merges the latest commits automatically.

```
  $ git clone git@github.com:akeneo/nelson.git
  $ cd nelson
  $ composer update
```

You can alosu also use the provided Docker container to install the app:

```
  $ docker-compose run --rm php composer update
```

## Create your own configuration

This package includes an example of configuration located in `config/example.yml` that you should adapt according to your settings and save it as `config/your_config.yml`.

If you want to manage several configurations for multiple projects, you can use `--config_file=yourconfig.yml`.

## How to use it?

- To display languages that are going to be synchronized

  `$ php bin/console nelson:info-translated-progress`

- To Create language packages in Crowdin

  `$ php bin/console nelson:refresh-packages`

- To push the new translation keys to Crowdin

  `$ php bin/console nelson:push-translation-keys`

- To pull translations (creates PR to your main repository)

  `$ php bin/console nelson:pull-translations`


## Update the crontab

You can set up a crontab to run Nelson commands on a regular basis.
The first step is always to refresh the Crowdin package to get the latest updates.
To update crontab, use `crontab -e`.

An example of crontab to push new keys every friday and create pull requests every saturday:
```
30 * * * * cd /path/to/nelson/ && bin/console nelson:refresh-packages > /tmp/nelson_refresh.log 2>&1
0 4 * * 1 cd /path/to/nelson/ && bin/console nelson:pull-translations > /tmp/nelson_pull.log 2>&1
0 5 * * 1 cd /path/to/nelson/ && bin/console nelson:push-translation-keys > /tmp/nelson_push.log 2>&1
```

## Testing

You can use the shipped docker container to develop and test.

Launch specs in the docker container:
```
docker-compose run --rm php vendor/bin/phpspec run
```

Launch a command with XDebug:
```
PHP_XDEBUG_ENABLED=1 docker-compose run --rm php bin/console nelson:refresh-packages --config_file=community-1.x-2.x.yml
```

Automatically fix your code style:
```
docker-compose run --rm php vendor/bin/php-cs-fixer fix --config=.php_cs.php
```

## Copyrights

Thanks to [Bouletmaton](http://www.zanorg.net/bouletmaton/) for the avatar ([bouletcorp.com](http://www.bouletcorp.com/), 
[zanorg.com](http://www.zanorg.com/))
