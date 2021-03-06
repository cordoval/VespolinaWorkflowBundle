<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * (c) Daniel Kucharski <daniel@xerias.be>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * This is a Zeta Components specific implementation of Vespolina's workflow activity
 */

namespace Vespolina\WorkflowBundle\Model\EczWorkflow;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Vespolina\WorkflowBundle\Model\WorkflowActivity;
use Vespolina\WorkflowBundle\Model\WorkflowExecutionInterface;
use Vespolina\WorkflowBundle\Model\WorkflowExecution;


class WorkflowActivityInvokerServiceObject implements \ezcWorkflowServiceObject {

    protected $activityName;
    protected $eventDispatcher;
    protected $workflowActivityClass;
    protected $workflowExecution;

    public function __construct($workflowActivityClass, $activityName )
    {

        $this->container = CustomWorkflowFactory::getDIContainer(); //TODO remove static stuff
        $this->activityName = $activityName;
        $this->eventDispatcher = $this->container->get('event_dispatcher');
        $this->workflowActivityClass = $workflowActivityClass;
      
    }

    public function execute( \ezcWorkflowExecution $execution )
    {
      
        $workflowService = $this->container->get('vespolina.workflow');

        $workflowContainer = $execution->getVariable('WorkflowContainer');

        $workflowExecution = $workflowService->getWorkflowExecutionByRuntimeInstance($execution);

       //Create the workflow activity instance
        $workflowActivity = new $this->workflowActivityClass(
                                            $this->activityName,
                                            $workflowExecution,
                                            $this->eventDispatcher);

        $workflowActivity->setContainer($this->container);

        // Activate (and execute) the workflow activity
        $workflowActivity->activate();

        /** If we come here normally execution should have been finished
         * If for some reason the execution was suspended (eg. human interaction is required)
         * the "is suspended" value will have been set to true
         */

        return !$workflowActivity->getIsSuspended();
                

    }

    public function __toString()
    {
        return "WorkflowActivityInvokerServiceObject{$this->workflowActivityClass}";
    }

    public function setWorkflowExecution(WorfklowExecutionInterface $workflowExecution)
    {
        $this->workflowExecution = $workflowExecution;
    }
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
