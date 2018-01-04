<?php

namespace Wds;

require '../vendor/autoload.php';

use Symfony\Component\Console\Application;

class Wds extends Application
{

	const VERSION = '0.0.1';
	const APPNAME = 'WDS';

	public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
	{
		parent::__construct(self::APPNAME, self::VERSION);
	}

	protected function getDefaultCommands() {
		$commands = array_merge(parent::getDefaultCommands(), array(
			new Command\AboutCommand(),
			new Command\ChangeVersionCommand(),
		));

		return $commands;
	}
}
