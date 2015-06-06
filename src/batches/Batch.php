<?php
namespace drieschel\batches;
/**
 * @author Immanuel Klinkenberg <klinkenberg@speicher-werk.de>
 */
class Batch
{
  /**
   * @var Job[]
   */
  protected $jobs = array();
 
  /**
   * @var string
   */
  protected $name;
  
  /**
   * @var string
   */
  protected $runFile;
  
  /**
   * @var string
   */
  protected $month = '*';
  
  /**
   * @var string
   */
  protected $day = '*';
  
  /**
   * @var string
   */
  protected $hour = '*';
  
  /**
   * @var string
   */
  protected $minute = '*';
  
  /**
   * @var string
   */
  protected $second = '*';

  /**
   * @var \DateTime
   */
  protected $executionDate;  
  
  /**
   * @var \DateTime
   */
  protected $lastRun;  
  
  /**
   * @param string $name
   * @param string $runFilesDir
   */
  function __construct($name, $runFilesDir)
  {
    $this->name = $name;
    if(empty($runFilesDir) || !is_string($runFilesDir))
    {
      throw new \Exception("The run files directory " . $runFilesDir . " has to be set correctly");
    }
    
    if(!is_dir($runFilesDir))
    {
      throw new \Exception("The run files directory " . $runFilesDir . " does not exist");
    }
    
    $this->runFile = $runFilesDir . '/batch_' . $name;    
    if(file_exists($this->runFile))
    {
      $this->lastRun = new \DateTime('@' . filemtime($this->runFile));
    }
  }
  
  /**
   * @param string $month
   * @param string $day
   * @param string $hour
   * @param string $minute
   * @param string $second
   */
  public function executionPlan($month, $day, $hour, $minute, $second)
  {
    $this->month = $month;
    $this->day = $day;
    $this->hour = $hour;
    $this->minute = $minute;
    $this->second = $second;
  }
  
  /**
   * @return void
   */
  public function run()
  {
    $this->adjustExecutionDate();
    $executionInterval = $this->calculateExecutionInterval();
    $now = new \DateTime();
    $nowSubExecutionInterval = clone $now;
    $nowSubExecutionInterval->sub($executionInterval);
    
    if(!$this->lastRun instanceof \DateTime || $nowSubExecutionInterval >= $this->lastRun || $now >= $this->executionDate && $this->lastRun < $this->executionDate)
    {
      $startTime = time();
      foreach ($this->jobs as $job)
      {
        $job->execute();
      }
      $endTime = time();
      $result = array('executed_at' => $now->format('d.m.Y H:i:s'), 'status' => 'success', 'job_amount' => count($this->jobs), 'runtime' => ($endTime - $startTime) . 's');
      file_put_contents($this->runFile, json_encode($result));
      $this->lastRun = $now;
    }
  }
  
  /**
   * @param \drieschel\batches\Job $job
   */
  public function addJob(Job $job)
  {
    if(!$this->containsJob($job))
    {
      $this->jobs[] = $job;
    }
  }
  
  /**
   * @param \drieschel\batches\Job $job
   */
  public function removeJob(Job $job)
  {
    $jobs = $this->jobs;
    $this->jobs = array_filter($jobs, function(Job $existingJob) use ($job){
      return $existingJob !== $job;
    });
  }
  
  /**
   * @param \drieschel\batches\Job $job
   * @return boolean
   */
  public function containsJob(Job $job)
  {
    foreach($this->jobs as $existingJob)
    {
      if($job === $existingJob)
      {
        return true;
      }
    }
    return false;
  }
  
  /**
   * @return Job[]
   */
  public function getJobs()
  {
    return $this->jobs;
  }
  
  /**
   * @return \DateTime
   */
  public function getLastRun()
  {
    return $this->lastRun;
  }
    
  /**
   * @param \DateTime $lastRun
   */
  public function setLastRun(\DateTime $lastRun)
  {
    $this->lastRun = $lastRun;
  }

  protected function adjustExecutionDate()
  {
    $year = date('Y');
    $month = $this->month !== '*' ? $this->month : date('m');
    $day = $this->day !== '*' ? $this->day : date('d');
    $hour = $this->hour !== '*' ? $this->hour : date('H');
    $minute = $this->minute !== '*' ? $this->minute : date('i');
    $second = $this->second !== '*' ? $this->second : date('s');
    
    $tDate = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second);
    $this->executionDate = new \DateTime($tDate);
  }
  
  /**
   * @return \DateInterval
   */
  protected function calculateExecutionInterval()
  {
    if($this->month !== '*')
    {
      $interval = 'P1Y';
    }
    else if($this->day !== '*')
    {
      $interval = 'P1M';
    }
    else if($this->hour !== '*')
    {
      $interval = 'P1D';
    }
    else if($this->minute !== '*')
    {
      $interval = 'PT1H';
    }
    else {
      $interval = 'PT1S';
    }
    return new \DateInterval($interval);
  }
}
