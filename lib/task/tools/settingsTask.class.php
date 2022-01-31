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

/**
 * Set or get setting values.
 *
 * @author  Mike Cantelon <mike@artefactual.com>
 */
class settingsTask extends arBaseTask
{
    protected $ormClasses;

    public function __construct(sfEventDispatcher $dispatcher, sfFormatter $formatter)
    {
        $this->setOrmClasses([
            'setting' => QubitSetting::class,
        ]);

        parent::__construct($dispatcher, $formatter);
    }

    public function setOrmClasses(array $classes): void
    {
        $this->ormClasses = $classes;
    }

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        $this->validateOptions($arguments, $options);

        try {
            switch (strtolower($arguments['operation'])) {

                case 'get':
                    $this->getSettingValue($arguments['name'], $options);
                    break;

                case 'set':
                    $this->setSettingValue($arguments['name'], $options);
                    break;

                default:
                    throw sfException('Invalid operation.');
                    exit;
            }
        } catch (ValueError $e) {
            throw sfException($e->getMessage());
        }

        $this->log('Done.');
    }

    public function getSetting($name, $options) {
        $criteria = new Criteria;
        $criteria->add($this->ormClasses['setting']::NAME, $name);

        if (!empty($options['scope'])) {
            $criteria->add($this->ormClasses['setting']::SCOPE, $options['scope']);
        }

        return $this->ormClasses['setting']::getOne($criteria);
    }

    public function getSettingValue($name, $options) {
        $setting = $this->getSetting($name, $options);

        if (empty($setting)) {
            throw new Exception("Setting does not exist.");
        }

        if (!empty($options['to-file'])) {
            # write to file
        } else {
            return $setting->getValue(['culture' => $options['culture']]);
        }
    }

    public function setSettingValue($name, $options) {
        $setting = $this->getSetting($name, $options);

        if (empty($setting)) {
            if (empty($options['strict'])) {
                $setting = new $this->ormClasses['setting'];

                if (!empty($options['scope'])) {
                    $setting->scope = $options['scope'];
                }

                $setting->name = $name;
            } else {
                throw new Exception("Settings can't be created in strict mode.");
            }
        }

        $setting->setValue($options['value'], ['culture' => $options['culture']]);
        $setting->save();
    }

    public function validateOptions($arguments, $options) {
        // Make sure culture is valid
        if (!sfCultureInfo::validCulture($options['culture'])) {
            throw new Exception("Culture is invalid.");
        }

        // Check that the "value" option is being used for the appropriate operation
        if (!empty($options['value']) && $argument['operation'] != 'get') {
            throw new Exception("The 'value' option must only be used with the 'get' operation.");
        }

        // Check that the "from-file" option is being used for the appropriate operation
        if (!empty($options['from-file']) && $argument['operation'] != 'set') {
            throw new Exception("The 'from-file' option must only be used with the 'set' operation.");
        }

        // Check that "from-file" option isn't being used at the same time as the "value" option
        if (!empty($options['from-file']) && !empty($options['value'])) {
            throw new Exception("The 'from-file' and 'value' options can't be used at the same time.");
        }

        // Check that the file specified by the "from-file" option actually exists
        if (!empty($options['from-file']) && !file_exists($options['from-file'])) {
            throw new Exception("The 'from-file' option must refer to an existing file.");
        }

        // Check that the file specified by the "from-file" option can be read
        if (!empty($options['from-file']) && !is_readable($options['from-file'])) {
            throw new Exception("The 'from-file' option must refer to a readable file.");
        }

        // Check that the "to-file" option is being used for the appropriate operation
        if (!empty($options['to-file']) && $argument['operation'] != 'get') {
            throw new Exception("The 'to-file' option must only be used with the 'get' operation.");
        }

        // Check that the file specified by the "to-file" option doesn't already exist
        if (!empty($options['to-file']) && file_exists($options['to-file'])) {
            throw new Exception("The 'to-file' option mustn't refer to an already existing file.");
        }

        // Check that the file specified by the "to-file" option can be written to
        if (!empty($options['to-file']) && !is_writeable($options['to-file'])) {
            throw new Exception("The 'to-file' option must refer to a writeable file path.");
        }
    }

    /**
     * @see sfBaseTask
     */
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument(
                'operation',
                sfCommandArgument::REQUIRED,
                'Setting operation ("get" or "set").'
            ),
            new sfCommandArgument(
                'name',
                sfCommandArgument::REQUIRED,
                'Name of setting.'
            ),
        ]);

        $this->addOptions([
            new sfCommandOption(
                'application',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'The application name',
                'qubit'
            ),
            new sfCommandOption(
                'env',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The environment',
                'cli'
            ),
            new sfCommandOption(
                'connection',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The connection name',
                'propel'
            ),

            // Tool options
            new sfCommandOption(
                'culture',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Culture (default: "en"))',
                "en"
            ),
            new sfCommandOption(
                'scope',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Scope of setting to get or set',
                null
            ),
            new sfCommandOption(
                'value',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Value (if using "set" operator and not using the "from-file" option))',
                null
            ),
            new sfCommandOption(
                'from-file',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'File whose contents are to be used as a value (if using "set" operation)',
                null
            ),
            new sfCommandOption(
                'to-file',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'File to write value to (if using "get" operation)',
                null
            ),
            new sfCommandOption(
                'strict',
                null,
                sfCommandOption::PARAMETER_NONE,
                'Prevent creation of new settings when performing "set" operation',
                null
            ),
        ]);

        $this->namespace = 'tools';
        $this->name = 'setting';
        $this->briefDescription = 'Get or set settings.';
        $this->detailedDescription = <<<'EOF'
Get or set settings.
EOF;
    }
}
