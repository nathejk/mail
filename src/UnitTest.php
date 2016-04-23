<?php
namespace Nathejk\Mail;

class UnitTest extends \PHPUnit_Framework_TestCase
{
    protected $app;

    public function setUp()
    {
        $app = new Application(['debug' => true]);
        $app->boot();
        $app['swiftmailer.transport'] = $app->share(
            function ($app) {
                return new \Swift_Transport_NullTransport($app['swiftmailer.transport.eventdispatcher']);
            }
        );
        $this->app = $app;
    }

    public function test_recieve_message()
    {
        return;
        $message1 = [
            "body" => "hello",
            "recipients" => ["name" => "Testsen", "mail" => "test@test.test"],
            "subject" => "test",
        ];
        $this->app['time'] = 987654;

        // expext 1 mail to be send
        $this->app['mail'] = $this->getMockBuilder(MailService::class)->disableOriginalConstructor()->getMock();
        $this->app['mail']->expects($this->once())->method('send')->willReturn('ok');
        //$this->app['mail.dsn'] = 'smtp://UN:PW@localhost';

        // expect message to be saved to database
        $this->app['message.repo'] = $this->getMockBuilder(Repository::class)->disableOriginalConstructor()->getMock();
        $this->app['message.repo']->expects($this->once())->method('save')->with((object)($message1 + ["status"=>'ok', "uts"=>987654]));

        // catch output to console
        $this->app['console.output'] = $this->getMockBuilder(get_class($this->app['console.output']))->getMock();
        $this->app['console.output']->method('writeln');

        // listen for messages
        $this->app['mq.client'] = new MessageQueueConnectionMock([json_encode($message1)]);
        $this->app['mq']->listen();
    }
}
