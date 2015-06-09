# batches
A lightweight php lib for creating scheduled batch jobs and integrating them easily in php applications. Jobs from a batch will be executed everytime when the execution time is reached or exceeded. That is a very important detail, because php is running request based and not like a daemon.

1. You have to create your own Job classes. They only need to implement the Job interface from this repository.
2. Instantiate a Batch object with a unique name and the directory where the run files has to be stored.
3. Set the executionPlan by method. Its cronjob notation like.
4. Instantiate the Job(s) you need and add them to the Batch object.
5. Call the run method from the Batch object.