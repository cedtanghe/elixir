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
                'Name of the module containing the seed classes'
             )
             ->addOption(
                'class',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the seed class'
             );
    }

    /**
     * @see Command::execute()
     */
    protected function execute(InputInterface $pInput, OutputInterface $pOutput)
    {
        $seeds = [];
        $DBName = $pInput->getOption('db');
        
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
            $DB = $this->_container->get($DBName);
        
            if(null === $DB)
            {
                $pOutput->writeln(sprintf('<error>DB "%s" is not a valid connection</error>', $DBName));
                return;
            }
            
            $DB->begin();
            
            foreach($seeds as $class)
            {
                try 
                {
                    $seed = new $class();
                    $seed->seed($DB);
                    
                    $pOutput->writeln(sprintf('<info>Seeded: %s</info>', $class));
                } 
                catch(\Exception $e)
                {
                    $DB->rollback();
                    $pOutput->writeln(sprintf('<error>An error occurred while attempting to seed class "%s"</error>', $class));
                    
                    return;
                }
            }
            
            $DB->commit();
            $pOutput->writeln('<info>Seeding finished</info>');
        }
        else
        {
            $pOutput->writeln('<info>No seed classes found</info>');
        }
    }
}