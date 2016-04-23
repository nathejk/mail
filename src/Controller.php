<?php
namespace Nathejk\Mail;

use Symfony\Component\HttpFoundation\Request;

class Controller
{
    public function indexAction(Application $app, Request $request)
    {
        $text = file_get_contents(__DIR__ . '/../README.md');
        return \Michelf\MarkdownExtra::defaultTransform($text);
    }
}
