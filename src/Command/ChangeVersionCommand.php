<?php

namespace Wds\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

Class ChangeVersionCommand extends Command
{
	protected function configure() {
		$this
			->setName('change-version')
			->setDescription('Change the version of a instance')
			->setDefinition(array(
				new InputArgument('version', InputArgument::OPTIONAL, 'New version of the instance.'),
				new InputArgument('instance_id', InputArgument::OPTIONAL, 'Instance ID that should be updated'),
				new InputOption('from', 'f', InputOption::VALUE_REQUIRED, 'If specified, update every instances which version is equal than specified.'),
			));
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		// $from = $input->getArgument('from');
		$instance = $input->getArgument('instance_id');
		$version = $input->getArgument('version');
		$from = $input->getOption('from');

		if ($from)
			$instances = $this->getInstancesFromVersion($from);

		foreach ($instances as $instance) {
			
		}
	}

	private function getInstancesFromVersion($version) {
		$instances = array();
		$initd = new \FilesystemIterator('/etc/init.d/');
		foreach ($initd as $file) {
			if (!$file->isLink()) 
				continue;
			$filename = $file->getFilename();
			$prefix = strtok($filename, '-');
			if ($prefix != 'wtm' && $prefix != 'ntp')
				continue;
			
			$path = explode('/', $file->getLinkTarget());
			if ($path[4] == $version)
				$instances[] = array('id' => strtok(''), 'prefix' => $prefix);
		}

		return $instances;
	}
}