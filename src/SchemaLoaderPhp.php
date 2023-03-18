<?php

namespace AProud\Twiew;

/**
 * Provide reading and writing shema in php files, as PHP array
 * File should be created in format <?php return array('schema_key' => array(...
 */
class SchemaLoaderPhp implements TwiewSchemaLoaderInterface
{
	/**
	 *  @param array $storageSettings Array with 'path' key - path to storage file
	 *  @return void
	 */
	private $storageSettings;
	
	public function __construct(Array $storageSettings)
	{
		$this->storageSettings = $storageSettings;
	}
	
	/**
	 *  {@inheritdoc}
	 */
	public function getSchema(): Array
	{
		try {
			$schema = require($this->storageSettings['path']);
		}
		catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
		if (!is_array($schema)) {
			throw new \Exception('SchemaLoaderPhp tried to parse '.$this->storageSettings['path'].' file. 
				Check this file, it doesn\'t contains valid PHP array. 
				Use setSchemaLoader() method to use another loader.');
		}
		return (array)$schema;
	}
	
	/**
	 *  {@inheritdoc}
	 */
	public function saveSchema(Array $schema): Boolean
	{
		return (bool)file_put_contents($this->storageSettings['path'], '<?php'.PHP_EOL.'return '.var_export($schema));
	}

}