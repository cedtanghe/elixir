<?php

namespace Elixir\Module\Console\Command;

use Elixir\Util\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateModule extends Command
{
    protected function configure()
    {
        $this->setName('base:create-module')
             ->setDescription('Creating a new module')
             ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of module'
             )
             ->addArgument(
                'parent',
                InputArgument::OPTIONAL,
                'Parent of module'
             )
             ->addOption(
                'dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Modules location',
                '../../../../../modules/'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $parent = $input->getArgument('parent');
        $dir = $input->getOption('dir');
        $modulePath = rtrim($dir, '/') . '/' . $name;
        
        if(!file_exists($modulePath))
        {
            if(File::copy(__DIR__ . '/../resources/module_skeleton', $modulePath))
            {
                $output->writeln('Module created !');
            }
            else
            {
                @unlink($modulePath);
                $output->writeln('Error when creating module, can not copy');
            }
        }
        else
        {
            $output->writeln(sprintf('Error when creating module, the %s module already exists', $name));
        }
    }
}