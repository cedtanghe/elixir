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

class ModuleCreate extends Command
{
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
        $this->setName('module:create')
             ->setDescription('Creating a new module')
             ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of module'
             )
             ->addOption(
                'parent',
                null,
                InputOption::VALUE_REQUIRED,
                'Parent of module'
             )
             ->addOption(
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'Module namespace'
             );
    }

    /**
     * @see Command::execute()
     */
    protected function execute(InputInterface $pInput, OutputInterface $pOutput)
    {
        $name = $pInput->getArgument('name');
        $parent = $pInput->getOption('parent');
        
        if(null === $parent)
        {
            $parent = '';
        }
        else
        {
            if(!$this->_application->hasModule($parent))
            {
                $pOutput->writeln(sprintf('<error>The %s module does not exist</error>', $parent));
                return;
            }
            
            $parent = "public function getParent()\n    {\n        return '" . $parent . "';\n    }";
        }
        
        $namespace = $pInput->getOption('namespace');
        
        if(null === $namespace)
        {
            $namespace = $name;
        }
        
        $modulePath = APPLICATION_PATH . '/modules/' . $name;
        
        if(!preg_match('/^[A-Z][a-zA-Z0-9]{2,}$/', $name))
        {
            $pOutput->writeln(sprintf('<error>Name of the %s module is invalid</error>', $name));
            return;
        }
        
        if(file_exists($modulePath) || $this->_application->hasModule($name))
        {
            $dialog = $this->getHelperSet()->get('dialog');

            if(!$dialog->askConfirmation($pOutput,
                                         sprintf('<question>The %s module already exists, continue anyway ? [y,n]</question>', $name),
                                         false)) 
            {
                return;
            }
        }
        
        if(File::copy(__DIR__ . '/../resources/module-skeleton', $modulePath))
        {
            $list = File::filesList($modulePath);
            
            foreach($list as $file)
            {
                $content = file_get_contents($file); 
                $content = str_replace('{NAMESPACE}', $namespace, $content);
                $content = str_replace('{MODULE}', $name, $content);
                $content = str_replace('{MODULE_PARENT}', $parent, $content);
                
                file_put_contents($file, $content . "\n");
            }
            
            /************ REGISTER MODULE ************/
            
            $file = APPLICATION_PATH . '/app.php';
            $lines = file($file, FILE_IGNORE_NEW_LINES);
            $content = implode("\n", $lines);
            $pattern = '/new\s+[\\\]' . $namespace . '[\\\]Bootstrap/';
            
            if(!preg_match($pattern, $content))
            {
                $i = count($lines);

                while(--$i)
                {
                    if(preg_match('/^\$application->addModule/', $lines[$i]))
                    {
                        array_splice(
                            $lines, 
                            $i + 1, 
                            0, 
                            '$application->addModule(new \\' . $namespace . '\Bootstrap());'
                        );

                        break;
                    }
                }

                file_put_contents($file, implode("\n", $lines) . "\n");
            }
            
            /************ REGISTER AUTOLOAD ************/
            
            $file = APPLICATION_PATH . '/autoload.php';
            $lines = file($file, FILE_IGNORE_NEW_LINES);
            $content = implode("\n", $lines);
            $pattern = '/\$loader->addNamespace\(\s*[\'"]' . $namespace . '[\'"]/';
            
            if(!preg_match($pattern, $content))
            {
                $i = count($lines);
                $added = false;

                while(--$i)
                {
                    if(preg_match('/^\$loader->addNamespace/', $lines[$i]))
                    {
                        $added = true;
                        
                        array_splice(
                            $lines, 
                            $i + 1, 
                            0, 
                            '$loader->addNamespace(\'' . $namespace . '\', __DIR__ . \'/modules/' . $name . '/\');'
                        );

                        break;
                    }
                }
                
                if(!$added)
                {
                    if(false !== strpos($lines[count($lines) - 1], '?>'))
                    {
                        array_splice(
                            $lines, 
                            count($lines) - 1, 
                            0, 
                            ['$loader->addNamespace(\'' . $namespace . '\', __DIR__ . \'/modules/' . $name . '/\');', '']
                        );
                    }
                    else
                    {
                        $lines[] = '$loader->addNamespace(\'' . $namespace . '\', __DIR__ . \'/modules/' . $name . '/\');';
                    }
                }
            }

            file_put_contents($file, implode("\n", $lines) . "\n");
            $pOutput->writeln(sprintf('<info>Module %s created</info>', $name));
            
            return;
        }
        
        if(file_exists($modulePath))
        {
            @unlink($modulePath);
        }

        $pOutput->writeln(sprintf('<error>Error when creating %s module, can not copy</error>', $name));
    }
}
