<?php

namespace AProud\Twiew;

/**
 * Provide customizable view based on twig template engine.
 * Template builds with prepedefined layouts and components, with settings stored in schema files.
 * Template could be generated with html attributes for any css framework - Bootstrap, Foundation or so
 * 
 * @author Sergey Krikovtsov <krikovtsov.sergey@gmail.com>
 */
class Twiew
{
	
	/**
	 * TwiewEnvironment object
	 * @var	TwiewEnvironment
	 */
	protected $twig;
	
	/**
	 * Loader object
	 * @var	TwiewSchemaLoaderInterface
	 */
	protected $schemaLoader;
	
	/**
	 * Path to project root directory
	 * @var	string
	 */
	protected $rootPath;
	
	/**
     * Store schema of layouts for current page
     * @var array 
     */
	protected $tplSchema;
	
	/**
	 * Store schema of css classes for selected css framework
	 * @var array
	 */
	protected $cssFrameworkSchema;
	
	/**
	 * @param Twig\Environment $twig   Twig environment object
	 * @param string $rootPath         Path to project root directory
	 * @return Twiew instance
	 */
	public function __construct(\Twig\Environment $twig, String $rootPath)
	{
		$this->twig = new TwiewEnvironment($twig->getLoader());
		$this->rootPath = $rootPath;
		$this->twig->getLoader()->addPath($rootPath.'/vendor/a-proud/twiew-bundle/templates', 'twiew');
		$this->schemasPath = '/vendor/a-proud/twiew-bundle/schemas';
		if (empty($this->tplSchema)) {
			$this->loadSchemaFromFile($this->schemasPath.'/twiew.tpl_schema.default.php', 'tplSchema');
		}
	}
	
	/**
	 * @return \AProud\Twiew 	Twiew object with all needed settings
	 */
	public function getView()
	{
		return $this;
	}
	
	/**
	 * @param string $path Path to tpl schema file
	 * @return	Twiew	Current twiew instance
	 */
	public function loadCssFrameworkSchemaFromFile(String $path)
	{
		$this->loadSchemaFromFile($path, 'cssFrameworkSchema');
		return $this;
	}
	
	/**
	 * @param string	$path	Path to tpl schema file
	 * @return void
	 */
	public function loadTplSchemaFromFile(String $path)
	{
		return $this->loadSchemaFromFile($path, 'tplSchema');
	}
	
	protected function loadSchemaFromFile(String $pathToSchemaFile, string $schemaName)
	{
		$schemaLoader = $this->schemaLoader;
		$pathToSchemaFile = $this->rootPath.'/'.trim($pathToSchemaFile,'/');
		$fileInfo = new \SplFileInfo($pathToSchemaFile);
		if (empty($this->schemaLoader) || $fileInfo->getExtension() == 'php') {
			$schemaLoader = new SchemaLoaderPhp(['path' => $pathToSchemaFile]);
		}
		if ($fileInfo->getExtension() == 'yaml') {
			$schemaLoader = new SchemaLoaderYaml(['path' => $pathToSchemaFile]);
		}
		$schema = $schemaLoader->getSchema();
		foreach ($schema as $route => $templateVars) {
			if (!method_exists($this, $schemaName)) {
				throw new \Exception('Method '.$schemaName.' is not declared.');
			}
			$this->$schemaName($route, $templateVars);
		}
		return $this;
	}
	
	/**
	 * @param string $key   Key in schema file
	 * @param mixed $value  (optional) Value for this key 
	 * @return Value for requested key (if get value), or current class instance (if set value)
	 */
	public function cssFrameworkSchema(String $key = '', $value = null)
	{
		if ($value !== null) { //set value
			if ($key === '' && is_array($value)) {
				$key = array_keys($value)[0];
				$value = $value[$key];
			}
			$this->cssFrameworkSchema = ArrHelper::set($this->cssFrameworkSchema, $key, $value);
			return $this;
		}
		if ($key == '') {
			return $this->cssFrameworkSchema;
		}
		return ArrHelper::get($this->cssFrameworkSchema, $key);
	}

	/**
	 * Get tplSchema parameter by key (if second parameter not setted). Or set value for tplSchema key.
	 * Return all tplSchema if both parameters not passed.
	 * Use dot notation to get nested values. Example - tplSchema('js_top.jquery_3_0_1')
	 * 
	 * @param string $key   Key in schema file
	 * @param mixed $value (optional) Value for this key 
	 * @return	Value for requested key (if get value), or current class instance (if set value)
	 */
	public function tplSchema(String $key = '', $value = null)
	{
		if ($value !== null) { //set value
			if ($key === '' && is_array($value)) {
				$key = array_keys($value)[0];
				$value = $value[$key];
			}
			if ($key == 'default') {
				$this->tplSchema = ArrHelper::mergeRecursiveDistinct($this->tplSchema, [$key => $value]);
			} else {
				$this->tplSchema = ArrHelper::set($this->tplSchema, $key, $value);
			}
			return $this;
		}
		if ($key == '') {
			return $this->tplSchema;
		}
		return ArrHelper::get($this->tplSchema, $key);
	}
	
	/**
	 * Wrapper for twig render() method
	 * @param string $route	Key for route in tplSchema
	 * @param string $route	Key for route in tplSchema
	 * @return string	rendered page
	 */
	public function render(String $route, Array $vars = []): String
	{
		$vars['twiew'] = array_merge_recursive($this->tplSchema('default'), (array)$this->tplSchema($route), $vars);
		$cssFramework = ArrHelper::get($vars, 'twiew.cssframework');
		if (!empty($cssFramework)) {
			$cssFrameworkSchema = $this->getCssFrameworkSchema($cssFramework);
			$vars['twiew'] = array_merge_recursive($vars['twiew'], $cssFrameworkSchema);
		}
		$layout = $this->tplSchema('default.root_layouts.fullpage');
		if (ArrHelper::get($vars, 'root_layout')) {
			$layout = $this->tplSchema('default.root_layouts.'.ArrHelper::get($vars, 'root_layout'));
		}
		return $this->twig->render($layout, $vars);
	}
	
	public function setSchemaLoader(TwiewSchemaLoaderInterface $loader)
	{
		$this->schemaLoader = $loader;
		return $this;
	}
	
	public function getSchemaLoader(): TwiewSchemaLoaderInterface
	{
		return $this->schemaLoader;
	}
	
}