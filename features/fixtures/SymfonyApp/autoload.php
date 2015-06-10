<?php

/*
 * This file is part of the LoopBackApiBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

date_default_timezone_set('UTC');

$loader = require __DIR__.'/../../../vendor/autoload.php';
require 'AppKernel.php';

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

return $loader;
