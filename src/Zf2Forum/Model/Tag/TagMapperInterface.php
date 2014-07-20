<?php

namespace Zf2Forum\Model\Tag;

interface TagMapperInterface
{
    /**
     * getTagById 
     * 
     * @param int $tagId 
     * @return TagInterface
     */
    public function getTagById($tagId);
    
    /**
     * getTags
     *
     * @return array of TagInterface's
     */
    public function getTags();
}