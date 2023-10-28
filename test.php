<?php
declare(strict_types = 1);

namespace Ben\Ancestor;

use DateInterval;
use DateTimeInterface;
use DateTimeZone;

interface UserInterface {

}

interface AdminInterface extends UserInterface {

}

class User implements UserInterface {
    public function check(AdminInterface $user)
    {

    }
}

class Admin implements AdminInterface {

}

$admin = new Admin();
$user = new User();
$user->check($admin);

class HuyTime implements \DateTimeInterface {

    public function diff(DateTimeInterface $targetObject, bool $absolute = false): DateInterval
    {
        // TODO: Implement diff() method.
    }

    public function format(string $format): string
    {
        // TODO: Implement format() method.
    }

    public function getOffset(): int
    {
        // TODO: Implement getOffset() method.
    }

    public function getTimestamp()
    {
        // TODO: Implement getTimestamp() method.
    }

    public function getTimezone(): DateTimeZone|false
    {
        // TODO: Implement getTimezone() method.
    }

    public function __wakeup(): void
    {
        // TODO: Implement __wakeup() method.
    }
}

