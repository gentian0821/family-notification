<?php

namespace App\UseCase;

use App\Domains\MakeNotifyMessage;
use App\Repositories\LineMessageApiRepository;
use Illuminate\Support\Facades\Config;

class NotifyUseCase
{
    public function __construct(private LineMessageApiRepository $messageRepository)
    {
    }

    public function __invoke(array $params): void
    {
        if (empty($params)) {
            return;
        }

        $makeMessage = app()->make(MakeNotifyMessage::class);
        $message = $makeMessage($params);

        $this->messageRepository->push(
            [
                [
                    'type' => 'text',
                    'text' => $message,
                ]
            ],
            Config::get('const.fayc4_send_to')
        );
    }
}
