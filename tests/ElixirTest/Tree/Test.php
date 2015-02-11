<?php

namespace ElixirTest\Tree;

use Elixir\Tree\Tree as Item;
use Elixir\Tree\Collection;
use Elixir\ClassLoader\PSR4;

class Test extends \PHPUnit_Framework_TestCase
{
    protected $_loader;
    
    public function __construct()
    {
        require_once __DIR__ . '/../../../src/framework/Elixir/ClassLoader/PSR4.php';
        
        $this->_loader = new PSR4();
        $this->_loader->register();
    }
    
    public function testParameters()
    {
        $params = ['id' => 1,
                        'name' => 'test',
                        'url' => 'http://www.example.com'];
        
        $tree = new Item();
        $tree->setParameter('test', $params);
        
        $param = $tree->getParameter('test');
        
        $this->assertEquals($params['url'], $param['url']);
    }
    
    public function testFind()
    {
        $id = 1;
        
        $tree = new Item();
        $tree->setParameter('id', $id);
        $tree->setParameter('name', 'tree' . $id);
        
        for($i = 0; $i < 2; ++$i)
        {
            $id++;
            
            $tree1 = new Item();
            $tree1->setParameter('id', $id);
            $tree1->setParameter('reference', 'ref');
            $tree1->setParameter('name', 'tree' . $id);
            
            for($j = 0; $j < 2; ++$j)
            {
                $id++;
                
                $tree2 = new Item();
                $tree2->setParameter('id', $id);
                $tree2->setParameter('name', 'tree' . $id);
                
                for($k = 0; $k < 2; ++$k)
                {
                    $id++;
                    
                    $tree3 = new Item();
                    $tree3->setParameter('id', 123);
                    $tree3->setParameter('name', 'tree123');
                    
                    for($l = 0; $l < 2; ++$l)
                    {
                        $id++;
                        
                        $tree4 = new Item();
                        $tree4->setParameter('id', $id);
                        $tree4->setParameter('reference', 'ref');
                        $tree4->setParameter('name', 'tree' . $id);
                        
                        $tree3->addChild($tree4);
                    }
                    
                    $tree2->addChild($tree3);
                }
                
                $tree1->addChild($tree2);
            }
            
            $tree->addChild($tree1);
            
            if($i == 0)
            {
                $this->assertEquals($tree1, $tree->find(['id' => 2]));
            }
        }
        
        $this->assertInstanceOf('Elixir\Tree\Tree', $tree->find(['reference' => 'ref']));
        $this->assertCount(2, $tree->find(['reference' => 'ref'], 1, true));
        $this->assertCount(2, $tree->find(['child_level' => 1], -1, true));
        $this->assertCount(18, $tree->find(['reference' => 'ref'], -1, true));
        $this->assertEquals($tree, $tree->find());
    }
}
