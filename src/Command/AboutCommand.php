<?php

namespace Wds\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

Class AboutCommand extends Command
{
	protected function configure() {
		$this
			->setName('about')
			->setDescription('Show about information')
			->setHelp('WTF?');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$output->write('about!');

	}

}