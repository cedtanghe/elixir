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
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'Modules namespace'
             )
             ->addOption(
                'dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Modules location',
                APPLICATION_PATH . '/modules/'
             );
    }

    protected function execute(InputInterface $pInput, OutputInterface $pOutput)
    {
        $name = $pInput->getArgument('name');
        $parent = $pInput->getArgument('parent');
        
        if(null === $parent)
        {
            $parent = '';
        }
        else
        {
            $parent = "public function getParent()\n\t{\n\t\treturn \'' . $parent . '\';\n\t}";
        }
        
        $namespace = $pInput->getOption('namespace') ?: $name;
        $dir = $pInput->getOption('dir');
        $modulePath = rtrim($dir, '/') . '/' . $name;
        
        if(!preg_match('/^[A-Z][a-zA-Z0-9]{2,}$/', $name))
        {
            $pOutput->writeln(sprintf('<error>Name of the %s module is invalid</error>', $name));
            return;
        }
        
        if(file_exists($modulePath))
        {
            $dialog = $this->getHelperSet()->get('dialog');

            if(!$dialog->askConfirmation($pOutput,
                                         sprintf('<question>The %s module already exists, continue anyway ? (y/n)</question>', $name),
                                         false
                                        )) 
            {
                return;
            }
        }
		
        if(File::copy(__DIR__ . '/../resources/module_skeleton', $modulePath))
        {
            $list = File::filesList($modulePath);
            
            foreach($list as $file)
            {
                $content = file_get_contents($file); 
                $content = str_replace('{NAMESPACE}', $namespace, $content);
                $content = str_replace('{MODULE}', $name, $content);
                $content = str_replace('{MODULE_PARENT}', $parent, $content);
                
                file_put_contents($file, $content);
            }
            
            $pOutput->writeln(sprintf('<info>Module %s created !</info>', $name));
        }
        else
        {
            if(file_exists($modulePath))
            {
                @unlink($modulePath);
            }

            $pOutput->writeln(sprintf('<error>Error when creating %s module, can not copy</error>', $name));
        }
    }
}