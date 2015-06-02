# batches
A lightweight php lib for creating batch jobs and integrating them easily in php applications.

1. You have to create your own Job classes. They only need to implement the Job interface from this repository.
2. Instantiate a Batch object with a unique name and the directory where the run files has to be saved.
3. Set the exectionPlan by method.
4. Instantiate the Job(s) you need and add them to the Batch object.
5. Call the run method from the Batch object.