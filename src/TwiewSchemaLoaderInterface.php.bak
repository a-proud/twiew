<?php

namespace AProud\Twiew;

/**
 * Interface for Twiew loader classes. 
 * Loaders needed to get template settings from files (with different formats), database, redis, api or so.
 *  
 * @author Sergey Krikovtsov <krikovtsov.sergey@gmail.com>
 */
interface TwiewSchemaLoaderInterface
{
	/**
	 * @param array $storageSettings	Parameters for storage - path to file (if storage is a file), db credentials (if storage is db).
	 * 
	 * @return	TwiewSchemaLoaderInterface	Loader object
	 */
	public function __construct(Array $storageSettings);
	
	/**
	 * @return array	Array with schema parameters - layout information, data for each layout and component.
	 */
	public function getSchema(): Array;
	
	/**
	 * @param array $schema	Schema to save.
	 * 
	 * @return bool	True if sucessfully saved, false on failed.
	 */
	public function saveSchema(Array $schema): Bool;
}