<?php

namespace Elixir\Module\Console\Command;

use Elixir\MVC\ApplicationInterface;
use Elixir\Util\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class AssetsExport extends Command
{
    /**
     * @var string
     */
    const COPY = 'copy';
    
    /**
     * @var string
     */
    const XML = 'xml';
    
    /**
     * @var ApplicationInterface 
     */
    protected $_application;
    
    /**
     * @param ApplicationInterface $pApplication
     */
    public function __construct(ApplicationInterface $pApplication) 
    {
        $this->_application = $pApplication;
        parent::__construct(null);
    }
    
    /**
     * @see Command::configure()
     */
    protected function configure()
    {
        $this->setName('assets:export')
             ->setDescription('Export assets from one/all module(s) to the public directory')
             ->addArgument(
                'module',
                InputArgument::OPTIONAL,
                'Name of the module containing the assets for export'
             )
                
            ->addOption(
                'shared',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Names of folders shared with other modules',
                ['vendor']
             )
             ->addOption(
                'dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Public directory',
                PUBLIC_PATH . '/medias'
             )
             ->addOption(
                'mode',
                null,
                InputOption::VALUE_REQUIRED,
                'Copy files or just create the xml? [copy, xml]',
                self::COPY
             );
    }

    /**
     * @see Command::execute()
     */
    protected function execute(InputInterface $pInput, OutputInterface $pOutput)
    {
        $module = $pInput->getArgument('module');
        $sharedFolders = $pInput->getOption('shared');
        $directory = rtrim($pInput->getOption('dir'), '/\\');
        $mode = $pInput->getOption('mode');
        
        if(!in_array($mode, [self::COPY, self::XML]))
        {
            $pOutput->writeln(sprintf('<error>Mode "%s" is invalid, use copy or xml</error>', $mode));
            return;
        }
        
        $modules = [];
        
        if(null !== $module)
        {
            if(!$this->_application->hasModule($module))
            {
                $pOutput->writeln(sprintf('<error>The %s module does not exist</error>', $module));
                return;
            }
            
            $modules[] = $this->_application->getModule($module);
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
            
            $modules = $this->_application->getModules();
        }
        
        foreach($modules as $module)
        {
            $pOutput->writeln(sprintf('<info>Beginning of the export %s module</info>', $module->getName()));
            
            $list = File::filesList($module->getPath() . '/resources/public');
            
            if(count($list) > 0)
            {
                $search = 'resources/public' . DIRECTORY_SEPARATOR;
                $xml = new \SimpleXMLElement('<data></data>');
                
                $files = [];
                
                foreach($list as $file)
                {
                    if(basename($file) == 'export.xml')
                    {
                        continue;
                    }
                    
                    $value = strtr(
                        substr(
                            $file, 
                            strpos($file, $search) + strlen($search)
                        ),
                        [DIRECTORY_SEPARATOR => '/']
                    );
                    
                    $child = $xml->addChild('file', $value);
                    $shared = false;
                    
                    foreach($sharedFolders as $folder)
                    {
                        if(substr($value, 0, strlen($folder)) == $folder)
                        {
                            $child->addAttribute('shared', 'true');
                            $shared = true;
                            
                            break;
                        }
                    }
                    
                    if($shared)
                    {
                        $files[$file] = $directory . '/' . $value;
                    }
                    else
                    {
                        $files[$file] = $directory . '/' . $module->getName() . '/' . $value;
                    }
                }
                
                $pOutput->writeln('Xml generation');

                $dom = new \DOMDocument('1.0');
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = true;
                $dom->loadXML($xml->asXML());
                $dom->save($module->getPath() . '/resources/public/export.xml');
                
                if($mode == self::COPY)
                {
                    $pOutput->writeln('Copying files');
                    
                    foreach($files as $key => $value)
                    {
                        File::copy($key, $value);
                    }

                    $pOutput->writeln(sprintf('<info>Assets for the module %s are exported</info>', $module->getName()));
                }
                else
                {
                    $pOutput->writeln(sprintf('<info>Export XML for the module %s is generated</info>', $module->getName()));
                }
            }
            else
            {
                $pOutput->writeln(sprintf('<comment>No assets available for export in %s module</comment>', $module->getName()));
            }
        }
        
        $pOutput->writeln('<info>Export finished</info>');
    }
}