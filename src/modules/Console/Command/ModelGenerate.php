<?php

namespace Elixir\Module\Console\Command;

use Elixir\DB\DBInterface;
use Elixir\DB\Result\SetAbstract;
use Elixir\DI\ContainerInterface;
use Elixir\MVC\ApplicationInterface;
use Elixir\MVC\Module\ModuleInterface;
use Elixir\Util\File;
use Elixir\Util\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class ModelGenerate extends Command
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
        $this->setName('model:generate')
             ->setDescription('Generate (or update) model(s) via the database')
             ->addArgument(
                'module',
                InputArgument::REQUIRED,
                'Module name when exporting models'
             )
             ->addArgument(
                'table',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Name(s) of the table(s) used to generate model(s) (separate with spaces)'
             )
             ->addOption(
                'prefix',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Prefix tables (not included in the model name)'
             )
             ->addOption(
                'db',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of service database',
                'db.default'
             )
             ->addOption(
                'filter',
                null,
                InputOption::VALUE_REQUIRED,
                'Regular expression filtering model(s) to generate'
             )   
             ->addOption(
                'copy',
                null,
                InputOption::VALUE_REQUIRED,
                'Copy model(s) if already exist',
                true
             );
    }
    
    /**
     * @see Command::execute()
     */
    protected function execute(InputInterface $pInput, OutputInterface $pOutput)
    {
        $module = $pInput->getArgument('module');
        $tables = $pInput->getArgument('table');
        $DBName = $pInput->getOption('db');
        
        $DB = $this->_container->get($DBName);
        
        if(null === $DB)
        {
            $pOutput->writeln(sprintf('<error>"%s" is not a valid connection</error>', $DBName));
            return;
        }
        
        $prefixs = $pInput->getOption('prefix');
        $filter = $pInput->getOption('filter');
        $copy = $pInput->getOption('copy') == 'true';
        
        if(!$this->_application->hasModule($module))
        {
            $pOutput->writeln(sprintf('<error>The %s module does not exist</error>', $module));
            return;
        }
        
        $module = $this->_application->getModule($module);
        
        if(count($tables) == 0)
        {
            $dialog = $this->getHelperSet()->get('dialog');

            if(!$dialog->askConfirmation($pOutput,
                                         '<question>No table(s) defined, continue anyway ? [y,n]</question>',
                                         false)) 
            {
                return;
            }
            
            $pOutput->writeln('<info>Retrieving table names</info>');
            $tables = $this->getTablesList($DB);
            
            if(count($tables) == 0)
            {
                $pOutput->writeln('<error>No tables available for the generation of models</error>');
                return;
            }
        }
        
        $version = $copy ? time() : 0;
        
        foreach($tables as $table)
        {
            if(null !== $filter)
            {
                if(!preg_match($filter, $table))
                {
                    continue;
                }
            }
            
            $pOutput->writeln(sprintf('<info>Model for %s table generation</info>', $table));
            $definition = $this->getTableDefinition($DB, $table);
            
            if(false === $definition)
            {
                $pOutput->writeln(sprintf('<error>Table "%s" does not exist</error>', $table));
                return;
            }
            
            $model = $table;
            
            foreach($prefixs as $prefix)
            {
                $model = preg_replace('/^' . $prefix . '/', '', $model);
            }
            
            $model = Str::camelize($model);
            
            if(!$this->generateModel($definition, $model, $module, $copy, $version))
            {
                $pOutput->writeln(sprintf('<error>Error when generating the %s model</error>', $model));
                return;
            }
            else
            {
                $pOutput->writeln(sprintf('<info>Model %s is generated</info>', $model));
            }
        }
        
        $pOutput->writeln('<info>Generation ended</info>');
    }
    
    /**
     * @param DBInterface $pDB
     * @return array
     */
    protected function getTablesList(DBInterface $pDB)
    {
        $tables = array();
        $stmt = $pDB->query('SHOW TABLES');
        
        foreach($stmt->fetchAll(SetAbstract::FETCH_NUM) as $row)
        {
            $tables[] = $row[0];
        }
        
        return $tables;
    }
    
    /**
     * @param DBInterface $pDB
     * @param string $pTable
     * @return array
     */
    protected function getTableDefinition(DBInterface $pDB, $pTable)
    {
        try 
        {
            $definition = array(
                'table' => $pTable,
                'columns' => array(),
                'primary' => null,
                'auto_increment' => false
            );
            
            $stmt = $pDB->query('DESCRIBE ' . $pTable);
            $rows = $stmt->fetchAll();
            
            foreach($rows as $row)
            {
                $definition['columns'][] = $row['Field'];
                
                if($row['Key'] == 'PRI')
                {
                    if(null === $definition['primary'])
                    {
                        $definition['primary'] = $row['Field'];
                    }
                    else
                    {
                        if(!is_array($definition['primary']))
                        {
                            $definition['primary'] = array($definition['primary']);
                        }
                        
                        $definition['primary'][] = $row['Field'];
                    }
                }
                
                if($row['Extra'] == 'auto_increment')
                {
                    $definition['auto_increment'] = true;
                }
            }
            
            return $definition;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }
    
    /**
     * @param array $pDefinition
     * @param string $pModel
     * @param ModuleInterface $pModule
     * @param boolean $pCopy
     * @param integer $pVersion
     */
    protected function generateModel(array $pDefinition, $pModel, $pModule, $pCopy, $pVersion)
    {
        $directory = $pModule->getPath() . '/Model/';
        $file = $directory . $pModel . '.php';
        $created = false;
        
        if(!file_exists($file))
        {
            $created = true;
            
            $skeleton = file_get_contents(__DIR__ . '/../resources/model-skeleton.php');
            $skeleton = str_replace('{NAMESPACE}', $pModule->getNamespace(), $skeleton);
            $skeleton = str_replace('{MODEL}', $pModel, $skeleton);
            
            file_put_contents($file, $skeleton);
        }
        
        $class = new \ReflectionClass($pModule->getNamespace() . '\Model\\' . $pModel);
        $allLines = file($class->getFileName(), FILE_IGNORE_NEW_LINES);

        $classStart = $class->getStartLine();
        $classEnd = $class->getEndLine();
        $classLines = array_slice($allLines, $classStart - 1, $classEnd - $classStart + 1);
        $classContent = implode("\n", $classLines);

        /************ COLUMNS ************/
        
        $method = $class->getMethod('defineColumns');
        
        if($method->getDeclaringClass()->getName() !== $class->getName())
        {
            if($created)
            {
                unlink($file);
            }
            
            return false;
        }
        
        $methodStart = $method->getStartLine();
        $methodEnd = $method->getEndLine();
        $methodLines = array_slice($allLines, $methodStart - 1, $methodEnd - $methodStart + 1);
        $methodeContent = implode("\n", $methodLines);
        
        foreach($pDefinition['columns'] as $column)
        {
            if(!preg_match('/\$this->' . $column . '\s*=/', $methodeContent))
            {
                array_splice($allLines, $methodEnd - 1, 0, sprintf(str_pad(' ', 8) . '$this->%s = null;', $column));
            }
        }
        
        /************ CONFIGURATION ************/
        
        $configuration = array(
            '_autoIncrement' => $pDefinition['auto_increment'] ? 'true' : 'false',
            '_primaryKey' => is_array($pDefinition['primary']) ? 'array(\'' . implode('\', \'', $pDefinition['primary']) . '\')' : '\'' . $pDefinition['primary'] . '\'',
            '_table' => '\'' .$pDefinition['table'] . '\''
        );
        
        $method = $class->getMethod('__construct');

        if($method->getDeclaringClass()->getName() == $class->getName())
        {
            $methodStart = $method->getStartLine();
            $methodEnd = $method->getEndLine();
            $methodLines = array_slice($allLines, $methodStart - 1, $methodEnd - $methodStart + 1);
            $methodeContent = implode("\n", $methodLines);

            foreach($configuration as $key => $value)
            {
                if(preg_match('/\$this->' . $key . '\s*=/', $methodeContent))
                {
                    $i = 0;
                    $matched = false;

                    foreach($methodLines as $line)
                    {
                        preg_replace_callback(
                            '/.*\$this->' . $key . '\s*=\s*((?:\'|")?[a-zA-Z_0-9]*(?:\'|")?)\s*;/', 
                            function($pMatches) use($i, $value, &$allLines, $methodStart, &$matched)
                            {
                                $matched = true;
                                $replace = str_replace($pMatches[1], $value, $pMatches[0]);
                                array_splice($allLines, $methodStart - 1 + $i, 1, $replace);

                                return $replace;
                            },
                            $line
                        );
                        
                        if($matched)
                        {
                            break;
                        }

                        ++$i;
                    }
                }
                else
                {
                    $value = str_pad(' ', 8) . '$this->' . $key . ' = ' . $value . ';';
                    
                    if(substr($methodLines[0], -1) == '}')
                    {
                        array_splice($methodLines, 0, 0, $value);
                        array_splice($allLines, $methodStart, 0, $value);
                    }
                    else
                    {
                        array_splice($methodLines, 1, 0, $value);
                        array_splice($allLines, $methodStart + 1, 0, $value);
                    }
                }
            }
        }
        else
        {
            foreach($configuration as $key => $value)
            {
                if(preg_match('/protected\s+\$' . $key . '/', $classContent))
                {
                    $i = 0;
                    $matched = false;
                    
                    foreach($classLines as $line)
                    {
                        preg_replace_callback(
                            '/.*protected\s+\$' . $key . '\s*=\s*((?:\'|")?[a-zA-Z_0-9]*(?:\'|")?)\s*;/', 
                            function($pMatches) use($i, $value, &$allLines, $classStart, &$matched)
                            {
                                $matched = true;
                                $replace = str_replace($pMatches[1], $value, $pMatches[0]);
                                array_splice($allLines, $classStart - 1 + $i, 1, $replace);

                                return $replace;
                            },
                            $line
                        );
                            
                        if($matched)
                        {
                            break;
                        }

                        ++$i;
                    }
                }
                else
                {
                    $value = str_pad(' ', 4) . 'protected $' . $key . ' = ' . $value . ';';
                    
                    if(substr($allLines[$classStart - 1], -1) == '}')
                    {
                        array_splice($classLines, 0, 0, $value);
                        array_splice($allLines, $classStart, 0, $value);
                    }
                    else
                    {
                        array_splice($classLines, 1, 0, $value);
                        array_splice($allLines, $classStart + 1, 0, $value);
                    }
                }
            }
        }
        
        /************ SAVE MODEL ************/
        
        if($pCopy && !$created)
        {
            File::copy($file, $directory . '_backup-' . $pVersion . '/' . $pModel . '.php');
        }

        file_put_contents($file, implode("\n", $allLines));
        return true;
    }
}