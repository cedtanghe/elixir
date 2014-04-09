<?php

namespace Elixir\Module\Console\Command;

use Elixir\MVC\Application;
use Elixir\Util\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class AssetsImport extends Command
{
    /**
     * @see Command::configure()
     */
    protected function configure()
    {
        $this->setName('assets:import')
             ->setDescription('Import assets from the public directory to one/all module(s)')
             ->addArgument(
                'module',
                InputArgument::OPTIONAL,
                'Name of the module containing the xml configuration for import'
             )
             ->addOption(
                'dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Public directory',
                PUBLIC_PATH . '/medias'
             );
    }

    /**
     * @see Command::execute()
     */
    protected function execute(InputInterface $pInput, OutputInterface $pOutput)
    {
        $application = Application::$registry->get('application');
        
        $module = $pInput->getArgument('module');
        $directory = trim($pInput->getOption('dir'), '/\\');
        
        $modules = array();
        
        if(null !== $module)
        {
            if(!$application->hasModule($module))
            {
                $pOutput->writeln(sprintf('<error>The %s module does not exist</error>', $module));
                return;
            }
            
            $modules[] = $application->getModule($module);
        }
        else
        {
            $dialog = $this->getHelperSet()->get('dialog');

            if(!$dialog->askConfirmation($pOutput,
                                         '<question>No modules defined, continue anyway ? [y,n]</question>',
                                         false)) 
            {
                return;
            }
            
            $modules = $application->getModules();
        }
        
        foreach($modules as $module)
        {
            $pOutput->writeln(sprintf('<info>Beginning of the import assets for %s module</info>', $module->getName()));
            $destination = $module->getPath() . '/resources/public/';
            
            if(!file_exists($destination . '/export.xml'))
            {
                $pOutput->writeln(sprintf('<comment>No xml configuration available in %s module</comment>', $module->getName()));
                continue;
            }
            
            $xml = simplexml_load_file($destination . '/export.xml');
            
            foreach($xml as $file)
            {
                $scr = $directory . '/' . ($file['shared'] ? '' : $module->getName() . '/') . $file;
                
                if(!File::copy($scr, $destination . '/' . $file))
                {
                    $pOutput->writeln(sprintf('<error>Error when import assets for %s module, can not copy</error>', $module->getName()));
                    return;
                }
            }
            
            $pOutput->writeln(sprintf('<info>Assets for the module %s are imported</info>', $module->getName()));
        }
    }
}