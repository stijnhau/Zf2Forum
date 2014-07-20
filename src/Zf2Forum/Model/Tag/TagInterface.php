<?php

namespace Zf2Forum\Model\Tag;

interface TagInterface
{
    /**
     * Get tagId.
     *
     * @return tagId
     */
    public function getTagId();

    /**
     * Set tagId.
     *
     * @param $tagId the value to be set
     */
    public function setTagId($tagId);

    /**
     * Get name.
     *
     * @return name
     */
    public function getName();

    /**
     * Set name.
     *
     * @param $name the value to be set
     */
    public function setName($name);
    
    /**
     * Get description.
     *
     * @return description
     */
    public function getDescription();

    /**
     * Set description.
     *
     * @param $description the value to be set
     */
    public function setDescription($description);

    /**
     * Get slug.
     *
     * @return slug
     */
    public function getSlug();

    /**
     * Set slug.
     *
     * @param $slug the value to be set
     */
    public function setSlug($slug);
}