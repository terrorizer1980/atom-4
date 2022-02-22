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
    const SCOPE = 'setting.SCOPE';
    const DEFAULT_CULTURE = 'en';

    protected static $idCounter = 1; // Used to simulate DB settings table primary key
    protected static $settings;      // Used to simulate DB storing of settings data
    protected static $index;         // Used to help simulate DB queries

    public $id;
    public $name;
    public $scope;
    public $sourceCulture;
    public $i18n = [];

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

        if ($sql == 'SELECT  FROM `setting` WHERE setting.NAME=:p1 AND setting.SCOPE=:p2')
        {
            // Determine name/scope values in query
            $name = $criteria->getCriterion('setting.NAME')->getValue();
            $scope = $criteria->getCriterion('setting.SCOPE')->getValue();

            // Use index to return mock setting for name/scope values
            $id = self::$index['by name and scope'][$name][$scope][0];
            return self::$settings[$id];
        }
    }

    public function save()
    {
        if (empty($this->id))
        {
          $this->id = self::$idCounter++;
        }

        if (empty($this->sourceCulture))
        {
          $this->sourceCulture = self::DEFAULT_CULTURE;
        }

        self::$settings[$this->id] = $this;

        $this->index();
    }

    private function index()
    {
        // Initialize indexes
        self::$index = [
            'by name' => [],
            'by name and scope' => []
        ];

        foreach (self::$settings as $id => $setting)
        {
            // Initialize index arrays if need be
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

            // Add IDs to indexes
            self::$index['by name'][$setting->name][] = $id;
            self::$index['by name and scope'][$setting->name][$setting->scope][] = $id;
        }
    }

    public function getValue($options = [])
    {
        $culture = (isset($options['culture']))
            ? $options['culture']
            : self::DEFAULT_CULTURE;

        // Handle source culture option
        $culture = (!empty($options['sourceCulture']))
            ? $this->sourceCulture
            : $culture;

        // Return i18n value if it exists
        $value = null;

        if (isset($this->i18n[$culture]))
        {
            return $this->i18n[$culture]['value'];
        }

        // Handle cultural fallback
        if ($options['cultureFallback'] && $culture != $this->sourceCulture && $value === null)
        {
            return $this->getValue(['culture' => $this->sourceCulture]);
        }

        return $value;
    }

    public function setValue($value, $options = [])
    {
        $culture = ($options['culture'])
            ? $options['culture']
            : self::DEFAULT_CULTURE;


        // Make sure that i18n value array exists
        if (!isset($this->i18n[$culture]))
        {
            $this->i18n[$culture] = [];
        }

        $this->i18n[$culture]['value'] = $value;
    }
}
