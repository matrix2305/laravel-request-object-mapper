<?php
declare(strict_types=1);
namespace Matrix2305\RequestObjectMapper\Exceptions;

use Illuminate\Support\MessageBag;
use RuntimeException;

class FailedValidationException extends RuntimeException
{
    public array $messages = [];
    public $message = '';

    public function __construct(MessageBag $messageBag) {
        $this->messages = $messageBag->messages();
        $this->message = $messageBag->first();
    }
}
