<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

namespace AccessToMemory\test\mock;

class QubitSetting
{
    const NAME = 'setting.NAME';

    protected static $idCounter;
    protected static $settings;
    protected static $index;

    public $id;
    public $name;
    public $scope;

    static public function getOne($criteria)
    {
        // Turn criteria into SQL query
        $params = [];
        $sql = \BasePeer::createSelectSql($criteria, $params);

        // Handle specific SQL query
        if ($sql == 'SELECT  FROM `setting` WHERE setting.NAME=:p1')
        {
            // Determine name value in query
            $name = $criteria->getCriterion('setting.NAME')->getValue();

            // Use index to return mock setting for name value
            $id = self::$index['by name'][$name][0];
            return self::$settings[$id];
        }
    }

    public function save()
    {
        if (empty($this->id))
        {
          $this->id = self::$idCounter++;
        }

        self::$settings[$this->id] = $this;

        $this->index();
    }

    private function index()
    {
        self::$index = [
            'by name' => [],
            'by name and scope' => []
        ];

        foreach (self::$settings as $id => $setting)
        {
            if (!empty(self::$index['by name'][$setting->name]))
            {
                self::$index['by name'][$setting->name] = [];
            }

            if (!empty(self::$index['by name and scope'][$setting->name]))
            {
                self::$index['by name and scope'][$setting->name] = [];
            }

            if (!empty(self::$index['by name and scope'][$setting->name][$setting->scope]))
            {
                self::$index['by name and scope'][$setting->name][$setting->scope] = [];
            }

            self::$index['by name'][$setting->name][] = $id;
            self::$index['by name and scope'][$setting->name][$setting->scope][] = $id;
        }
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value, $options)
    {
       $this->value = $value;
    }
}
