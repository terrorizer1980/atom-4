<?php

use org\bovigo\vfs\vfsStream;

/**
 * @internal
 * @covers \settingsTask
 */
class settingsTaskTest extends \PHPUnit\Framework\TestCase
{
    protected $ormClasses;
    protected $vfs; // virtual filesystem

    // Fixtures

    public function setUp(): void
    {
        $this->ormClasses = [
            'setting' => \AccessToMemory\test\mock\QubitSetting::class,
        ];

        $setting = new $this->ormClasses['setting'];
        $setting->name = 'siteTitle';
        $setting->setValue('My Site');
        $setting->save();

        $setting = new $this->ormClasses['setting'];
        $setting->name = 'informationobject';
        $setting->scope = 'ui_label';
        $setting->setValue('Archival description');
        $setting->save();

        $setting = new $this->ormClasses['setting'];
        $setting->name = 'settingWithTranslation';
        $setting->setValue('Français', ['culture' => 'fr']);
        $setting->save();

        /*
        // Define virtual file system
        $directory = [
            'test.csv' => $this->csvHeader."\n".implode("\n", $this->csvData),
            'noheader.csv' => implode("\n", $this->csvData)."\n",
            'unreadable.csv' => $this->csvData[0],
            'error.log' => '',
        ];

        // Set up and cache the virtual file system
        $this->vfs = vfsStream::setup('root', null, $directory);

        // Make 'unreadable.csv' owned and readable only by root user
        $file = $this->vfs->getChild('root/unreadable.csv');
        $file->chmod('0400');
        $file->chown(vfsStream::OWNER_USER_1);
        */
    }

    // Data providers

    public function getSettingValueProvider(): array
    {
        $inputs = [
            [
                'name' => 'siteTitle',
                'options' => []
            ],
            [
                'name' => 'informationobject',
                'options' => ['scope' => 'ui_label']
            ],
        ];

        $outputs = [
            [
                'type' => 'text',
                'value' => 'My Site',
            ],
            [
                'type' => 'text',
                'value' => 'Archival description',
            ],
        ];

        return [
            [$inputs[0], $outputs[0]],
            [$inputs[1], $outputs[1]]
        ];
    }

    public function setSettingValueProvider(): array
    {
        $inputs = [
            [
                'name' => 'siteTitle',
                'options' => ['value' => 'Cool Site']
            ],
            [
                'name' => 'informationobject',
                'options' => ['scope' => 'ui_label', 'value' => 'Description']
            ],
        ];

        $outputs = [
            [
                'type' => 'text',
                'value' => 'Cool Site',
            ],
            [
                'type' => 'text',
                'value' => 'Description',
            ],
        ];

        return [
            [$inputs[0], $outputs[0]]
        ];
    }

    // Tests

    /**
     * @dataProvider getSettingValueProvider
     *
     * @param mixed $params
     * @param mixed $expected
     */
    public function testGetSettingValue($params, $expected): void
    {
        $task = new settingsTask(new sfEventDispatcher, new sfFormatter);
        $task->setOrmClasses($this->ormClasses);

        $value = $task->getSettingValue($params['name'], $params['options']);

        $this->assertSame($value, $expected['value']);
    }

    /**
     * @dataProvider setSettingValueProvider
     *
     * @param mixed $params
     * @param mixed $expected
     */
    public function testSetSettingValue($params, $expected): void
    {
        $task = new settingsTask(new sfEventDispatcher, new sfFormatter);
        $task->setOrmClasses($this->ormClasses);

        $task->setSettingValue($params['name'], $params['options']);

        $setting = $task->getSetting($params['name'], $params['options']);

        $this->assertSame($setting->getValue(), $expected['value']);
    }

    public function testGetSettingValueForNonexistent(): void
    {
        $this->expectException(Exception::class);

        $task = new settingsTask(new sfEventDispatcher, new sfFormatter);
        $task->setOrmClasses($this->ormClasses);

        $task->getSettingValue('this does not exist', []);
    }

    # TODO: make provider?
    # TODO: see if we can read exception text?
    public function testValidateOptionsNoCulture(): void
    {
        $this->expectException(Exception::class);

        $task = new settingsTask(new sfEventDispatcher, new sfFormatter);
        $task->setOrmClasses($this->ormClasses);

        $task->validateOptions(['get', 'setting name'], []);
    }

    public function testValidateOptionsBadCulture(): void
    {
        $this->expectException(Exception::class);

        $task = new settingsTask(new sfEventDispatcher, new sfFormatter);
        $task->setOrmClasses($this->ormClasses);

        $task->validateOptions(['get', 'setting name'], ['culture' => 'invalid']);
    }

    public function testValidateOptionsValueUsedForWrongOperation(): void
    {
        $this->expectException(Exception::class);

        $task = new settingsTask(new sfEventDispatcher, new sfFormatter);
        $task->setOrmClasses($this->ormClasses);

        $task->validateOptions(['set', 'setting name'], ['value' => 'some value', 'culture' => 'en']);
    }

