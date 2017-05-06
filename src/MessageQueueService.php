<?php
namespace Nathejk\Mail;

class MessageQueueService
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function listen()
    {
        $app = $this->app;
        $this->app['mq.client']->subscribe("mail", function ($payload) use ($app) {
            print "message received: $payload\n";
            $this->app->pingConnections();

            $message = json_decode($payload);
            try {
                $this->app['jsonschema']['message']->validate($message);
                $message->status = $app['mail']->send($message);
                $message->uts = $app['time'];
                $app['message.repo']->save($message);
                $app['console.output']->writeln(json_encode($message));
            } catch (\JsonSchemaValidation\ValidationException $e) {
                $app['console.output']->writeln($e->getViolation());
                return false;
            } catch (\Exception $e) {
                $app['console.output']->writeln($e->getMessage());
                return false;
            }
        });
        $this->app['mq.client']->wait();
    }
}
