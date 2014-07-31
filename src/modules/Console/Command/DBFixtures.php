<?php

namespace Elixir\Module\Console\Command;

use Elixir\DI\ContainerInterface;
use Elixir\Module\Console\Command\FixtureInterface;
use Elixir\MVC\ApplicationInterface;
use Elixir\Util\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class DBFixtures extends Command
{
    /**
     * @var ApplicationInterface 
     */
    protected $_application;
    
    /**
     * @var ContainerInterface 
     */
    protected $_container;
    
    /**
     * @param ApplicationInterface $pApplication
     * @param ContainerInterface $pContainer
     */
    public function __construct(ApplicationInterface $pApplication, ContainerInterface $pContainer) 
    {
        $this->_application = $pApplication;
        $this->_container = $pContainer;
        
        parent::__construct(null);
    }
    
    /**
     * @see Command::configure()
     */
    protected function configure()
    {
        $this->setName('db:fixtures')
             ->setDescription('Seed your database with test data using fixtures classes.')
             ->addOption(
                'db',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of service database',
                'db.default'
             )
             ->addOption(
                'module',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the module containing the fixtures classes'
             )
             ->addOption(
                'class',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the fixtures class'
             );
    }

    /**
     * @see Command::execute()
     */
    protected function execute(InputInterface $pInput, OutputInterface $pOutput)
    {
        $fixtures = [];
        $DBName = $pInput->getOption('db');
        
        $class = $pInput->getOption('class');
        
        if(null !== $class)
        {
            $fixtures[$class] = new $class();
        }
        
        $module = $pInput->getOption('module');
        
        if(null === $module && null === $class)
        {
            $dialog = $this->getHelperSet()->get('dialog');

            if(!$dialog->askConfirmation($pOutput,
                                         '<question>No modules defined, continue anyway and use all modules ? [y,n]</question>',
                                         false)) 
            {
                return;
            }
            
            $modules = $this->_application->getModules();
        }
        else
        {
            if(!$this->_application->hasModule($module))
            {
                $pOutput->writeln(sprintf('<error>The %s module does not exist</error>', $module));
                return;
            }
            
            $modules[] = $this->_application->getModule($module);
        }
        
        foreach($modules as $module)
        {
            if($this->_application->hasModule($module))
            {
                $namespace = $module->getNamespace();
                $path = $module->getPath() . '/database/fixtures/';
                $list = File::filesList($path);
                
                foreach($list as $file)
                {
                    $class = '\\' . $namespace . '\Fixtures\\' . File::filename($file);
                    
                    if(class_exists($class))
                    {
                        $fixtures[$class] = new $class();
                    }
                }
            }
        }
        
        if(count($fixtures) > 0)
        {
            usort($fixtures, [$this, 'compare']);
            
            $DB = $this->_container->get($DBName);
        
            if(null === $DB)
            {
                $pOutput->writeln(sprintf('<error>DB "%s" is not a valid connection</error>', $DBName));
                return;
            }
            
            $DB->begin();
            
            foreach($fixtures as $class => $fixture)
            {
                $fixture->setContainer($this->_container);
                $fixture->load($DB);
                
                $pOutput->writeln(sprintf('<info>Loaded: %s</info>', $class));
            }
            
            $DB->commit();
            $pOutput->writeln('<info>Fixtures loaded</info>');
        }
        else
        {
            $pOutput->writeln('<info>No fixtures classes found</info>');
        }
    }
    
    /**
     * @param FixtureInterface $p1
     * @param FixtureInterface $p2
     * @return integer
     */
    protected function compare($p1, $p2)
    {
        return ($p1->getOrder() > $p2->getOrder()) ? -1 : 1;
    }
}