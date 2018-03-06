<?php

namespace Wds\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

Class ChangeVersionCommand extends Command
{

	const STATUS_STOPPED = 0;
	const STATUS_STARTED = 1;
	const STATUS_UNKNOWN = 2;

	private $initd_scripts = array("wc7", "ntp", "wtm");

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
				$instances[] = strtok('');
		}

		return $instances;
	}

	private function getInstanceStatus($ini_script) {

		$output = exec("service $ini_script status");
		if (stristr($output, "started"))
			return self::STATUS_STARTED;
		if (stristr($output, "stopped"))
			return self::STATUS_STOPPED;

		return self::STATUS_UNKNOWN;
	}
	
	protected function configure() {
		$this
			->setName('change-version')
			->setDescription('Change the version of a instance')
			->setDefinition(array(
				new InputArgument('version', InputArgument::REQUIRED, 'New version of the instance.'),
				new InputArgument('instance_id', InputArgument::OPTIONAL, 'Instance ID that should be updated'),
				new InputOption('from', 'f', InputOption::VALUE_REQUIRED, 'If specified, update every instances which version is equal than specified.'),
			));
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		// $from = $input->getArgument('from');
		$instance = $input->getArgument('instance_id');
		$version = $input->getArgument('version');
		$from = $input->getOption('from');

		// if (!file_exists("/home/instances/versions/{$version}")) {
		// 	$output->writeln("ERROR: Version {$version} not found.");
		// 	exit;
		// }

		if (!$from && !$instance) {
			$output->writeln('You should provide a instance number or --from argument');
			exit;
		}

		if ($from)
			$instances = $this->getInstancesFromVersion($from);
		else
			$instances[] = $instance;

		foreach ($instances as $instance) {
			//find the current init script to change to a new version.
			$prefix = $status = NULL;
			foreach ($this->initd_scripts as $ini) {
				if (is_file("/etc/init.d/{$ini}-{$instance}") || is_link("/etc/init.d/{$ini}-{$instance}")) {
					$prefix = $ini;
					$status = $this->getInstanceStatus("{$ini}-{$instance}");
					break;
				}
			}
			if ($prefix == NULL) {
				$output->writeln("ERROR: It was not possible to discover the initd script.");
				exit;
			}
			@unlink("/etc/init.d/{$prefix}-{$instance}");
			symlink("/home/instances/versions/{$version}/util/{$prefix}.initd", "/etc/init.d/{$prefix}-{$instance}");
			if ($status == self::STATUS_STARTED)
				popen("service {$prefix}-{$instance} restart", "r");
				// exec("service {$prefix}-{$instance} restart");
		}
	}

}