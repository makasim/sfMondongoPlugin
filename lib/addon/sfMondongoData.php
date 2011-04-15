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
use Mondongo\DataLoader;

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
   * @var DataLoader
   */
  protected $dataLoader;
  
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
    $this->dataLoader = new DataLoader($mondongo);
  }
  
  /**
   * Loads data from a file or directory into a Mondongo data source
   
   *
   * @param mixed   $directoryOrFile  A file or directory path or an array of files or directories
   */
  public function loadData($directoryOrFile = null)
  {
    $files = $this->getFiles($directoryOrFile);
    $this->doLoadData($files);
  }
  
  public function loadDataFromArray($data)
  {
    $this->dataLoader->setData($data);
    $this->dataLoader->load(false);
  }
  
  /**
   * (non-PHPdoc)
   * @see vendor/symfony/lib/addon/sfData::doLoadData()
   */
  protected function doLoadData(array $files)
  {
    $data = array();
    foreach ($files as $file) {
      $data = sfToolkit::arrayDeepMerge($data, sfYaml::load($file));
    }
    
    $this->loadDataFromArray($data);
  }
  
  /**
   *
   * @throws sfException If a class mentioned in a fixture can not be found
   */
  public function doDropMongoDB()
  {
    if (false == $this->getDeleteCurrentData()) return;
    
    foreach ($this->mondongo->getConnections() as $connection) {
      $connection->getMongoDB()->drop();
    }
  }
}