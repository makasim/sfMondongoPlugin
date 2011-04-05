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

use Mondongo\Mondongo;

/**
 * sfMondongoData.
 *
 * @package sfMondongoPlugin
 * @author  Kotlyar Maksim <kotlyar.maksim@gmail.com>
 */
class sfMondongoData extends sfData
{
  /**
   * 
   * @var Mondongo
   */
  protected $mondongo;
  
  
  /**
   * 
   * @param Mondongo $mondongo
   */
  public function __construct(Mondongo $mondongo)
  {
    $this->mondongo = $mondongo;
  }
  
  /**
   * Loads data from a file or directory into a Mondongo data source
   
   *
   * @param mixed   $directoryOrFile  A file or directory path or an array of files or directories
   */
  public function loadData($directoryOrFile = null)
  {
    $files = $this->getFiles($directoryOrFile);

    $this->doDropMongoDB();
    $this->doLoadData($files);
  }
  
  public function loadDataFromArray($data)
  {    
    $classes = array();
    foreach ($data as $class => $documents)
    {
      $dataMap = $class::getDataMap();
      $classes[$class] = $dataMap['references'];
    }

    $this->logSection('mondongo', 'loading data');

    do
    {
      $change = false;

      foreach ($classes as $class => $references)
      {
        $process = true;

        foreach ($references as $reference)
        {
          if (isset($classes[$reference['class']]))
          {
            $process = false;
          }
        }

        if ($process)
        {
          foreach ($data[$class] as $field => $datum)
          {
            // references
            foreach ($references as $name => $reference)
            {
              if (isset($datum[$name]))
              {
                // many
                if ('many' == $reference['type'])
                {
                  $datums = array();
                  foreach ($datum[$name] as $key)
                  {
                    if (!isset($this->object_references[$reference['class']][$key]))
                    {
                      throw new InvalidArgumentException(sprintf('The reference "%s" of the class "%s" does not exists.', $key, $reference['class']));
                    }

                    $datums[] = $this->object_references[$reference['class']][$key]->getId();
                  }

                  $datum[$reference['field']] = $datums;
                }
                // one
                else
                {
                  if (!isset($this->object_references[$reference['class']][$datum[$name]]))
                  {
                    throw new InvalidArgumentException(sprintf('The reference "%s" of the class "%s" does not exists.', $name, $reference['class']));
                  }

                  $datum[$reference['field']] = $this->object_references[$reference['class']][$datum[$name]]->getId();
                }

                unset($datum[$name]);
              }
            }
            
            $document = new $class();
            $document->fromArray($datum);
            $document->save();

            $this->object_references[$class][$field] = $document;
          }

          $change = true;
          unset($classes[$class]);
        }
      }
    }
    while ($classes && $change);

    if (!$change)
    {
      throw new RuntimeException('Unable to process everything.');
    }
  }
  
  /**
   * (non-PHPdoc)
   * @see vendor/symfony/lib/addon/sfData::doLoadData()
   */
  protected function doLoadData(array $files)
  {
    $this->object_references = array();

    $data = array();
    foreach ($files as $file) {
      $data = sfToolkit::arrayDeepMerge($data, sfYaml::load($file));
    }
    
    $this->loadDataFromArray($data);
  }
  
  /**
   * 
   * @return Mondongo
   */
  protected function getMondongo()
  {
    return $this->mondongo;
  }
  
  /**
   *
   * @throws sfException If a class mentioned in a fixture can not be found
   */
  public function doDropMongoDB()
  {
    if (false === $this->deleteCurrentData) return;
    
    foreach ($this->getMondongo()->getConnections() as $connection) {
      $connection->getMongoDB()->drop();
    }
  }
  
  protected function logSection($section, $message, $size = null, $style = 'INFO')
  {
    // TODO
   // $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection($section, $message, $size, $style))));
  }
}