<?php

namespace Xin\AttrRoute\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;

class MissingModelException extends AuthorizationException
{
    /**
     * The model that the user model.
     *
     * @var string
     */
    protected string $model;

    /**
     * Create a new missing scope exception.
     *
     * @param  string  $model
     * @param  string  $message
     * @return void
     */
    public function __construct($model, $message = 'Invalid model provided.')
    {
        parent::__construct($message);

        $this->model = $model;
    }

    /**
     * Get the abilities that the user did not have.
     *
     * @return string
     */
    public function model(): string
    {
        return $this->model;
    }
}
