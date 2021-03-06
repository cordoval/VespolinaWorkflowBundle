<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * (c) Daniel Kucharski <daniel@xerias.be>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\WorkflowBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Vespolina\WorkflowBundle\Event\WorkflowActivityEvent;
use Vespolina\WorkflowBundle\Model\WorkflowActivityInterface;
use Vespolina\WorkflowBundle\Model\WorkflowContainer;


class WorkflowActivity implements WorkflowActivityInterface{

    protected $container;   //DI container
    protected $dispatcher;
    protected $isExecutionFinished;
    protected $isSuspended;
    protected $name;
    protected $workflowContainer;
    protected $workflowExecution;

    public function __construct($name, $workflowExecution, $eventDispatcher)
    {
       
        $this->eventDispatcher = $eventDispatcher;
        $this->name = $name;
        $this->isExecutionFinished = false;
        $this->workflowContainer = $workflowExecution->getWorkflowContainer();
        $this->workflowExecution = $workflowExecution;
    }

    /**
     * @inheritdoc
     */
    public function complete()
    {

        $this->fireEvent('completed');
    }

    /**
     * @inheritdoc
     */
    public function init()
    {

        $this->fireEvent('init');
    }
    
    /**
     * @inheritdoc
     */
    public function activate()
    {
        $this->init();

        $this->log('activated');

        $this->execute();

        $isSuspended = $this->getIsSuspended();
        
        if( !$isSuspended )
        {
            $this->log('completed');

            //Trigger completion event listeners
            $this->complete();
        }else
        {
            $this->log('suspended');

        }

         return !$isSuspended;

    }

   /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->fireEvent('execute');
    }

    /**
     * @inheritdoc
     */
    public function getWorkflowContainer()
    {

        return $this->workflowContainer;
    }

    /**
     * @inheritdoc
     */
    public function getIsExecutionFinished()
    {

        return $this->isExecutionFinished;
    }
    /**
     * @inheritdoc
     */
    public function getIsSuspended()
    {

        return $this->isSuspended;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {

        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getWorkflowExecution()
    {

        return $this->workflowExecution;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function setIsExecutionFinished($executionIsFinished)
    {

        return $this->isExecutionFinished = $executionIsFinished;
    }

    /**
     * @inheritdoc
     */
    public function suspend()
    {

        $this->isExecutionFinished = false;
        $this->isSuspended = true;

        $this->workflowExecution->setIsSuspended(true);
    }

    
    protected function fireEvent($name)
    {

        if( $this->eventDispatcher )
        {
            $event = new WorkflowActivityEvent($this);
            $this->eventDispatcher->dispatch('vespolina.workflow.activity.' . $name, $event);
        }
    }

    protected function log($message, $type = 'E')
    {
        //For now just delegate this to the workflow execution instance

        $this->workflowExecution->logWorkflowActivityMessage($this, $this->name . ':' . $message, $type);
    }


}
