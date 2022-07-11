<?php

namespace App\Events;

/**
 * This class defines the names of all the events dispatched in
 * our project. It's not mandatory to create a
 * class like this, but it's considered a good practice.
 *
 */
final class RegistrationEvents
{
    /**
     * For the event naming conventions, see:
     * https://symfony.com/doc/current/components/event_dispatcher.html#naming-conventions.
     *
     * @Event("Symfony\Component\EventDispatcher\GenericEvent")
     *
     * @var string
     */
    const USER_REGISTERED = 'user.registered';

    /**
     * @var string
     */
    const USER_REGISTERED_NEEDS_VALIDATION = 'user.registered.needs_validation';

    /**
     * @var string
     */
    const BAD_VALIDATION_TOKEN = 'bad.validation_token';

    /**
     * @var string
     */
    const USER_ACOUNT_VALIDATED = 'user.acount_validated';
}
