<?php

namespace Zf2Forum;

use Zend\ModuleManager\ModuleManager;

class Module
{
    protected static $options;

    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->getEventManager()->attach('loadModules.post', array($this, 'modulesLoaded'));
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Zf2Forum_post_form_hydrator'   => 'Zend\Stdlib\Hydrator\ClassMethods',
                'Zf2Forum_thread'               => 'Zf2Forum\Model\Topic\Topic',
                'Zf2Forum_message'              => 'Zf2Forum\Model\Message\Message',
                'Zf2Forum_form'                 => 'Zf2Forum\Form\PostForm',
                'Zf2Forum_replyform'            => 'Zf2Forum\Form\ReplyForm',
            ),
            'factories' => array(
                'Zf2Forum\ModuleOptions'        => 'Zf2Forum\Factory\ModuleOptionsFactory',
                'Zf2Forum_user_mapper'          => 'Zf2Forum\Factory\UserMapperFactory',

                'Zf2Forum_discuss_service' => function ($sm) {
                    $service = new \Zf2Forum\Service\Discuss;
                    $service->setTopicMapper($sm->get('Zf2Forum_topic_mapper'))
                            ->setMessageMapper($sm->get('Zf2Forum_message_mapper'))
                            ->setCategoryMapper($sm->get('Zf2Forum_category_mapper'))
                            ->setVisitMapper($sm->get('Zf2Forum_visit_mapper'));
                    return $service;
                },
                'Zf2Forum_topic_mapper' => function ($sm) {
                    $mapper = new \Zf2Forum\Model\Topic\TopicMapper;
                    $threadModelClass = Module::getOption('topic_model_class');
                    $mapper->setEntityPrototype(new $threadModelClass);
                    $mapper->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods);
                    $mapper->setServiceLocator($sm);
                    return $mapper;

                },
                'Zf2Forum_category_mapper' => function ($sm) {
                    $mapper = new \Zf2Forum\Model\Category\CategoryMapper;
                    $categoryModelClass = Module::getOption('category_model_class');
                    $mapper->setEntityPrototype(new $categoryModelClass);
                    $mapper->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods);
                    return $mapper;
                },
                'Zf2Forum_message_mapper' => function ($sm) {
                    $mapper = new \Zf2Forum\Model\Message\MessageMapper;
                    //$messageModelClass = static::getOption('message_model_class');
                    $messageModelClass = Module::getOption('message_model_class');
                    $mapper->setEntityPrototype(new $messageModelClass);
                    $mapper->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods);
                    return $mapper;
                },
                'Zf2Forum_visit_mapper' => function ($sm) {
                    $mapper = new \Zf2Forum\Model\Visit\VisitMapper;
                    $visitModelClass = Module::getOption('visit_model_class');
                    $mapper->setEntityPrototype(new $visitModelClass);
                    $mapper->setHydrator(new \Zend\StdLib\Hydrator\ClassMethods);
                    return $mapper;
                },
                'Zf2Forum_visit' => function ($sm) {
                    $visit = new \Zf2Forum\Model\Visit\Visit;
                    $visit->setIpAddress($_SERVER['REMOTE_ADDR'])
                          ->setVisitTime(new \DateTime);
                    return $visit;
                }
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof Service\DbAdapterAwareInterface) {
                        $dbAdapter = $sm->get('Zf2Forum_zend_db_adapter');
                        return $instance->setDbAdapter($dbAdapter);
                    }
                },
            ),
        );

    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'Zf2Forum\Controller\DiscussController' => 'Zf2Forum\Controller\Factory\DiscussController',
            ),
        );
    }

    public function modulesLoaded($e)
    {
        $config = $e->getConfigListener()->getMergedConfig();
        static::$options = $config['Zf2Forum'];
    }

    /**
     * @TODO: Come up with a better way of handling module settings/options
     */
    public static function getOption($option)
    {
        if (!isset(static::$options[$option])) {
            return null;
        }
        return static::$options[$option];
    }

    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'RenderForm'        => 'Zf2Forum\View\Helper\RenderForm',
                'privateSmartTime'  => 'Zf2Forum\View\Helper\SmartTime',
            )
        );

    }
}
