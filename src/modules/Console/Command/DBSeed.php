<?php

namespace Elixir\Module\Console\Command;

use Elixir\DI\ContainerInterface;
use Elixir\MVC\ApplicationInterface;
use Elixir\Util\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class DBSeed extends Command
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
        $this->setName('db:seed')
             ->setDescription('Seed your database with test data using seed classes.')
             ->addOption(
                'module',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the module containing the seeds classes'
             )
             ->addOption(
                'class',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the seeds class'
             );
    }

    /**
     * @see Command::execute()
     */
    protected function execute(InputInterface $pInput, OutputInterface $pOutput)
    {
        $seeds = [];
        
        $class = $pInput->getOption('class');
        
        if(null !== $class)
        {
            $seeds[] = $class;
        }
        
        $module = $pInput->getOption('module');
        
        if(null !== $module)
        {
            if($this->_application->hasModule($module))
            {
                $namespace = $this->_application->getModule($module)->getNamespace();
                $path = APPLICATION_PATH . '/modules/' . $module . '/database/seeds/';
                $list = File::filesList($path);
                
                foreach($list as $file)
                {
                    $class = '\\' . $namespace . '\Seed\\' . File::filename($file);
                    
                    if(class_exists($class))
                    {
                        $seeds[] = $class;
                    }
                }
            }
        }
        
        if(count($seeds) > 0)
        {
            foreach($seeds as $class)
            {
                try 
                {
                    $seed = new $class();
                    $seed->seed($this->_container);
                    
                    $pOutput->writeln(sprintf('<info>Seeded: %s</info>', $class));
                } 
                catch(Exception $e)
                {
                    $pOutput->writeln(sprintf('<error>An error occurred while attempting to seeds class "%s"</error>', $class));
                }
            }
            
            $pOutput->writeln('<info>Seeding finished</info>');
        }
        else
        {
            $pOutput->writeln('<error>No seeds classes found</error>');
        }
    }
}