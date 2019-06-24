<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramChannel extends Model
{
    private $index = 'telegram_peer_info';

    public function getIndex(): string
    {
        return $this->index;
    }
}
