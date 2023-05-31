---
title: Exploring Async PHP
date: "2023-05-31T18:00:00.000Z"
---

Asynchronous programming is a foundational building block for scaling web applications due to the increasing need to do more in each web request. A typical example of this is sending an email as part of a request. 

In many web applications, when something is processed on the server we want to notify people via email and it's common for this to be a separate HTTP request to a third-party service such as SendGrid, Mailchimp etc.

This becomes a more than trivial example when you need to send a lot of emails at once. In PHP, if you want to send an email and the HTTP process takes 100ms to complete, you'd quickly increase the total time for the request by sending tens or hundreds of emails. 

Of course, any good third-party email service would provide a bulk endpoint to negate this, but for the sake of the example - let's say you want to send 100 emails and each has to be processed individually.

So, we need to make a decision: **how can we move the processing of the emails into a separate process so that it doesn't block the original web request?**
That is what we'll explore in this post, particularly all the different ways this can be solved in PHP with or without new infrastructure.

## Using exec()

[exec()](https://www.php.net/manual/en/function.exec.php) is a native function in PHP that can be used to execute an external program and returns the result. In our case, it could be a script that sends emails. This function uses the operating system to spawn a completely new (blank, nothing copied or shared) process and you can pass any state you need to it.

Let's take a look at an example.

```php
<?php
// handle a web request

// record the start time of the web request
$start = microtime(true);
$path = __DIR__ . '/send_email.php';

// output to /dev/null & so we don't block to wait for the result
$command = 'php ' . $path . ' --email=%s > /dev/null &';
$emails = ['joe@blogs.com', 'jack@test.com'];

// for each of the emails, call exec to start a new script
foreach ($emails as $email) {
    // Execute the command
    exec(sprintf($command, $email));
}

// record the finish time of the web request
$finish = microtime(true);
$duration = round($finish - $start, 4);

// output duration of web request
echo "finished web request in $duration\n";
```

**send_email.php**
```php
<?php

$email = explode('--email=', $argv[1])[1];
// this blocking sleep won't affect the web request duration
// (illustrative purposes only)
sleep(5);

// here we can send the email
echo "sending email to $email\n";
```

**Output**

`$ php src/exec.php`

```bash
finished web request in 0.0184
```

The above scripts show the web request still finishes in milliseconds, even though there is a blocking `sleep` function call in the send_email.php script.

The reason it doesn't block is because we've told `exec` with the inclusion of `> /dev/null &` in the command that we don't want to wait for `exec` command to finish so we can get the result, meaning it can happen in the background and the web request can continue.

In this way, the web request script is simply responsible for running the script, not for monitoring its execution and/or failure. 

This is an inherent downside of this solution, as the monitoring of the process falls to the process itself and it cannot be restarted. However, this is an easy way to get asynchronous behaviour into a PHP application without much effort.

`exec` runs a command on a server so you have to be careful about how the script is executed, particularly if it involves user input. It can be hard to manage using `exec` particularly as you manage scaling the application, as the script is likely running on the exact same box that is processing external web requests, so you could end up exhausing CPU and memory if many hundreds or thousands of new processes are spawned via `exec`.

### pcntl_fork

[pcntl_fork](https://www.php.net/manual/en/function.pcntl-fork.php) is a low-level function which requires PCNTL extension to be enabled and is a powerful, yet potentially error prone method for writing asynchronous code in PHP.

`pcntl_fork` will fork or clone the current process and split it into a parent and a number of child processes (depending on how many times it is called). By detecting the Process ID or PID we can run different code when in the context of a parent process or a child process.

The parent process will be responsibile for spawning child processes and waiting until the spawned processes have completed before it can complete.

In this case, we can have more control over how the processes exit and can easily write some logic to handle retries in case of failure in the child process.

Now, on to the example code for our use case to send emails in a non-blocking way.

```php
<?php

function sendEmail($to, $subject, $message)
{
    // Code to send email (replace with your email sending logic)
    // This is just a mock implementation for demonstration purposes
    sleep(3); // Simulating sending email by sleeping for 3 seconds
    echo "Email sent to: $to\n";
}

$emails = [
    [
        'to' => 'john@example.com',
        'subject' => 'Hello John',
        'message' => 'This is a test email for John.',
    ],
    [
        'to' => 'jane@example.com',
        'subject' => 'Hello Jane',
        'message' => 'This is a test email for Jane.',
    ],
    // Add more email entries as needed
];

$children = [];

foreach ($emails as $email) {
    $pid = pcntl_fork();

    if ($pid == -1) {
        // Fork failed
        die('Error: Unable to fork process.');
    } elseif ($pid == 0) {
        // Child process
        sendEmail($email['to'], $email['subject'], $email['message']);
        exit(); // Exit the child process
    } else {
        // Parent process
        $children[] = $pid;
    }
}

echo "running some other things in parent process\n";
sleep(3);

// Parent process waits for each child process to finish
foreach ($children as $pid) {
    pcntl_waitpid($pid, $status);
    $status = pcntl_wexitstatus($status);
    echo "Child process $pid exited with status: $status\n";
}

echo 'All emails sent.';
```

In the above example using `pcntl_fork` we can fork the current process, which copies the parent process into new child processes and wait for the execution to complete. Additionally, after forking the child processses to send emails, the parent process can continue doing other things, before ultimately ensuring the child processes have finished.

This is a step above using `exec` where we were pretty limited in what is possible because the scripts are completely separate contexts so monitoring is not possible from an overall perspective.

We also gain process isolation as each child process runs in a separate memory space and does not affect other processes.
By tracking the process IDs we can effectively monitor and manage execution flow.

A downside in forking requests in this way, directly from the web request (parent process) is that by waiting for the child processes to finish, there's no benefit to the response time of the original request in doing it this way.

Fortunately, there is a solution to this and it's to combine both `exec` and `pcntl_fork` to get the best of both worlds, which looks like this:

1. Web request uses exec() to spawn a new PHP process
2. The spawned process is passed a list of emails as a batch
3. The spawned process becomes the parent as it forks to send each email individually

This can all happen in the background, rather than blocking the original request.

Let's take a look at making this work:

```php
<?php

$start = microtime(true);
$path = __DIR__ . '/pcntl_fork_send_email.php';
$emails = implode(',', ['joe@blogs.com', 'jack@test.com']);
$command = 'php ' . $path . ' --emails=%s > /dev/null &';

// Execute the command
echo "running exec\n";
exec(sprintf($command, $emails));
$finish = microtime(true);

$duration = round($finish - $start, 4);
echo "finished web request in $duration\n";
```

**pctnl_fork_send_email.php**

```php
<?php

$param = explode('--emails=', $argv[1])[1];
$emails = explode(',', $param);

function sendEmail($to)
{
    sleep(3); // Simulating sending email by sleeping for 3 seconds
    echo "Email sent to: $to\n";
}

$children = [];

foreach ($emails as $email) {
    $pid = pcntl_fork();

    if ($pid == -1) {
        // Fork failed
        die('Error: Unable to fork process.');
    } elseif ($pid == 0) {
        // Child process
        sendEmail($email);
        exit(); // Exit the child process
    } else {
        // Parent process
        $children[] = $pid;
    }
}

echo "running some other things in parent process\n";
sleep(3);

// Parent process waits for each child process to finish
foreach ($children as $pid) {
    pcntl_waitpid($pid, $status);
    $status = pcntl_wexitstatus($status);
    echo "Child process $pid exited with status: $status\n";
}

echo "All emails sent.\n";
```

The beauty of this solution, albeit more complicated, is that you can set up a separate process all together whose responsibility it is to run and monitor forked processes for the purpose of doing work asynchronously.

## AMPHP

[amphp](https://amphp.org/) (Asynchronous Multi-tasking PHP) is a collection of libraries that allow you to build fast, concurrent applications with PHP.

The release of PHP 8.1 in November 2021 shipped support for [Fibers](https://www.php.net/releases/8.1/en.php#fibers) which implement a lightweight cooperative concurrency model. 

Now we know a little bit about how `amphp` works and why it's exciting for the future of PHP programs, let's take look at an example:

```php
<?php

require __DIR__ . '/../vendor/autoload.php'; // Include the autoload file for the amphp/amp library

use function Amp\delay;
use function Amp\async;

function sendEmail($to, $subject, $message)
{
    delay(3000)->onResolve(function () use ($to) {
        echo "Email sent to: $to\n";
    });
}

$emails = [
    [
        'to' => 'john@example.com',
        'subject' => 'Hello John',
        'message' => 'This is a test email for John.',
    ],
    [
        'to' => 'jane@example.com',
        'subject' => 'Hello Jane',
        'message' => 'This is a test email for Jane.',
    ],
    // Add more email entries as needed
];

foreach ($emails as $email) {
    $future = async(static function () use ($email) {
        $to = $email['to'];
        $subject = $email['subject'];
        $message = $email['message'];
        sendEmail($to, $subject, $message);
    });

    // block current process by running $future->await();
}

echo "All emails sent.\n";
```

The above script is a very simple version of running things asynchronously. It will create a new fiber asynchronously using the given closure, returning a Future (object).

This is a much simpler version than rolling your own and does the heavy lifting for you, which is key for building an application as you don't need to worry about how the work is queued internally - you just know it happens asynchronously.

## Queues and Workers

A solution to this problem also exists outside of PHP and prior to PHP 8.1 it could be considered the gold standard because it's language independent and highly scalable.

The use of queues such as [Amazon SQS](https://aws.amazon.com/sqs/), [RabbitMQ](https://www.rabbitmq.com/) or [Apache Kafka](https://kafka.apache.org/) has been a widely accepted solution for some time.

Queues are pieces of infrastructure to run workers indepdenent to your application for the processing of any work asynchronously. This is not without risk or downside either, but tried and tested over time.

Let's get into an example:

Sender, in this example, is typically your existsing web application.

**sender.php**
```php
<?php

require 'vendor/autoload.php';

use Aws\Sqs\SqsClient;

// Initialize the SQS client
$client = new SqsClient([
    'region' => 'us-east-1',
    'version' => 'latest',
    'credentials' => [
        'key' => 'YOUR_AWS_ACCESS_KEY',
        'secret' => 'YOUR_AWS_SECRET_ACCESS_KEY',
    ],
]);

// Define the message details
$message = [
    'to' => 'john@example.com',
    'subject' => 'Hello John',
    'message' => 'This is a test email for John.',
];

// Send the message to SQS
$result = $client->sendMessage([
    'QueueUrl' => 'YOUR_SQS_QUEUE_URL',
    'MessageBody' => json_encode($message),
]);

echo "Message sent to SQS with MessageId: " . $result['MessageId'] . "\n";
```

Workers are an additional deployment of running code to process jobs.

**worker.php**

```php
<?php

require 'vendor/autoload.php';

use Aws\Sqs\SqsClient;

// Initialize the SQS client
$client = new SqsClient([
    'region' => 'us-east-1',
    'version' => 'latest',
    'credentials' => [
        'key' => 'YOUR_AWS_ACCESS_KEY',
        'secret' => 'YOUR_AWS_SECRET_ACCESS_KEY',
    ],
]);

// Receive and process messages from SQS
while (true) {
    $result = $client->receiveMessage([
        'QueueUrl' => 'YOUR_SQS_QUEUE_URL',
        'MaxNumberOfMessages' => 1,
        'WaitTimeSeconds' => 20,
    ]);

    if (!empty($result['Messages'])) {
        foreach ($result['Messages'] as $message) {
            $body = json_decode($message['Body'], true);

            // Process the message (send email in this case)
            sendEmail($body['to'], $body['subject'], $body['message']);

            // Delete the message from SQS
            $client->deleteMessage([
                'QueueUrl' => 'YOUR_SQS_QUEUE_URL',
                'ReceiptHandle' => $message['ReceiptHandle'],
            ]);
        }
    }
}

function sendEmail($to, $subject, $message)
{
    sleep(3); // Simulating sending email by sleeping for 3 seconds
    echo "Email sent to: $to\n";
}
```

This solution is comprised of two parts:
- Sender (pushes a message to an SQS queue)
- Worker (receives a message from a queue and sends an email)

It can be scaled through increasing the number of workers relative to the number of messages that get sent by any number of senders.

By using a queue, the worker is completely independent from the sender and can be written in any language as the communication between sender and worker is through JSON messages.

## Which solution is best?

It's almost impossible to say out of all of the solutions we've explored above, which would be the best for your application because although they all aim at solving the problem of running asynchronous code with PHP the implementations are quite different and have different benefits and drawbacks.

To summarise each option in a few points:
#### exec()
- Perhaps the simplest and most effective way to run PHP scripts async
- Fraught with potential security implications particularly around user input
- Nothing is shared can be both a blessing and a curse
- May cause increase in existing server resources (CPU/Memory)

#### pcntl_fork()
- Allows management of parent/child processes to customise behaviour
- Can be abstracted away in a simpler API for your application
- Cloning the current process may cause other downstream issues

#### AMPHP
- Requires PHP 8.1 for the user of Fibers
- Library has abstracted away the "hard parts" of running async code
- Steep learning curve over other more traditional methods (understanding event loop and multi-tasking in PHP)

#### Queues and Workers
- Language independent, flexible for any use case
- Introduces a distributed system (can be a good or bad thing in the long run)
- Many solutions around and different queue providers to make it easy

## Conclusion
The main reason I wanted to dive a bit deeper into all the different possibilities of async code in PHP is to understand how (if at all) the introduction of Fibers in PHP 8.1 changes how we can write async programs in the future.

There are many solutions available without requiring PHP 8.1 that have been battle tested, but it's interesting to see the direction the PHP language is going in to compete with the likes of [Golang](https://go.dev/) and [Elixir](https://elixir-lang.org/), both of which support async programming and have done for years.

Ultimately, I would probably still reach for a Queue/Worker approach given the scalability and cross-platform/cross-language support - however I think over time we might see libraries such as `AMPHP` become more feature rich and make this problem easier to solve without introducing new infrastructure.

To see the code samples used in this blog post, you can find them on [GitHub](https://github.com/jackmarchant/async-php/tree/main/src).