<?php
namespace Nathejk\Mail;

class Application extends \Silex\Application
{
    public function boot()
    {
        $this['time'] = function() {return time();};
        //$this['console.input'] = new \Symfony\Component\Console\Input\ArgvInput();
        $this['console.output'] = new \Symfony\Component\Console\Output\ConsoleOutput();

        $this->registerRoutes();
        $this->registerServices();
        parent::boot();
    }

    protected function registerRoutes()
    {
        $this->get('/', Controller::class . '::indexAction');
    }

    protected function registerServices()
    {
        $this['message.repo'] = $this->share(function ($app) { return new Repository($app); });

        $this->register(new \JsonSchemaValidation\SilexServiceProvider, ['jsonschemas' => [
            'message' => __DIR__ . '/../schema.json',
        ]]);

        $this->register(new MessageQueueServiceProvider, ['mq.dsn' => getenv('MQ_DSN')]);

        $dsn = parse_url(getenv('DB_DSN'));
        if (isset($dsn['scheme'])) {
            $this->register(
                new \Silex\Provider\DoctrineServiceProvider(),
                ['dbs.options' => [
                    'default' => [
                        'driver'        => 'pdo_' . $dsn['scheme'],
                        'host'          => $dsn['host'],
                        'dbname'        => substr($dsn['path'], 1),
                        'user'          => $dsn['user'],
                        'password'      => $dsn['pass'],
                        'charset'       => 'utf8',
                        // Do not silently truncate strings/numbers that are too big.
                        'driverOptions' => [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode = "STRICT_ALL_TABLES"'],
                    ],
                ]]
            );
        }

        $this['smtp.dsn'] = parse_url(getenv('SMTP_DSN'));
        $this['mail'] = $this->share(function($app) { return new MailService($app); });
        $this['mail.default_sender'] = array('tilmeld@nathejk.dk' => 'Nathejk');

        if (isset($this['smtp.dsn']['scheme'])) {
            $this->register(new \Silex\Provider\SwiftmailerServiceProvider, ['swiftmailer.options' => [
                'host' => $this['smtp.dsn']['host'],
                'port' => $this['smtp.dsn']['port'],
                'username' => $this['smtp.dsn']['user'],
                'password' => $this['smtp.dsn']['pass'],
                'encryption' => in_array($this['smtp.dsn']['scheme'], ['ssl', 'tls']) ? $this['smtp.dsn']['scheme'] : null,
            ]]);
        }
        // Override $app['mailer'] to avoid using Swift_SpoolTransport(). Instead
        // we send the mails directly to the EsmtpTransport (SMTP), so that any
        // errors will not go unnoticed.
        $this['mailer'] = $this->share(
            function ($app) {
                $mailer = new \Swift_Mailer($app['swiftmailer.transport']);

                // Register plugin that puts style into tags for stupid mail client i.e. gmail
                $converter = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles();
                $converter->setUseInlineStylesBlock();
                $converter->setStripOriginalStyleTags();

                $mailer->registerPlugin(new \Openbuildings\Swiftmailer\CssInlinerPlugin($converter));
                return $mailer;
            }
        );
    }
}
