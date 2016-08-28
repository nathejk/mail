<?php
namespace Nathejk\Mail;

class MailService
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function send(\stdClass $message)
    {
        $to = [];
        foreach ($message->recipients as $recipient) {
            if (empty($recipient->name)) {
                $to[] = $recipient->mail;
            } else {
                $to[$recipient->mail] = $recipient->name;
            }
        }
        $mail = \Swift_Message::newInstance()
            ->setSubject($message->subject)
            ->setFrom($this->app['mail.default_sender'])
            ->setTo($to)
            ->setBody($message->body, $message->contentType);

        // Mark mails sent from cron jobs, services etc. according to RFC 3834.
        // This tells recipient mail servers to not send out-of-office-replies etc.
        $mail->getHeaders()->addTextHeader('Auto-Submitted', 'auto-generated');

        $this->app['mailer']->send($mail, $failed);
        $this->app['mailer']->getTransport()->stop(); # quick fix to handle daemonized queues

        if (count($failed)) {
            $this->app['console.output']->writeln("some recipient failed: " . implode(', ', $failed));
            return 'fail';
        }
        return 'ok';
    }
}
