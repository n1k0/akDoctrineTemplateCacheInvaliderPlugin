akDoctrineTemplateCacheInvaliderPlugin
======================================

<small>Pretty name, huh?</small>

This plugin allows to configure dynamic [Symfony](http://www.symfony-project.org/) templates cache invalidation on [Doctrine](http://www.doctrine-project.org/) objects persistence operations. In other words, you can quite easily define which cached templates will be deleted when your models will be updated.

This can be useful when you need performance using Symfony, Doctrine and [Template Caching](http://www.symfony-project.org/jobeet/1_4/Doctrine/en/21).

Plugin Installation
-------------------

No, seriously, we're all grown-ups here. Just don't forget to enable the plugin in your `ProjectConfiguration.class.php` and clear your cache.

Usage
-----

Create a `doctrine_cache_invalider.yml` file in the `config` folder of your project (or the one of any plugin, if you work with plugins), and define template cache uris for each Doctrine model you want:

    Article:
      uris:
        MyContent/index?sf_culture=*:               frontend
        MyContent/article?sf_culture=*&slug=%slug%: frontend
    Comment:
      uris:
        MyContent/article?sf_culture=*&slug=%article_slug%: frontend

<small>Note that the `Article` and `Comment` Doctrine models should be defined in your `schema.yml` file.</small>

As you can see, you define template cache uris by their internal Symfony one, plus you define the application name where the cache is used, eg. `frontend`. This is especially useful if you work with a `backend` application (eg. using forms provided by the admin-generator) and want your `frontend` templates cache files to be invalidated when records are created, deleted or updated.

You can also define several applications where the cache uris are used, for example if you share some templates accross applications:

    Article:
      uris:
        MyContent/index?sf_culture=*: [frontend, otherapp]
        MyContent/article?sf_culture=*&slug=%slug%: [frontend, otherapp]
    Comment:
      uris:
        MyContent/article?sf_culture=*&slug=%Article.slug%: frontend

Also note the `%field_name%`-like and `%RelatedModel.field_name%`-like parameters, they're just placeholders and will be replaced by the record and related object field value of the model instance which is being created or updated. 

I18n is also managed, so if your models implement the Doctrine [I18n Behavior](http://www.doctrine-project.org/projects/orm/1.2/docs/manual/behaviors/en#core-behaviors:i18n), supplementary template cache uris will be invalidated to handle all available translations for the field.

If you want to see sample use, check out the bundled [fixture project](http://github.com/n1k0/akDoctrineTemplateCacheInvaliderPlugin/tree/master/test/fixtures/project/) provided in the functional tests of the plugin.

License
-------

This plugin is licensed under the terms of the [MIT license](http://en.wikipedia.org/wiki/MIT_License).

Credits
-------

This plugin is developped by [Nicolas Perriault](http://prendreuncafe.com/).

So, why the hell is this plugin prefixed with `ak`? Because it's a plugin I've developed and am currently using in my company, [Akei](http://www.akei.com/).