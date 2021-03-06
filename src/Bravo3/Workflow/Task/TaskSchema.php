<?php
namespace Bravo3\Workflow\Task;

use Bravo3\Workflow\Exceptions\UnexpectedValueException;

class TaskSchema
{
    /**
     * @var string
     */
    protected $activity_name;

    /**
     * @var string
     */
    protected $activity_version;

    /**
     * @var array
     */
    protected $requires = [];

    /**
     * @var int
     */
    protected $retry = 0;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $input;

    /**
     * @var string
     */
    protected $input_factory;

    /**
     * @var string
     */
    protected $control;

    /**
     * @var string
     */
    protected $tasklist;

    /**
     * @var int
     */
    protected $schedule_to_start_timeout;

    /**
     * @var int
     */
    protected $schedule_to_close_timeout;

    /**
     * @var int
     */
    protected $start_to_close_timeout;

    /**
     * @var int
     */
    protected $heartbeat_timeout;

    public function __construct($activity_name = null, $activity_version = null)
    {
        $this->activity_name    = $activity_name;
        $this->activity_version = $activity_version;
    }

    /**
     * Create a task schema from an array of schema properties
     *
     * @param array  $arr
     * @param string $name
     * @param string $version
     * @return TaskSchema
     */
    public static function fromArray(array $arr, $name, $version)
    {
        $vars = [
            'requires'                  => [],
            'retry'                     => 0,
            'class'                     => null,
            'input'                     => null,
            'input_factory'             => null,
            'tasklist'                  => null,
            'control'                   => null,
            'schedule_to_close_timeout' => null,
            'schedule_to_start_timeout' => null,
            'start_to_close_timeout'    => null,
            'heartbeat_timeout'         => null,
        ];

        $schema = new TaskSchema();
        $schema->setActivityName($name);
        $schema->setActivityVersion($version);

        foreach ($vars as $key => $default) {
            $fn    = self::snakeToCamel('set_'.$key);
            $value = array_key_exists($key, $arr) ? $arr[$key] : $default;

            if (is_array($default) && !is_array($value)) {
                if ($value === null) {
                    $value = [];
                } else {
                    $value = [$value];
                }
            }

            $schema->$fn($value);
        }

        return $schema;
    }


    /**
     * Extract the name and version from a task key and return it in a new TaskSchema
     *
     * @param string $key
     * @return TaskSchema
     */
    public static function fromKey($key)
    {
        $task_parts = explode('/', $key, 2);

        if (count($task_parts) != 2) {
            throw new UnexpectedValueException("Task name '".$key."' is not in the format 'task_name/version'");
        }

        return new self($task_parts[0], $task_parts[1]);
    }

    /**
     * Convert snake_case to lowerCamelCase
     *
     * @param string $val
     * @return string
     */
    private static function snakeToCamel($val)
    {
        preg_match('#^_*#', $val, $underscores);
        $underscores = current($underscores);
        $camel       = str_replace(' ', '', ucwords(str_replace('_', ' ', $val)));
        $camel       = strtolower(substr($camel, 0, 1)).substr($camel, 1);

        return $underscores.$camel;
    }

    /**
     * Get ActivityName
     *
     * @return string
     */
    public function getActivityName()
    {
        return $this->activity_name;
    }

    /**
     * Set ActivityName
     *
     * @param string $activity_name
     * @return $this
     */
    public function setActivityName($activity_name)
    {
        $this->activity_name = $activity_name;
        return $this;
    }

    /**
     * Get ActivityVersion
     *
     * @return string
     */
    public function getActivityVersion()
    {
        return $this->activity_version;
    }

    /**
     * Set ActivityVersion
     *
     * @param string $activity_version
     * @return $this
     */
    public function setActivityVersion($activity_version)
    {
        $this->activity_version = $activity_version;
        return $this;
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set Class
     *
     * @param string $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * Get Control
     *
     * @return string
     */
    public function getControl()
    {
        return $this->control;
    }

    /**
     * Set Control
     *
     * @param string $control
     * @return $this
     */
    public function setControl($control)
    {
        $this->control = $control;
        return $this;
    }

    /**
     * Get HeartbeatTimeout
     *
     * @return int
     */
    public function getHeartbeatTimeout()
    {
        return $this->heartbeat_timeout;
    }

    /**
     * Set HeartbeatTimeout
     *
     * @param int $heartbeat_timeout
     * @return $this
     */
    public function setHeartbeatTimeout($heartbeat_timeout)
    {
        $this->heartbeat_timeout = $heartbeat_timeout;
        return $this;
    }

    /**
     * Get Input
     *
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set Input
     *
     * @param string $input
     * @return $this
     */
    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * Get the list of requirements
     *
     * @return array
     */
    public function getRequires()
    {
        return $this->requires;
    }

    /**
     * Set the list of requirements
     *
     * @param array $requires
     * @return $this
     */
    public function setRequires(array $requires)
    {
        $this->requires = $requires;
        return $this;
    }

    /**
     * Get the retry limit for failed tasks
     *
     * @return int
     */
    public function getRetry()
    {
        return $this->retry;
    }

    /**
     * Set the retry limit for failed tasks
     *
     * @param int $retry
     * @return $this
     */
    public function setRetry($retry)
    {
        $this->retry = $retry;
        return $this;
    }

    /**
     * Get ScheduleToCloseTimeout
     *
     * @return int
     */
    public function getScheduleToCloseTimeout()
    {
        return $this->schedule_to_close_timeout;
    }

    /**
     * Set ScheduleToCloseTimeout
     *
     * @param int $schedule_to_close_timeout
     * @return $this
     */
    public function setScheduleToCloseTimeout($schedule_to_close_timeout)
    {
        $this->schedule_to_close_timeout = $schedule_to_close_timeout;
        return $this;
    }

    /**
     * Get ScheduleToStartTimeout
     *
     * @return int
     */
    public function getScheduleToStartTimeout()
    {
        return $this->schedule_to_start_timeout;
    }

    /**
     * Set ScheduleToStartTimeout
     *
     * @param int $schedule_to_start_timeout
     * @return $this
     */
    public function setScheduleToStartTimeout($schedule_to_start_timeout)
    {
        $this->schedule_to_start_timeout = $schedule_to_start_timeout;
        return $this;
    }

    /**
     * Get StartToCloseTimeout
     *
     * @return int
     */
    public function getStartToCloseTimeout()
    {
        return $this->start_to_close_timeout;
    }

    /**
     * Set StartToCloseTimeout
     *
     * @param int $start_to_close_timeout
     * @return $this
     */
    public function setStartToCloseTimeout($start_to_close_timeout)
    {
        $this->start_to_close_timeout = $start_to_close_timeout;
        return $this;
    }

    /**
     * Get Tasklist
     *
     * @return string
     */
    public function getTasklist()
    {
        return $this->tasklist;
    }

    /**
     * Set Tasklist
     *
     * @param string $tasklist
     * @return $this
     */
    public function setTasklist($tasklist)
    {
        $this->tasklist = $tasklist;
        return $this;
    }

    /**
     * Get InputFactory
     *
     * @return string
     */
    public function getInputFactory()
    {
        return $this->input_factory;
    }

    /**
     * Set InputFactory
     *
     * @param string $input_factory
     * @return $this
     */
    public function setInputFactory($input_factory)
    {
        $this->input_factory = $input_factory;
        return $this;
    }
}
