# batches
A lightweight php lib for creating batch jobs and integrating them easily in php applications.

1. You have to create your own Job classes. They only need to implement the Job interface from this repository.
2. Instantiate a Batch object with a unique name, an interval and the directory where the run files has to be saved.
3. Instantiate the Jobs you want and add them to the Batch object.
4. Call the run method from the Batch object.