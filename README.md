Collection of Commands to synchronize Akeneo repositories with Crowdin translations

# Local usage

**Warning** You must create your own forks in your git repositories if you want to pull translations and create pull requests.
You must **never** run these next commands using akeneo repositories, because this script merge commits automatically.

The crowdin tool is located in `dev-tools` repository. To install it, clone the `dev-tools` repository:
```
  $ git clone git@github.com:akeneo/dev-tools.git
  $ cd dev-tools
  $ curl -sS https://getcomposer.org/installer | php
  $ cd crowdin
  $ php ../composer.phar update
```

- To show the language up to 80% of translated progress
  `$ php app/console crowdin:info-translated-progress`

- To create a new build in Crowdin (automatically created every 30 minutes)
  `$ php app/console crowdin:refresh-packages`

- To pull translations (creates PR to Akeneo repository)
  You have to create 2 forks (community and enterprise) in your own repository
  `$ php app/console crowdin:pull-translations <your_github_username> community`
  `$ php app/console crowdin:pull-translations <your_github_username> enterprise`

- To push the new translations to Crowdin
  `$ php app/console crowdin:push-translation-keys <your_github_username> community`
  `$ php app/console crowdin:push-translation-keys <your_github_username> enterprise`

# Server use

These commands are used by githook server. This folder is located in `/home/akeneo/crowdin/`.

## Update the crowdin tool

Connect with SSH into githook with your SSH agent, then git pull.

```
  $ ssh githook -A
  $ cd /home/akeneo/crowdin
  $ git pull
```

## Update the crontab

The crontab is located in githook server (user akeneo). Every monday, new translation keys are sent to Crowdin, and new valid translations create pull requests.
First step is always to refresh the Crowdin package to get the last updates.
To update crontab, use `crontab -e`.

The default crontab looks like
```
30 * * * * cd /home/akeneo/crowdin/ && app/console crowdin:refresh-packages > /tmp/crowdin_refresh.log
0 4 * * 1 cd /home/akeneo/crowdin/ && app/console crowdin:pull-translations nono-akeneo community > /tmp/crowdin_pull_ce.log
30 4 * * 1 cd /home/akeneo/crowdin/ && app/console crowdin:pull-translations nono-akeneo enterprise > /tmp/crowdin_pull_ee.log
0 5 * * 1 cd /home/akeneo/crowdin/ && app/console crowdin:push-translation-keys nono-akeneo community > /tmp/crowdin_push_ce.log
30 5 * * 1 cd /home/akeneo/crowdin/ && app/console crowdin:push-translation-keys nono-akeneo enterprise > /tmp/crowdin_push_ee.log
```

# Known issues

## New bundle / new file issue

If the *push* of the translations from Github to Crowdin does not work after the creation of a new bundle, you should get the logs in `app/logs/application.log`.
You have to check in the last 10 files if the file exists in Crowdin.

For example, with this next line, you have to check if `PimCommunity/LocalizationBundle/messages.en.yml` exists.
```
Push file "/tmp/.../src/Pim/Bundle/LocalizationBundle/Resources/translations/messages.en.yml" to "PimCommunity/LocalizationBundle/messages.en.yml"
```

If not, you have to:

- create folder and file in Crowndin

- update file settings to set the resulting file name when exported

- run the push again
