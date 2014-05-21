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
	public abstract function generateVirtualHost();
	public abstract function saveVirtualHost();
	public abstract function enableVirtualHost();
	public abstract function createPHPSocket();
}