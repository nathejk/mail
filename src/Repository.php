<?php
namespace Nathejk\Mail;

class Repository
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function save(\stdClass $message)
    {
        $record = $message;
        $record->recipient = json_encode($record->recipients);
        unset($record->recipients);
        $fields = implode(', ', array_keys((array)$record));
        $values = ':' . implode(', :', array_keys((array)$record));
        $query = $this->app['db']->prepare("INSERT INTO message ($fields) VALUES ($values)");
        return $query->execute((array)$record);
    }
}
