<?php

use \Illuminate\Container\Container as Container;
use \Illuminate\Support\Facades\Facade as Facade;

/**
 * Setup a new app instance container
 * 
 * @var Illuminate\Container\Container
 */
$app = new Container();
$app->singleton('app', 'Illuminate\Container\Container');

/**
 * Set $app as FacadeApplication handler
 */
Facade::setFacadeApplication($app);
