<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramChannelMessage extends Model
{
    private $index = 'telegram_message_channel';

    public function getIndex(): string
    {
        return $this->index;
    }
}
