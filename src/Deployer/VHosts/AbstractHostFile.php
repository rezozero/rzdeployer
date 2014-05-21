<?php 
/**
 * Copyright REZO ZERO 2014
 * 
 * 
 *
 *
 * @file AbstractHostFile.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
namespace rezozero\VHosts;

class AbstractHostFile
{
	private $loader;
	private $twig;

	public abstract function generateVirtualHost();
	public abstract function enableVirtualHost();
	public abstract function generatePHPPool();
	public abstract function enablePHPPool();

	public function __construct(){
		//parent::__construct();
		
		$this->loader = new Twig_Loader_Filesystem(APP_ROOT.'/views');
		$this->twig = new Twig_Environment($loader);
	}

	/**
	 * Wrap Twig_Environment::render method
	 * @param  string $template Relative path to template
	 * @param  array $vars     Variables to inject into template
	 * @return string Output content
	 */
	public function render( $template, &$vars )
	{
		return $twig->render($template, $vars);
	}
	/**
	 * Genere a file according to template and variables
	 * @param  string $dest     Destination file absolute path
	 * @param  string $template Relative path to template
	 * @param  array $vars Variables to inject into template
	 * @return boolean
	 */
	public function generateFile( $dest, $template, &$vars )
	{
		if (is_writable(dirname($dest))) {
			$content = $this->render($template, $vars);
			if (file_put_contents($dest, $content)) {
				return true;
			}
			else {
				echo "[ERROR] — Unable to create ".$dest." file.".PHP_EOL;
			}
		}
		else {
			echo "[ERROR] — ".dirname($dest)." folder is not writable.".PHP_EOL;
		}
		return false;
	}
}