    public function testGetSettingValueWithNonDefaultLanguage(): void
    {
        $task = new settingsTask(new sfEventDispatcher, new sfFormatter);
        $task->setOrmClasses($this->ormClasses);

        $setting = $task->getSetting('settingWithTranslation');

        $this->assertSame($setting->getValue(['culture' => 'fr']), 'Français');
    }

    public function testSetSettingValueWithNonDefaultLanguage(): void
    {
        $task = new settingsTask(new sfEventDispatcher, new sfFormatter);
        $task->setOrmClasses($this->ormClasses);

        $options = ['value' => 'Español', 'culture' => 'es'];
        $setting = $task->setSettingValue('settingWithTranslation', $options);

        $setting = $task->getSetting('settingWithTranslation');

        $this->assertSame($setting->getValue(['culture' => 'es']), 'Español');
        $this->assertSame($setting->getValue(['culture' => 'fr']), 'Français');
    }


    /*
    public function validateOptions($arguments, $options) {
        // Make sure culture is valid
        if (!sfCultureInfo::validCulture($options['culture'])) {
            throw new Exception("Culture is invalid.");
        }

        // Check that the "value" option is being used for the appropriate operation
        if (!empty($options['value']) && $argument['operation'] != 'get') {
            throw new Exception("The 'value' option must only be used with the 'get' operation.");
        }
    */

    /*
    public function testSetAndGetSourceName(): void
    {
        $auditer = new CsvImportAuditer();

        $auditer->setSourceName('some_import');
        $this->assertSame('some_import', $auditer->getSourceName());
    }

    public function testSetAndGetTargetName(): void
    {
        $auditer = new CsvImportAuditer();

        $auditer->setTargetName('some_target_name');
        $this->assertSame('some_target_name', $auditer->getTargetName());
    }

    public function testSetAndGetFilename()
    {
        $auditer = new CsvImportAuditer();

        $auditer->setFileName($this->vfs->url().'/test.csv');
        $this->assertSame($this->vfs->url().'/test.csv', $auditer->getFileName());
    }

    public function testSetFilenameFileNotFoundException(): void
    {
        $this->expectException(sfException::class);
        $auditer = new CsvImportAuditer();
        $auditer->setFilename('bad_name.csv');
    }

    public function testSetFilenameFileUnreadableException()
    {
        $this->expectException(sfException::class);
        $auditer = new CsvImportAuditer();
        $auditer->setFilename($this->vfs->url().'/unreadable.csv');
    }

    public function testSetFilenameSuccess()
    {
        $importer = new CsvImportAuditer();
        $importer->setFilename($this->vfs->url().'/test.csv');
        $this->assertSame(
            $this->vfs->url().'/test.csv',
            $importer->getFilename()
        );
    }

    public function testSetOptionsThrowsTypeError(): void
    {
        $this->expectException(TypeError::class);

        $auditer = new CsvImportAuditer();
        $auditer->setOptions(new stdClass());
    }

    public function testSetAndGetIdColumnName(): void
    {
        $auditer = new CsvImportAuditer();

        $auditer->setOption('idColumnName', 'some_column');
        $this->assertSame('some_column', $auditer->getOption('idColumnName'));
    }

    public function testSetOptionFromOptions(): void
    {
        $auditer = new CsvImportAuditer();
        $auditer->setOptions([
            'progressFrequency' => 5,
            'idColumnName' => 'some_column',
        ]);

        $this->assertSame(5, $auditer->getOption('progressFrequency'));
        $this->assertSame('some_column', $auditer->getOption('idColumnName'));
    }

    public function testSourceNameDefaultsToFilename()
    {
        $filename = $this->vfs->url().'/test.csv';
        $importer = new CsvImportAuditer();
        $importer->setFilename($filename);

        $this->assertSame(basename($filename), $importer->getSourceName());
    }

    public function testDoAuditNoFilenameException(): void
    {
        $this->expectException(sfException::class);

        $auditer = new CsvImportAuditer();
        $auditer->doAudit();
    }

    public function testImportRowsWithDefaultTargetName(): void
    {
        $auditer = new CsvImportAuditer(['quiet' => true]);
        $auditer->setFilename($this->vfs->url().'/test.csv');

        $auditer->setOrmClasses($this->ormClasses);
        $auditer->setSourceName('test_import');

        $auditer->doAudit();

        $this->assertSame($auditer->getMissingIds(), [124 => 2]);
    }

    public function testProcessRowThrowsExceptionIfBadLegacyIdColumn(): void
    {
        $this->expectException(UnexpectedValueException::class);

        // Row with mis-named ID column
        $row = [
            'id' => '123',
        ];

        $auditer = new CsvImportAuditer();
        $auditer->setOrmClasses($this->ormClasses);
        $auditer->setSourceName('test_import');

        $result = $auditer->processRow($row);
    }

    public function testProcessRowThrowsExceptionIfNoIdColumn(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $auditer = new CsvImportAuditer();

        $auditer->processRow([]);
    }
    */
}
