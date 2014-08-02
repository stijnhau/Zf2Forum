<?php

namespace Zf2Forum\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface,
    Zend\ServiceManager\ServiceManager,
    Zf2Forum\Model\Message\MessageInterface,
    Zf2Forum\Model\Message\MessageMapperInterface,
    Zf2Forum\Model\Thread\ThreadInterface,
    Zf2Forum\Model\Thread\ThreadMapperInterface,
    Zf2Forum\Model\Tag\TagInterface,
    Zf2Forum\Model\Tag\TagMapperInterface,
    Zf2Forum\Model\Visit\VisitInterface,
    Zf2Forum\Model\Visit\VisitMapperInterface,
    ZfcBase\EventManager\EventProvider;

class Discuss extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    
    /**
     * @var ThreadMapperInterface
     */
    protected $threadMapper;

    /**
     * @var MessageMapperInterface
     */
    protected $messageMapper;

    /**
     * @var TagMapperInterface
     */
    protected $tagMapper;

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }
    
    /**
     * Set service manager instance
     *
     * @param ServiceManager $serviceManager
     * @return Discuss
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
    
    /**
     * getLatestThreads
     *
     * @param int $limit
     * @param int $offset
     * @param int $tagId
     * @return array
     */
    public function getLatestThreads($limit = 25, $offset = 0, $tagId = false)
    {
        return $this->threadMapper->getLatestThreads($limit, $offset, $tagId);
    }

    /**
     * getMessagesByThread
     *
     * @param ThreadInterface $thread
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getMessagesByThread(ThreadInterface $thread, $limit = 25, $offset = 0)
    {
        $messages = $this->messageMapper->getMessagesByThread($thread->getThreadId(), $limit, $offset);
        $messagesRet = array();
        foreach ($messages as $message) {
            $sender = $this->getServiceManager()->get("Zf2Forum_user_mapper")->findById($message->getUserId());
            /**
             * @return \Zd2Forum\Options\ModuleOptions 
             */
            $options = $this->getServiceManager()->get('Zf2Forum\ModuleOptions');
            $funcName = "get" . $options->getUserColumn();
            $message->user = $sender->$funcName();
            $messagesRet[] = $message;
        }
        
        return $messagesRet;
    }

    /**
     * createThread
     *
     * @param TagInterface $tag
     * @param ThreadInterface $thread
     * @param MessageInterface $message
     * @return ThreadInterface
     */
    public function createThread(TagInterface $tag, ThreadInterface $thread, MessageInterface $message)
    {
        $thread->setSubject($message->getSubject());
        $thread->settag_id($tag->getTagId());
        
        $message->setPostTime(new \DateTime);
        $message->setUserId($this->getServiceManager()->get('zfcuser_auth_service')->getIdentity()->getId());
        
        $this->getEventManager()->trigger(
            __FUNCTION__,
            $message,
            array(
                'message' => $message,
                'thread'  => $thread,
            )
        );
        
        $thread = $this->threadMapper->persist($thread);
        $message = $this->messageMapper->persist($message);
        
        $this->getEventManager()->trigger(
            __FUNCTION__ . '.post',
            $message,
            array(
                'message' => $message,
                'thread'  => $thread,
            )
        );
        
        return $thread;
    }

    /**
     * updateThread
     *
     * @param ThreadInterface $thread
     * @return ThreadInterface
     */
    public function updateThread(ThreadInterface $thread)
    {
        return $this->threadMapper->persist($thread);
    }

    /**
     * createMessage
     *
     * @param MessageInterface $message
     * @return MessageInterface
     */
    public function createMessage(MessageInterface $message)
    {   
        // Set post time and persist message.
        $message->setUserId($this->getServiceManager()->get('zfcuser_auth_service')->getIdentity()->getId());
        $message->setPostTime(new \DateTime);
        
        $this->getEventManager()->trigger(
            __FUNCTION__,
            $message,
            array(
                'message' => $message,
            )
        );
        
        $message = $this->messageMapper->persist($message);
        
        $this->getEventManager()->trigger(
            __FUNCTION__ . '.post',
            $message,
            array(
                'message' => $message,
            )
        );
        return $message;
    }

    /**
     * updateMessage
     *
     * @param MessageInterface $message
     * @return MessageInterface
     */
    public function updateMessage(MessageInterface $message)
    {   
        $message->setPostTime(new \DateTime);
        return $this->messageMapper->persist($message); 
    }

    /**
     * getTagById
     *
     * @param int $tagId
     * @return TagInterface
     */
    public function getTagById($tagId)
    {
        return $this->tagMapper->getTagById($tagId);
    }
    
    /**
     * getTags
     * 
     * @return array
     */
    public function getTags()
    {
        return $this->tagMapper->getTags();
    }

    /**
     * getThreadById
     *
     * @param int $threadId
     * @return ThreadInterface
     */
    public function getThreadById($threadId)
    {
        return $this->threadMapper->getThreadById($threadId);
    }

    /**
     * getMessageById
     * 
     * @param int $messageId
     * @return MessageInterface
     */
    public function getMessageById($messageId)
    {
        return $this->messageMapper->getMessageById($messageId);
    }
    
    /**
     * getThreadMapper
     *
     * @return ThreadMapperInterface
     */
    public function getThreadMapper()
    {
        return $this->threadMapper;
    }

    /**
     * setThreadMapper
     *
     * @param ThreadMapperInterface $threadMapper
     * @return Discuss
     */
    public function setThreadMapper($threadMapper)
    {
        $this->threadMapper = $threadMapper;
        return $this;
    }

    /**
     * getMessageMapper
     *
     * @return MessageMapperInterface
     */
    public function getMessageMapper()
    {
        return $this->messageMapper;
    }

    /**
     * setMessageMapper
     *
     * @param MessageMapperInterface $messageMapper
     * @return Discuss
     */
    public function setMessageMapper($messageMapper)
    {
        $this->messageMapper = $messageMapper;
        return $this;
    }

    /**
     * Get tagMapper.
     *
     * @return tagMapper
     */
    public function getTagMapper()
    {
        return $this->tagMapper;
    }

    /**
     * Set tagMapper.
     *
     * @param TagMapperInterface $tagMapper the value to be set
     */
    public function setTagMapper(TagMapperInterface $tagMapper)
    {
        $this->tagMapper = $tagMapper;
        return $this;
    }
    
    /**
     * Set Visit Mapper
     * 
     * Enter description here ...
     * @param VisitMapperInterface $visitMapper
     */
    public function setVisitMapper(VisitMapperInterface $visitMapper)
    {
    	$this->visitMapper = $visitMapper;
    	return $this;
    }
    
    /**
     * Get Vist Mapper.
     * 
     * Enter description here ...
     */
    public function getVisitMapper()
    {
    	return $this->visitMapper;
    }
    
    /**
     * storeVisitIfUnique - records the visit if it is unuiqe.
     * @param ThreadInterface $thread
     * @return \Zf2Forum\Service\Discuss
     */
    public function storeVisitIfUnique(VisitInterface $visit)
    {
        $this->getVisitMapper()->storeVisitIfUnique($visit);
        return $this;
    }
}
