<?php

namespace Elixir\Module\Console\Command;

use Elixir\DI\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class CacheClear extends Command
{
    /**
     * @var ContainerInterface 
     */
    protected $_container;
    
    /**
     * @param ContainerInterface $pContainer
     */
    public function __construct(ContainerInterface $pContainer) 
    {
        $this->_container = $pContainer;
        parent::__construct(null);
    }
    
    /**
     * @see Command::configure()
     */
    protected function configure()
    {
        $this->setName('cache:clear')
             ->setDescription('Erasing data be cached')
            ->addOption(
                'tag',
                null,
                InputOption::VALUE_REQUIRED,
                'Tag associated to the cache services',
                'cache'
             );
    }

    /**
     * @see Command::execute()
     */
    protected function execute(InputInterface $pInput, OutputInterface $pOutput)
    {
        $tag = $pInput->getOption('tag');
        $caches = $this->_container->findByTagByTag($tag);
        
        foreach($caches as $cache)
        {
            $cache->clear();
        }
        
        $pOutput->writeln('<info>Cache(s) cleared</info>');
    }
}