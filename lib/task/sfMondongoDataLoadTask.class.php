<?php

/*
 * Copyright 2010 Pablo Díez Pascual <pablodip@gmail.com>
 *
 * This file is part of sfMondongoPlugin.
 *
 * sfMondongoPlugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * sfMondongoPlugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with sfMondongoPlugin. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Load Mondongo fixtures.
 *
 * @package sfMondongoPlugin
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class sfMondongoDataLoadTask extends sfMondongoTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('dir_or_file', sfCommandArgument::OPTIONAL | sfCommandArgument::IS_ARRAY, 'Directory or file to load'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('append', null, sfCommandOption::PARAMETER_NONE, 'Don\'t delete current data in the database'),
    ));

    $this->namespace = 'mondongo';
    $this->name = 'data-load';
    $this->briefDescription = 'Load fixture data';

    $this->detailedDescription = <<<EOF

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
     sfContext::createInstance($this->configuration);

     $this->logSection('mondongo', 'parsing data');

     if (!$arguments['dir_or_file']) {
        $arguments['dir_or_file'] = array(sfConfig::get('sf_root_dir').'/data/mondongo');
     }

     $finder = sfFinder::type('file')->name('*.yml')->sort_by_name()->follow_link();
     $files = array();
     foreach ($arguments['dir_or_file'] as $dirOrFile) {
        if (is_dir($dirOrFile)) {
            $files = array_merge($files, $finder->in($dirOrFile));
        } elseif (is_file($dirOrFile)) {
            $files[] = $dirOrFile;
        } else {
            throw new \InvalidArgumentException(sprintf('"%s" is not a dir or file.', $dirOrFile));
        }
     }
     $files = array_unique($files);

     $data = array();
     foreach ($files as $file) {
        $data = sfToolkit::arrayDeepMerge(sfYaml::load($file));
     }

     $this->logSection('mondongo', 'loading data');

     $dataLoader = new Mondongo\DataLoader($this->getMondongo());
     $dataLoader->setData($data);
     $dataLoader->load(!$options['append']);
  }
}
