<?php

namespace Isatech\PostType\Item;

interface ItemInterface 
{
    public function getName();
    public function setPostId($pValue);
    public function getPostId();
    public function render();
    public function save($pPostId);
    public function isRequired();
    public function isEmpty();
    public function getValue();
    public function getErrors();
}