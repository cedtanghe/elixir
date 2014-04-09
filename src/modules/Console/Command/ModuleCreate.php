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

class ModuleCreate extends Command
{
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
        $application = Application::$registry->get('application');
        $name = $pInput->getArgument('name');
        $parent = $pInput->getOption('parent');
        
        if(null === $parent)
        {
            $parent = '';
        }
        else
        {
            if(!$application->hasModule($parent))
            {
                $pOutput->writeln(sprintf('<error>The %s module does not exist</error>', $parent));
                return;
            }
            
            $parent = "public function getParent()\n\t{\n\t\treturn '" . $parent . "';\n\t}";
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
        
        if(file_exists($modulePath) || $application->hasModule($name))
        {
            $dialog = $this->getHelperSet()->get('dialog');

            if(!$dialog->askConfirmation($pOutput,
                                         sprintf('<question>The %s module already exists, continue anyway ? [y,n]</question>', $name),
                                         false)) 
            {
                return;
            }
        }
        
        $error = false;
		
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
            
            // Register module
            $r = fopen(APPLICATION_PATH . '/app.php', 'r+');
            $lines = array();
            $content = '';

            while(($buffer = fgets($r, 4096)) !== false) 
            {
                $lines[] = $buffer;
                $content .= $buffer;
            }

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
                            array(
                                '$application->addModule(new \\' . $namespace . '\Bootstrap());',
                                "\n"
                            )
                        );

                        break;
                    }
                }

                ftruncate($r, 0);
                rewind($r);
                fwrite($r, implode('', $lines));
                fclose($r);
            }
            
            // Register autoload
            $r = fopen(APPLICATION_PATH . '/autoload.php', 'r+');
            $lines = array();
            $content = '';

            while(($buffer = fgets($r, 4096)) !== false) 
            {
                $lines[] = $buffer;
                $content .= $buffer;
            }

            $pattern = '/\$loader->addNamespace\(\s*[\'"]' . $namespace . '[\'"]/';
            
            if(!preg_match($pattern, $content))
            {
                $i = count($lines);

                while(--$i)
                {
                    if(preg_match('/^\$loader->addNamespace/', $lines[$i]))
                    {
                        array_splice(
                            $lines, 
                            $i + 1, 
                            0, 
                            array(
                                "\n",
                                '$loader->addNamespace(\'' . $namespace . '\', __DIR__ . \'/modules/' . $name . '/\');'
                            )
                        );

                        break;
                    }
                }

                ftruncate($r, 0);
                rewind($r);
                fwrite($r, implode('', $lines));
                fclose($r);
            }

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