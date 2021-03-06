<?php
namespace Bravo3\Workflow\Services;

use Bravo3\Workflow\Enum\Event;
use Bravo3\Workflow\Enum\WorkflowResult;
use Bravo3\Workflow\Events\DecisionEvent;
use Bravo3\Workflow\Memory\JailedMemoryPool;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * A service that handles workflow decisions
 */
class Decider extends WorkflowService implements EventSubscriberInterface
{
    const MSG_WF_FAILED = 'Workflow failed';

    public static function getSubscribedEvents()
    {
        return [Event::TASK_DECISION_READY => 'processDecisionEvent'];
    }

    /**
     * @var bool
     */
    protected $fail_on_activity_failure = true;

    /**
     * Check if the entire workflow should fail if an activity fails
     *
     * @return boolean
     */
    public function getFailOnActivityFailure()
    {
        return $this->fail_on_activity_failure;
    }

    /**
     * Define if the entire workflow should fail if an activity fails
     *
     * @param boolean $fail_on_activity_failure
     * @return $this
     */
    public function setFailOnActivityFailure($fail_on_activity_failure)
    {
        $this->fail_on_activity_failure = $fail_on_activity_failure;
        return $this;
    }

    /**
     * Process the decision event, generating a decision response (included in the DecisionEvent)
     *
     * @param DecisionEvent $event
     */
    public function processDecisionEvent(DecisionEvent $event)
    {
        $history  = $event->getHistory();
        $decision = $event->getDecision();
        $tasks    = $this->getWorkflow()->getTasks();

        // Check if we need to fail
        if (($this->getFailOnActivityFailure() && $history->hasActivityFailure()) || $history->hasWorkflowFailed()) {
            $decision->setWorkflowResult(WorkflowResult::FAIL());
            $decision->setReason(implode(", ", $history->getErrorMessages()));
            return;
        }

        // Create a memory pool jailed to this execution
        $memory_pool = $this->getWorkflow()->getJailMemoryPool() ?
            JailedMemoryPool::jail($this->getMemoryPool(), ':'.$event->getExecutionId()) :
            $this->getMemoryPool();

        // Check if we need to schedule
        $parser    = new InputParser($history, $memory_pool);
        $scheduler = new Scheduler($history, $memory_pool);
        foreach ($tasks as $task) {
            if ($scheduler->canScheduleTask($task)) {
                $decision->scheduledTask($parser->compileTaskInput($task));
            }
        }

        // Check if we need to complete
        if (count($decision->getScheduledTasks()) == 0 && !$scheduler->haveOpenActivities()) {
            $decision->setWorkflowResult(WorkflowResult::COMPLETE());
        }
    }
}
