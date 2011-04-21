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

use Mondongo\Query;

/**
 * sfMondongoPager.
 *
 * Based in sf|Propel/Doctrine|Pager.
 *
 * @package sfMondongoPlugin
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class sfMondongoPager extends sfPager
{
  protected $query;

  /**
   * Sets the query.
   *
   * @param Mondongo\Query $query The query.
   */
  public function setQuery(Query $query)
  {
    $this->query = $query;
  }

  /**
   * Returns the query.
   *
   * @return Mondongo\Query The query.
   */
  public function getQuery()
  {
    if (!$this->query)
    {
        $class = $this->getClass();
        $this->query = $class::query();
    }

    return $this->query;
  }

  /**
   * @see sfPager
   */
  public function init()
  {
    $this->resetIterator();

    $query = $this->getQuery();

    $count = $query->count();
    $this->setNbResults($count);

    if (0 == $this->getPage() || 0 == $this->getMaxPerPage() || 0 == $this->getNbResults())
    {
      $this->setLastPage(0);
    }
    else
    {
      $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

      $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));

      $query->limit($this->getMaxPerPage())->skip($offset);
    }
  }

  /**
   * @see sfPager
   */
  public function getResults()
  {
    return $this->getQuery()->all();
  }

  /**
   * @see sfPager
   */
  public function retrieveObject($offset)
  {
    return $this->getQuery()->skip($offset - 1)->one();
  }
}
