<?php

namespace Wds\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;

Class DiffCommand extends Command
{
	protected function configure() {
		$this
			->setName('diff')
			->setDescription('Show changes between local and remote')
			->setDefinition(array(
				new InputArgument('server', InputArgument::REQUIRED, 'Remote server host'),
				new InputArgument('file', InputArgument::OPTIONAL, 'Show changes for a specific file.'),
			));
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$server = $input->getArgument('server');
		$file = $input->getArgument('file');

		if (!$file || (isset($file) && is_dir(getcwd().DIRECTORY_SEPARATOR.$file))) {
			passthru("/usr/local/bin/difflist {$server} {$file}");
		}
		
		if (isset($file) && is_file(getcwd().DIRECTORY_SEPARATOR.$file)) {
			passthru("/usr/local/bin/diffto {$server} {$file}");
		}
	}

}